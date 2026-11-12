<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/admin/dashboard',
            },
            {
                title: 'Tutor',
                href: '/admin/tutors',
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import DataTable, { type Column } from '@/components/DataTable.svelte';
    import Award from '@lucide/svelte/icons/award';
    import Plus from '@lucide/svelte/icons/plus';
    import Mail from '@lucide/svelte/icons/mail';
    import Phone from '@lucide/svelte/icons/phone';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import X from '@lucide/svelte/icons/x';

    let {
        tutors = [],
    }: {
        tutors: any[];
    } = $props();

    const columns: Column[] = [
        { key: 'name', label: 'Tutor & Gelar', sortable: true },
        { key: 'email', label: 'Kontak', sortable: true },
        { key: 'specialization', label: 'Spesialisasi', sortable: true },
        { key: 'courses_count', label: 'Kelas', sortable: true, align: 'center', class: 'w-24' },
        { key: 'is_active', label: 'Status', sortable: true, align: 'center', class: 'w-24' },
    ];

    // Modal state
    let isTutorModalOpen = $state(false);
    let editingTutor = $state<any | null>(null);

    let tutorForm = $state({
        name: '',
        email: '',
        phone: '',
        title: '',
        specialization: '',
        bio: '',
        avatar_url: '',
        is_active: true,
    });

    function openCreateTutor() {
        editingTutor = null;
        tutorForm = {
            name: '',
            email: '',
            phone: '',
            title: '',
            specialization: '',
            bio: '',
            avatar_url: '',
            is_active: true,
        };
        isTutorModalOpen = true;
    }

    function openEditTutor(t: any) {
        editingTutor = t;
        tutorForm = {
            name: t.name,
            email: t.email,
            phone: t.phone || '',
            title: t.title || '',
            specialization: t.specialization || '',
            bio: t.bio || '',
            avatar_url: t.avatar_url || '',
            is_active: !!t.is_active,
        };
        isTutorModalOpen = true;
    }

    let isSubmitting = $state(false);

    function submitTutor(e: SubmitEvent) {
        e.preventDefault();
        isSubmitting = true;

        if (editingTutor) {
            router.put(`/admin/tutors/${editingTutor.id}`, tutorForm, {
                onSuccess: () => (isTutorModalOpen = false),
                onFinish: () => (isSubmitting = false),
            });
        } else {
            router.post('/admin/tutors', tutorForm, {
                onSuccess: () => (isTutorModalOpen = false),
                onFinish: () => (isSubmitting = false),
            });
        }
    }

    function deleteTutor(t: any) {
        if (confirm(`Hapus data tutor '${t.name}'?`)) {
            router.delete(`/admin/tutors/${t.id}`);
        }
    }
</script>

<AppHead title="Tutor - Corporate LMS" />

<div class="flex-1 space-y-5 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <Breadcrumbs
                breadcrumbs={[
                    { title: 'Admin', href: '/admin/dashboard' },
                    { title: 'Tutor', href: '#' },
                ]}
                class="mb-2"
            />
            <h1 class="text-xl font-bold text-foreground tracking-tight flex items-center gap-2">
                <Award class="size-5 text-indigo-600" />
                <span>Tutor & Instruktur</span>
            </h1>
        </div>
        <button
            type="button"
            onclick={openCreateTutor}
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
        >
            <Plus class="size-4" />
            <span>Tambah Tutor</span>
        </button>
    </div>

    <!-- Reusable DataTable -->
    <DataTable
        data={tutors}
        columns={columns}
        searchKeys={['name', 'email', 'title', 'specialization']}
        placeholder="Cari nama, email, spesialisasi tutor..."
        initialPageSize={10}
        pageSizeOptions={[5, 10, 25, 50]}
    >
        {#snippet cell({ row, column })}
            {#if column.key === 'name'}
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-full overflow-hidden bg-muted flex items-center justify-center shrink-0 border border-border">
                        {#if row.avatar_url}
                            <img src={row.avatar_url} alt={row.name} class="size-full object-cover" />
                        {:else}
                            <span class="font-bold text-xs text-indigo-600">{row.name.charAt(0)}</span>
                        {/if}
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-foreground text-xs">{row.name}</div>
                        {#if row.title}
                            <div class="text-[11px] text-muted-foreground truncate">{row.title}</div>
                        {/if}
                    </div>
                </div>
            {:else if column.key === 'email'}
                <div class="space-y-0.5 text-[11px]">
                    <div class="flex items-center gap-1.5 text-foreground">
                        <Mail class="size-3 text-muted-foreground" />
                        <span>{row.email}</span>
                    </div>
                    {#if row.phone}
                        <div class="flex items-center gap-1.5 text-muted-foreground">
                            <Phone class="size-3" />
                            <span>{row.phone}</span>
                        </div>
                    {/if}
                </div>
            {:else if column.key === 'specialization'}
                <span class="text-[11px] font-medium text-foreground">
                    {row.specialization || '-'}
                </span>
            {:else if column.key === 'courses_count'}
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-muted font-medium text-[11px]">
                    <BookOpen class="size-3 text-indigo-500" />
                    <span>{row.courses_count ?? 0}</span>
                </span>
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
                    onclick={() => openEditTutor(row)}
                    title="Edit"
                    class="p-1.5 rounded-lg border border-border hover:bg-muted text-foreground transition-colors"
                >
                    <Edit3 class="size-3.5" />
                </button>
                <button
                    type="button"
                    onclick={() => deleteTutor(row)}
                    title="Hapus"
                    class="p-1.5 rounded-lg border border-border hover:bg-rose-50 dark:hover:bg-rose-950/50 text-rose-600 transition-colors"
                >
                    <Trash2 class="size-3.5" />
                </button>
            </div>
        {/snippet}
    </DataTable>
</div>

<!-- Modal: Tambah / Edit Tutor -->
{#if isTutorModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    {editingTutor ? 'Edit Data Tutor' : 'Tambah Tutor Baru'}
                </h3>
                <button
                    type="button"
                    onclick={() => (isTutorModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitTutor} class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-foreground mb-1">Nama Lengkap & Gelar *</label>
                    <input
                        type="text"
                        required
                        placeholder="Contoh: Dr. Budi Santoso, M.Kom"
                        bind:value={tutorForm.name}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:border-indigo-500 focus:outline-hidden"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Email *</label>
                        <input
                            type="email"
                            required
                            placeholder="budi.santoso@eduja.academy"
                            bind:value={tutorForm.email}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Nomor Telepon</label>
                        <input
                            type="text"
                            placeholder="+62 812..."
                            bind:value={tutorForm.phone}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Jabatan / Gelar Profesional</label>
                        <input
                            type="text"
                            placeholder="VP of Artificial Intelligence"
                            bind:value={tutorForm.title}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Spesialisasi Keahlian</label>
                        <input
                            type="text"
                            placeholder="AI, Cloud, Agile..."
                            bind:value={tutorForm.specialization}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">URL Foto Profil / Avatar</label>
                    <input
                        type="url"
                        placeholder="https://images.unsplash.com/..."
                        bind:value={tutorForm.avatar_url}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                    />
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Biografi Singkat</label>
                    <textarea
                        rows="3"
                        placeholder="Profil pengalaman mengajar dan keahlian praktis..."
                        bind:value={tutorForm.bio}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isTutorModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isSubmitting}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs"
                    >
                        {isSubmitting ? 'Menyimpan...' : 'Simpan Tutor'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
