<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminInventoryController extends Controller
{
    /**
     * Halaman Utama Manajemen Inventori & Stok.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $categoryId = $request->filled('category') ? (int) $request->input('category') : null;
        $stockFilter = trim((string) $request->input('stock_status', 'semua')); // semua, low, out, healthy

        $query = Product::with('category');

        if ($search !== '') {
            $query->where('product_title', 'like', "%{$search}%");
        }

        if ($categoryId !== null) {
            $query->where('product_cat', $categoryId);
        }

        if ($stockFilter === 'low') {
            $query->where('stock', '>', 0)->where('stock', '<=', 10);
        } elseif ($stockFilter === 'out') {
            $query->where('stock', '<=', 0);
        } elseif ($stockFilter === 'healthy') {
            $query->where('stock', '>', 10);
        }

        $products = $query->orderBy('stock', 'asc')
            ->orderBy('product_id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('cat_title')->get();

        // Ringkasan Inventori
        $totalItems = (int) Product::sum('stock');
        $outOfStockCount = Product::where('stock', '<=', 0)->count();
        $lowStockCount = Product::where('stock', '>', 0)->where('stock', '<=', 10)->count();
        $healthyStockCount = Product::where('stock', '>', 10)->count();
        $totalStockValue = (float) Product::selectRaw('SUM(stock * product_price) as total_value')->value('total_value') ?? 0;

        return view('admin.inventory.index', compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'stockFilter',
            'totalItems',
            'outOfStockCount',
            'lowStockCount',
            'healthyStockCount',
            'totalStockValue'
        ));
    }

    /**
     * Riwayat Pergerakan Stok (Audit Trail).
     */
    public function history(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $type = trim((string) $request->input('type', 'semua'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = StockMovement::with(['product', 'user']);

        if ($search !== '') {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('product_title', 'like', "%{$search}%");
            });
        }

        if ($type !== 'semua' && in_array($type, [
            InventoryService::TYPE_IN,
            InventoryService::TYPE_OUT,
            InventoryService::TYPE_ADJUSTMENT,
            InventoryService::TYPE_PURCHASE,
            InventoryService::TYPE_CANCELLATION,
        ], true)) {
            $query->where('type', $type);
        }

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $movements = $query->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.history', compact(
            'movements',
            'search',
            'type',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Proses Penyesuaian Stok (Stock-In, Stock-Out, Adjustment).
     */
    public function adjust(Request $request, InventoryService $inventoryService)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,product_id',
            'action_type' => 'required|in:in,out,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $inventoryService->adjustStock(
                (int) $validated['product_id'],
                $validated['action_type'],
                (int) $validated['quantity'],
                $validated['reason']
            );

            $label = match ($validated['action_type']) {
                'in' => "Penambahan stok sebanyak +{$validated['quantity']} unit",
                'out' => "Pengurangan stok sebanyak -{$validated['quantity']} unit",
                'set' => "Penetapan stok menjadi {$validated['quantity']} unit",
            };

            return back()->with('success', "{$label} untuk '{$result['product']->product_title}' berhasil dicatat.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal menyesuaikan stok: ' . $e->getMessage());
        }
    }
}
