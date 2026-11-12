<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/admin/dashboard',
            },
            {
                title: 'Karyawan',
                href: '/admin/employees',
            },
            {
                title: 'Group Karyawan',
                href: '/admin/employee-groups',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import DataTable, { type Column } from '@/components/DataTable.svelte';
    import UsersRound from '@lucide/svelte/icons/users-round';
    import Users from '@lucide/svelte/icons/users';
    import Plus from '@lucide/svelte/icons/plus';
    import Layers from '@lucide/svelte/icons/layers';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import Building2 from '@lucide/svelte/icons/building-2';
    import Search from '@lucide/svelte/icons/search';
    import X from '@lucide/svelte/icons/x';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import FolderPlus from '@lucide/svelte/icons/folder-plus';

    let {
        employeeGroups = [],
        allEmployees = [],
        allClassGroups = [],
        companies = [],
    }: {
        employeeGroups: any[];
        allEmployees: any[];
        allClassGroups: any[];
        companies: any[];
    } = $props();

    const columns: Column[] = [
        { key: 'name', label: 'Nama Group Karyawan', sortable: true },
        { key: 'company', label: 'Kantor / Cabang', sortable: true, class: 'w-44' },
        { key: 'employees_count', label: 'Anggota Karyawan', sortable: true, class: 'min-w-[180px]' },
        { key: 'class_groups_count', label: 'Group Kelas Ter-assign', sortable: true, class: 'min-w-[220px]' },
        { key: 'is_active', label: 'Status', sortable: true, align: 'center', class: 'w-24' },
    ];

    // Color palettes for badge
    const colorClasses: Record<string, string> = {
        indigo: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
        blue: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border-blue-200 dark:border-blue-800',
        emerald: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
        amber: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border-amber-200 dark:border-amber-800',
        purple: 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 border-purple-200 dark:border-purple-800',
        rose: 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 border-rose-200 dark:border-rose-800',
        slate: 'bg-slate-100 text-slate-700 dark:bg-slate-900 dark:text-slate-300 border-slate-200 dark:border-slate-800',
    };

    // Modal: Create / Edit Employee Group
    let isGroupModalOpen = $state(false);
    let editingGroup = $state<any | null>(null);

    let groupForm = $state({
        name: '',
        code: '',
        company_id: '',
        description: '',
        badge_color: 'indigo',
        is_active: true,
        user_ids: [] as number[],
        class_group_ids: [] as number[],
    });

    function openCreateGroup() {
        editingGroup = null;
        groupForm = {
            name: '',
            code: '',
            company_id: '',
            description: '',
            badge_color: 'indigo',
            is_active: true,
            user_ids: [],
            class_group_ids: [],
        };
        isGroupModalOpen = true;
    }

    function openEditGroup(grp: any) {
        editingGroup = grp;
        groupForm = {
            name: grp.name,
            code: grp.code || '',
            company_id: grp.company_id ? String(grp.company_id) : '',
            description: grp.description || '',
            badge_color: grp.badge_color || 'indigo',
            is_active: !!grp.is_active,
            user_ids: [],
            class_group_ids: [],
        };
        isGroupModalOpen = true;
    }

    let isSubmitting = $state(false);

    function submitGroup(e: SubmitEvent) {
        e.preventDefault();
        isSubmitting = true;

        if (editingGroup) {
            router.put(`/admin/employee-groups/${editingGroup.id}`, groupForm, {
                onSuccess: () => (isGroupModalOpen = false),
                onFinish: () => (isSubmitting = false),
            });
        } else {
            router.post('/admin/employee-groups', groupForm, {
                onSuccess: () => (isGroupModalOpen = false),
                onFinish: () => (isSubmitting = false),
            });
        }
    }

    function deleteGroup(grp: any) {
        if (confirm(`Hapus group karyawan '${grp.name}'? Akses group kelas yang terhubung ke karyawan di dalam group ini akan otomatis disesuaikan.`)) {
            router.delete(`/admin/employee-groups/${grp.id}`);
        }
    }

    // Modal: Manage Employees in Group
    let isEmployeesModalOpen = $state(false);
    let targetGroupForEmployees = $state<any | null>(null);
    let selectedUserIds = $state<number[]>([]);
    let employeeSearch = $state('');

    function openManageEmployeesModal(grp: any) {
        targetGroupForEmployees = grp;
        selectedUserIds = (grp.employees || []).map((e: any) => e.id);
        employeeSearch = '';
        isEmployeesModalOpen = true;
    }

    function toggleSelectUser(userId: number) {
        if (selectedUserIds.includes(userId)) {
            selectedUserIds = selectedUserIds.filter((id) => id !== userId);
        } else {
            selectedUserIds = [...selectedUserIds, userId];
        }
    }

    let isSavingEmployees = $state(false);

    function submitSaveEmployees() {
        if (!targetGroupForEmployees) return;
        isSavingEmployees = true;
        router.post(`/admin/employee-groups/${targetGroupForEmployees.id}/assign-employees`, { user_ids: selectedUserIds }, {
            onSuccess: () => (isEmployeesModalOpen = false),
            onFinish: () => (isSavingEmployees = false),
        });
    }

    function removeEmployeeFromGroup(grp: any, emp: any) {
        if (confirm(`Lepas karyawan '${emp.name}' dari group '${grp.name}'?`)) {
            router.delete(`/admin/employee-groups/${grp.id}/remove-employee/${emp.id}`);
        }
    }

    const filteredEmployees = $derived(
        allEmployees.filter((emp) => {
            const matchesQuery =
                emp.name.toLowerCase().includes(employeeSearch.toLowerCase()) ||
                emp.email.toLowerCase().includes(employeeSearch.toLowerCase()) ||
                (emp.position && emp.position.toLowerCase().includes(employeeSearch.toLowerCase()));
            const matchesCompany =
                !targetGroupForEmployees?.company_id ||
                emp.company_id === targetGroupForEmployees.company_id;
            return matchesQuery && matchesCompany;
        })
    );

    // Modal: Assign Class Groups to Employee Group
    let isClassGroupsModalOpen = $state(false);
    let targetGroupForClasses = $state<any | null>(null);
    let selectedClassGroupIds = $state<number[]>([]);

    function openAssignClassGroupsModal(grp: any) {
        targetGroupForClasses = grp;
        selectedClassGroupIds = (grp.class_groups || []).map((cg: any) => cg.id);
        isClassGroupsModalOpen = true;
    }

    function toggleSelectClassGroup(cgId: number) {
        if (selectedClassGroupIds.includes(cgId)) {
            selectedClassGroupIds = selectedClassGroupIds.filter((id) => id !== cgId);
        } else {
            selectedClassGroupIds = [...selectedClassGroupIds, cgId];
        }
    }

    let isSavingClassGroups = $state(false);

    function submitSaveClassGroups() {
        if (!targetGroupForClasses) return;
        isSavingClassGroups = true;
        router.post(`/admin/employee-groups/${targetGroupForClasses.id}/assign-class-groups`, { class_group_ids: selectedClassGroupIds }, {
            onSuccess: () => (isClassGroupsModalOpen = false),
            onFinish: () => (isSavingClassGroups = false),
        });
    }

    function removeClassGroupFromEmployeeGroup(grp: any, cg: any) {
        if (confirm(`Lepas group kelas '${cg.name}' dari group karyawan '${grp.name}'?`)) {
            router.delete(`/admin/employee-groups/${grp.id}/remove-class-group/${cg.id}`);
        }
    }
</script>

<AppHead title="Group Karyawan & Pembagian Kelas - Corporate LMS" />

<div class="flex-1 space-y-5 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border/80">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-indigo-600 mb-1">
                <UsersRound class="size-4" />
                <span>Pengelompokan Tim & Departemen</span>
            </div>
            <h1 class="text-xl md:text-2xl font-extrabold text-foreground tracking-tight">
                Group Karyawan
            </h1>
            <p class="text-xs text-muted-foreground mt-1">
                Kelompokkan karyawan ke dalam grup/tim/batch, dan assign group kelas langsung ke group karyawan.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <Link
                href="/admin/employees"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-border bg-card hover:bg-muted text-foreground text-xs font-semibold shadow-xs transition-colors shrink-0"
            >
                <Users class="size-4 text-muted-foreground" />
                <span>Daftar Karyawan</span>
            </Link>
            <button
                type="button"
                onclick={openCreateGroup}
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
            >
                <Plus class="size-4" />
                <span>Tambah Group Karyawan</span>
            </button>
        </div>
    </div>

    <!-- Sub-navigation Tabs: Karyawan vs Group Karyawan -->
    <div class="flex items-center gap-2 border-b border-border/80 pb-1">
        <Link
            href="/admin/employees"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-medium transition-all text-muted-foreground hover:text-foreground hover:bg-muted"
        >
            <Users class="size-4" />
            <span>Daftar Karyawan ({allEmployees.length})</span>
        </Link>
        <Link
            href="/admin/employee-groups"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800"
        >
            <UsersRound class="size-4" />
            <span>Group Karyawan ({employeeGroups.length})</span>
        </Link>
    </div>

    <!-- Reusable DataTable -->
    <DataTable
        data={employeeGroups}
        columns={columns}
        searchKeys={['name', 'code', 'description']}
        placeholder="Cari nama group karyawan atau kode..."
        initialPageSize={10}
        pageSizeOptions={[5, 10, 25, 50]}
    >
        {#snippet cell({ row, column })}
            {#if column.key === 'name'}
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold border {colorClasses[row.badge_color] ?? colorClasses.indigo}">
                            {row.name}
                        </span>
                        {#if row.code}
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-muted text-muted-foreground">
                                {row.code}
                            </span>
                        {/if}
                    </div>
                    {#if row.description}
                        <p class="text-[11px] text-muted-foreground line-clamp-1 max-w-md">
                            {row.description}
                        </p>
                    {/if}
                </div>
            {:else if column.key === 'company'}
                <div class="flex items-center gap-1.5 text-xs">
                    <Building2 class="size-3.5 text-muted-foreground shrink-0" />
                    <span class="font-medium text-foreground truncate">
                        {row.company?.name ?? 'Semua Kantor'}
                    </span>
                </div>
            {:else if column.key === 'employees_count'}
                <div class="flex items-center gap-2">
                    <div class="flex -space-x-1.5 overflow-hidden">
                        {#each (row.employees || []).slice(0, 3) as emp (emp.id)}
                            <div
                                title="{emp.name}"
                                class="inline-block size-6 rounded-full ring-2 ring-card bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold flex items-center justify-center"
                            >
                                {#if emp.avatar}
                                    <img src={emp.avatar} alt={emp.name} class="size-full rounded-full object-cover" />
                                {:else}
                                    {emp.name.charAt(0)}
                                {/if}
                            </div>
                        {/each}
                    </div>
                    <button
                        type="button"
                        onclick={() => openManageEmployeesModal(row)}
                        class="text-[11px] font-semibold text-indigo-600 hover:underline"
                    >
                        {row.employees_count ?? 0} Karyawan
                    </button>
                </div>
            {:else if column.key === 'class_groups_count'}
                <div class="flex flex-wrap items-center gap-1.5">
                    {#if (row.class_groups || []).length === 0}
                        <span class="text-[11px] text-muted-foreground italic">Belum ada group kelas</span>
                    {:else}
                        {#each (row.class_groups || []).slice(0, 2) as cg (cg.id)}
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-muted border border-border">
                                <Layers class="size-3 text-indigo-500" />
                                <span class="truncate max-w-[100px]">{cg.name}</span>
                            </span>
                        {/each}
                        {#if (row.class_groups || []).length > 2}
                            <span class="text-[10px] font-mono text-muted-foreground font-semibold">
                                +{(row.class_groups || []).length - 2}
                            </span>
                        {/if}
                    {/if}
                    <button
                        type="button"
                        onclick={() => openAssignClassGroupsModal(row)}
                        title="Atur Group Kelas"
                        class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-100"
                    >
                        Kelola
                    </button>
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
                    onclick={() => openManageEmployeesModal(row)}
                    title="Kelola Anggota Karyawan"
                    class="p-1.5 rounded-lg border border-border hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 transition-colors"
                >
                    <UserPlus class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => openAssignClassGroupsModal(row)}
                    title="Assign Group Kelas"
                    class="p-1.5 rounded-lg border border-border hover:bg-purple-50 dark:hover:bg-purple-950/50 hover:text-purple-600 transition-colors"
                >
                    <Layers class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => openEditGroup(row)}
                    title="Edit Group"
                    class="p-1.5 rounded-lg border border-border hover:bg-muted text-foreground transition-colors"
                >
                    <Edit3 class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => deleteGroup(row)}
                    title="Hapus Group"
                    class="p-1.5 rounded-lg border border-border hover:bg-rose-50 dark:hover:bg-rose-950/50 text-rose-600 transition-colors"
                >
                    <Trash2 class="size-3.5" />
                </button>
            </div>
        {/snippet}
    </DataTable>
</div>

<!-- Modal 1: Buat / Edit Group Karyawan -->
{#if isGroupModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    {editingGroup ? 'Edit Group Karyawan' : 'Tambah Group Karyawan Baru'}
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
                    <label class="block font-semibold text-foreground mb-1">Nama Group Karyawan *</label>
                    <input
                        type="text"
                        required
                        bind:value={groupForm.name}
                        placeholder="Contoh: Backend Developers, Batch MT 2026, Divisi Finance"
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:border-indigo-500 focus:outline-hidden"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Kode Group (Opsional)</label>
                        <input
                            type="text"
                            bind:value={groupForm.code}
                            placeholder="Contoh: GRP-DEV-01"
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Kantor / Cabang</label>
                        <select
                            bind:value={groupForm.company_id}
                            class="w-full pl-3 pr-8 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                        >
                            <option value="">Semua Kantor / Pusat</option>
                            {#each companies as comp}
                                <option value={comp.id}>{comp.name} ({comp.code})</option>
                            {/each}
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Tema Warna Badge</label>
                    <select
                        bind:value={groupForm.badge_color}
                        class="w-full pl-3 pr-8 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                    >
                        <option value="indigo">Indigo</option>
                        <option value="blue">Blue</option>
                        <option value="emerald">Emerald</option>
                        <option value="amber">Amber</option>
                        <option value="purple">Purple</option>
                        <option value="rose">Rose</option>
                        <option value="slate">Slate</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Deskripsi Group</label>
                    <textarea
                        rows="3"
                        bind:value={groupForm.description}
                        placeholder="Tujuan, kriteria atau catatan pengelompokan..."
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                    ></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="is_active_check"
                        bind:checked={groupForm.is_active}
                        class="rounded text-indigo-600 focus:ring-indigo-500"
                    />
                    <label for="is_active_check" class="font-medium text-foreground">Group Aktif</label>
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
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs disabled:opacity-50"
                    >
                        {isSubmitting ? 'Menyimpan...' : (editingGroup ? 'Simpan Perubahan' : 'Buat Group')}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Modal 2: Kelola Anggota Karyawan dalam Group -->
{#if isEmployeesModalOpen && targetGroupForEmployees}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-xl max-h-[85vh] flex flex-col p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <div>
                    <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                        <Users class="size-4.5 text-indigo-600" />
                        <span>Anggota Group: {targetGroupForEmployees.name}</span>
                    </h3>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Centang karyawan yang tergabung ke dalam group ini ({selectedUserIds.length} terpilih).
                    </p>
                </div>
                <button
                    type="button"
                    onclick={() => (isEmployeesModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- Search employee -->
            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                <input
                    type="text"
                    placeholder="Cari nama karyawan, jabatan, atau email..."
                    bind:value={employeeSearch}
                    class="w-full pl-9 pr-3 py-1.5 rounded-xl text-xs bg-muted/40 border border-border focus:outline-hidden focus:border-indigo-500"
                />
            </div>

            <!-- Employee list with checkboxes -->
            <div class="flex-1 overflow-y-auto space-y-1.5 pr-1 max-h-[350px]">
                {#if filteredEmployees.length === 0}
                    <div class="p-8 text-center text-muted-foreground text-xs">
                        Tidak ada karyawan yang cocok dengan pencarian.
                    </div>
                {:else}
                    {#each filteredEmployees as emp (emp.id)}
                        {@const isSelected = selectedUserIds.includes(emp.id)}
                        <label
                            class="flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer {isSelected ? 'bg-indigo-50/80 dark:bg-indigo-950/60 border-indigo-400 font-semibold shadow-xs' : 'border-border/70 hover:bg-muted/40'}"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <input
                                    type="checkbox"
                                    checked={isSelected}
                                    onchange={() => toggleSelectUser(emp.id)}
                                    class="size-4 rounded border-border text-indigo-600 focus:ring-indigo-500"
                                />
                                <div class="size-8 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-300 font-bold flex items-center justify-center shrink-0 text-xs">
                                    {emp.name.charAt(0)}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs text-foreground truncate">{emp.name}</div>
                                    <div class="text-[11px] text-muted-foreground truncate">{emp.position ?? 'Karyawan'} • {emp.email}</div>
                                </div>
                            </div>
                            {#if emp.company}
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-muted text-muted-foreground shrink-0 ml-2">
                                    {emp.company.code}
                                </span>
                            {/if}
                        </label>
                    {/each}
                {/if}
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-border/80">
                <span class="text-xs text-muted-foreground">
                    Total Anggota: <strong class="text-foreground">{selectedUserIds.length}</strong> karyawan
                </span>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick={() => (isEmployeesModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        onclick={submitSaveEmployees}
                        disabled={isSavingEmployees}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs disabled:opacity-50"
                    >
                        {isSavingEmployees ? 'Menyimpan...' : 'Simpan Anggota'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}

<!-- Modal 3: Assign Group Kelas ke Group Karyawan -->
{#if isClassGroupsModalOpen && targetGroupForClasses}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-xl max-h-[85vh] flex flex-col p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <div>
                    <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                        <Layers class="size-4.5 text-purple-600" />
                        <span>Assign Group Kelas ke: {targetGroupForClasses.name}</span>
                    </h3>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Pilih group kelas yang otomatis dapat diakses oleh seluruh anggota group ini.
                    </p>
                </div>
                <button
                    type="button"
                    onclick={() => (isClassGroupsModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- Informative Notice -->
            <div class="p-3 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-700 dark:text-purple-300 text-xs flex items-center gap-2.5">
                <Layers class="size-4 shrink-0" />
                <span>
                    Setiap modul dan kelas di dalam group kelas yang dipilih akan <strong>otomatis terbuka</strong> untuk semua {targetGroupForClasses.employees_count ?? 0} anggota group karyawan ini.
                </span>
            </div>

            <!-- Class Groups List with Checkboxes -->
            <div class="flex-1 overflow-y-auto space-y-2 pr-1 max-h-[350px]">
                {#if allClassGroups.length === 0}
                    <div class="p-8 text-center text-muted-foreground text-xs">
                        Belum ada group kelas yang dibuat. Silakan tambahkan group kelas terlebih dahulu.
                    </div>
                {:else}
                    {#each allClassGroups as cg (cg.id)}
                        {@const isSelected = selectedClassGroupIds.includes(cg.id)}
                        <label
                            class="flex items-start justify-between p-3 rounded-xl border transition-all cursor-pointer {isSelected ? 'bg-purple-50/80 dark:bg-purple-950/60 border-purple-400 font-semibold shadow-xs' : 'border-border/70 hover:bg-muted/40'}"
                        >
                            <div class="flex items-start gap-3 min-w-0">
                                <input
                                    type="checkbox"
                                    checked={isSelected}
                                    onchange={() => toggleSelectClassGroup(cg.id)}
                                    class="size-4 rounded border-border text-purple-600 focus:ring-purple-500 mt-0.5"
                                />
                                <div class="min-w-0 space-y-0.5">
                                    <div class="text-xs text-foreground font-bold flex items-center gap-2">
                                        <span>{cg.name}</span>
                                        <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-muted font-normal text-muted-foreground">
                                            {cg.courses_count ?? 0} Kelas
                                        </span>
                                    </div>
                                    {#if cg.description}
                                        <p class="text-[11px] text-muted-foreground line-clamp-1 font-normal">
                                            {cg.description}
                                        </p>
                                    {/if}
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-muted text-muted-foreground shrink-0">
                                {cg.badge_color}
                            </span>
                        </label>
                    {/each}
                {/if}
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-border/80">
                <span class="text-xs text-muted-foreground">
                    Terpilih: <strong class="text-foreground">{selectedClassGroupIds.length}</strong> group kelas
                </span>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick={() => (isClassGroupsModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        onclick={submitSaveClassGroups}
                        disabled={isSavingClassGroups}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-purple-600 hover:bg-purple-500 text-white shadow-xs disabled:opacity-50"
                    >
                        {isSavingClassGroups ? 'Menyimpan...' : 'Simpan Group Kelas'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}
