<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/dashboard',
            },
            {
                title: 'Group & Kategori Kelas',
                href: '/admin/groups',
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Layers from '@lucide/svelte/icons/layers';
    import Plus from '@lucide/svelte/icons/plus';
    import Users from '@lucide/svelte/icons/users';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import X from '@lucide/svelte/icons/x';

    let {
        classGroups = [],
        allEmployees = [],
    }: {
        classGroups: any[];
        allEmployees: any[];
    } = $props();

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
        if (confirm(`Hapus grup kelas '${grp.name}'? Kelas di dalamnya tidak akan terhapus, hanya dilepas dari grup.`)) {
            router.delete(`/admin/groups/${grp.id}`);
        }
    }

    // Assign Employees to Group Modal
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
        if (confirm(`Lepas karyawan '${emp.name}' dari grup '${grp.name}'? Akses ke seluruh kelas di grup ini akan dicabut.`)) {
            router.delete(`/admin/groups/${grp.id}/remove-user/${emp.id}`);
        }
    }
</script>

<AppHead title="Group & Kategori Kelas - Corporate LMS" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-foreground tracking-tight flex items-center gap-2">
                <Layers class="size-6 text-indigo-600" />
                <span>Pengelolaan Group & Kategori Kelas</span>
            </h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Kelompokkan kurikulum ke dalam Program Track korporat untuk mempermudah pemberian hak akses masal ke karyawan.
            </p>
        </div>
        <button
            type="button"
            onclick={openCreateGroup}
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
        >
            <Plus class="size-4" />
            <span>Buat Group Baru</span>
        </button>
    </div>

    <!-- Core Feature Highlight Card -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-900/10 via-indigo-600/10 to-transparent border border-indigo-500/20 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <h3 class="text-sm font-bold text-foreground flex items-center gap-1.5">
                <CheckCircle2 class="size-4 text-indigo-600" />
                <span>Otomatisasi Akses Kelas Berbasis Group (Track)</span>
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed max-w-3xl">
                Setiap kelas yang dimasukkan ke dalam group akan otomatis mewarisi hak akses ke seluruh karyawan yang di-assign ke group tersebut. Anda tidak perlu repot memasukkan karyawan satu per satu ke setiap kelas!
            </p>
        </div>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {#each classGroups as group (group.id)}
            <div class="rounded-2xl border border-border bg-card p-5 shadow-xs hover:border-indigo-400/50 transition-all space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                                {group.badge_color}
                            </span>
                            <span class="text-xs text-muted-foreground font-mono">
                                /{group.slug}
                            </span>
                        </div>
                        <h2 class="text-base font-bold text-foreground">
                            {group.name}
                        </h2>
                        {#if group.description}
                            <p class="text-xs text-muted-foreground leading-relaxed">
                                {group.description}
                            </p>
                        {/if}
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        <button
                            type="button"
                            onclick={() => openEditGroup(group)}
                            class="p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted"
                            title="Edit Group"
                        >
                            <Edit3 class="size-4" />
                        </button>
                        <button
                            type="button"
                            onclick={() => deleteGroup(group)}
                            class="p-1.5 rounded-lg text-muted-foreground hover:text-rose-600 hover:bg-rose-50"
                            title="Hapus Group"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Stats: Courses & Employees in Group -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-muted/30 border border-border/60">
                        <div class="flex items-center justify-between text-xs text-muted-foreground mb-1">
                            <span>Total Kelas</span>
                            <BookOpen class="size-3.5 text-indigo-600" />
                        </div>
                        <div class="text-xl font-extrabold text-foreground">{group.courses_count}</div>
                    </div>

                    <div class="p-3 rounded-xl bg-muted/30 border border-border/60">
                        <div class="flex items-center justify-between text-xs text-muted-foreground mb-1">
                            <span>Karyawan Terdaftar</span>
                            <Users class="size-3.5 text-emerald-600" />
                        </div>
                        <div class="text-xl font-extrabold text-foreground">{group.employees_count}</div>
                    </div>
                </div>

                <!-- Assigned Courses Tags -->
                <div>
                    <span class="text-[11px] font-semibold text-muted-foreground block mb-1.5">Kelas Tergabung:</span>
                    <div class="flex flex-wrap gap-1.5">
                        {#each group.courses as c}
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-muted text-foreground border border-border/60 truncate max-w-[220px]">
                                📚 {c.title}
                            </span>
                        {/each}
                        {#if group.courses.length === 0}
                            <span class="text-xs text-muted-foreground italic">Belum ada kelas di group ini.</span>
                        {/if}
                    </div>
                </div>

                <!-- Assigned Employees & Assign Button -->
                <div class="pt-3 border-t border-border/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5">
                        {#each group.employees.slice(0, 4) as emp}
                            {#if emp.avatar}
                                <img
                                    src={emp.avatar}
                                    alt={emp.name}
                                    class="size-6 rounded-full object-cover border-2 border-background -ml-1 first:ml-0"
                                    title={emp.name}
                                />
                            {:else}
                                <div class="size-6 rounded-full bg-indigo-600 text-white text-[10px] flex items-center justify-center font-bold -ml-1 first:ml-0">
                                    {emp.name.charAt(0)}
                                </div>
                            {/if}
                        {/each}
                        {#if group.employees.length > 4}
                            <span class="text-[11px] font-semibold text-muted-foreground ml-1">
                                +{group.employees.length - 4} lainnya
                            </span>
                        {/if}
                    </div>

                    <button
                        type="button"
                        onclick={() => openAssignModal(group)}
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition-colors"
                    >
                        <UserPlus class="size-3.5" />
                        <span>Assign Karyawan</span>
                    </button>
                </div>
            </div>
        {/each}
    </div>
</div>

<!-- Modal: Buat / Edit Group -->
{#if isGroupModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    {editingGroup ? 'Edit Group Kelas' : 'Buat Group Kelas Baru'}
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
                    <label class="block font-semibold text-foreground mb-1">Nama Group / Track *</label>
                    <input
                        type="text"
                        required
                        placeholder="Contoh: Digital Transformation & AI Track"
                        bind:value={groupForm.name}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 focus:border-indigo-500"
                    />
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Tema Warna Badge</label>
                    <select
                        bind:value={groupForm.badge_color}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 text-foreground"
                    >
                        <option value="indigo">Indigo / Blue</option>
                        <option value="emerald">Emerald / Green</option>
                        <option value="amber">Amber / Orange</option>
                        <option value="violet">Violet / Purple</option>
                        <option value="rose">Rose / Red</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Deskripsi Group</label>
                    <textarea
                        rows="3"
                        placeholder="Tujuan program, kurikulum atau target kompetensi..."
                        bind:value={groupForm.description}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
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
                        {isSubmitting ? 'Menyimpan...' : 'Simpan Group'}
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
                        Assign Karyawan ke Group
                    </h3>
                    <p class="text-xs text-indigo-600 font-semibold mt-0.5">
                        Track: {targetGroup.name}
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

            <p class="text-xs text-muted-foreground leading-relaxed">
                Pilih karyawan di bawah. Karyawan terpilih akan otomatis memiliki hak akses belajar ke <strong>seluruh {targetGroup.courses_count} kelas</strong> di dalam group ini.
            </p>

            <div class="space-y-1.5 max-h-[300px] overflow-y-auto pr-1">
                {#each allEmployees as emp (emp.id)}
                    {@const isAlreadyAssigned = targetGroup.employees.some((u: any) => u.id === emp.id)}
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
                            <span class="text-[10px] font-semibold text-emerald-600">Sudah Terdaftar</span>
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
                        {isAssigning ? 'Memproses...' : 'Assign Akses Masal'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}
