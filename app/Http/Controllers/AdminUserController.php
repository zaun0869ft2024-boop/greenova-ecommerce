<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $role = trim((string) $request->input('role', 'semua'));

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role !== 'semua' && in_array($role, ['admin', 'staff', 'user'], true)) {
            $query->where('role', $role);
        }

        $users = $query->latest('id')
            ->paginate(15)
            ->withQueryString();

        $roleStats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'customer' => User::where('role', 'user')->count(),
        ];

        return view('admin.users.index', compact('users', 'search', 'role', 'roleStats'));
    }

    /**
     * Ubah hak akses / peran (role) pengguna.
     * Hanya Administrator penuh yang memiliki wewenang ini.
     */
    public function updateRole(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Hanya Administrator yang berwenang mengubah role pengguna.');
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,staff,user',
        ]);

        $user = User::findOrFail($id);

        // Mencegah admin mendemosi akunnya sendiri
        if ((int) $user->id === (int) auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri untuk mencegah penguncian sistem.');
        }

        $oldRole = $user->role ?? 'user';
        $newRole = $validated['role'];

        $user->role = $newRole;
        $user->save();

        return back()->with('success', "Peran pengguna '{$user->name}' berhasil diperbarui menjadi " . strtoupper($newRole) . ".");
    }
}
