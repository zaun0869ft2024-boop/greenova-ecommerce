<?php

namespace Tests\Feature;


use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\InventoryService;
use Tests\TestCase;
use Tests\UsesIsolatedDatabase;

class GreenovaOptimizationTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected User $admin;
    protected User $staff;
    protected User $customer;
    protected User $otherCustomer;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@greenova.id',
        ]);
        $this->admin->role = 'admin';
        $this->admin->save();

        $this->staff = User::factory()->create([
            'name' => 'Staff Gudang',
            'email' => 'staff@greenova.id',
        ]);
        $this->staff->role = 'staff';
        $this->staff->save();

        $this->customer = User::factory()->create([
            'name' => 'Budi Customer',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 12',
        ]);
        $this->customer->role = 'user';
        $this->customer->save();

        $this->otherCustomer = User::factory()->create([
            'name' => 'Siti Customer',
            'email' => 'siti@example.com',
        ]);
        $this->otherCustomer->role = 'user';
        $this->otherCustomer->save();

        $this->category = Category::factory()->create([
            'cat_title' => 'Dapur & Rumah Tangga',
        ]);

        $this->product = Product::factory()->create([
            'product_title' => 'Sedotan Bambu Organik',
            'product_price' => 25000,
            'product_cat' => $this->category->cat_id,
            'stock' => 20,
        ]);
    }

    // =========================================================================
    // 1. CUSTOMER DASHBOARD & ACCOUNT TESTS
    // =========================================================================

    public function test_customer_can_access_dashboard_with_order_statistics()
    {
        Order::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
            'qty' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
            'status' => 'Pending',
            'payment_status' => 'pending',
            'payment_method' => 'midtrans',
            'date' => now()->toDateString(),
            'recipient_name' => 'Budi',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Mawar No. 12',
        ]);

        $response = $this->actingAs($this->customer)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Akun Saya');
        $response->assertSee('Sedotan Bambu Organik');
        $response->assertSee('Rp 50.000');
    }

    public function test_admin_is_redirected_to_admin_dashboard_from_customer_dashboard()
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    // =========================================================================
    // 2. HTTP METHODS & CART SECURITY
    // =========================================================================

    public function test_cart_item_can_be_removed_via_delete_method()
    {
        CartItem::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
        ]);

        $response = $this->actingAs($this->customer)->delete(route('remove.cart', $this->product->product_id));

        $response->assertRedirect(route('cart'));
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
        ]);
    }

    // =========================================================================
    // 3. INVENTORY MANAGEMENT & STOCK MOVEMENTS
    // =========================================================================

    public function test_admin_can_adjust_stock_with_audit_trail()
    {
        $inventoryService = app(InventoryService::class);

        // Stock in +15 unit
        $res = $inventoryService->adjustStock(
            $this->product->product_id,
            'in',
            15,
            'Restock dari produsen bambu',
            $this->admin->id
        );

        $this->assertTrue($res['success']);
        $this->assertEquals(20, $res['stock_before']);
        $this->assertEquals(35, $res['stock_after']);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->product_id,
            'type' => 'in',
            'quantity' => 15,
            'stock_before' => 20,
            'stock_after' => 35,
        ]);
    }

    public function test_stock_cannot_be_reduced_below_zero()
    {
        $this->expectException(\InvalidArgumentException::class);

        $inventoryService = app(InventoryService::class);
        $inventoryService->adjustStock(
            $this->product->product_id,
            'out',
            999, // melebihi stok 20
            'Pengurangan abnormal'
        );
    }

    public function test_admin_inventory_index_and_history_are_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.inventory.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Inventori');

        $responseHist = $this->actingAs($this->admin)->get(route('admin.inventory.history'));
        $responseHist->assertStatus(200);
        $responseHist->assertSee('Audit Trail Pergerakan Stok');
    }

    // =========================================================================
    // 4. TRANSACTION REPORTING & EXPORT
    // =========================================================================

    public function test_admin_can_view_transaction_reports_and_filter()
    {
        Order::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
            'qty' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
            'status' => 'Selesai',
            'payment_status' => 'paid',
            'payment_method' => 'midtrans',
            'date' => now()->toDateString(),
            'recipient_name' => 'Budi',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Mawar No. 12',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.transactions', [
            'payment_status' => 'paid',
            'order_status' => 'Selesai',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Laporan Transaksi');
        $response->assertSee('Rp 25.000');
    }

    public function test_admin_can_export_transactions_as_csv()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export_csv'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    // =========================================================================
    // 5. USER & ROLE MANAGEMENT
    // =========================================================================

    public function test_admin_can_promote_customer_to_staff()
    {
        $response = $this->actingAs($this->admin)->patch(route('admin.users.update_role', $this->customer->id), [
            'role' => 'staff',
        ]);

        $response->assertRedirect();
        $this->customer->refresh();
        $this->assertEquals('staff', $this->customer->role);
    }

    public function test_admin_cannot_demote_themselves()
    {
        $response = $this->actingAs($this->admin)->patch(route('admin.users.update_role', $this->admin->id), [
            'role' => 'user',
        ]);

        $this->admin->refresh();
        $this->assertEquals('admin', $this->admin->role);
    }

    public function test_customer_cannot_access_admin_or_change_roles()
    {
        $response = $this->actingAs($this->customer)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $responsePatch = $this->actingAs($this->customer)->patch(route('admin.users.update_role', $this->customer->id), [
            'role' => 'admin',
        ]);
        $responsePatch->assertStatus(403);
    }

    // =========================================================================
    // 6. ORDER TRACKING & ORDER STATUS DIKIRIM
    // =========================================================================

    public function test_order_status_can_be_updated_to_dikirim()
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'product_id' => $this->product->product_id,
            'qty' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
            'status' => 'Diproses',
            'payment_status' => 'paid',
            'payment_method' => 'midtrans',
            'date' => now()->toDateString(),
            'recipient_name' => 'Budi',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Mawar No. 12',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.orders.status', $order->orders_id), [
            'status' => 'Dikirim',
        ]);

        $response->assertRedirect(route('admin.orders.index'));
        $order->refresh();
        $this->assertEquals('Dikirim', $order->status);

        // Verify order tracking shows Dikirim
        $trackResponse = $this->actingAs($this->customer)->get(route('orders.show', $order->orders_id));
        $trackResponse->assertStatus(200);
        $trackResponse->assertSee('Dikirim');
        $trackResponse->assertSee('Dalam Pengiriman');
    }

    public function test_activity_log_is_removed_and_all_admin_pages_render_cleanly()
    {
        $this->actingAs($this->admin)->get('/admin/activity-logs')->assertNotFound();

        $adminPages = [
            route('admin.dashboard'),
            route('admin.dashboard', ['period' => 'today']),
            route('admin.products.index'),
            route('admin.categories.index'),
            route('admin.orders.index'),
            route('admin.inventory.index'),
            route('admin.reports.transactions'),
            route('admin.users.index'),
            route('admin.reviews.index'),
            url('/admin/discounts'),
            url('/admin/vouchers'),
        ];

        foreach ($adminPages as $url) {
            $this->actingAs($this->admin)
                ->get($url)
                ->assertOk()
                ->assertDontSee('Log Aktivitas');
        }
    }
}
