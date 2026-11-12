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
    <!-- Mobile View: Hanya Menampilkan Logo (Kiri) -->
    <div class="flex md:hidden items-center">
        <Link
            href={isAdmin ? '/admin/dashboard' : '/my-summary'}
            class="flex items-center gap-2 py-1 font-extrabold text-foreground tracking-tight shrink-0"
        >
            <AppLogoIcon class="size-7 object-contain" />
            <span class="text-base font-black bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Eduja</span>
        </Link>
    </div>

    <!-- Desktop View: Sidebar Trigger + Breadcrumbs (Kiri) -->
    <div class="hidden md:flex items-center gap-2 min-w-0">
        <SidebarTrigger class="-ml-1" />
        {#if breadcrumbs && breadcrumbs.length > 0}
            <Breadcrumbs {breadcrumbs} />
        {/if}
    </div>

    <!-- Mobile View: Hanya Menampilkan Profile Picture User (Kanan) -->
    <div class="flex md:hidden items-center shrink-0">
        <Link
            href="/settings/profile"
            class="size-8 rounded-full overflow-hidden border border-border/80 shadow-xs flex items-center justify-center bg-indigo-600 text-white font-bold text-xs ring-2 ring-background transition-transform active:scale-95"
            aria-label="Profil Akun"
        >
            {#if user?.avatar}
                <img src={user.avatar} alt={user?.name || 'User'} class="size-full object-cover" />
            {:else}
                <span>{user?.name ? user.name.charAt(0).toUpperCase() : 'U'}</span>
            {/if}
        </Link>
    </div>
</header>
