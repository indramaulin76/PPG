<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Desa;
use App\Models\Kelompok;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminManagementController extends Controller
{
    /**
     * Display a listing of admins that current user can manage
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Build query based on current user's role
        $query = User::query();
        
        if ($user->isDeveloper()) {
            // Developer can see all admins including Super Admins (except themselves)
            $query->whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_DESA, User::ROLE_ADMIN_KELOMPOK])
                  ->where('id', '!=', $user->id);
        } elseif ($user->isSuperAdmin()) {
            // Super admin can see all admin levels except Developer
            $query->whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_DESA, User::ROLE_ADMIN_KELOMPOK])
                  ->where('id', '!=', $user->id);
        } elseif ($user->isAdminDesa()) {
            // Admin desa can only see admin kelompok in their desa
            $query->where('role', User::ROLE_ADMIN_KELOMPOK)
                  ->where('desa_id', $user->desa_id);
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        if ($request->filled('desa_id') && $user->isSuperAdmin()) {
            $query->where('desa_id', $request->desa_id);
        }
        
        // Load relations and paginate
        $admins = $query->with(['desa', 'kelompok'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($admin) => [
                'id' => $admin->id,
                'name' => $admin->name,
                'username' => $admin->username,
                'role' => $admin->role,
                'desa' => $admin->desa?->nama_desa,
                'desa_id' => $admin->desa_id,
                'kelompok' => $admin->kelompok?->nama_kelompok,
                'kelompok_id' => $admin->kelompok_id,
                'is_active' => $admin->is_active,
                'created_at' => $admin->created_at->format('d M Y'),
            ]);
        
        // Prepare dropdown options
        if ($user->isDeveloper()) {
            $allowedRoles = [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_DESA, User::ROLE_ADMIN_KELOMPOK];
        } elseif ($user->isSuperAdmin()) {
            $allowedRoles = [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_DESA, User::ROLE_ADMIN_KELOMPOK];
        } else {
            $allowedRoles = [User::ROLE_ADMIN_KELOMPOK];
        }
        
        $desas = $user->isSuperAdmin() 
            ? Desa::select('id', 'nama_desa')->orderBy('nama_desa')->get()
            : Desa::where('id', $user->desa_id)->get();
        
        $kelompoks = $user->isSuperAdmin()
            ? Kelompok::with('desa')->select('id', 'desa_id', 'nama_kelompok')->orderBy('nama_kelompok')->get()
            : Kelompok::where('desa_id', $user->desa_id)->select('id', 'desa_id', 'nama_kelompok')->get();
        
        return Inertia::render('Admin/Index', [
            'admins' => $admins,
            'filters' => $request->only(['search', 'role', 'desa_id']),
            'allowedRoles' => $allowedRoles,
            'desas' => $desas,
            'kelompoks' => $kelompoks,
        ]);
    }

    /**
     * Store a newly created admin
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $allowedRoles = [];
        if ($user->isDeveloper()) {
            $allowedRoles = [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_DESA, User::ROLE_ADMIN_KELOMPOK];
        } elseif ($user->isSuperAdmin()) {
            $allowedRoles = [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_DESA, User::ROLE_ADMIN_KELOMPOK];
        } else {
            $allowedRoles = [User::ROLE_ADMIN_KELOMPOK];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in($allowedRoles)],
            'desa_id' => 'required_if:role,' . User::ROLE_ADMIN_DESA . '|required_if:role,' . User::ROLE_ADMIN_KELOMPOK . '|nullable|exists:desas,id',
            'kelompok_id' => 'required_if:role,' . User::ROLE_ADMIN_KELOMPOK . '|nullable|exists:kelompoks,id',
            'is_active' => 'sometimes|boolean',
        ]);

        // Fix desa_id validation for Super Admin - it should be null
        if ($validated['role'] === User::ROLE_SUPER_ADMIN) {
            $validated['desa_id'] = null;
            $validated['kelompok_id'] = null;
        }
        
        // Security check: Admin desa can't create admin desa
        if ($user->isAdminDesa() && $validated['role'] === User::ROLE_ADMIN_DESA) {
            return back()->withErrors(['role' => 'Anda tidak bisa membuat Admin Desa']);
        }
        
        // Security check: Admin desa can only create admin kelompok in their desa
        if ($user->isAdminDesa()) {
            $kelompok = Kelompok::find($validated['kelompok_id']);
            if (!$kelompok || $kelompok->desa_id !== $user->desa_id) {
                return back()->withErrors(['kelompok_id' => 'Anda hanya bisa mengelola admin di desa Anda']);
            }
            $validated['desa_id'] = $user->desa_id;
        }
        
        // Auto-fill desa_id for admin kelompok if not set
        if ($validated['role'] === User::ROLE_ADMIN_KELOMPOK && !isset($validated['desa_id'])) {
            $kelompok = Kelompok::find($validated['kelompok_id']);
            $validated['desa_id'] = $kelompok->desa_id;
        }
        
        User::create($validated);
        
        return back()->with('success', 'Admin berhasil ditambahkan');
    }

    /**
     * Update specified admin
     */
    public function update(Request $request, User $admin)
    {
        $user = auth()->user();
        
        // Check permission
        if (!$user->canManageUser($admin)) {
            abort(403, 'Anda tidak bisa mengelola admin ini');
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => ['sometimes', 'string', Rule::unique('users')->ignore($admin->id)],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'is_active' => 'sometimes|boolean',
            'desa_id' => 'sometimes|nullable|exists:desas,id',
            'kelompok_id' => 'sometimes|nullable|exists:kelompoks,id',
        ]);
        
        // Remove password if not provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        
        $admin->update($validated);
        
        return back()->with('success', 'Admin berhasil diperbarui');
    }

    /**
     * Remove specified admin
     */
    public function destroy(User $admin)
    {
        $user = auth()->user();
        
        // Check permission
        if (!$user->canManageUser($admin)) {
            abort(403, 'Anda tidak bisa menghapus admin ini');
        }
        
        // Prevent deleting yourself
        if ($admin->id === $user->id) {
            return back()->withErrors(['delete' => 'Anda tidak bisa menghapus akun sendiri']);
        }
        
        $admin->delete();
        
        return back()->with('success', 'Admin berhasil dihapus');
    }
}
