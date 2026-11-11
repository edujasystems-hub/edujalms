<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Award from '@lucide/svelte/icons/award';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Building2 from '@lucide/svelte/icons/building-2';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';
    import Layers from '@lucide/svelte/icons/layers';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import Users from '@lucide/svelte/icons/users';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
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
    const userRole = $derived((user?.role ?? '').toLowerCase());
    const isAdmin = $derived(userRole === 'admin');

    const adminNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/admin/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Kelas',
            href: '/admin/courses',
            icon: GraduationCap,
        },
        {
            title: 'Kategori',
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
            title: 'Karyawan',
            href: '/admin/employees',
            icon: Users,
        },
    ];

    const employeeNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/my-summary',
            icon: LayoutGrid,
        },
        {
            title: 'Kelas Saya',
            href: '/my-courses',
            icon: BookOpen,
        },
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={isAdmin ? '/admin/dashboard' : '/my-summary'}
                            class={props.class}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent class="py-2">
        {#if isAdmin}
            <NavMain items={adminNavItems} label="Admin Panel" />

            <div class="mt-4 pt-2 border-t border-sidebar-border/60">
                <NavMain items={employeeNavItems} label="Portal Karyawan" />
            </div>
        {:else}
            <NavMain items={employeeNavItems} label="Menu Utama" />
        {/if}
    </SidebarContent>

    <SidebarFooter>
        <div class="px-3 py-2 text-xs border-b border-sidebar-border/50 mb-1">
            <div class="flex items-center justify-between">
                <span class="text-muted-foreground font-medium">Peran:</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {isAdmin ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'}">
                    {isAdmin ? 'Admin' : 'Employee'}
                </span>
            </div>
            {#if user?.company}
                <div class="truncate text-[11px] mt-1 text-muted-foreground flex items-center gap-1.5">
                    <span>🏢</span>
                    <span class="truncate">{user.company.name}</span>
                </div>
            {/if}
        </div>
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
