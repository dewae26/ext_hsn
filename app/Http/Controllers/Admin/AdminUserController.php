<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::orderBy('name')->paginate(15);

        return view('admin.admins.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'string', 'max:25', Rule::unique('users', 'employee_id')],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')],
            'is_super_admin' => ['nullable', 'boolean'],
        ]);

        $data['is_super_admin'] = $request->boolean('is_super_admin');
        $data['is_active'] = true;

        User::create($data);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil ditambahkan.');
    }

    public function update(Request $request, User $admin)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($admin->id)],
        ]);

        $data['is_super_admin'] = $request->boolean('is_super_admin');
        $data['is_active'] = $request->boolean('is_active');

        $admin->update($data);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Data admin berhasil diperbarui.');
    }

    public function destroy(Request $request, User $admin)
    {
        if ($request->user()->id === $admin->id) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin berhasil dihapus.');
    }
}
