<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();
</script>

<img
    src="/images/logo-icon.png"
    alt="Eduja"
    class="{className} object-contain"
    {...rest}
/>
