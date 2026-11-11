<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/dashboard',
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
    import Award from '@lucide/svelte/icons/award';
    import Plus from '@lucide/svelte/icons/plus';
    import Search from '@lucide/svelte/icons/search';
    import Mail from '@lucide/svelte/icons/mail';
    import Phone from '@lucide/svelte/icons/phone';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import X from '@lucide/svelte/icons/x';

    let {
        tutors = [],
        filters = { search: '' },
    }: {
        tutors: any[];
        filters: { search: string };
    } = $props();

    let search = $state(filters.search);

    function applySearch() {
        router.get('/admin/tutors', { search: search || undefined }, { preserveState: true, replace: true });
    }

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

<AppHead title="Pengelolaan Tutor & Instruktur - Corporate LMS" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-foreground tracking-tight flex items-center gap-2">
                <Award class="size-6 text-indigo-600" />
                <span>Pengelolaan Tutor & Instruktur</span>
            </h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Kelola narasumber, praktisi ahli industri, dan instruktur kelas korporat.
            </p>
        </div>
        <button
            type="button"
            onclick={openCreateTutor}
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
        >
            <Plus class="size-4" />
            <span>Daftarkan Tutor Baru</span>
        </button>
    </div>

    <!-- Search Bar -->
    <div class="p-3 rounded-2xl border border-border bg-card shadow-xs flex items-center gap-3">
        <Search class="size-4 text-muted-foreground ml-2" />
        <input
            type="text"
            placeholder="Cari nama tutor, email, atau bidang keahlian..."
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

    <!-- Tutors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        {#each tutors as tutor (tutor.id)}
            <div class="rounded-2xl border border-border bg-card p-5 shadow-xs hover:border-indigo-400/50 transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            {#if tutor.avatar_url}
                                <img
                                    src={tutor.avatar_url}
                                    alt={tutor.name}
                                    class="size-12 rounded-xl object-cover border border-border shrink-0"
                                />
                            {:else}
                                <div class="size-12 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center font-bold text-base shrink-0">
                                    {tutor.name.charAt(0)}
                                </div>
                            {/if}
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-foreground truncate">{tutor.name}</h3>
                                <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium truncate">{tutor.title || 'Instruktur Korporat'}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <button
                                type="button"
                                onclick={() => openEditTutor(tutor)}
                                class="p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted"
                                title="Edit Tutor"
                            >
                                <Edit3 class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                onclick={() => deleteTutor(tutor)}
                                class="p-1.5 rounded-lg text-muted-foreground hover:text-rose-600 hover:bg-rose-50"
                                title="Hapus Tutor"
                            >
                                <Trash2 class="size-3.5" />
                            </button>
                        </div>
                    </div>

                    {#if tutor.specialization}
                        <div class="text-[11px] text-muted-foreground bg-muted/30 p-2 rounded-lg border border-border/50">
                            <strong>Spesialisasi:</strong> {tutor.specialization}
                        </div>
                    {/if}

                    {#if tutor.bio}
                        <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed">
                            {tutor.bio}
                        </p>
                    {/if}

                    <div class="space-y-1 text-xs text-muted-foreground pt-1">
                        <div class="flex items-center gap-1.5 truncate">
                            <Mail class="size-3 text-muted-foreground" />
                            <span class="truncate">{tutor.email}</span>
                        </div>
                        {#if tutor.phone}
                            <div class="flex items-center gap-1.5 truncate">
                                <Phone class="size-3 text-muted-foreground" />
                                <span>{tutor.phone}</span>
                            </div>
                        {/if}
                    </div>
                </div>

                <div class="pt-3 border-t border-border/60 flex items-center justify-between text-xs text-muted-foreground">
                    <span class="inline-flex items-center gap-1 font-medium">
                        <BookOpen class="size-3.5 text-indigo-600" />
                        <span>{tutor.courses_count || 0} Kelas Diampu</span>
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {tutor.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-muted text-muted-foreground'}">
                        {tutor.is_active ? 'AKTIF' : 'NONAKTIF'}
                    </span>
                </div>
            </div>
        {/each}
    </div>
</div>

<!-- Modal: Tambah / Edit Tutor -->
{#if isTutorModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    {editingTutor ? 'Edit Data Tutor' : 'Daftarkan Tutor Baru'}
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
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 focus:border-indigo-500"
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
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Nomor Telepon</label>
                        <input
                            type="text"
                            placeholder="+62 812..."
                            bind:value={tutorForm.phone}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Jabatan / Gelar Profesional</label>
                        <input
                            type="text"
                            placeholder="Contoh: VP of Artificial Intelligence"
                            bind:value={tutorForm.title}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Spesialisasi Keahlian</label>
                        <input
                            type="text"
                            placeholder="AI, Cloud, Leadership, Agile..."
                            bind:value={tutorForm.specialization}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">URL Foto Profil / Avatar</label>
                    <input
                        type="url"
                        placeholder="https://images.unsplash.com/..."
                        bind:value={tutorForm.avatar_url}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                    />
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Biografi Singkat</label>
                    <textarea
                        rows="3"
                        placeholder="Pengalaman karir dan rekam jejak industri instruktur..."
                        bind:value={tutorForm.bio}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
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
