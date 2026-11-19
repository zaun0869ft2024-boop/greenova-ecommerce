<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class InventoryService
{
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_CANCELLATION = 'cancellation';
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * Catat pergerakan stok jika tabel tersedia.
     */
    public function recordMovement(
        int $productId,
        int $quantity,
        int $stockBefore,
        int $stockAfter,
        string $type,
        ?string $description = null,
        ?int $userId = null
    ): ?StockMovement {
        if (!Schema::hasTable('stock_movements')) {
            return null;
        }

        return StockMovement::create([
            'product_id' => $productId,
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'description' => $description,
        ]);
    }

    /**
     * Sesuaikan stok produk secara atomik (Stock-In, Stock-Out, atau Set/Adjustment).
     */
    public function adjustStock(
        int $productId,
        string $actionType, // 'in', 'out', 'set'
        int $quantity,
        string $reason,
        ?int $adminUserId = null
    ): array {
        return DB::transaction(function () use ($productId, $actionType, $quantity, $reason, $adminUserId) {
            $product = Product::where('product_id', $productId)->lockForUpdate()->firstOrFail();
            $stockBefore = (int) ($product->stock ?? 0);

            if ($actionType === 'in') {
                if ($quantity <= 0) {
                    throw new InvalidArgumentException('Jumlah penambahan stok harus lebih besar dari 0.');
                }
                $stockAfter = $stockBefore + $quantity;
                $movementType = self::TYPE_IN;
                $diff = $quantity;
            } elseif ($actionType === 'out') {
                if ($quantity <= 0) {
                    throw new InvalidArgumentException('Jumlah pengurangan stok harus lebih besar dari 0.');
                }
                if ($stockBefore < $quantity) {
                    throw new InvalidArgumentException("Stok tidak mencukupi. Stok saat ini: {$stockBefore}.");
                }
                $stockAfter = $stockBefore - $quantity;
                $movementType = self::TYPE_OUT;
                $diff = -$quantity;
            } elseif ($actionType === 'set') {
                if ($quantity < 0) {
                    throw new InvalidArgumentException('Stok tidak boleh bernilai negatif.');
                }
                $stockAfter = $quantity;
                $movementType = self::TYPE_ADJUSTMENT;
                $diff = $stockAfter - $stockBefore;
            } else {
                throw new InvalidArgumentException('Tipe aksi inventori tidak valid.');
            }

            $product->stock = $stockAfter;
            $product->save();

            $movement = $this->recordMovement(
                $productId,
                $diff,
                $stockBefore,
                $stockAfter,
                $movementType,
                $reason,
                $adminUserId ?? auth()->id()
            );

            return [
                'success' => true,
                'product' => $product,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'movement' => $movement,
            ];
        });
    }

    /**
     * Catat pengurangan stok akibat pembelian pesanan.
     */
    public function recordPurchase(Product $product, int $quantity, ?string $orderReference = null, ?int $userId = null): void
    {
        $stockBefore = (int) ($product->stock ?? 0);
        $stockAfter = max(0, $stockBefore - $quantity);

        $this->recordMovement(
            $product->product_id,
            -$quantity,
            $stockBefore,
            $stockAfter,
            self::TYPE_PURCHASE,
            "Pembelian pesanan (" . ($orderReference ?: 'Checkout') . ")",
            $userId ?? auth()->id()
        );
    }

    /**
     * Catat penambahan kembali stok akibat pembatalan pesanan.
     */
    public function recordCancellation(Product $product, int $quantity, ?string $orderReference = null, ?int $userId = null): void
    {
        $stockBefore = (int) ($product->stock ?? 0);
        $stockAfter = $stockBefore + $quantity;

        $this->recordMovement(
            $product->product_id,
            $quantity,
            $stockBefore,
            $stockAfter,
            self::TYPE_CANCELLATION,
            "Pembatalan pesanan (" . ($orderReference ?: 'Dibatalkan') . ")",
            $userId ?? auth()->id()
        );
    }
}
