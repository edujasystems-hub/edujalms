<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/admin/dashboard',
            },
            {
                title: 'Perusahaan',
                href: '/admin/companies',
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import DataTable from '@/components/DataTable.svelte';
    import Building2 from '@lucide/svelte/icons/building-2';
    import Plus from '@lucide/svelte/icons/plus';
    import Users from '@lucide/svelte/icons/users';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Mail from '@lucide/svelte/icons/mail';
    import Phone from '@lucide/svelte/icons/phone';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import X from '@lucide/svelte/icons/x';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import TableIcon from '@lucide/svelte/icons/table';

    interface CompanyStats {
        total_companies?: number;
        active_companies?: number;
        inactive_companies?: number;
        total_employees?: number;
        total_courses?: number;
        avg_employees_per_company?: number;
    }

    let {
        companies = [],
        stats = {
            total_companies: 0,
            active_companies: 0,
            inactive_companies: 0,
            total_employees: 0,
            total_courses: 0,
            avg_employees_per_company: 0,
        },
        filters = { search: '' },
    }: {
        companies: any[];
        stats?: CompanyStats;
        filters?: { search: string };
    } = $props();

    // View toggle: table vs grid
    let viewMode = $state<'table' | 'grid'>('table');

    const columns = [
        { key: 'name', label: 'Perusahaan', sortable: true },
        { key: 'industry', label: 'Industri / Sektor', sortable: true },
        { key: 'employees_count', label: 'Karyawan', sortable: true },
        { key: 'courses_count', label: 'Kelas Khusus', sortable: true },
        { key: 'email', label: 'Kontak', sortable: false },
        { key: 'is_active', label: 'Status', sortable: true },
    ];

    // Modal state
    let isCompanyModalOpen = $state(false);
    let editingCompany = $state<any | null>(null);

    let companyForm = $state({
        name: '',
        code: '',
        industry: '',
        email: '',
        phone: '',
        address: '',
        logo_url: '',
        is_active: true,
    });

    function openCreateCompany() {
        editingCompany = null;
        companyForm = {
            name: '',
            code: '',
            industry: '',
            email: '',
            phone: '',
            address: '',
            logo_url: '',
            is_active: true,
        };
        isCompanyModalOpen = true;
    }

    function openEditCompany(comp: any) {
        editingCompany = comp;
        companyForm = {
            name: comp.name,
            code: comp.code,
            industry: comp.industry || '',
            email: comp.email || '',
            phone: comp.phone || '',
            address: comp.address || '',
            logo_url: comp.logo_url || '',
            is_active: !!comp.is_active,
        };
        isCompanyModalOpen = true;
    }

    let isSubmitting = $state(false);

    function submitCompany(e: SubmitEvent) {
        e.preventDefault();
        isSubmitting = true;

        if (editingCompany) {
            router.put(`/admin/companies/${editingCompany.id}`, companyForm, {
                onSuccess: () => (isCompanyModalOpen = false),
                onFinish: () => (isSubmitting = false),
            });
        } else {
            router.post('/admin/companies', companyForm, {
                onSuccess: () => (isCompanyModalOpen = false),
                onFinish: () => (isSubmitting = false),
            });
        }
    }

    function deleteCompany(comp: any) {
        if (confirm(`Hapus data perusahaan '${comp.name}'?`)) {
            router.delete(`/admin/companies/${comp.id}`);
        }
    }
</script>

<AppHead title="Perusahaan - Corporate LMS" />

<div class="flex-1 space-y-5 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-foreground tracking-tight flex items-center gap-2">
                <Building2 class="size-5 text-indigo-600" />
                <span>Klien Perusahaan</span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <!-- View Mode Switcher -->
            <div class="inline-flex items-center p-0.5 rounded-lg border border-border bg-card">
                <button
                    type="button"
                    onclick={() => (viewMode = 'table')}
                    class="p-1.5 rounded-md text-xs transition-colors {viewMode === 'table' ? 'bg-indigo-600 text-white font-semibold' : 'text-muted-foreground hover:text-foreground'}"
                    title="Tampilan Tabel"
                >
                    <TableIcon class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => (viewMode = 'grid')}
                    class="p-1.5 rounded-md text-xs transition-colors {viewMode === 'grid' ? 'bg-indigo-600 text-white font-semibold' : 'text-muted-foreground hover:text-foreground'}"
                    title="Tampilan Grid Kartu"
                >
                    <LayoutGrid class="size-3.5" />
                </button>
            </div>

            <button
                type="button"
                onclick={openCreateCompany}
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
            >
                <Plus class="size-3.5" />
                <span>Tambah Perusahaan</span>
            </button>
        </div>
    </div>

    <!-- Summary & Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- 1. Total Perusahaan -->
        <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Total Perusahaan</span>
                <div class="size-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center">
                    <Building2 class="size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-extrabold text-foreground">{stats?.total_companies ?? 0}</div>
                <div class="text-[11px] text-muted-foreground mt-0.5">
                    Mitra korporasi terdaftar
                </div>
            </div>
        </div>

        <!-- 2. Perusahaan Aktif -->
        <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Mitra Aktif</span>
                <div class="size-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
                    <CheckCircle2 class="size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-extrabold text-foreground">{stats?.active_companies ?? 0}</div>
                <div class="text-[11px] text-muted-foreground mt-0.5">
                    {stats?.inactive_companies ? `${stats.inactive_companies} non-aktif` : '100% aktif beroperasi'}
                </div>
            </div>
        </div>

        <!-- 3. Total Karyawan Mitra -->
        <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Karyawan Mitra</span>
                <div class="size-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center">
                    <Users class="size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-extrabold text-foreground">{stats?.total_employees ?? 0}</div>
                <div class="text-[11px] text-muted-foreground mt-0.5">
                    Rata-rata {stats?.avg_employees_per_company ?? 0} per perusahaan
                </div>
            </div>
        </div>

        <!-- 4. Kelas Khusus Korporat -->
        <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Kelas Korporat</span>
                <div class="size-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center">
                    <BookOpen class="size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-extrabold text-foreground">{stats?.total_courses ?? 0}</div>
                <div class="text-[11px] text-muted-foreground mt-0.5">
                    Program khusus perusahaan
                </div>
            </div>
        </div>
    </div>

    <!-- Content: DataTable View -->
    {#if viewMode === 'table'}
        <DataTable
            data={companies}
            columns={columns}
            searchKeys={['name', 'code', 'industry', 'email', 'phone']}
            placeholder="Cari nama, kode, industri, atau email perusahaan..."
            initialPageSize={10}
            pageSizeOptions={[5, 10, 25, 50]}
        >
            {#snippet cell({ row, column })}
                {#if column.key === 'name'}
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg overflow-hidden bg-muted flex items-center justify-center shrink-0 border border-border">
                            {#if row.logo_url}
                                <img src={row.logo_url} alt={row.name} class="size-full object-cover" />
                            {:else}
                                <span class="font-bold text-xs text-indigo-600">{row.code || row.name.charAt(0)}</span>
                            {/if}
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-foreground text-xs">{row.name}</div>
                            <span class="font-mono text-[10px] text-muted-foreground uppercase">{row.code}</span>
                        </div>
                    </div>
                {:else if column.key === 'industry'}
                    <span class="text-xs font-medium text-foreground">
                        {row.industry || '-'}
                    </span>
                {:else if column.key === 'employees_count'}
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-muted font-medium text-[11px]">
                        <Users class="size-3 text-indigo-500" />
                        <span>{row.employees_count ?? 0}</span>
                    </span>
                {:else if column.key === 'courses_count'}
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-muted font-medium text-[11px]">
                        <BookOpen class="size-3 text-emerald-500" />
                        <span>{row.courses_count ?? 0}</span>
                    </span>
                {:else if column.key === 'email'}
                    <div class="space-y-0.5 text-[11px]">
                        {#if row.email}
                            <div class="flex items-center gap-1 text-foreground">
                                <Mail class="size-3 text-muted-foreground" />
                                <span class="truncate max-w-[150px]">{row.email}</span>
                            </div>
                        {/if}
                        {#if row.phone}
                            <div class="flex items-center gap-1 text-muted-foreground">
                                <Phone class="size-3" />
                                <span>{row.phone}</span>
                            </div>
                        {/if}
                        {#if !row.email && !row.phone}
                            <span class="text-muted-foreground">-</span>
                        {/if}
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
                        onclick={() => openEditCompany(row)}
                        title="Edit Perusahaan"
                        class="p-1.5 rounded-lg border border-border hover:bg-muted text-foreground transition-colors"
                    >
                        <Edit3 class="size-3.5" />
                    </button>
                    <button
                        type="button"
                        onclick={() => deleteCompany(row)}
                        title="Hapus Perusahaan"
                        class="p-1.5 rounded-lg border border-border hover:bg-rose-50 hover:text-rose-600 text-muted-foreground transition-colors"
                    >
                        <Trash2 class="size-3.5" />
                    </button>
                </div>
            {/snippet}
        </DataTable>
    {:else}
        <!-- Content: Grid View -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {#each companies as comp (comp.id)}
                <div class="rounded-xl border border-border bg-card p-4 shadow-xs hover:border-indigo-400/50 transition-all flex flex-col justify-between space-y-3">
                    <div class="space-y-2.5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                {#if comp.logo_url}
                                    <img
                                        src={comp.logo_url}
                                        alt={comp.name}
                                        class="size-10 rounded-lg object-cover border border-border shrink-0"
                                    />
                                {:else}
                                    <div class="size-10 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">
                                        {comp.code || comp.name.charAt(0)}
                                    </div>
                                {/if}
                                <div class="min-w-0">
                                    <h3 class="font-bold text-xs text-foreground truncate">{comp.name}</h3>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-muted text-muted-foreground">
                                            {comp.code}
                                        </span>
                                        <span class="text-[11px] text-muted-foreground truncate">
                                            {comp.industry || 'Umum'}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <button
                                    type="button"
                                    onclick={() => openEditCompany(comp)}
                                    class="p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted"
                                    title="Edit"
                                >
                                    <Edit3 class="size-3.5" />
                                </button>
                                <button
                                    type="button"
                                    onclick={() => deleteCompany(comp)}
                                    class="p-1.5 rounded-lg text-muted-foreground hover:text-rose-600 hover:bg-rose-50"
                                    title="Hapus"
                                >
                                    <Trash2 class="size-3.5" />
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1 text-[11px] text-muted-foreground pt-1">
                            {#if comp.email}
                                <div class="flex items-center gap-1.5 truncate">
                                    <Mail class="size-3 text-muted-foreground" />
                                    <span class="truncate">{comp.email}</span>
                                </div>
                            {/if}
                            {#if comp.phone}
                                <div class="flex items-center gap-1.5 truncate">
                                    <Phone class="size-3 text-muted-foreground" />
                                    <span>{comp.phone}</span>
                                </div>
                            {/if}
                            {#if comp.address}
                                <div class="flex items-start gap-1.5 text-[10px] leading-tight">
                                    <MapPin class="size-3 text-muted-foreground mt-0.5 shrink-0" />
                                    <span class="line-clamp-1">{comp.address}</span>
                                </div>
                            {/if}
                        </div>
                    </div>

                    <div class="pt-2 border-t border-border/60 flex items-center justify-between text-[11px] text-muted-foreground">
                        <span class="inline-flex items-center gap-1 font-medium">
                            <Users class="size-3 text-indigo-600" />
                            <span>{comp.employees_count || 0} Karyawan</span>
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium">
                            <BookOpen class="size-3 text-emerald-600" />
                            <span>{comp.courses_count || 0} Kelas</span>
                        </span>
                    </div>
                </div>
            {/each}
        </div>
    {/if}
</div>

<!-- Modal: Tambah / Edit Perusahaan -->
{#if isCompanyModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    {editingCompany ? 'Edit Data Perusahaan' : 'Tambah Perusahaan Baru'}
                </h3>
                <button
                    type="button"
                    onclick={() => (isCompanyModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitCompany} class="space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="company_name" class="block font-semibold text-foreground mb-1">Nama Perusahaan *</label>
                        <input
                            id="company_name"
                            type="text"
                            required
                            placeholder="Contoh: PT Telkom Indonesia Tbk"
                            bind:value={companyForm.name}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 focus:border-indigo-500"
                        />
                    </div>
                    <div>
                        <label for="company_code" class="block font-semibold text-foreground mb-1">Kode Unik *</label>
                        <input
                            id="company_code"
                            type="text"
                            required
                            placeholder="TLKM"
                            bind:value={companyForm.code}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 uppercase font-mono"
                        />
                    </div>
                </div>

                <div>
                    <label for="company_industry" class="block font-semibold text-foreground mb-1">Sektor / Industri</label>
                    <input
                        id="company_industry"
                        type="text"
                        placeholder="Telekomunikasi, Perbankan, Manufaktur..."
                        bind:value={companyForm.industry}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="company_email" class="block font-semibold text-foreground mb-1">Email Kontak</label>
                        <input
                            id="company_email"
                            type="email"
                            placeholder="corporate@telkom.co.id"
                            bind:value={companyForm.email}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                    <div>
                        <label for="company_phone" class="block font-semibold text-foreground mb-1">Nomor Telepon</label>
                        <input
                            id="company_phone"
                            type="text"
                            placeholder="+62 21..."
                            bind:value={companyForm.phone}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                </div>

                <div>
                    <label for="company_logo_url" class="block font-semibold text-foreground mb-1">URL Logo Perusahaan</label>
                    <input
                        id="company_logo_url"
                        type="url"
                        placeholder="https://images.unsplash.com/..."
                        bind:value={companyForm.logo_url}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                    />
                </div>

                <div>
                    <label for="company_address" class="block font-semibold text-foreground mb-1">Alamat Kantor</label>
                    <textarea
                        id="company_address"
                        rows="2"
                        placeholder="Gedung / Jalan, Kota..."
                        bind:value={companyForm.address}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isCompanyModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isSubmitting}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs"
                    >
                        {isSubmitting ? 'Menyimpan...' : 'Simpan Perusahaan'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
