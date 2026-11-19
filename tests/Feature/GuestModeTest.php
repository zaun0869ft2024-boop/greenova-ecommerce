<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;
use Tests\UsesIsolatedDatabase;

class GuestModeTest extends TestCase
{
    use UsesIsolatedDatabase;

    // =========================================================================
    // A. RUTE PUBLIK — Semua pengunjung (termasuk tamu) HARUS bisa mengakses
    // =========================================================================

    public function test_guest_can_access_home()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_guest_can_access_product_catalog()
    {
        $response = $this->get('/products');
        $response->assertStatus(200);
    }

    public function test_guest_can_access_product_detail()
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);

        $response = $this->get(route('product.detail', $product->product_id));
        $response->assertStatus(200);
    }

    public function test_guest_can_access_chatbot_products()
    {
        $response = $this->get('/chatbot/products');
        $response->assertStatus(200);
    }

    // =========================================================================
    // B. RUTE TERPROTEKSI — Tamu WAJIB diarahkan ke /login
    // =========================================================================

    public function test_guest_is_redirected_from_cart()
    {
        $response = $this->get('/cart');
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_orders()
    {
        $response = $this->get('/orders');
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_profile()
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_checkout()
    {
        $response = $this->get('/checkout');
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_post_to_add_cart()
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);

        $response = $this->post(route('add.cart', $product->product_id));
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_post_to_buy_now()
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);

        $response = $this->post(route('buy.now', $product->product_id));
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_post_review()
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);

        $response = $this->post(route('product.review.store', $product->product_id), [
            'order_id' => 1,
            'rating'   => 5,
            'comment'  => 'Test tamu coba beri ulasan',
        ]);
        $response->assertRedirect('/login');
    }

    public function test_wishlist_route_is_removed_and_returns_404()
    {
        $response = $this->get('/wishlist');
        $response->assertNotFound();
    }

    // =========================================================================
    // C. PENGGUNA TERAUTENTIKASI — Dapat mengakses rute terproteksi
    // =========================================================================

    public function test_authenticated_user_can_access_cart()
    {
        $user     = User::factory()->create();
        $response = $this->actingAs($user)->get('/cart');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_orders()
    {
        $user     = User::factory()->create();
        $response = $this->actingAs($user)->get('/orders');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_profile()
    {
        $user     = User::factory()->create();
        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
    }

    // =========================================================================
    // D. STATUS LABEL PESANAN — Validasi label teks sesuai status
    // =========================================================================

    public function test_order_index_shows_correct_status_labels()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);

        // Buat 4 pesanan dengan status yang berbeda
        Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Pending',
        ]);
        Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Diproses',
        ]);
        Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);
        Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Dibatalkan',
        ]);

        $response = $this->actingAs($user)->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('Pending');
        $response->assertSee('Diproses');
        $response->assertSee('Selesai');
        $response->assertSee('Dibatalkan');
    }

    public function test_order_show_displays_correct_status_label()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);

        $order = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        $response = $this->actingAs($user)->get(route('orders.show', $order->orders_id));
        $response->assertStatus(200);
        $response->assertSee('Selesai');
    }

    // =========================================================================
    // E. ADMIN — Tamu tidak dapat mengakses area admin
    // =========================================================================

    public function test_guest_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_user_cannot_access_admin_dashboard()
    {
        // User dengan role 'user' (bukan 'admin') tidak boleh akses dashboard admin
        $user     = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get('/admin');
        // Harus 403 atau redirect, bukan 200
        $this->assertNotEquals(200, $response->getStatusCode());
    }
}
