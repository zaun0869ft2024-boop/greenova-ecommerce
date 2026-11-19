<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminDiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::with('product')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('admin.discounts.index', compact('discounts'));
    }

    public function create()
    {
        $products = Product::orderBy('product_title')->get();

        return view('admin.discounts.create', compact('products'));
    }

    public function store(Request $request)
    {
        $rules = [
            'product_id'     => 'required|integer|exists:products,product_id',
            'discount_type'  => 'required|in:percent,nominal',
            'discount_value' => 'required|numeric|min:0.01',
            'starts_at'      => 'nullable|date',
            'ends_at'        => 'nullable|date|after_or_equal:starts_at',
            'is_active'      => 'boolean',
        ];

        $validated = $request->validate($rules);

        // Percent tidak boleh > 100
        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()
                ->withErrors(['discount_value' => 'Diskon persen maksimal 100%.'])
                ->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Discount::create($validated);

        return redirect()
            ->route('admin.discounts.index')
            ->with('success', 'Diskon berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $discount = Discount::findOrFail($id);
        $products = Product::orderBy('product_title')->get();

        return view('admin.discounts.edit', compact('discount', 'products'));
    }

    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        $rules = [
            'product_id'     => 'required|integer|exists:products,product_id',
            'discount_type'  => 'required|in:percent,nominal',
            'discount_value' => 'required|numeric|min:0.01',
            'starts_at'      => 'nullable|date',
            'ends_at'        => 'nullable|date|after_or_equal:starts_at',
            'is_active'      => 'boolean',
        ];

        $validated = $request->validate($rules);

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()
                ->withErrors(['discount_value' => 'Diskon persen maksimal 100%.'])
                ->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $discount->update($validated);

        return redirect()
            ->route('admin.discounts.index')
            ->with('success', 'Diskon berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();

        return redirect()
            ->route('admin.discounts.index')
            ->with('success', 'Diskon berhasil dihapus.');
    }
}
