<script lang="ts">
    import { page, Link } from '@inertiajs/svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import { SidebarTrigger } from '@/components/ui/sidebar';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
    }: {
        breadcrumbs?: BreadcrumbItem[];
    } = $props();

    const user = $derived(page.props.auth?.user as any);
    const userRole = $derived((user?.role ?? '').toLowerCase());
    const isAdmin = $derived(
        userRole === 'admin' ||
        user?.email === 'admin@corporate.lms' ||
        user?.email === 'eduja.systems@gmail.com'
    );
</script>

<header
    class="flex h-14 md:h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-4 md:px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12"
>
    <div class="flex items-center gap-2 min-w-0">
        <!-- Sidebar Trigger: Admin always, or desktop -->
        <SidebarTrigger class="-ml-1 {isAdmin ? '' : 'hidden md:inline-flex'}" />

        <!-- Mobile brand mark for employee -->
        {#if !isAdmin}
            <Link href="/my-summary" class="flex md:hidden items-center gap-2 py-1 pr-1 font-extrabold text-foreground tracking-tight shrink-0">
                <AppLogoIcon class="size-6 object-contain" />
                <span class="text-sm font-black bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Eduja</span>
            </Link>
        {/if}

        {#if breadcrumbs && breadcrumbs.length > 0}
            <Breadcrumbs {breadcrumbs} />
        {/if}
    </div>

    <!-- Right Header Elements for Mobile / Employee -->
    {#if !isAdmin && user}
        <div class="flex items-center gap-2 md:hidden shrink-0">
            {#if user.company}
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900 truncate max-w-[120px]">
                    {user.company.name}
                </span>
            {/if}
            <Link href="/settings/profile" class="size-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                {user.name ? user.name.charAt(0).toUpperCase() : 'U'}
            </Link>
        </div>
    {/if}
</header>
