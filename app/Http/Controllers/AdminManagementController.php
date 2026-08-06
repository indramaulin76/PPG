<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminStoreRequest;
use App\Http\Requests\AdminUpdateRequest;
use App\Models\Desa;
use App\Models\Kelompok;
use App\Models\User;
use Illuminate\Http\Request;
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

        if ($user->isSuperAdmin()) {
            // Covers both Developer and Super Admin: see all admin levels (except themselves)
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
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('username', 'like', '%'.$request->search.'%');
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
        $allowedRoles = $user->allowedRolesToManage();

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
    public function store(AdminStoreRequest $request)
    {
        $user = auth()->user();

        $validated = $request->validated();

        // Extract role and is_active before mass assignment
        $role = $validated['role'];
        $isActive = $validated['is_active'] ?? true;
        unset($validated['role'], $validated['is_active']);

        // Fix desa_id validation for Super Admin - it should be null
        if ($role === User::ROLE_SUPER_ADMIN) {
            $validated['desa_id'] = null;
            $validated['kelompok_id'] = null;
        }

        // Security check: Admin desa can't create admin desa
        if ($user->isAdminDesa() && $role === User::ROLE_ADMIN_DESA) {
            return back()->withErrors(['role' => 'Anda tidak bisa membuat Admin Desa']);
        }

        // Security check: Admin desa can only create admin kelompok in their desa
        if ($user->isAdminDesa()) {
            $kelompok = Kelompok::find($validated['kelompok_id']);
            if (! $kelompok || $kelompok->desa_id !== $user->desa_id) {
                return back()->withErrors(['kelompok_id' => 'Anda hanya bisa mengelola admin di desa Anda']);
            }
            $validated['desa_id'] = $user->desa_id;
        }

        // Auto-fill desa_id for admin kelompok if not set
        if ($role === User::ROLE_ADMIN_KELOMPOK && ! isset($validated['desa_id'])) {
            $kelompok = Kelompok::find($validated['kelompok_id']);
            $validated['desa_id'] = $kelompok->desa_id;
        }

        $admin = User::create($validated);
        $admin->setRole($role);
        $admin->setIsActive($isActive);
        $admin->save();

        return back()->with('success', 'Admin berhasil ditambahkan');
    }

    /**
     * Update specified admin
     */
    public function update(AdminUpdateRequest $request, User $admin)
    {
        $user = auth()->user();

        // Check permission
        if (! $user->canManageUser($admin)) {
            abort(403, 'Anda tidak bisa mengelola admin ini');
        }

        $validated = $request->validated();

        // Scope guard: Admin Desa can only manage admins within their own desa.
        // A malicious request could otherwise move a kelompok admin to another desa.
        if ($user->isAdminDesa()) {
            $targetDesaId = $validated['desa_id'] ?? $admin->desa_id;
            if ($targetDesaId !== $user->desa_id) {
                return back()->withErrors(['desa_id' => 'Anda hanya bisa mengelola admin di desa Anda']);
            }

            if (isset($validated['kelompok_id'])) {
                $kelompok = Kelompok::find($validated['kelompok_id']);
                if (! $kelompok || $kelompok->desa_id !== $user->desa_id) {
                    return back()->withErrors(['kelompok_id' => 'Anda hanya bisa mengelola admin di desa Anda']);
                }
            }
        }

        // Extract is_active before mass assignment
        $isActive = $validated['is_active'] ?? null;
        unset($validated['is_active']);

        // Remove password if not provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $admin->update($validated);

        if ($isActive !== null) {
            $admin->setIsActive($isActive);
            $admin->save();
        }

        return back()->with('success', 'Admin berhasil diperbarui');
    }

    /**
     * Remove specified admin
     */
    public function destroy(User $admin)
    {
        $user = auth()->user();

        // Check permission
        if (! $user->canManageUser($admin)) {
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
