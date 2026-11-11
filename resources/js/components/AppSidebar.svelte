<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Award from '@lucide/svelte/icons/award';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Building2 from '@lucide/svelte/icons/building-2';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';
    import Layers from '@lucide/svelte/icons/layers';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import Users from '@lucide/svelte/icons/users';
    import Compass from '@lucide/svelte/icons/compass';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const user = $derived(page.props.auth?.user as any);
    const isAdmin = $derived(user?.role === 'admin');

    const adminNavItems: NavItem[] = [
        {
            title: 'Dashboard Admin',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Pengelolaan Kelas',
            href: '/admin/courses',
            icon: GraduationCap,
        },
        {
            title: 'Group & Kategori Kelas',
            href: '/admin/groups',
            icon: Layers,
        },
        {
            title: 'Tutor',
            href: '/admin/tutors',
            icon: Award,
        },
        {
            title: 'Perusahaan',
            href: '/admin/companies',
            icon: Building2,
        },
        {
            title: 'Karyawan & Akses',
            href: '/admin/employees',
            icon: Users,
        },
    ];

    const employeeNavItems: NavItem[] = [
        {
            title: 'My Summary',
            href: '/my-summary',
            icon: LayoutGrid,
        },
        {
            title: 'Kelas Saya',
            href: '/my-courses',
            icon: BookOpen,
        },
    ];

    const navItems = $derived(isAdmin ? adminNavItems : employeeNavItems);
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href="/dashboard"
                            class={props.class}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={navItems} />

        {#if isAdmin}
            <div class="px-4 py-2 mt-4 border-t border-sidebar-border/50">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Portal Karyawan</span>
                <div class="mt-2 space-y-1">
                    <Link
                        href="/my-summary"
                        class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-medium text-sidebar-foreground/80 hover:text-sidebar-foreground hover:bg-sidebar-accent rounded-md transition-colors"
                    >
                        <Compass class="size-3.5 text-indigo-500" />
                        <span>Pratinjau My Summary</span>
                    </Link>
                    <Link
                        href="/my-courses"
                        class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-medium text-sidebar-foreground/80 hover:text-sidebar-foreground hover:bg-sidebar-accent rounded-md transition-colors"
                    >
                        <Sparkles class="size-3.5 text-amber-500" />
                        <span>Pratinjau Kelas Saya</span>
                    </Link>
                </div>
            </div>
        {/if}
    </SidebarContent>

    <SidebarFooter>
        <div class="px-3 py-2 text-xs text-muted-foreground border-b border-sidebar-border/50 mb-1">
            <div class="flex items-center justify-between">
                <span class="font-medium text-sidebar-foreground">Role:</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {isAdmin ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'}">
                    {user?.role?.toUpperCase() ?? 'GUEST'}
                </span>
            </div>
            {#if user?.company}
                <div class="truncate text-[11px] mt-1 text-sidebar-foreground/70">
                    🏢 {user.company.name}
                </div>
            {/if}
        </div>
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
