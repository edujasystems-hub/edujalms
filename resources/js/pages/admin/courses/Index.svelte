<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/dashboard',
            },
            {
                title: 'Pengelolaan Kelas',
                href: '/admin/courses',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';
    import Plus from '@lucide/svelte/icons/plus';
    import Search from '@lucide/svelte/icons/search';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Users from '@lucide/svelte/icons/users';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import X from '@lucide/svelte/icons/x';

    let {
        courses = [],
        classGroups = [],
        tutors = [],
        companies = [],
        filters = { search: '', group_id: '', level: '' },
    }: {
        courses: any[];
        classGroups: any[];
        tutors: any[];
        companies: any[];
        filters: { search: string; group_id: string; level: string };
    } = $props();

    let search = $state(filters.search);
    let groupId = $state(filters.group_id);
    let level = $state(filters.level);

    function applyFilter() {
        router.get('/admin/courses', {
            search: search || undefined,
            group_id: groupId || undefined,
            level: level || undefined,
        }, { preserveState: true, replace: true });
    }

    // Modal state for creating new course
    let isCreateModalOpen = $state(false);
    let newCourseForm = $state({
        title: '',
        short_description: '',
        description: '',
        class_group_id: '',
        tutor_id: '',
        company_id: '',
        level: 'all_levels',
        estimated_duration_hours: 2.5,
        thumbnail_url: '',
        status: 'published',
    });

    let isSubmitting = $state(false);

    function submitCreateCourse(e: SubmitEvent) {
        e.preventDefault();
        isSubmitting = true;
        router.post('/admin/courses', newCourseForm, {
            onSuccess: () => {
                isCreateModalOpen = false;
                newCourseForm = {
                    title: '',
                    short_description: '',
                    description: '',
                    class_group_id: '',
                    tutor_id: '',
                    company_id: '',
                    level: 'all_levels',
                    estimated_duration_hours: 2.5,
                    thumbnail_url: '',
                    status: 'published',
                };
            },
            onFinish: () => {
                isSubmitting = false;
            },
        });
    }

    function deleteCourse(course: any) {
        if (confirm(`Apakah Anda yakin ingin menghapus kelas '${course.title}' beserta seluruh modulnya?`)) {
            router.delete(`/admin/courses/${course.id}`);
        }
    }
</script>

<AppHead title="Pengelolaan Kelas - Corporate LMS" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-foreground tracking-tight flex items-center gap-2">
                <GraduationCap class="size-6 text-indigo-600" />
                <span>Pengelolaan Kelas (Courses)</span>
            </h1>
            <p class="text-sm text-muted-foreground mt-0.5">
                Kelola kurikulum, modul pelajaran, instruktur/tutor, serta penugasan akses karyawan.
            </p>
        </div>
        <button
            type="button"
            onclick={() => (isCreateModalOpen = true)}
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
        >
            <Plus class="size-4" />
            <span>Tambah Kelas Baru</span>
        </button>
    </div>

    <!-- Search & Filters -->
    <div class="p-4 rounded-2xl border border-border bg-card shadow-xs flex flex-wrap gap-3 items-center">
        <div class="relative flex-1 min-w-[220px]">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
            <input
                type="text"
                placeholder="Cari judul kelas atau materi..."
                bind:value={search}
                onkeydown={(e) => e.key === 'Enter' && applyFilter()}
                class="w-full pl-9 pr-3 py-2 rounded-xl text-xs bg-muted/40 border border-border focus:outline-hidden focus:border-indigo-500"
            />
        </div>

        <select
            bind:value={groupId}
            onchange={applyFilter}
            class="py-2 px-3 rounded-xl text-xs bg-muted/40 border border-border text-foreground"
        >
            <option value="">Semua Group Kelas</option>
            {#each classGroups as grp}
                <option value={grp.id}>{grp.name}</option>
            {/each}
        </select>

        <select
            bind:value={level}
            onchange={applyFilter}
            class="py-2 px-3 rounded-xl text-xs bg-muted/40 border border-border text-foreground"
        >
            <option value="">Semua Tingkat</option>
            <option value="beginner">Pemula (Beginner)</option>
            <option value="intermediate">Menengah (Intermediate)</option>
            <option value="advanced">Mahir (Advanced)</option>
        </select>

        <button
            type="button"
            onclick={applyFilter}
            class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground transition-colors"
        >
            Filter
        </button>
    </div>

    <!-- Courses Table / Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        {#each courses as course (course.id)}
            <div class="group flex flex-col rounded-2xl border border-border bg-card overflow-hidden hover:border-indigo-400 hover:shadow-md transition-all">
                <div class="relative aspect-video w-full overflow-hidden bg-muted">
                    {#if course.thumbnail_url}
                        <img
                            src={course.thumbnail_url}
                            alt={course.title}
                            class="size-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                    {:else}
                        <div class="size-full flex items-center justify-center bg-indigo-50 dark:bg-indigo-950 text-indigo-600">
                            <BookOpen class="size-10" />
                        </div>
                    {/if}
                    <div class="absolute top-2 left-2 flex gap-1.5">
                        {#if course.class_group}
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-900/80 text-white backdrop-blur-xs">
                                {course.class_group.name}
                            </span>
                        {/if}
                    </div>
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {course.status === 'published' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'}">
                            {course.status.toUpperCase()}
                        </span>
                    </div>
                </div>

                <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                    <div>
                        <h3 class="text-sm font-bold text-foreground leading-snug group-hover:text-indigo-600 transition-colors line-clamp-2">
                            <Link href="/admin/courses/{course.id}">
                                {course.title}
                            </Link>
                        </h3>
                        {#if course.short_description}
                            <p class="text-xs text-muted-foreground line-clamp-2 mt-1">
                                {course.short_description}
                            </p>
                        {/if}
                    </div>

                    <div class="pt-2 border-t border-border/60 flex items-center justify-between text-xs text-muted-foreground">
                        <span class="flex items-center gap-1">
                            <BookOpen class="size-3.5 text-indigo-600" />
                            <span>{course.lessons_count} Pelajaran</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <Users class="size-3.5 text-emerald-600" />
                            <span>{course.enrolled_users_count} Karyawan</span>
                        </span>
                    </div>

                    <div class="pt-2 flex items-center justify-between gap-2">
                        <Link
                            href="/admin/courses/{course.id}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition-colors"
                        >
                            <Edit3 class="size-3.5" />
                            <span>Kelola Pelajaran & Akses</span>
                        </Link>
                        <button
                            type="button"
                            onclick={() => deleteCourse(course)}
                            class="p-2 rounded-xl text-muted-foreground hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                            title="Hapus Kelas"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        {/each}
    </div>

    {#if courses.length === 0}
        <div class="rounded-2xl border border-dashed border-border p-12 text-center bg-card">
            <GraduationCap class="size-12 mx-auto text-muted-foreground/40 mb-3" />
            <h3 class="text-sm font-semibold text-foreground">Tidak ada kelas ditemukan</h3>
            <p class="text-xs text-muted-foreground mt-1">Coba sesuaikan kata kunci pencarian atau buat kelas baru.</p>
        </div>
    {/if}
</div>

<!-- Modal: Buat Kelas Baru -->
{#if isCreateModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-card border border-border rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                    <Plus class="size-4.5 text-indigo-600" />
                    <span>Buat Kelas Korporat Baru</span>
                </h3>
                <button
                    type="button"
                    onclick={() => (isCreateModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitCreateCourse} class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-foreground mb-1">Judul Kelas *</label>
                    <input
                        type="text"
                        required
                        bind:value={newCourseForm.title}
                        placeholder="Contoh: Generative AI for Corporate Productivity"
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 focus:border-indigo-500"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Group / Kategori Kelas</label>
                        <select
                            bind:value={newCourseForm.class_group_id}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 text-foreground"
                        >
                            <option value="">-- Tanpa Group --</option>
                            {#each classGroups as grp}
                                <option value={grp.id}>{grp.name}</option>
                            {/each}
                        </select>
                        <p class="text-[10px] text-muted-foreground mt-0.5">
                            Karyawan di grup ini otomatis dapat mengakses kelas ini.
                        </p>
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Instruktur / Tutor</label>
                        <select
                            bind:value={newCourseForm.tutor_id}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 text-foreground"
                        >
                            <option value="">-- Pilih Tutor --</option>
                            {#each tutors as tut}
                                <option value={tut.id}>{tut.name} ({tut.specialization || 'Tutor'})</option>
                            {/each}
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Perusahaan Mitra (Opsional)</label>
                        <select
                            bind:value={newCourseForm.company_id}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 text-foreground"
                        >
                            <option value="">-- Semua Perusahaan --</option>
                            {#each companies as comp}
                                <option value={comp.id}>{comp.name}</option>
                            {/each}
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Tingkat Kesulitan</label>
                        <select
                            bind:value={newCourseForm.level}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 text-foreground"
                        >
                            <option value="all_levels">Semua Level</option>
                            <option value="beginner">Pemula (Beginner)</option>
                            <option value="intermediate">Menengah (Intermediate)</option>
                            <option value="advanced">Mahir (Advanced)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Estimasi Durasi (Jam)</label>
                        <input
                            type="number"
                            step="0.5"
                            min="0.5"
                            bind:value={newCourseForm.estimated_duration_hours}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">URL Gambar Thumbnail</label>
                    <input
                        type="url"
                        placeholder="https://images.unsplash.com/photo-..."
                        bind:value={newCourseForm.thumbnail_url}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                    />
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Deskripsi Singkat</label>
                    <textarea
                        rows="2"
                        placeholder="Rangkuman 1-2 kalimat untuk preview katalog..."
                        bind:value={newCourseForm.short_description}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                    ></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Silabus & Deskripsi Lengkap</label>
                    <textarea
                        rows="4"
                        placeholder="Detail penjelasan materi kelas..."
                        bind:value={newCourseForm.description}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isCreateModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isSubmitting}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs"
                    >
                        {isSubmitting ? 'Menyimpan...' : 'Simpan & Lanjut Kelola Modul'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
