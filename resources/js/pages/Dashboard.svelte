<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/admin/dashboard',
            },
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminDashboard from './admin/Dashboard.svelte';

    let props = $props();
</script>

<AdminDashboard {...props} />
