<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    let { class: className = '', children }: { class?: string; children?: Snippet } = $props();
</script>

<ol class={cn('flex flex-wrap items-center gap-1.5', className)}>
    {@render children?.()}
</ol>

