<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    let { class: className = '', children }: { class?: string; children?: Snippet } = $props();
</script>

<nav aria-label="breadcrumb" class={cn('text-sm', className)}>
    {@render children?.()}
</nav>

