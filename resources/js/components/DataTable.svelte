<script lang="ts" generics="T extends Record<string, any>">
    import type { Snippet } from 'svelte';
    import ArrowDown from '@lucide/svelte/icons/arrow-down';
    import ArrowUp from '@lucide/svelte/icons/arrow-up';
    import ArrowUpDown from '@lucide/svelte/icons/arrow-up-down';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import ChevronsLeft from '@lucide/svelte/icons/chevrons-left';
    import ChevronsRight from '@lucide/svelte/icons/chevrons-right';
    import Search from '@lucide/svelte/icons/search';

    export interface Column<R = any> {
        key: string;
        label: string;
        sortable?: boolean;
        class?: string;
        headerClass?: string;
        align?: 'left' | 'center' | 'right';
    }

    let {
        data = [],
        columns = [],
        searchKeys = [],
        initialPageSize = 10,
        pageSizeOptions = [5, 10, 25, 50],
        placeholder = 'Cari data...',
        headerActions,
        cell,
        actions,
        emptyText = 'Tidak ada data ditemukan.',
    }: {
        data: T[];
        columns: Column<T>[];
        searchKeys?: string[];
        initialPageSize?: number;
        pageSizeOptions?: number[];
        placeholder?: string;
        headerActions?: Snippet;
        cell?: Snippet<[{ row: T; column: Column<T>; index: number }]>;
        actions?: Snippet<[{ row: T; index: number }]>;
        emptyText?: string;
    } = $props();

    let search = $state('');
    let sortKey = $state<string | null>(null);
    let sortDirection = $state<'asc' | 'desc'>('asc');
    let currentPage = $state(1);
    let pageSize = $state(initialPageSize);

    function toggleSort(col: Column<T>) {
        if (col.sortable === false) return;
        if (sortKey === col.key) {
            if (sortDirection === 'asc') {
                sortDirection = 'desc';
            } else {
                sortKey = null;
                sortDirection = 'asc';
            }
        } else {
            sortKey = col.key;
            sortDirection = 'asc';
        }
    }

    // 1. Filtered data
    const filteredData = $derived.by(() => {
        const query = search.trim().toLowerCase();
        if (!query) return data;

        return data.filter((row) => {
            const keysToSearch = searchKeys.length > 0 ? searchKeys : columns.map((c) => c.key);
            return keysToSearch.some((k) => {
                const val = (row as any)[k];
                if (val === null || val === undefined) return false;
                return String(val).toLowerCase().includes(query);
            });
        });
    });

    // 2. Sorted data
    const sortedData = $derived.by(() => {
        if (!sortKey) return filteredData;

        return [...filteredData].sort((a, b) => {
            const aVal = (a as any)[sortKey!];
            const bVal = (b as any)[sortKey!];

            if (aVal === bVal) return 0;
            if (aVal === null || aVal === undefined) return 1;
            if (bVal === null || bVal === undefined) return -1;

            if (typeof aVal === 'number' && typeof bVal === 'number') {
                return sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
            }

            const strA = String(aVal).toLowerCase();
            const strB = String(bVal).toLowerCase();

            return sortDirection === 'asc'
                ? strA.localeCompare(strB, 'id')
                : strB.localeCompare(strA, 'id');
        });
    });

    // 3. Paginated data
    const totalRecords = $derived(sortedData.length);
    const totalPages = $derived(Math.max(1, Math.ceil(totalRecords / pageSize)));

    // Keep currentPage within bounds when data changes
    $effect(() => {
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }
        if (currentPage < 1) {
            currentPage = 1;
        }
    });

    const paginatedData = $derived.by(() => {
        const start = (currentPage - 1) * pageSize;
        return sortedData.slice(start, start + pageSize);
    });

    const startRecord = $derived(totalRecords === 0 ? 0 : (currentPage - 1) * pageSize + 1);
    const endRecord = $derived(Math.min(currentPage * pageSize, totalRecords));
</script>

