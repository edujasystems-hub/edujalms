<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { home } from '@/routes';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();
</script>

<div
    class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
>
    <div class="w-full max-w-sm">
        <div class="flex flex-col gap-8">
            <div class="flex flex-col items-center gap-4">
                <Link
                    href={home()}
                    class="flex flex-col items-center gap-2 font-medium"
                >
                    <img
                        src="/images/logo.png"
                        alt="Eduja Learning Media"
                        class="h-14 md:h-16 w-auto object-contain hover:opacity-95 transition-opacity"
                    />
                    <span class="sr-only">{title}</span>
                </Link>
                <div class="space-y-1.5 text-center">
                    <h1 class="text-xl font-bold tracking-tight text-foreground">{title}</h1>
                    <p class="text-center text-xs text-muted-foreground">
                        {description}
                    </p>
                </div>
            </div>
            {@render children?.()}
        </div>
    </div>
</div>
