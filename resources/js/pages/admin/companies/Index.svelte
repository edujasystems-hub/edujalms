<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/dashboard',
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
    import Building2 from '@lucide/svelte/icons/building-2';
    import Plus from '@lucide/svelte/icons/plus';
    import Search from '@lucide/svelte/icons/search';
    import Users from '@lucide/svelte/icons/users';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Mail from '@lucide/svelte/icons/mail';
    import Phone from '@lucide/svelte/icons/phone';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import X from '@lucide/svelte/icons/x';

    let {
        companies = [],
        filters = { search: '' },
    }: {
        companies: any[];
        filters: { search: string };
    } = $props();

    let search = $state(filters.search);

    function applySearch() {
        router.get('/admin/companies', { search: search || undefined }, { preserveState: true, replace: true });
    }

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

<AppHead title="Pengelolaan Perusahaan - Corporate LMS" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-foreground tracking-tight flex items-center gap-2">
                <Building2 class="size-6 text-indigo-600" />
                <span>Pengelolaan Perusahaan Klien</span>
            </h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Kelola mitra enterprise, entitas korporat, dan pengelompokan akun karyawan.
            </p>
        </div>
        <button
            type="button"
            onclick={openCreateCompany}
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
        >
            <Plus class="size-4" />
            <span>Tambah Perusahaan</span>
        </button>
    </div>

    <!-- Search Bar -->
    <div class="p-3 rounded-2xl border border-border bg-card shadow-xs flex items-center gap-3">
        <Search class="size-4 text-muted-foreground ml-2" />
        <input
            type="text"
            placeholder="Cari nama perusahaan, kode entitas, atau industri..."
            bind:value={search}
            onkeydown={(e) => e.key === 'Enter' && applySearch()}
            class="flex-1 text-xs bg-transparent border-0 focus:outline-hidden"
        />
        <button
            type="button"
            onclick={applySearch}
            class="px-4 py-1.5 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
        >
            Cari
        </button>
    </div>

    <!-- Companies Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        {#each companies as comp (comp.id)}
            <div class="rounded-2xl border border-border bg-card p-5 shadow-xs hover:border-indigo-400/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            {#if comp.logo_url}
                                <img
                                    src={comp.logo_url}
                                    alt={comp.name}
                                    class="size-12 rounded-xl object-cover border border-border shrink-0"
                                />
                            {:else}
                                <div class="size-12 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center font-bold text-base shrink-0">
                                    {comp.code || comp.name.charAt(0)}
                                </div>
                            {/if}
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <h3 class="font-bold text-sm text-foreground truncate">{comp.name}</h3>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="px-2 py-0.2 rounded text-[10px] font-mono font-bold bg-muted text-muted-foreground">
                                        {comp.code}
                                    </span>
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium truncate">
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
                                title="Edit Perusahaan"
                            >
                                <Edit3 class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                onclick={() => deleteCompany(comp)}
                                class="p-1.5 rounded-lg text-muted-foreground hover:text-rose-600 hover:bg-rose-50"
                                title="Hapus Perusahaan"
                            >
                                <Trash2 class="size-3.5" />
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs text-muted-foreground pt-1">
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
                            <div class="flex items-start gap-1.5 text-[11px] leading-tight">
                                <MapPin class="size-3 text-muted-foreground mt-0.5 shrink-0" />
                                <span class="line-clamp-2">{comp.address}</span>
                            </div>
                        {/if}
                    </div>
                </div>

                <div class="pt-3 border-t border-border/60 flex items-center justify-between text-xs text-muted-foreground">
                    <span class="inline-flex items-center gap-1 font-medium">
                        <Users class="size-3.5 text-indigo-600" />
                        <span>{comp.employees_count || 0} Karyawan</span>
                    </span>
                    <span class="inline-flex items-center gap-1 font-medium">
                        <BookOpen class="size-3.5 text-emerald-600" />
                        <span>{comp.courses_count || 0} Kelas Khusus</span>
                    </span>
                </div>
            </div>
        {/each}
    </div>
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
                        <label class="block font-semibold text-foreground mb-1">Nama Perusahaan *</label>
                        <input
                            type="text"
                            required
                            placeholder="Contoh: PT Telkom Indonesia Tbk"
                            bind:value={companyForm.name}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 focus:border-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Kode Unik *</label>
                        <input
                            type="text"
                            required
                            placeholder="TLKM"
                            bind:value={companyForm.code}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 uppercase font-mono"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Sektor / Industri</label>
                    <input
                        type="text"
                        placeholder="Telekomunikasi, Perbankan, Manufaktur..."
                        bind:value={companyForm.industry}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Email Kontak</label>
                        <input
                            type="email"
                            placeholder="corporate@telkom.co.id"
                            bind:value={companyForm.email}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Nomor Telepon</label>
                        <input
                            type="text"
                            placeholder="+62 21..."
                            bind:value={companyForm.phone}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">URL Logo Perusahaan</label>
                    <input
                        type="url"
                        placeholder="https://images.unsplash.com/..."
                        bind:value={companyForm.logo_url}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                    />
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Alamat Kantor</label>
                    <textarea
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