<div class="space-y-4">
    <!-- Top Bar: Search, Page Size, Header Actions -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3 flex-1">
            <div class="relative w-full max-w-xs">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                <input
                    type="text"
                    bind:value={search}
                    placeholder={placeholder}
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-card border border-border focus:border-indigo-500 focus:outline-hidden transition-colors"
                />
            </div>

            <div class="flex items-center gap-2 text-xs text-muted-foreground shrink-0">
                <span class="hidden sm:inline">Tampilkan:</span>
                <select
                    bind:value={pageSize}
                    class="pl-2 pr-6 py-1.5 text-xs rounded-lg bg-card border border-border text-foreground focus:outline-hidden focus:border-indigo-500"
                >
                    {#each pageSizeOptions as opt}
                        <option value={opt}>{opt}</option>
                    {/each}
                </select>
            </div>
        </div>

        {#if headerActions}
            <div class="shrink-0 flex items-center gap-2">
                {@render headerActions()}
            </div>
        {/if}
    </div>

    <!-- Table Container -->
    <div class="rounded-2xl border border-border bg-card overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-border bg-muted/30 text-muted-foreground font-semibold">
                    <tr>
                        {#each columns as col (col.key)}
                            {@const isSortable = col.sortable !== false}
                            {@const isSorted = sortKey === col.key}
                            <th
                                class="px-4 py-3 select-none {col.headerClass ?? ''} {isSortable ? 'cursor-pointer hover:text-foreground' : ''}"
                                onclick={() => toggleSort(col)}
                            >
                                <div class="inline-flex items-center gap-1.5 {col.align === 'right' ? 'justify-end w-full' : col.align === 'center' ? 'justify-center w-full' : ''}">
                                    <span>{col.label}</span>
                                    {#if isSortable}
                                        {#if isSorted}
                                            {#if sortDirection === 'asc'}
                                                <ArrowUp class="size-3 text-indigo-600" />
                                            {:else}
                                                <ArrowDown class="size-3 text-indigo-600" />
                                            {/if}
                                        {:else}
                                            <ArrowUpDown class="size-3 opacity-30 group-hover:opacity-60" />
                                        {/if}
                                    {/if}
                                </div>
                            </th>
                        {/each}
                        {#if actions}
                            <th class="px-4 py-3 text-right">Aksi</th>
                        {/if}
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60">
                    {#if paginatedData.length === 0}
                        <tr>
                            <td colspan={columns.length + (actions ? 1 : 0)} class="px-4 py-8 text-center text-muted-foreground">
                                {emptyText}
                            </td>
                        </tr>
                    {:else}
                        {#each paginatedData as row, idx ((row as any).id ?? idx)}
                            <tr class="hover:bg-muted/20 transition-colors">
                                {#each columns as col (col.key)}
                                    <td class="px-4 py-3 {col.class ?? ''} {col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : ''}">
                                        {#if cell}
                                            {@render cell({ row, column: col, index: idx })}
                                        {:else}
                                            {(row as any)[col.key] ?? '-'}
                                        {/if}
                                    </td>
                                {/each}
                                {#if actions}
                                    <td class="px-4 py-3 text-right">
                                        {@render actions({ row, index: idx })}
                                    </td>
                                {/if}
                            </tr>
                        {/each}
                    {/if}
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="px-4 py-3 border-t border-border flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-muted-foreground">
            <div>
                Menampilkan <span class="font-semibold text-foreground">{startRecord}</span> - <span class="font-semibold text-foreground">{endRecord}</span> dari <span class="font-semibold text-foreground">{totalRecords}</span>
            </div>

            <div class="flex items-center gap-1">
                <button
                    type="button"
                    onclick={() => (currentPage = 1)}
                    disabled={currentPage === 1}
                    aria-label="Halaman Pertama"
                    class="p-1.5 rounded-lg border border-border hover:bg-muted disabled:opacity-30 disabled:pointer-events-none transition-colors"
                >
                    <ChevronsLeft class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => currentPage--}
                    disabled={currentPage === 1}
                    aria-label="Sebelumnya"
                    class="p-1.5 rounded-lg border border-border hover:bg-muted disabled:opacity-30 disabled:pointer-events-none transition-colors"
                >
                    <ChevronLeft class="size-3.5" />
                </button>

                <div class="px-2 font-medium">
                    {currentPage} / {totalPages}
                </div>

                <button
                    type="button"
                    onclick={() => currentPage++}
                    disabled={currentPage === totalPages}
                    aria-label="Selanjutnya"
                    class="p-1.5 rounded-lg border border-border hover:bg-muted disabled:opacity-30 disabled:pointer-events-none transition-colors"
                >
                    <ChevronRight class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => (currentPage = totalPages)}
                    disabled={currentPage === totalPages}
                    aria-label="Halaman Terakhir"
                    class="p-1.5 rounded-lg border border-border hover:bg-muted disabled:opacity-30 disabled:pointer-events-none transition-colors"
                >
                    <ChevronsRight class="size-3.5" />
                </button>
            </div>
        </div>
    </div>
</div>
