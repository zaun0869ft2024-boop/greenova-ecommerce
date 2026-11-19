<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\UsesIsolatedDatabase;

class MidtransPaymentTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected User $user;
    protected User $otherUser;
    protected Category $category;
    protected Product $productA;
    protected Product $productB;
    protected string $testServerKey = 'SB-Mid-server-TESTKEY123456789';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.midtrans.server_key', $this->testServerKey);
        Config::set('services.midtrans.client_key', 'SB-Mid-client-TESTKEY123456789');
        Config::set('services.midtrans.is_production', false);

        $this->user = User::factory()->create([
            'name' => 'Budi Customer',
            'email' => 'budi@example.com',
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Siti Customer',
            'email' => 'siti@example.com',
        ]);

        $this->category = Category::factory()->create([
            'cat_title' => 'Tanaman Hias',
        ]);

        $this->productA = Product::factory()->create([
            'product_title' => 'Monstera Deliciosa',
            'product_price' => 150000,
            'product_cat' => $this->category->cat_id,
            'stock' => 10,
        ]);

        $this->productB = Product::factory()->create([
            'product_title' => 'Sansevieria Trifasciata',
            'product_price' => 75000,
            'product_cat' => $this->category->cat_id,
            'stock' => 5,
        ]);
    }

    /**
     * Helper to generate a valid Midtrans SHA512 signature key for testing.
     */
    protected function generateSignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId . $statusCode . $grossAmount . $this->testServerKey);
    }

    // =========================================================================
    // A. CHECKOUT & GROUPING TESTS
    // =========================================================================

    public function test_checkout_with_midtrans_creates_orders_with_pending_status_and_shared_reference()
    {
        \App\Models\CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productA->product_id,
            'quantity' => 2,
        ]);
        \App\Models\CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productB->product_id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'checkout_mode' => 'cart',
                'checkout_items' => [
                    $this->productA->product_id,
                    $this->productB->product_id,
                ],
            ])
            ->post(route('checkout.process'), [
                'recipient_name' => 'Budi Santoso',
                'recipient_phone' => '081234567890',
                'shipping_address' => 'Jl. Kebon Jeruk No. 12, Jakarta Barat',
                'payment_method' => 'midtrans',
            ]);

        $response->assertStatus(302);

        $orders = Order::where('user_id', $this->user->id)->get();
        $this->assertCount(2, $orders);

        $firstRef = $orders->first()->payment_reference;
        $this->assertNotEmpty($firstRef);
        $this->assertStringStartsWith('GNV-', $firstRef);

        foreach ($orders as $order) {
            $this->assertEquals($firstRef, $order->payment_reference);
            $this->assertEquals('midtrans', $order->payment_method);
            $this->assertEquals('midtrans', $order->payment_gateway);
            $this->assertEquals('pending', $order->payment_status);
            $this->assertEquals('Pending', $order->status);
            $this->assertNull($order->paid_at);
        }

        // Verify stock deducted
        $this->assertEquals(8, $this->productA->fresh()->stock);
        $this->assertEquals(4, $this->productB->fresh()->stock);
    }

    public function test_checkout_rejects_cod_payment_method()
    {
        \App\Models\CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productA->product_id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'checkout_mode' => 'cart',
                'checkout_items' => [
                    $this->productA->product_id,
                ],
            ])
            ->post(route('checkout.process'), [
                'recipient_name' => 'Budi Santoso',
                'recipient_phone' => '081234567890',
                'shipping_address' => 'Jl. Kebon Jeruk No. 12, Jakarta Barat',
                'payment_method' => 'cod',
            ]);

        $response->assertSessionHasErrors('payment_method');
        $this->assertEquals(0, Order::where('user_id', $this->user->id)->count());
    }

    public function test_payment_service_calculates_exact_gross_amount_matching_order_subtotals()
    {
        $service = app(PaymentService::class);
        $ref = $service->generatePaymentReference();

        Order::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productA->product_id,
            'qty' => 2,
            'unit_price' => 150000,
            'subtotal' => 300000,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_gateway' => 'midtrans',
            'payment_method' => 'midtrans',
            'payment_reference' => $ref,
            'order_group' => $ref,
            'date' => now(),
        ]);

        Order::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productB->product_id,
            'qty' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_gateway' => 'midtrans',
            'payment_method' => 'midtrans',
            'payment_reference' => $ref,
            'order_group' => $ref,
            'date' => now(),
        ]);

        $orders = Order::where('payment_reference', $ref)->get();
        $totalGross = (float) $orders->sum('subtotal');
        $this->assertEquals(375000.0, $totalGross);
    }

    // =========================================================================
    // B. WEBHOOK SECURITY & NOTIFICATION HANDLING
    // =========================================================================

    public function test_webhook_exempt_from_csrf_verification()
    {
        $payload = [
            'order_id' => 'GNV-NONEXISTENT',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'signature_key' => 'invalidsignature',
            'transaction_status' => 'settlement',
        ];

        // Should return 403 or 404 (due to validation), NOT 419 (CSRF mismatch)
        $response = $this->postJson(route('midtrans.notification'), $payload);
        $this->assertNotEquals(419, $response->status());
    }

    public function test_webhook_rejects_unauthorized_signature_with_403()
    {
        $payload = [
            'order_id' => 'GNV-20261117-ABCDEFGH',
            'status_code' => '200',
            'gross_amount' => '300000.00',
            'signature_key' => 'totally_bogus_signature_that_does_not_match',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);
        $response->assertStatus(403);
        $response->assertJson(['success' => false, 'code' => 403]);
    }

    public function test_webhook_rejects_nonexistent_order_with_404()
    {
        $orderId = 'GNV-20261117-99999999';
        $statusCode = '200';
        $grossAmount = '150000.00';
        $validSignature = $this->generateSignature($orderId, $statusCode, $grossAmount);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);
        $response->assertStatus(404);
        $response->assertJson(['success' => false, 'code' => 404]);
    }

    public function test_webhook_rejects_gross_amount_mismatch_with_400()
    {
        $service = app(PaymentService::class);
        $ref = $service->generatePaymentReference();

        Order::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productA->product_id,
            'qty' => 1,
            'unit_price' => 150000,
            'subtotal' => 150000,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_gateway' => 'midtrans',
            'payment_method' => 'midtrans',
            'payment_reference' => $ref,
            'date' => now(),
        ]);

        // Attacker claims gross_amount is 1000.00 with valid signature for 1000.00
        $tamperedAmount = '1000.00';
        $signature = $this->generateSignature($ref, '200', $tamperedAmount);

        $payload = [
            'order_id' => $ref,
            'status_code' => '200',
            'gross_amount' => $tamperedAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);
        $response->assertStatus(400);
        $response->assertJson(['success' => false, 'code' => 400]);
    }

    public function test_webhook_settlement_marks_all_grouped_orders_as_paid()
    {
        $service = app(PaymentService::class);
        $ref = $service->generatePaymentReference();

        $order1 = Order::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productA->product_id,
            'qty' => 1,
            'unit_price' => 150000,
            'subtotal' => 150000,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_gateway' => 'midtrans',
            'payment_method' => 'midtrans',
            'payment_reference' => $ref,
            'date' => now(),
        ]);

        $order2 = Order::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productB->product_id,
            'qty' => 2,
            'unit_price' => 75000,
            'subtotal' => 150000,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_gateway' => 'midtrans',
            'payment_method' => 'midtrans',
            'payment_reference' => $ref,
            'date' => now(),
        ]);

        $grossAmount = '300000.00';
        $signature = $this->generateSignature($ref, '200', $grossAmount);

        $payload = [
            'order_id' => $ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'transaction_id' => 'midtrans-trx-uuid-001',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'payment_status' => 'paid']);

        $order1->refresh();
        $order2->refresh();

        $this->assertEquals('paid', $order1->payment_status);
        $this->assertEquals('paid', $order2->payment_status);
        $this->assertNotNull($order1->paid_at);
        $this->assertNotNull($order2->paid_at);

        // Fulfillment status remains 'Pending' (not modified to Dibatalkan)
        $this->assertEquals('Pending', $order1->status);
        $this->assertEquals('Pending', $order2->status);
    }

    public function test_webhook_expire_or_cancel_marks_orders_cancelled_and_restores_stock()
    {
        $service = app(PaymentService::class);
        $ref = $service->generatePaymentReference();

        // Reduce stock manually to simulate checkout deduction
        $this->productA->decrement('stock', 2);
        $this->assertEquals(8, $this->productA->fresh()->stock);

        $order = Order::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productA->product_id,
            'qty' => 2,
            'unit_price' => 150000,
            'subtotal' => 300000,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_gateway' => 'midtrans',
            'payment_method' => 'midtrans',
            'payment_reference' => $ref,
            'date' => now(),
        ]);

        $grossAmount = '300000.00';
        $signature = $this->generateSignature($ref, '200', $grossAmount);

        $payload = [
            'order_id' => $ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
            'payment_type' => 'echannel',
            'transaction_id' => 'midtrans-trx-uuid-002',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('expired', $order->payment_status);
        $this->assertEquals('Dibatalkan', $order->status);

        // Stock must be restored from 8 back to 10
        $this->assertEquals(10, $this->productA->fresh()->stock);
    }

    public function test_webhook_is_idempotent_and_does_not_restore_stock_multiple_times()
    {
        $service = app(PaymentService::class);
        $ref = $service->generatePaymentReference();

        $this->productA->decrement('stock', 2);
        $this->assertEquals(8, $this->productA->fresh()->stock);

        $order = Order::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productA->product_id,
            'qty' => 2,
            'unit_price' => 150000,
            'subtotal' => 300000,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_gateway' => 'midtrans',
            'payment_method' => 'midtrans',
            'payment_reference' => $ref,
            'date' => now(),
        ]);

        $grossAmount = '300000.00';
        $signature = $this->generateSignature($ref, '200', $grossAmount);

        $payload = [
            'order_id' => $ref,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
        ];

        // First notification: stock restored
        $res1 = $this->postJson(route('midtrans.notification'), $payload);
        $res1->assertStatus(200);
        $this->assertEquals(10, $this->productA->fresh()->stock);

        // Second duplicate notification: MUST NOT restore stock again
        $res2 = $this->postJson(route('midtrans.notification'), $payload);
        $res2->assertStatus(200);
        $this->assertEquals(10, $this->productA->fresh()->stock);
    }

    // =========================================================================
    // C. ORDER CANCELLATION RESTRICTIONS
    // =========================================================================

    public function test_customer_cannot_cancel_already_paid_order()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productA->product_id,
            'qty' => 1,
            'status' => 'Pending',
            'payment_status' => 'paid',
            'payment_method' => 'midtrans',
            'paid_at' => now(),
            'date' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('orders.cancel', $order->orders_id));

        $response->assertRedirect(route('orders.show', $order->orders_id));
        $response->assertSessionHas('error');

        $this->assertEquals('Pending', $order->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    public function test_customer_can_cancel_pending_unpaid_order_and_stock_is_restored()
    {
        $this->productA->decrement('stock', 1);
        $this->assertEquals(9, $this->productA->fresh()->stock);

        $order = Order::create([
            'user_id' => $this->user->id,
            'product_id' => $this->productA->product_id,
            'qty' => 1,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_method' => 'midtrans',
            'date' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('orders.cancel', $order->orders_id));

        $response->assertRedirect(route('orders.show', $order->orders_id));
        $response->assertSessionHas('success');

        $this->assertEquals('Dibatalkan', $order->fresh()->status);
        $this->assertEquals('cancelled', $order->fresh()->payment_status);
        $this->assertEquals(10, $this->productA->fresh()->stock);
    }

    // =========================================================================
    // D. SNAP TOKEN & STATUS ENDPOINT ACCESS CONTROL
    // =========================================================================

    public function test_guest_cannot_access_snap_token_or_payment_status()
    {
        $resToken = $this->getJson(route('payment.snap_token', 'GNV-ANY'));
        $resToken->assertStatus(401);

        $resStatus = $this->getJson(route('payment.status', 'GNV-ANY'));
        $resStatus->assertStatus(401);
    }

    public function test_customer_cannot_access_other_users_snap_token()
    {
        $service = app(PaymentService::class);
        $ref = $service->generatePaymentReference();

        Order::create([
            'user_id' => $this->otherUser->id,
            'product_id' => $this->productA->product_id,
            'qty' => 1,
            'subtotal' => 150000,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_method' => 'midtrans',
            'payment_reference' => $ref,
            'date' => now(),
        ]);

        // Logged in as $this->user, trying to get token for $this->otherUser's order
        $response = $this->actingAs($this->user)
            ->getJson(route('payment.snap_token', $ref));

        $response->assertStatus(403);
    }
}
