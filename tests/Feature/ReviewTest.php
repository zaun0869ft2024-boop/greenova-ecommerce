<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Tests\TestCase;
use Tests\UsesIsolatedDatabase;

class ReviewTest extends TestCase
{
    use UsesIsolatedDatabase;

    public function test_user_with_selesai_order_can_submit_rating_and_review()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        $response = $this->actingAs($user)->post(route('product.review.store', $product->product_id), [
            'order_id' => $order->orders_id,
            'rating'   => 5,
            'comment'  => 'Produk ramah lingkungan sangat berkualitas dan memuaskan!',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('product_reviews', [
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'order_id'   => $order->orders_id,
            'rating'     => 5,
            'comment'    => 'Produk ramah lingkungan sangat berkualitas dan memuaskan!',
        ]);
    }

    public function test_user_cannot_review_if_order_status_is_pending()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Pending',
        ]);

        $response = $this->actingAs($user)->post(route('product.review.store', $product->product_id), [
            'order_id' => $order->orders_id,
            'rating'   => 4,
            'comment'  => 'Mencoba mengulas pesanan pending',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('product_reviews', [
            'order_id' => $order->orders_id,
        ]);
    }

    public function test_user_cannot_review_if_order_status_is_diproses()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Diproses',
        ]);

        $response = $this->actingAs($user)->post(route('product.review.store', $product->product_id), [
            'order_id' => $order->orders_id,
            'rating'   => 5,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('product_reviews', [
            'order_id' => $order->orders_id,
        ]);
    }

    public function test_user_cannot_review_if_order_status_is_dibatalkan()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Dibatalkan',
        ]);

        $response = $this->actingAs($user)->post(route('product.review.store', $product->product_id), [
            'order_id' => $order->orders_id,
            'rating'   => 3,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('product_reviews', [
            'order_id' => $order->orders_id,
        ]);
    }

    public function test_user_cannot_review_without_order_or_with_invalid_order_id()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);

        $response = $this->actingAs($user)->post(route('product.review.store', $product->product_id), [
            'order_id' => 99999,
            'rating'   => 5,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, ProductReview::count());
    }

    public function test_user_cannot_review_an_order_belonging_to_another_user()
    {
        $owner    = User::factory()->create();
        $other    = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $owner->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        $response = $this->actingAs($other)->post(route('product.review.store', $product->product_id), [
            'order_id' => $order->orders_id,
            'rating'   => 5,
            'comment'  => 'Mencoba membajak pesanan orang lain',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('product_reviews', [
            'order_id' => $order->orders_id,
        ]);
    }

    public function test_duplicate_review_for_same_order_item_is_prevented()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        // Ulasan pertama
        ProductReview::create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'order_id'   => $order->orders_id,
            'rating'     => 5,
            'comment'    => 'Ulasan pertama',
        ]);

        // Percobaan ulasan kedua untuk order yang sama
        $response = $this->actingAs($user)->post(route('product.review.store', $product->product_id), [
            'order_id' => $order->orders_id,
            'rating'   => 4,
            'comment'  => 'Ulasan kedua duplikat',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, ProductReview::where('order_id', $order->orders_id)->count());
    }

    public function test_rating_must_be_between_1_and_5()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        $response = $this->actingAs($user)->post(route('product.review.store', $product->product_id), [
            'order_id' => $order->orders_id,
            'rating'   => 6, // Tidak valid
        ]);

        $response->assertSessionHasErrors(['rating']);
        $this->assertEquals(0, ProductReview::count());
    }

    public function test_user_can_update_their_own_review()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        $review = ProductReview::create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'order_id'   => $order->orders_id,
            'rating'     => 4,
            'comment'    => 'Bagus tapi pengiriman agak lama',
        ]);

        $response = $this->actingAs($user)->patch(route('review.update', $review->id), [
            'rating'  => 5,
            'comment' => 'Update ulasan: setelah dipakai beberapa hari sangat puas!',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('product_reviews', [
            'id'      => $review->id,
            'rating'  => 5,
            'comment' => 'Update ulasan: setelah dipakai beberapa hari sangat puas!',
        ]);
    }

    public function test_user_cannot_update_another_users_review()
    {
        $owner    = User::factory()->create();
        $intruder = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $owner->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        $review = ProductReview::create([
            'user_id'    => $owner->id,
            'product_id' => $product->product_id,
            'order_id'   => $order->orders_id,
            'rating'     => 5,
            'comment'    => 'Ulasan pemilik',
        ]);

        $response = $this->actingAs($intruder)->patch(route('review.update', $review->id), [
            'rating'  => 1,
            'comment' => 'Mencoba merusak ulasan orang lain',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('product_reviews', [
            'id'     => $review->id,
            'rating' => 5,
        ]);
    }

    public function test_admin_can_view_reviews_and_delete_inappropriate_review()
    {
        $admin    = User::factory()->create(['role' => 'admin']);
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);
        $order    = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        $review = ProductReview::create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'order_id'   => $order->orders_id,
            'rating'     => 1,
            'comment'    => 'Komentar mengandung spam',
        ]);

        // Admin bisa buka halaman moderasi
        $indexResponse = $this->actingAs($admin)->get(route('admin.reviews.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Komentar mengandung spam');

        // Admin menghapus ulasan
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review->id));
        $deleteResponse->assertRedirect(route('admin.reviews.index'));
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('product_reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_non_admin_cannot_access_review_moderation()
    {
        $customer = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($customer)->get(route('admin.reviews.index'));
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_deleted_review_is_not_counted_in_product_average_rating()
    {
        $user1    = User::factory()->create();
        $user2    = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id]);

        $order1 = Order::factory()->create(['user_id' => $user1->id, 'product_id' => $product->product_id, 'status' => 'Selesai']);
        $order2 = Order::factory()->create(['user_id' => $user2->id, 'product_id' => $product->product_id, 'status' => 'Selesai']);

        $rev1 = ProductReview::create([
            'user_id'    => $user1->id,
            'product_id' => $product->product_id,
            'order_id'   => $order1->orders_id,
            'rating'     => 5,
        ]);

        $rev2 = ProductReview::create([
            'user_id'    => $user2->id,
            'product_id' => $product->product_id,
            'order_id'   => $order2->orders_id,
            'rating'     => 1,
        ]);

        // Awalnya: (5 + 1) / 2 = 3.0
        $this->assertEquals(3.0, $product->fresh()->averageRating());

        // Hapus review 2 (rating 1)
        $rev2->delete();

        // Sekarang rata-rata menjadi 5.0
        $this->assertEquals(5.0, $product->fresh()->averageRating());
        $this->assertEquals(1, $product->fresh()->reviewsCount());
    }

    public function test_orders_page_renders_rating_triggers_correctly()
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id, 'product_title' => 'Tas Daur Ulang']);

        // Order selesai belum diulas
        $orderSelesai = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        // Order pending
        $orderPending = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Pending',
        ]);

        $response = $this->actingAs($user)->get(route('orders'));
        $response->assertStatus(200);

        // Tombol Beri Penilaian muncul
        $response->assertSee('Beri Penilaian');

        // Setelah diulas
        ProductReview::create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'order_id'   => $orderSelesai->orders_id,
            'rating'     => 5,
            'comment'    => 'Hebat',
        ]);

        $responseAfter = $this->actingAs($user)->get(route('orders'));
        $responseAfter->assertStatus(200);
        $responseAfter->assertSee('Edit Ulasan');
    }

    public function test_product_detail_page_displays_reviews_without_rating_form()
    {
        $user     = User::factory()->create(['name' => 'Budi Santoso']);
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['product_cat' => $category->cat_id, 'product_title' => 'Sikat Gigi Bambu']);
        $order    = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->product_id, 'status' => 'Selesai']);

        ProductReview::create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'order_id'   => $order->orders_id,
            'rating'     => 5,
            'comment'    => 'Sangat lembut dan nyaman digunakan.',
        ]);

        $response = $this->actingAs($user)->get(route('product.detail', $product->product_id));
        $response->assertStatus(200);

        // Ulasan dan nama pembeli tampil
        $response->assertSee('Budi Santoso');
        $response->assertSee('Sangat lembut dan nyaman digunakan.');
        $response->assertSee('5/5');

        // Pastikan TIDAK ADA form pemberian rating di halaman detail
        $response->assertDontSee('id="star-rating-container"', false);
        $response->assertDontSee('Tulis Ulasan Anda');
    }
}
