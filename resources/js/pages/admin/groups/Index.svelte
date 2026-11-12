<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/admin/dashboard',
            },
            {
                title: 'Kategori Kelas',
                href: '/admin/groups',
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import DataTable, { type Column } from '@/components/DataTable.svelte';
    import Layers from '@lucide/svelte/icons/layers';
    import Plus from '@lucide/svelte/icons/plus';
    import Users from '@lucide/svelte/icons/users';
    import UsersRound from '@lucide/svelte/icons/users-round';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import X from '@lucide/svelte/icons/x';

    let {
        classGroups = [],
        allEmployees = [],
        allEmployeeGroups = [],
    }: {
        classGroups: any[];
        allEmployees: any[];
        allEmployeeGroups: any[];
    } = $props();

    const columns: Column[] = [
        { key: 'name', label: 'Nama Kategori & Deskripsi', sortable: true },
        { key: 'courses_count', label: 'Total Kelas', sortable: true, align: 'center', class: 'w-24' },
        { key: 'employee_groups_count', label: 'Group Karyawan', sortable: true, class: 'min-w-[190px]' },
        { key: 'employees_count', label: 'Karyawan Langsung', sortable: true, class: 'min-w-[170px]' },
        { key: 'is_active', label: 'Status', sortable: true, align: 'center', class: 'w-24' },
    ];

    // Create / Edit Group Modal
    let isGroupModalOpen = $state(false);
    let editingGroup = $state<any | null>(null);

    let groupForm = $state({
        name: '',
        description: '',
        badge_color: 'indigo',
        icon: 'sparkles',
        is_active: true,
    });

    function openCreateGroup() {
        editingGroup = null;
        groupForm = {
            name: '',
            description: '',
            badge_color: 'indigo',
            icon: 'sparkles',
            is_active: true,
        };
        isGroupModalOpen = true;
    }

    function openEditGroup(grp: any) {
        editingGroup = grp;
        groupForm = {
            name: grp.name,
            description: grp.description || '',
            badge_color: grp.badge_color || 'indigo',
            icon: grp.icon || 'sparkles',
            is_active: !!grp.is_active,
        };
        isGroupModalOpen = true;
    }

    let isSubmitting = $state(false);

    function submitGroup(e: SubmitEvent) {
        e.preventDefault();
        isSubmitting = true;

        if (editingGroup) {
            router.put(`/admin/groups/${editingGroup.id}`, groupForm, {
                onSuccess: () => (isGroupModalOpen = false),
                onFinish: () => (isSubmitting = false),
            });
        } else {
            router.post('/admin/groups', groupForm, {
                onSuccess: () => (isGroupModalOpen = false),
                onFinish: () => (isSubmitting = false),
            });
        }
    }

    function deleteGroup(grp: any) {
        if (confirm(`Hapus kategori '${grp.name}'? Kelas di dalamnya tidak akan terhapus, hanya dilepas dari kategori.`)) {
            router.delete(`/admin/groups/${grp.id}`);
        }
    }

    // Assign Employees Modal
    let isAssignModalOpen = $state(false);
    let targetGroup = $state<any | null>(null);
    let selectedUserIds = $state<number[]>([]);

    function openAssignModal(grp: any) {
        targetGroup = grp;
        selectedUserIds = [];
        isAssignModalOpen = true;
    }

    function toggleSelectUser(userId: number) {
        if (selectedUserIds.includes(userId)) {
            selectedUserIds = selectedUserIds.filter((id) => id !== userId);
        } else {
            selectedUserIds = [...selectedUserIds, userId];
        }
    }

    let isAssigning = $state(false);

    function submitAssignEmployees() {
        if (!targetGroup || selectedUserIds.length === 0) return;
        isAssigning = true;
        router.post(`/admin/groups/${targetGroup.id}/assign`, { user_ids: selectedUserIds }, {
            onSuccess: () => {
                isAssignModalOpen = false;
                selectedUserIds = [];
            },
            onFinish: () => (isAssigning = false),
        });
    }

    function removeEmployeeFromGroup(grp: any, emp: any) {
        if (confirm(`Lepas akses karyawan '${emp.name}' dari '${grp.name}'?`)) {
            router.delete(`/admin/groups/${grp.id}/remove-user/${emp.id}`);
        }
    }

    // Assign Employee Groups Modal
    let isAssignEmployeeGroupsModalOpen = $state(false);
    let targetClassGroupForEmployeeGroups = $state<any | null>(null);
    let selectedEmployeeGroupIds = $state<number[]>([]);

    function openAssignEmployeeGroupsModal(grp: any) {
        targetClassGroupForEmployeeGroups = grp;
        selectedEmployeeGroupIds = (grp.employee_groups || []).map((eg: any) => eg.id);
        isAssignEmployeeGroupsModalOpen = true;
    }

    function toggleSelectEmployeeGroup(egId: number) {
        if (selectedEmployeeGroupIds.includes(egId)) {
            selectedEmployeeGroupIds = selectedEmployeeGroupIds.filter((id) => id !== egId);
        } else {
            selectedEmployeeGroupIds = [...selectedEmployeeGroupIds, egId];
        }
    }

    let isAssigningEmployeeGroups = $state(false);

    function submitAssignEmployeeGroups() {
        if (!targetClassGroupForEmployeeGroups) return;
        isAssigningEmployeeGroups = true;
        router.post(`/admin/groups/${targetClassGroupForEmployeeGroups.id}/assign-employee-groups`, { employee_group_ids: selectedEmployeeGroupIds }, {
            onSuccess: () => {
                isAssignEmployeeGroupsModalOpen = false;
            },
            onFinish: () => (isAssigningEmployeeGroups = false),
        });
    }

    function removeEmployeeGroupFromClassGroup(grp: any, eg: any) {
        if (confirm(`Lepas group karyawan '${eg.name}' dari '${grp.name}'?`)) {
            router.delete(`/admin/groups/${grp.id}/remove-employee-group/${eg.id}`);
        }
    }
</script>

<AppHead title="Kategori Kelas - Corporate LMS" />

<div class="flex-1 space-y-5 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-foreground tracking-tight flex items-center gap-2">
                <Layers class="size-5 text-indigo-600" />
                <span>Kategori Kelas</span>
            </h1>
        </div>
        <button
            type="button"
            onclick={openCreateGroup}
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
        >
            <Plus class="size-4" />
            <span>Tambah Kategori</span>
        </button>
    </div>

    <!-- Reusable DataTable -->
    <DataTable
        data={classGroups}
        columns={columns}
        searchKeys={['name', 'description']}
        placeholder="Cari kategori kelas..."
        initialPageSize={10}
        pageSizeOptions={[5, 10, 25, 50]}
    >
        {#snippet cell({ row, column })}
            {#if column.key === 'name'}
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                            {row.name}
                        </span>
                    </div>
                    {#if row.description}
                        <p class="text-[11px] text-muted-foreground line-clamp-1 max-w-md">
                            {row.description}
                        </p>
                    {/if}
                </div>
            {:else if column.key === 'courses_count'}
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-muted font-medium text-[11px]">
                    <BookOpen class="size-3 text-indigo-500" />
                    <span>{row.courses_count ?? 0}</span>
                </span>
            {:else if column.key === 'employee_groups_count'}
                <div class="flex flex-wrap items-center gap-1.5">
                    {#if (row.employee_groups || []).length === 0}
                        <span class="text-[11px] text-muted-foreground italic">Belum ada group</span>
                    {:else}
                        {#each (row.employee_groups || []).slice(0, 2) as eg (eg.id)}
                            <button
                                type="button"
                                title="{eg.name} ({eg.employees_count ?? 0} orang) - klik untuk lepas"
                                onclick={() => removeEmployeeGroupFromClassGroup(row, eg)}
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-rose-50 hover:text-rose-600 transition-colors"
                            >
                                <UsersRound class="size-3 shrink-0" />
                                <span class="truncate max-w-[90px]">{eg.name}</span>
                            </button>
                        {/each}
                        {#if (row.employee_groups || []).length > 2}
                            <span class="text-[10px] font-mono text-muted-foreground font-semibold">
                                +{(row.employee_groups || []).length - 2}
                            </span>
                        {/if}
                    {/if}
                    <button
                        type="button"
                        onclick={() => openAssignEmployeeGroupsModal(row)}
                        title="Atur Group Karyawan"
                        class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-muted hover:bg-muted/80 text-foreground"
                    >
                        + Atur
                    </button>
                </div>
            {:else if column.key === 'employees_count'}
                <div class="flex items-center gap-2">
                    <div class="flex -space-x-1.5 overflow-hidden">
                        {#each (row.employees || []).slice(0, 3) as emp (emp.id)}
                            <button
                                type="button"
                                title="{emp.name} (klik untuk hapus)"
                                onclick={() => removeEmployeeFromGroup(row, emp)}
                                class="inline-block size-6 rounded-full ring-2 ring-card bg-muted text-[10px] font-bold flex items-center justify-center hover:opacity-75 transition-opacity"
                            >
                                {#if emp.avatar}
                                    <img src={emp.avatar} alt={emp.name} class="size-full rounded-full object-cover" />
                                {:else}
                                    {emp.name.charAt(0)}
                                {/if}
                            </button>
                        {/each}
                    </div>
                    <span class="text-[11px] text-muted-foreground">
                        {row.employees_count ?? 0} orang
                    </span>
                </div>
            {:else if column.key === 'is_active'}
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {row.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-muted text-muted-foreground'}">
                    {row.is_active ? 'Aktif' : 'Nonaktif'}
                </span>
            {/if}
        {/snippet}

        {#snippet actions({ row })}
            <div class="flex items-center justify-end gap-1.5">
                <button
                    type="button"
                    onclick={() => openAssignEmployeeGroupsModal(row)}
                    title="Assign Group Karyawan"
                    class="p-1.5 rounded-lg border border-border hover:bg-purple-50 dark:hover:bg-purple-950/50 hover:text-purple-600 transition-colors"
                >
                    <UsersRound class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => openAssignModal(row)}
                    title="Assign Karyawan Langsung"
                    class="p-1.5 rounded-lg border border-border hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 transition-colors"
                >
                    <UserPlus class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => openEditGroup(row)}
                    title="Edit"
                    class="p-1.5 rounded-lg border border-border hover:bg-muted text-foreground transition-colors"
                >
                    <Edit3 class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => deleteGroup(row)}
                    title="Hapus"
                    class="p-1.5 rounded-lg border border-border hover:bg-rose-50 dark:hover:bg-rose-950/50 text-rose-600 transition-colors"
                >
                    <Trash2 class="size-3.5" />
                </button>
            </div>
        {/snippet}
    </DataTable>
</div>

<!-- Modal: Buat / Edit Group -->
{#if isGroupModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    {editingGroup ? 'Edit Kategori Kelas' : 'Tambah Kategori Kelas'}
                </h3>
                <button
                    type="button"
                    onclick={() => (isGroupModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitGroup} class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-foreground mb-1">Nama Kategori *</label>
                    <input
                        type="text"
                        required
                        placeholder="Contoh: Digital Transformation Track"
                        bind:value={groupForm.name}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:border-indigo-500 focus:outline-hidden"
                    />
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Tema Warna</label>
                    <select
                        bind:value={groupForm.badge_color}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                    >
                        <option value="indigo">Indigo / Blue</option>
                        <option value="emerald">Emerald / Green</option>
                        <option value="amber">Amber / Orange</option>
                        <option value="violet">Violet / Purple</option>
                        <option value="rose">Rose / Red</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Deskripsi Singkat</label>
                    <textarea
                        rows="3"
                        placeholder="Tujuan program atau kurikulum..."
                        bind:value={groupForm.description}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isGroupModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isSubmitting}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs"
                    >
                        {isSubmitting ? 'Menyimpan...' : 'Simpan'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Modal: Assign Karyawan ke Group -->
{#if isAssignModalOpen && targetGroup}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <div>
                    <h3 class="text-base font-bold text-foreground">
                        Assign Karyawan ke Kategori
                    </h3>
                    <p class="text-xs text-indigo-600 font-semibold mt-0.5">
                        {targetGroup.name}
                    </p>
                </div>
                <button
                    type="button"
                    onclick={() => (isAssignModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <p class="text-xs text-muted-foreground">
                Karyawan yang dipilih otomatis mendapat akses ke <strong>seluruh {targetGroup.courses_count} kelas</strong> dalam kategori ini.
            </p>

            <div class="space-y-1.5 max-h-[300px] overflow-y-auto pr-1">
                {#each allEmployees as emp (emp.id)}
                    {@const isAlreadyAssigned = (targetGroup.employees || []).some((u: any) => u.id === emp.id)}
                    <label class="flex items-center gap-3 p-2.5 rounded-xl border border-border/70 hover:bg-muted/40 cursor-pointer {isAlreadyAssigned ? 'opacity-50 cursor-not-allowed bg-muted/20' : ''}">
                        <input
                            type="checkbox"
                            disabled={isAlreadyAssigned}
                            checked={selectedUserIds.includes(emp.id) || isAlreadyAssigned}
                            onchange={() => toggleSelectUser(emp.id)}
                            class="rounded border-border text-indigo-600 size-4"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-xs text-foreground truncate">{emp.name}</div>
                            <div class="text-[10px] text-muted-foreground truncate">{emp.email} • {emp.company?.name || 'Perusahaan'}</div>
                        </div>
                        {#if isAlreadyAssigned}
                            <span class="text-[10px] font-semibold text-emerald-600">Terdaftar</span>
                        {/if}
                    </label>
                {/each}
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-border/80">
                <span class="text-xs text-muted-foreground">
                    {selectedUserIds.length} karyawan dipilih
                </span>
                <div class="flex gap-2">
                    <button
                        type="button"
                        onclick={() => (isAssignModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        disabled={isAssigning || selectedUserIds.length === 0}
                        onclick={submitAssignEmployees}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs disabled:opacity-50"
                    >
                        {isAssigning ? 'Memproses...' : 'Simpan Akses'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}

<!-- Modal: Assign Group Karyawan ke Group Kelas Ini -->
{#if isAssignEmployeeGroupsModalOpen && targetClassGroupForEmployeeGroups}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <div>
                    <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                        <UsersRound class="size-4.5 text-purple-600" />
                        <span>Assign Group Karyawan</span>
                    </h3>
                    <p class="text-xs text-purple-600 font-semibold mt-0.5">
                        Kategori: {targetClassGroupForEmployeeGroups.name}
                    </p>
                </div>
                <button
                    type="button"
                    onclick={() => (isAssignEmployeeGroupsModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <p class="text-xs text-muted-foreground">
                Setiap karyawan di dalam group karyawan yang dipilih akan <strong>otomatis memperoleh akses</strong> ke seluruh {targetClassGroupForEmployeeGroups.courses_count ?? 0} kelas dalam kategori ini.
            </p>

            <div class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                {#if allEmployeeGroups.length === 0}
                    <div class="p-6 text-center text-muted-foreground text-xs">
                        Belum ada group karyawan yang dibuat. Buka menu Group Karyawan untuk membuatnya.
                    </div>
                {:else}
                    {#each allEmployeeGroups as eg (eg.id)}
                        {@const isSelected = selectedEmployeeGroupIds.includes(eg.id)}
                        <label
                            class="flex items-center justify-between p-3 rounded-xl border transition-all cursor-pointer {isSelected ? 'bg-purple-50/80 dark:bg-purple-950/60 border-purple-400 font-semibold shadow-xs' : 'border-border/70 hover:bg-muted/40'}"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <input
                                    type="checkbox"
                                    checked={isSelected}
                                    onchange={() => toggleSelectEmployeeGroup(eg.id)}
                                    class="size-4 rounded border-border text-purple-600 focus:ring-purple-500"
                                />
                                <div class="min-w-0">
                                    <div class="text-xs text-foreground font-bold flex items-center gap-2">
                                        <span>{eg.name}</span>
                                        {#if eg.code}
                                            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-muted text-muted-foreground font-normal">
                                                {eg.code}
                                            </span>
                                        {/if}
                                    </div>
                                    <div class="text-[11px] text-muted-foreground font-normal">
                                        {eg.employees_count ?? 0} Karyawan Terdaftar
                                    </div>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-muted text-muted-foreground shrink-0">
                                {eg.badge_color}
                            </span>
                        </label>
                    {/each}
                {/if}
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-border/80">
                <span class="text-xs text-muted-foreground">
                    {selectedEmployeeGroupIds.length} group karyawan dipilih
                </span>
                <div class="flex gap-2">
                    <button
                        type="button"
                        onclick={() => (isAssignEmployeeGroupsModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        disabled={isAssigningEmployeeGroups}
                        onclick={submitAssignEmployeeGroups}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-purple-600 hover:bg-purple-500 text-white shadow-xs disabled:opacity-50"
                    >
                        {isAssigningEmployeeGroups ? 'Memproses...' : 'Simpan Group'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}
