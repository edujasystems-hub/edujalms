<script lang="ts">
    import type { Snippet } from 'svelte';
    import { page } from '@inertiajs/svelte';
    import AppContent from '@/components/AppContent.svelte';
    import AppShell from '@/components/AppShell.svelte';
    import AppSidebar from '@/components/AppSidebar.svelte';
    import AppSidebarHeader from '@/components/AppSidebarHeader.svelte';
    import EmployeeBottomNav from '@/components/EmployeeBottomNav.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();

    const user = $derived(page.props.auth?.user as any);
    const userRole = $derived((user?.role ?? '').toLowerCase());
    const isAdmin = $derived(
        userRole === 'admin' ||
        user?.email === 'admin@corporate.lms' ||
        user?.email === 'eduja.systems@gmail.com'
    );
</script>

<AppShell variant="sidebar">
    <AppSidebar />
    <AppContent
        variant="sidebar"
        class="min-w-0 overflow-x-clip {!isAdmin ? 'pb-24 md:pb-6' : ''}"
    >
        <AppSidebarHeader {breadcrumbs} />
        {@render children?.()}
    </AppContent>
    {#if !isAdmin}
        <EmployeeBottomNav />
    {/if}
    <Toaster />
</AppShell>
