import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Mirrors the role-helper semantics in App\Models\User (isSuperAdmin() etc.
 * includes developer) so role checks aren't re-implemented ad hoc per page.
 */
export function useAuth() {
    const page = usePage();
    const user = computed(() => page.props.auth?.user || {});
    const userRole = computed(() => user.value?.role);

    const isDeveloper = computed(() => userRole.value === 'developer');
    const isSuperAdmin = computed(() => userRole.value === 'super_admin' || isDeveloper.value);
    const isAdminDesa = computed(() => userRole.value === 'admin_desa');
    const isAdminKelompok = computed(() => userRole.value === 'admin_kelompok');

    return {
        user,
        userRole,
        isDeveloper,
        isSuperAdmin,
        isAdminDesa,
        isAdminKelompok,
    };
}
