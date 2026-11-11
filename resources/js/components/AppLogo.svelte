<script lang="ts">
    let {
        class: className = '',
    }: {
        class?: string;
    } = $props();
</script>

<div class="flex items-center gap-2 min-w-0 {className}">
    <img
        src="/images/logo.png"
        alt="Eduja Learning Media"
        class="h-8 max-h-9 w-auto max-w-[180px] object-contain group-data-[collapsible=icon]:hidden"
    />
    <img
        src="/images/logo-icon.png"
        alt="Eduja"
        class="size-7 mx-auto object-contain hidden group-data-[collapsible=icon]:block"
    />
</div>
