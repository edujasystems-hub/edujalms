<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/dashboard',
            },
            {
                title: 'Karyawan & Akses',
                href: '/admin/employees',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Users from '@lucide/svelte/icons/users';
    import UsersRound from '@lucide/svelte/icons/users-round';
    import Plus from '@lucide/svelte/icons/plus';
    import Search from '@lucide/svelte/icons/search';
    import Building2 from '@lucide/svelte/icons/building-2';
    import Layers from '@lucide/svelte/icons/layers';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Award from '@lucide/svelte/icons/award';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import X from '@lucide/svelte/icons/x';

    let {
        employees = [],
        companies = [],
        classGroups = [],
        courses = [],
        filters = { search: '', company_id: '' },
    }: {
        employees: any[];
        companies: any[];
        classGroups: any[];
        courses: any[];
        filters: { search: string; company_id: string };
    } = $props();

    let search = $state(filters.search);
    let companyId = $state(filters.company_id);

    function applyFilter() {
        router.get('/admin/employees', {
            search: search || undefined,
            company_id: companyId || undefined,
        }, { preserveState: true, replace: true });
    }

    // Modal: Register Employee
    let isRegisterModalOpen = $state(false);
    let registerForm = $state({
        name: '',
        email: '',
        password: 'password',
        company_id: '',
        position: '',
        department: '',
        phone: '',
        group_ids: [] as number[],
    });

    let isRegistering = $state(false);

    function submitRegister(e: SubmitEvent) {
        e.preventDefault();
        isRegistering = true;
        router.post('/admin/employees', registerForm, {
            onSuccess: () => {
                isRegisterModalOpen = false;
                registerForm = {
                    name: '',
                    email: '',
                    password: 'password',
                    company_id: '',
                    position: '',
                    department: '',
                    phone: '',
                    group_ids: [],
                };
            },
            onFinish: () => (isRegistering = false),
        });
    }

    // Modal: Manage Employee Access
    let isAccessModalOpen = $state(false);
    let targetEmployee = $state<any | null>(null);
    let selectedGroupIds = $state<number[]>([]);
    let selectedCourseIds = $state<number[]>([]);

    function openAccessModal(emp: any) {
        targetEmployee = emp;
        selectedGroupIds = emp.class_groups ? emp.class_groups.map((g: any) => g.id) : [];
        selectedCourseIds = emp.enrolled_courses ? emp.enrolled_courses.map((c: any) => c.id) : [];
        isAccessModalOpen = true;
    }

    function toggleGroup(id: number) {
        if (selectedGroupIds.includes(id)) {
            selectedGroupIds = selectedGroupIds.filter((gid) => gid !== id);
        } else {
            selectedGroupIds = [...selectedGroupIds, id];
        }
    }

    function toggleCourse(id: number) {
        if (selectedCourseIds.includes(id)) {
            selectedCourseIds = selectedCourseIds.filter((cid) => cid !== id);
        } else {
            selectedCourseIds = [...selectedCourseIds, id];
        }
    }

    let isUpdatingAccess = $state(false);

    function submitAccess(e: SubmitEvent) {
        e.preventDefault();
        if (!targetEmployee) return;
        isUpdatingAccess = true;

        router.put(`/admin/employees/${targetEmployee.id}/access`, {
            group_ids: selectedGroupIds,
            course_ids: selectedCourseIds,
        }, {
            onSuccess: () => (isAccessModalOpen = false),
            onFinish: () => (isUpdatingAccess = false),
        });
    }
</script>

<AppHead title="Pengelolaan Karyawan & Hak Akses - Corporate LMS" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-foreground tracking-tight flex items-center gap-2">
                <Users class="size-6 text-indigo-600" />
                <span>Pengelolaan Karyawan & Hak Akses</span>
            </h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Kelola data karyawan korporat, tentukan akses langsung ke kelas, atau masukkan ke dalam Group Track.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <Link
                href="/admin/employee-groups"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-border bg-card hover:bg-muted text-foreground text-xs font-semibold shadow-xs transition-colors shrink-0"
            >
                <UsersRound class="size-4 text-indigo-600" />
                <span>Kelola Group Karyawan</span>
            </Link>
            <button
                type="button"
                onclick={() => (isRegisterModalOpen = true)}
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
            >
                <Plus class="size-4" />
                <span>Daftarkan Karyawan Baru</span>
            </button>
        </div>
    </div>

    <!-- Sub-navigation Tabs: Karyawan vs Group Karyawan -->
    <div class="flex items-center gap-2 border-b border-border/80 pb-1">
        <Link
            href="/admin/employees"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800"
        >
            <Users class="size-4" />
            <span>Daftar Karyawan ({employees.length})</span>
        </Link>
        <Link
            href="/admin/employee-groups"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-medium transition-all text-muted-foreground hover:text-foreground hover:bg-muted"
        >
            <UsersRound class="size-4" />
            <span>Group Karyawan</span>
        </Link>
    </div>

    <!-- Filters -->
    <div class="p-3 rounded-2xl border border-border bg-card shadow-xs flex flex-wrap gap-3 items-center">
        <div class="relative flex-1 min-w-[220px]">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
            <input
                type="text"
                placeholder="Cari nama karyawan, email, divisi, atau jabatan..."
                bind:value={search}
                onkeydown={(e) => e.key === 'Enter' && applyFilter()}
                class="w-full pl-9 pr-3 py-2 rounded-xl text-xs bg-muted/40 border border-border focus:outline-hidden"
            />
        </div>

        <select
            bind:value={companyId}
            onchange={applyFilter}
            class="py-2 pl-3 pr-8 rounded-xl text-xs bg-muted/40 border border-border text-foreground"
        >
            <option value="">Semua Perusahaan</option>
            {#each companies as comp}
                <option value={comp.id}>{comp.name}</option>
            {/each}
        </select>

        <button
            type="button"
            onclick={applyFilter}
            class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
        >
            Filter
        </button>
    </div>

    <!-- Employees Table -->
    <div class="rounded-2xl border border-border bg-card overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-muted/50 border-b border-border/80 text-muted-foreground font-semibold">
                    <tr>
                        <th class="p-3 pl-4">Karyawan</th>
                        <th class="p-3">Perusahaan & Divisi</th>
                        <th class="p-3">Group Track Terdaftar</th>
                        <th class="p-3">Kelas Langsung</th>
                        <th class="p-3 pr-4 text-right">Kelola Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60">
                    {#each employees as emp (emp.id)}
                        <tr class="hover:bg-muted/30 transition-colors">
                            <td class="p-3 pl-4">
                                <div class="flex items-center gap-2.5">
                                    {#if emp.avatar}
                                        <img
                                            src={emp.avatar}
                                            alt={emp.name}
                                            class="size-8 rounded-full object-cover border border-border shrink-0"
                                        />
                                    {:else}
                                        <div class="size-8 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0">
                                            {emp.name.charAt(0)}
                                        </div>
                                    {/if}
                                    <div class="min-w-0">
                                        <div class="font-bold text-foreground truncate">{emp.name}</div>
                                        <div class="text-[10px] text-muted-foreground truncate">{emp.email}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-3">
                                <div class="font-medium text-foreground">
                                    {emp.company?.name || 'Independen'}
                                </div>
                                <div class="text-[10px] text-muted-foreground">
                                    {emp.department || '-'} • {emp.position || '-'}
                                </div>
                            </td>

                            <td class="p-3">
                                <div class="flex flex-wrap gap-1 max-w-[220px]">
                                    {#each emp.class_groups as grp}
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                                            {grp.name}
                                        </span>
                                    {/each}
                                    {#if emp.class_groups.length === 0}
                                        <span class="text-[11px] text-muted-foreground italic">Belum ada group</span>
                                    {/if}
                                </div>
                            </td>

                            <td class="p-3">
                                <span class="font-mono font-bold text-xs text-foreground">
                                    {emp.enrolled_courses.length}
                                </span>
                                <span class="text-muted-foreground text-[11px]"> kelas</span>
                            </td>

                            <td class="p-3 pr-4 text-right">
                                <button
                                    type="button"
                                    onclick={() => openAccessModal(emp)}
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900 transition-colors"
                                >
                                    <ShieldCheck class="size-3.5" />
                                    <span>Atur Akses</span>
                                </button>
                            </td>
                        </tr>
                    {/each}
                    {#if employees.length === 0}
                        <tr>
                            <td colspan="5" class="p-8 text-center text-muted-foreground">
                                Tidak ada data karyawan yang cocok.
                            </td>
                        </tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Daftarkan Karyawan Baru -->
{#if isRegisterModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    Daftarkan Karyawan Baru
                </h3>
                <button
                    type="button"
                    onclick={() => (isRegisterModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitRegister} class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-foreground mb-1">Nama Lengkap *</label>
                    <input
                        type="text"
                        required
                        placeholder="Contoh: Rendi Pratama"
                        bind:value={registerForm.name}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 focus:border-indigo-500"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Email Korporasi *</label>
                        <input
                            type="email"
                            required
                            placeholder="rendi@perusahaan.co.id"
                            bind:value={registerForm.email}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Password Awal *</label>
                        <input
                            type="password"
                            required
                            bind:value={registerForm.password}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Perusahaan Mitra</label>
                        <select
                            bind:value={registerForm.company_id}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 text-foreground"
                        >
                            <option value="">-- Pilih Perusahaan --</option>
                            {#each companies as comp}
                                <option value={comp.id}>{comp.name}</option>
                            {/each}
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Divisi / Departemen</label>
                        <input
                            type="text"
                            placeholder="Digital Marketing / IT"
                            bind:value={registerForm.department}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Jabatan / Posisi</label>
                        <input
                            type="text"
                            placeholder="Senior Officer"
                            bind:value={registerForm.position}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Nomor Telepon</label>
                        <input
                            type="text"
                            placeholder="+62 812..."
                            bind:value={registerForm.phone}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isRegisterModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isRegistering}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs"
                    >
                        {isRegistering ? 'Mendaftarkan...' : 'Daftarkan Karyawan'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Modal: Kelola Hak Akses Karyawan -->
{#if isAccessModalOpen && targetEmployee}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-xl max-h-[85vh] overflow-y-auto p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <div>
                    <h3 class="text-base font-bold text-foreground">
                        Kelola Hak Akses Pembelajaran
                    </h3>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Karyawan: <span class="font-bold text-indigo-600">{targetEmployee.name}</span> ({targetEmployee.email})
                    </p>
                </div>
                <button
                    type="button"
                    onclick={() => (isAccessModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitAccess} class="space-y-5 text-xs">
                <!-- Group Tracks (Batch Access) -->
                <div class="space-y-2">
                    <div class="flex items-center gap-1.5 font-bold text-foreground">
                        <Layers class="size-4 text-indigo-600" />
                        <span>1. Akses Group Kelas / Program Track:</span>
                    </div>
                    <p class="text-[11px] text-muted-foreground leading-relaxed">
                        Memberi akses ke group otomatis membuka <strong>seluruh kelas</strong> yang ada di dalam group tersebut.
                    </p>

                    <div class="space-y-1.5 max-h-[160px] overflow-y-auto pr-1">
                        {#each classGroups as grp (grp.id)}
                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-border bg-muted/20 hover:bg-muted/50 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={selectedGroupIds.includes(grp.id)}
                                    onchange={() => toggleGroup(grp.id)}
                                    class="rounded border-border text-indigo-600 size-4"
                                />
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-foreground truncate">{grp.name}</div>
                                    <div class="text-[10px] text-muted-foreground">{grp.courses_count || 0} Kelas di dalamnya</div>
                                </div>
                            </label>
                        {/each}
                    </div>
                </div>

                <!-- Direct Classes Access -->
                <div class="space-y-2 pt-2 border-t border-border/60">
                    <div class="flex items-center gap-1.5 font-bold text-foreground">
                        <BookOpen class="size-4 text-emerald-600" />
                        <span>2. Akses Langsung Kelas Individual:</span>
                    </div>
                    <p class="text-[11px] text-muted-foreground">
                        Beri akses ke kelas spesifik yang mungkin berada di luar group di atas.
                    </p>

                    <div class="space-y-1.5 max-h-[180px] overflow-y-auto pr-1">
                        {#each courses as c (c.id)}
                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-border bg-muted/20 hover:bg-muted/50 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={selectedCourseIds.includes(c.id)}
                                    onchange={() => toggleCourse(c.id)}
                                    class="rounded border-border text-indigo-600 size-4"
                                />
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-foreground truncate">{c.title}</div>
                                </div>
                            </label>
                        {/each}
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isAccessModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isUpdatingAccess}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs"
                    >
                        {isUpdatingAccess ? 'Menyimpan...' : 'Simpan Perubahan Akses'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
