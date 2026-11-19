<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use Tests\TestCase;
use Tests\UsesIsolatedDatabase;

class CatalogFilterTest extends TestCase
{
    use UsesIsolatedDatabase;

    protected Category $home;
    protected Category $fashion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->home    = Category::factory()->create(['cat_title' => 'Rumah Tangga']);
        $this->fashion = Category::factory()->create(['cat_title' => 'Fashion']);
    }

    private function product(string $title, int $price, Category $cat, int $stock = 10, string $desc = 'Deskripsi produk'): Product
    {
        return Product::factory()->create([
            'product_title' => $title,
            'product_price' => $price,
            'product_cat'   => $cat->cat_id,
            'stock'         => $stock,
            'product_desc'  => $desc,
        ]);
    }

    public function test_search_respects_category_filter(): void
    {
        $this->product('Sikat Bambu', 20000, $this->home);
        $this->product('Tas Bambu', 90000, $this->fashion);
        // Cocok lewat deskripsi tetapi kategori lain — tidak boleh lolos dari filter kategori
        $this->product('Kaos Organik', 50000, $this->fashion, 10, 'Kemasan berbahan bambu');

        $response = $this->get(route('products', ['search' => 'bambu', 'category' => $this->home->cat_id]));

        $response->assertOk()
            ->assertSee('Sikat Bambu')
            ->assertDontSee('Tas Bambu')
            ->assertDontSee('Kaos Organik');
    }

    public function test_search_respects_price_and_stock_filters(): void
    {
        $this->product('Botol Bambu Murah', 15000, $this->home);
        $this->product('Botol Bambu Mahal', 150000, $this->home);
        $this->product('Botol Bambu Habis', 30000, $this->home, 0);

        $response = $this->get(route('products', [
            'search' => 'botol', 'min_price' => 10000, 'max_price' => 100000, 'in_stock' => 1,
        ]));

        $response->assertOk()
            ->assertSee('Botol Bambu Murah')
            ->assertDontSee('Botol Bambu Mahal')
            ->assertDontSee('Botol Bambu Habis');
    }

    public function test_price_filter_uses_discounted_price(): void
    {
        $discounted = $this->product('Sedotan Diskon', 80000, $this->home);
        Discount::create([
            'product_id' => $discounted->product_id,
            'discount_type' => 'percent',
            'discount_value' => 50,
            'is_active' => true,
        ]);
        $this->product('Sedotan Normal', 80000, $this->home);

        $this->get(route('products', ['max_price' => 50000]))
            ->assertOk()
            ->assertSee('Sedotan Diskon')
            ->assertDontSee('Sedotan Normal');
    }

    public function test_swapped_min_and_max_price_are_corrected(): void
    {
        $this->product('Produk Tengah', 50000, $this->home);

        $this->get(route('products', ['min_price' => 100000, 'max_price' => 10000]))
            ->assertOk()
            ->assertSee('Produk Tengah');
    }

    public function test_price_sorting_ascending_and_descending(): void
    {
        $this->product('Produk Harga Tinggi', 300000, $this->home);
        $this->product('Produk Harga Rendah', 10000, $this->home);
        $this->product('Produk Harga Sedang', 100000, $this->home);

        $this->get(route('products', ['sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder(['Produk Harga Rendah', 'Produk Harga Sedang', 'Produk Harga Tinggi']);

        $this->get(route('products', ['sort' => 'price_desc']))
            ->assertOk()
            ->assertSeeInOrder(['Produk Harga Tinggi', 'Produk Harga Sedang', 'Produk Harga Rendah']);
    }

    public function test_best_seller_and_rating_sorts_work_with_filters(): void
    {
        $popular = $this->product('Produk Laris', 40000, $this->home);
        $this->product('Produk Sepi', 40000, $this->home);
        Order::factory()->create(['product_id' => $popular->product_id, 'qty' => 5, 'status' => 'Selesai']);

        $this->get(route('products', ['sort' => 'best_seller', 'category' => $this->home->cat_id, 'search' => 'produk']))
            ->assertOk()
            ->assertSeeInOrder(['Produk Laris', 'Produk Sepi']);

        $this->get(route('products', ['sort' => 'rating', 'min_price' => 1000]))
            ->assertOk()
            ->assertSee('Produk Laris');
    }

    public function test_invalid_sort_and_category_values_do_not_error(): void
    {
        $this->product('Produk Aman', 40000, $this->home);

        $this->get(route('products', ['sort' => 'drop table', 'category' => 'abc', 'min_price' => '-5']))
            ->assertOk()
            ->assertSee('Produk Aman');
    }

    public function test_pagination_links_preserve_active_filters(): void
    {
        for ($i = 1; $i <= 14; $i++) {
            $this->product('Produk Seri ' . $i, 20000 + $i, $this->home);
        }

        $response = $this->get(route('products', [
            'category' => $this->home->cat_id, 'sort' => 'price_asc', 'search' => 'seri',
        ]));

        $response->assertOk();
        $html = html_entity_decode($response->getContent());
        $this->assertStringContainsString('page=2', $html);
        $this->assertMatchesRegularExpression('/[?&]search=seri[^"]*page=2|page=2[^"]*search=seri/', $html);
        $this->assertMatchesRegularExpression('/sort=price_asc[^"]*page=2|page=2[^"]*sort=price_asc/', $html);
    }
}
