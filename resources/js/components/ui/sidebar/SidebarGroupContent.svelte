<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    let {
        class: className = '',
        children,
    }: {
        class?: string;
        children?: Snippet;
    } = $props();
</script>

<div data-slot="sidebar-group-content" data-sidebar="group-content" class={cn('w-full text-sm', className)}>
    {@render children?.()}
</div>

