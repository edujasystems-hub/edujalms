<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import {
        SidebarGroup,
        SidebarGroupLabel,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import type { NavItem } from '@/types';

    let {
        items = [],
        label = 'Platform',
    }: {
        items: NavItem[];
        label?: string;
    } = $props();

    const url = currentUrlState();

    function isItemActive(href: any): boolean {
        const urlStr = toUrl(href);
        if (urlStr === '/dashboard' || urlStr === '/admin/dashboard' || urlStr === '/my-summary' || urlStr === '/my-courses') {
            return url.isCurrentUrl(href, url.currentUrl) || (urlStr === '/admin/dashboard' && url.currentUrl === '/dashboard');
        }
        return url.isCurrentOrParentUrl(href, url.currentUrl);
    }
</script>

<SidebarGroup class="px-2 py-0">
    {#if label}
        <SidebarGroupLabel>{label}</SidebarGroupLabel>
    {/if}
    <SidebarMenu>
        {#each items as item (toUrl(item.href))}
            {@const Icon = item.icon}
            <SidebarMenuItem>
                <SidebarMenuButton
                    asChild
                    isActive={isItemActive(item.href)}
                    tooltip={item.title}
                >
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl(item.href)}
                            class={props.class}
                        >
                            {#if Icon}
                                <Icon class="size-4 shrink-0" />
                            {/if}
                            <span>{item.title}</span>
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        {/each}
    </SidebarMenu>
</SidebarGroup>

