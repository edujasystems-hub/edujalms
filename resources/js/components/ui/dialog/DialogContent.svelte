<script lang="ts">
    import type { Snippet } from 'svelte';
    import { getContext } from 'svelte';
    import { cn } from '@/lib/utils';
    import { DIALOG_CONTEXT, type DialogContext } from './context';

    let { class: className = '', children }: { class?: string; children?: Snippet } = $props();

    const { open, setOpen } = getContext<DialogContext>(DIALOG_CONTEXT);

    const close = () => setOpen(false);
</script>

{#if open()}
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <button
            type="button"
            class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
            aria-label="Close"
            onclick={close}
        ></button>
        <div
            class={cn(
                'relative z-10 w-full max-w-lg border bg-background p-6 shadow-xl transition-all',
                'rounded-t-2xl sm:rounded-2xl border-b-0 sm:border-b max-h-[88vh] sm:max-h-[85vh] overflow-y-auto',
                'animate-in slide-in-from-bottom duration-300 sm:slide-in-from-bottom-0 sm:zoom-in-95',
                'pb-[max(1.5rem,env(safe-area-inset-bottom))] sm:pb-6',
                className,
            )}
            role="dialog"
            aria-modal="true"
        >
            <!-- Mobile Bottom Sheet Drag Handle -->
            <div class="flex sm:hidden justify-center pt-0 pb-3 -mt-2" aria-hidden="true">
                <div class="h-1.5 w-12 rounded-full bg-muted-foreground/30"></div>
            </div>
            {@render children?.()}
        </div>
    </div>
{/if}
