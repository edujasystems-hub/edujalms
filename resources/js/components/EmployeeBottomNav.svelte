<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Trophy from '@lucide/svelte/icons/trophy';
    import CircleUser from '@lucide/svelte/icons/circle-user';

    const url = currentUrlState();

    interface NavItem {
        href: string;
        label: string;
        icon: any;
        matcher: (current: string) => boolean;
    }

    const items: NavItem[] = [
        {
            href: '/my-summary',
            label: 'Summary',
            icon: LayoutGrid,
            matcher: (cur) => cur === '/my-summary' || cur === '/dashboard',
        },
        {
            href: '/my-courses',
            label: 'Kelas',
            icon: BookOpen,
            matcher: (cur) => cur.startsWith('/my-courses') || cur.startsWith('/learn'),
        },
        {
            href: '/leaderboard',
            label: 'Klasemen',
            icon: Trophy,
            matcher: (cur) => cur === '/leaderboard',
        },
        {
            href: '/settings/profile',
            label: 'Akun',
            icon: CircleUser,
            matcher: (cur) => cur.startsWith('/settings'),
        },
    ];
</script>

<nav
    class="fixed bottom-0 inset-x-0 z-40 md:hidden bg-card/95 backdrop-blur-md border-t border-border/80 shadow-[0_-4px_20px_rgba(0,0,0,0.07)] pb-[max(0.5rem,env(safe-area-inset-bottom))]"
    aria-label="Navigasi Menu Karyawan"
>
    <div class="grid grid-cols-4 h-16 max-w-md mx-auto px-1 items-center">
        {#each items as item}
            {@const Icon = item.icon}
            {@const active = item.matcher(url.currentUrl)}
            <Link
                href={item.href}
                class="flex flex-col items-center justify-center gap-1 h-full py-1.5 relative select-none active:scale-95 transition-all duration-150 {active ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-muted-foreground hover:text-foreground font-medium'}"
            >
                {#if active}
                    <!-- Top Active Bar Indicator -->
                    <span class="absolute top-0 inset-x-5 h-0.75 bg-indigo-600 dark:bg-indigo-400 rounded-full"></span>
                {/if}

                <div class="relative px-3 py-1 rounded-full transition-colors duration-200 {active ? 'bg-indigo-100/80 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400' : ''}">
                    <Icon class="size-5 transition-transform duration-200 {active ? 'scale-110' : ''}" />
                </div>

                <span class="text-[11px] tracking-tight leading-none {active ? 'font-bold' : ''}">
                    {item.label}
                </span>
            </Link>
        {/each}
    </div>
</nav>
