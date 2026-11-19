<?php

namespace App\Http\Controllers;


use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminProductController extends Controller
{
    // =====================================
    // 1. DAFTAR PRODUK
    // =====================================

    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(
                'product_title',
                'like',
                "%{$search}%"
            );
        }

        if ($request->filled('category')) {
            $query->where('product_cat', $request->category);
        }

        $products = $query
            ->orderBy('product_id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $categories = Category::orderBy('cat_title')->get();

        return view('admin.products.index', compact(
            'products',
            'totalProducts',
            'totalCategories',
            'categories'
        ));
    }

    // =====================================
    // 2. FORM TAMBAH PRODUK
    // =====================================

    public function create()
    {
        $categories = Category::all();

        return view(
            'admin.products.create',
            compact('categories')
        );
    }

    // =====================================
    // 3. SIMPAN PRODUK BARU
    // =====================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_title' => 'required|string|max:255',
            'product_cat' => 'required|integer',
            'product_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'product_desc' => 'nullable|string',
            'product_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $categoryExists = Category::where(
            'cat_id',
            $validated['product_cat']
        )->exists();

        if (!$categoryExists) {
            return back()
                ->withErrors([
                    'product_cat' => 'Kategori tidak ditemukan.'
                ])
                ->withInput();
        }

        $image = $request->file('product_image');

        $imageName = time() . '_' .
            $image->getClientOriginalName();

        $image->move(
            public_path('product_images'),
            $imageName
        );

        $product = new Product();

        $product->product_title = $validated['product_title'];
        $product->product_cat = $validated['product_cat'];
        $product->product_price = $validated['product_price'];
        $product->stock = $validated['stock'];
        $product->product_desc = $validated['product_desc'] ?? null;
        $product->product_image = $imageName;

        $product->save();

        if (\Illuminate\Support\Facades\Schema::hasTable('stock_movements') && (int) $product->stock > 0) {
            app(\App\Services\InventoryService::class)->recordMovement(
                $product->product_id,
                (int) $product->stock,
                0,
                (int) $product->stock,
                \App\Services\InventoryService::TYPE_IN,
                'Stok awal pembuatan produk baru'
            );
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    // =====================================
    // 4. FORM EDIT PRODUK
    // =====================================

    public function edit($id)
    {
        $product = Product::findOrFail($id);

        $categories = Category::orderBy('cat_title')
            ->get();

        return view('admin.products.edit', compact(
            'product',
            'categories'
        ));
    }

    // =====================================
    // 5. UPDATE PRODUK
    // =====================================

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'product_title' => 'required|string|max:255',
            'product_price' => 'required|numeric|min:0',
            'product_cat' => 'required|integer',
            'product_desc' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'product_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $categoryExists = Category::where(
            'cat_id',
            $validated['product_cat']
        )->exists();

        if (!$categoryExists) {
            return back()
                ->withErrors([
                    'product_cat' => 'Kategori tidak ditemukan.'
                ])
                ->withInput();
        }

        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');

            $imageName = time() . '.' .
                $image->extension();

            $image->move(
                public_path('product_images'),
                $imageName
            );

            $validated['product_image'] = $imageName;
        }

        $oldPrice = (int) $product->product_price;
        $newPrice = (int) $validated['product_price'];
        $oldStock = (int) $product->stock;
        $newStock = (int) $validated['stock'];

        $product->update($validated);

        if (\Illuminate\Support\Facades\Schema::hasTable('stock_movements') && $oldStock !== $newStock) {
            $diff = $newStock - $oldStock;
            app(\App\Services\InventoryService::class)->recordMovement(
                $product->product_id,
                $diff,
                $oldStock,
                $newStock,
                \App\Services\InventoryService::TYPE_ADJUSTMENT,
                'Penyesuaian stok saat update data produk'
            );
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    // =====================================
    // 6. HAPUS PRODUK
    // =====================================

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $title = $product->product_title;

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // =====================================
    // 7. IMPORT PRODUK CSV
    // =====================================

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'csv_file.required' => 'Pilih file CSV terlebih dahulu.',
            'csv_file.mimes' => 'File harus berformat CSV.',
            'csv_file.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $file = $request->file('csv_file');

        $handle = fopen(
            $file->getRealPath(),
            'r'
        );

        if (!$handle) {
            return back()->with(
                'error',
                'File CSV tidak dapat dibaca.'
            );
        }

        try {
            // Deteksi pemisah koma atau titik koma
            $firstLine = fgets($handle);

            if ($firstLine === false) {
                return back()->with(
                    'error',
                    'File CSV kosong.'
                );
            }

            $delimiter = substr_count($firstLine, ';')
                > substr_count($firstLine, ',')
                ? ';'
                : ',';

            rewind($handle);

            // Baca header CSV
            $headers = fgetcsv(
                $handle,
                0,
                $delimiter,
                '"',
                ''
            );

            if (!$headers) {
                return back()->with(
                    'error',
                    'Header CSV tidak ditemukan.'
                );
            }

            // Bersihkan BOM UTF-8
            $headers = array_map(function ($header) {
                return trim(
                    preg_replace(
                        '/^\xEF\xBB\xBF/',
                        '',
                        $header
                    )
                );
            }, $headers);

            $requiredHeaders = [
                'product_title',
                'product_cat',
                'product_price',
                'stock',
                'product_desc',
                'product_image',
            ];

            // Validasi header CSV
            if (
                count($headers) !== count($requiredHeaders) ||
                count(array_unique($headers)) !== count($headers) ||
                array_diff($requiredHeaders, $headers)
            ) {
                return back()->with(
                    'error',
                    'Format kolom CSV tidak sesuai template.'
                );
            }

            $validCategories = Category::orderBy('cat_id')
                ->pluck('cat_title', 'cat_id')
                ->all();

            $categoryHint = collect($validCategories)
                ->map(fn($title, $id) => "{$id} ({$title})")
                ->implode(', ');

            $existingTitles = Product::pluck('product_title')
                ->map(fn($title) => mb_strtolower(trim((string) $title)))
                ->flip()
                ->all();

            $seenTitles = [];
            $rows = [];
            $skippedDuplicates = 0;
            $line = 1;

            // Baca seluruh data produk
            while (
                ($values = fgetcsv(
                    $handle,
                    0,
                    $delimiter,
                    '"',
                    ''
                )) !== false
            ) {
                $line++;

                // Lewati baris kosong
                if (
                    count($values) === 1 &&
                    trim($values[0] ?? '') === ''
                ) {
                    continue;
                }

                // Periksa jumlah kolom
                if (count($values) !== count($headers)) {
                    return back()->with(
                        'error',
                        "Jumlah kolom tidak sesuai pada baris {$line}."
                    );
                }

                $row = array_map(
                    'trim',
                    array_combine($headers, $values)
                );

                // Validasi kategori secara eksplisit agar tidak salah mapping
                $rawCategory = $row['product_cat'] ?? '';
                if (!ctype_digit((string) $rawCategory) || !array_key_exists((int) $rawCategory, $validCategories)) {
                    return back()->with(
                        'error',
                        "ID kategori '{$rawCategory}' pada baris {$line} tidak valid. Gunakan ID kategori yang tersedia: {$categoryHint}."
                    );
                }

                // Validasi setiap produk
                $validator = Validator::make($row, [
                    'product_title' => 'required|string|max:255',
                    'product_cat' => 'required|integer|exists:categories,cat_id',
                    'product_price' => 'required|numeric|min:0',
                    'stock' => 'required|integer|min:0',
                    'product_desc' => 'nullable|string',
                    'product_image' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    return back()->with(
                        'error',
                        "Data tidak valid pada baris {$line}: " .
                        $validator->errors()->first()
                    );
                }

                // Pastikan nama file gambar aman
                $imageName = $row['product_image'];

                if (
                    $imageName !== basename($imageName) ||
                    str_contains($imageName, '\\') ||
                    !preg_match(
                        '/\.(jpg|jpeg|png|webp)$/i',
                        $imageName
                    )
                ) {
                    return back()->with(
                        'error',
                        "Nama gambar tidak valid pada baris {$line}."
                    );
                }

                // Gambar harus sudah tersedia
                $imagePath = public_path(
                    'product_images/' . $imageName
                );

                if (!is_file($imagePath)) {
                    return back()->with(
                        'error',
                        "Gambar {$imageName} pada baris {$line} tidak ditemukan."
                    );
                }

                // Cegah duplikasi produk yang sudah ada atau duplikat dalam file CSV
                $normalizedTitle = mb_strtolower($row['product_title']);
                if (isset($existingTitles[$normalizedTitle]) || isset($seenTitles[$normalizedTitle])) {
                    $skippedDuplicates++;
                    continue;
                }
                $seenTitles[$normalizedTitle] = true;

                // Simpan sementara untuk divalidasi
                $rows[] = $row;

                // Batas maksimal import
                if (count($rows) > 300) {
                    return back()->with(
                        'error',
                        'Maksimal 300 produk dalam satu file CSV.'
                    );
                }
            }

            if (empty($rows)) {
                if ($skippedDuplicates > 0) {
                    return back()->with(
                        'error',
                        "Tidak ada produk baru yang diimport ({$skippedDuplicates} produk sudah terdaftar)."
                    );
                }

                return back()->with(
                    'error',
                    'Tidak ada produk untuk diimport.'
                );
            }

            // Simpan seluruh produk dalam satu transaksi
            DB::transaction(function () use ($rows) {

                foreach ($rows as $row) {

                    $product = new Product();

                    $product->product_title =
                        $row['product_title'];

                    $product->product_cat =
                        (int) $row['product_cat'];

                    $product->product_price =
                        $row['product_price'];

                    $product->stock =
                        (int) $row['stock'];

                    $product->product_desc =
                        $row['product_desc'] ?: null;

                    $product->product_image =
                        $row['product_image'];

                    $product->save();
                }
            });

            $message = count($rows) . ' produk berhasil diimport.';
            if ($skippedDuplicates > 0) {
                $message .= " ({$skippedDuplicates} produk dilewati karena sudah ada.)";
            }

            return redirect()
                ->route('admin.products.index')
                ->with('success', $message);

        } finally {
            fclose($handle);
        }
    }
}
