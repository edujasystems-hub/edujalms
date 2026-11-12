<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/admin/dashboard',
            },
            {
                title: 'Kelas',
                href: '/admin/courses',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';
    import Plus from '@lucide/svelte/icons/plus';
    import Search from '@lucide/svelte/icons/search';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Users from '@lucide/svelte/icons/users';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import Calendar from '@lucide/svelte/icons/calendar';
    import X from '@lucide/svelte/icons/x';
    import { formatDate } from '@/lib/utils';

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
        points: 200,
        thumbnail_url: '',
        status: 'published',
        due_date: '',
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
                    points: 200,
                    thumbnail_url: '',
                    status: 'published',
                    due_date: '',
                };
            },
            onFinish: () => {
                isSubmitting = false;
            },
        });
    }

    function deleteCourse(course: any) {
        if (confirm(`Hapus kelas '${course.title}'?`)) {
            router.delete(`/admin/courses/${course.id}`);
        }
    }
</script>

<AppHead title="Kelas - Corporate LMS" />

<div class="flex-1 space-y-5 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <Breadcrumbs
                breadcrumbs={[
                    { title: 'Admin', href: '/admin/dashboard' },
                    { title: 'Kelas', href: '#' },
                ]}
                class="mb-2"
            />
            <h1 class="text-xl font-bold text-foreground tracking-tight flex items-center gap-2">
                <GraduationCap class="size-5 text-indigo-600" />
                <span>Kelas</span>
            </h1>
        </div>
        <button
            type="button"
            onclick={() => (isCreateModalOpen = true)}
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
        >
            <Plus class="size-4" />
            <span>Tambah Kelas</span>
        </button>
    </div>

    <!-- Search & Filters -->
    <div class="p-3 rounded-2xl border border-border bg-card shadow-xs flex flex-wrap gap-2.5 items-center">
        <div class="relative flex-1 min-w-[200px]">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
            <input
                type="text"
                placeholder="Cari kelas..."
                bind:value={search}
                onkeydown={(e) => e.key === 'Enter' && applyFilter()}
                class="w-full pl-9 pr-3 py-1.5 rounded-xl text-xs bg-muted/30 border border-border focus:outline-hidden focus:border-indigo-500"
            />
        </div>

        <select
            bind:value={groupId}
            onchange={applyFilter}
            class="py-1.5 pl-3 pr-8 rounded-xl text-xs bg-card border border-border text-foreground"
        >
            <option value="">Semua Kategori</option>
            {#each classGroups as grp}
                <option value={grp.id}>{grp.name}</option>
            {/each}
        </select>

        <select
            bind:value={level}
            onchange={applyFilter}
            class="py-1.5 pl-3 pr-8 rounded-xl text-xs bg-card border border-border text-foreground"
        >
            <option value="">Semua Tingkat</option>
            <option value="beginner">Pemula</option>
            <option value="intermediate">Menengah</option>
            <option value="advanced">Mahir</option>
        </select>

        <button
            type="button"
            onclick={applyFilter}
            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground transition-colors"
        >
            Filter
        </button>
    </div>

    <!-- 4-Kolom Courses Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        {#each courses as course (course.id)}
            <div class="group flex flex-col rounded-2xl border border-border bg-card overflow-hidden hover:border-indigo-400/60 hover:shadow-md transition-all">
                <div class="relative aspect-video w-full overflow-hidden bg-muted">
                    {#if course.thumbnail_url}
                        <img
                            src={course.thumbnail_url}
                            alt={course.title}
                            class="size-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                    {:else}
                        <div class="size-full flex items-center justify-center bg-indigo-50 dark:bg-indigo-950 text-indigo-600">
                            <BookOpen class="size-8" />
                        </div>
                    {/if}
                    <div class="absolute top-2 left-2 flex gap-1.5">
                        {#if course.class_group}
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-black/75 text-white backdrop-blur-xs">
                                {course.class_group.name}
                            </span>
                        {/if}
                    </div>
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {course.status === 'published' ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white'}">
                            {course.status.toUpperCase()}
                        </span>
                    </div>
                </div>

                <div class="p-3.5 flex-1 flex flex-col justify-between space-y-3">
                    <div class="space-y-1">
                        <h3 class="text-xs font-bold text-foreground leading-snug group-hover:text-indigo-600 transition-colors line-clamp-2">
                            <Link href="/admin/courses/{course.id}">
                                {course.title}
                            </Link>
                        </h3>

                        {#if course.due_date}
                            <div class="flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                                <Calendar class="size-3" />
                                <span>Batas: {formatDate(course.due_date)}</span>
                            </div>
                        {/if}
                    </div>

                    <div class="pt-2 border-t border-border/60 flex items-center justify-between text-[11px] text-muted-foreground">
                        <span class="flex items-center gap-1">
                            <BookOpen class="size-3 text-indigo-500" />
                            <span>{course.lessons_count} Pelajaran</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <Users class="size-3 text-emerald-500" />
                            <span>{course.enrolled_users_count} Karyawan</span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-1.5">
                        <Link
                            href="/admin/courses/{course.id}"
                            class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition-colors"
                        >
                            <Edit3 class="size-3.5" />
                            <span>Kelola</span>
                        </Link>
                        <button
                            type="button"
                            onclick={() => deleteCourse(course)}
                            class="p-1.5 rounded-xl text-muted-foreground hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                            title="Hapus Kelas"
                        >
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        {/each}
    </div>

    {#if courses.length === 0}
        <div class="p-8 rounded-2xl border border-dashed border-border text-center">
            <GraduationCap class="size-10 mx-auto text-muted-foreground/40 mb-2" />
            <h3 class="text-xs font-semibold text-foreground">Tidak ada kelas ditemukan</h3>
        </div>
    {/if}
</div>

<!-- Modal: Buat Kelas Baru -->
{#if isCreateModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-card border border-border rounded-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                    <Plus class="size-4 text-indigo-600" />
                    <span>Tambah Kelas</span>
                </h3>
                <button
                    type="button"
                    onclick={() => (isCreateModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitCreateCourse} class="space-y-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-foreground mb-1">Judul Kelas *</label>
                    <input
                        type="text"
                        required
                        bind:value={newCourseForm.title}
                        placeholder="Contoh: Generative AI for Corporate Productivity"
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:border-indigo-500 focus:outline-hidden"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Kategori Kelas</label>
                        <select
                            bind:value={newCourseForm.class_group_id}
                            class="w-full pl-3 pr-8 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                        >
                            <option value="">-- Tanpa Kategori --</option>
                            {#each classGroups as grp}
                                <option value={grp.id}>{grp.name}</option>
                            {/each}
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Instruktur / Tutor</label>
                        <select
                            bind:value={newCourseForm.tutor_id}
                            class="w-full pl-3 pr-8 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                        >
                            <option value="">-- Pilih Tutor --</option>
                            {#each tutors as tut}
                                <option value={tut.id}>{tut.name}</option>
                            {/each}
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Tingkat</label>
                        <select
                            bind:value={newCourseForm.level}
                            class="w-full pl-3 pr-8 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                        >
                            <option value="all_levels">Semua Level</option>
                            <option value="beginner">Pemula</option>
                            <option value="intermediate">Menengah</option>
                            <option value="advanced">Mahir</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Tenggat Waktu / Due Date</label>
                        <input
                            type="date"
                            bind:value={newCourseForm.due_date}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Estimasi Durasi (Jam)</label>
                        <input
                            type="number"
                            step="0.5"
                            min="0.5"
                            bind:value={newCourseForm.estimated_duration_hours}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Bonus Skor Kelulusan (XP)</label>
                        <input
                            type="number"
                            min="0"
                            step="10"
                            bind:value={newCourseForm.points}
                            placeholder="200"
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">URL Gambar Thumbnail</label>
                    <input
                        type="url"
                        placeholder="https://images.unsplash.com/..."
                        bind:value={newCourseForm.thumbnail_url}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                    />
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Deskripsi Singkat</label>
                    <textarea
                        rows="2"
                        placeholder="Deskripsi ringkas kelas..."
                        bind:value={newCourseForm.short_description}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
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
                        {isSubmitting ? 'Menyimpan...' : 'Simpan Kelas'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
