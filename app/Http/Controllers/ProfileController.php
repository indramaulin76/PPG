<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load(['desa', 'kelompok']);

        return inertia('Profile/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'email' => $user->email,
                'no_telepon' => $user->no_telepon,
                'desa' => $user->desa?->nama_desa,
                'kelompok' => $user->kelompok?->nama_kelompok,
                'scope_label' => $user->scope_label,
                'created_at' => $user->created_at->format('d/m/Y'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$user->id,
            'no_telepon' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
        ];

        if ($request->filled('password') || $request->filled('password_confirmation')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->username = $validated['username'];

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if (isset($validated['no_telepon'])) {
            $user->no_telepon = $validated['no_telepon'];
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
