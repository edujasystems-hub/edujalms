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
            {
                title: 'Detail Kelas & Modul',
                href: '#',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Plus from '@lucide/svelte/icons/plus';
    import Users from '@lucide/svelte/icons/users';
    import Video from '@lucide/svelte/icons/video';
    import Headphones from '@lucide/svelte/icons/headphones';
    import FileText from '@lucide/svelte/icons/file-text';
    import Download from '@lucide/svelte/icons/download';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import Mail from '@lucide/svelte/icons/mail';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import Layers from '@lucide/svelte/icons/layers';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import X from '@lucide/svelte/icons/x';

    let {
        course,
        allEmployees = [],
        classGroups = [],
        tutors = [],
        companies = [],
    }: {
        course: any;
        allEmployees: any[];
        classGroups: any[];
        tutors: any[];
        companies: any[];
    } = $props();

    let activeTab = $state<'lessons' | 'access'>('lessons');

    // Lesson Modal state
    let isLessonModalOpen = $state(false);
    let editingLesson = $state<any | null>(null);

    let lessonForm = $state({
        title: '',
        description: '',
        duration_minutes: 15,
        content_type: 'video',
        video_url: '',
        audio_url: '',
        content_text: '',
        pdf_url: '',
        attachments: [] as Array<{ name: string; url: string; size?: string; type?: string }>,
        is_preview: false,
    });

    let newAttachmentName = $state('');
    let newAttachmentUrl = $state('');
    let newAttachmentSize = $state('');

    function addAttachment() {
        if (!newAttachmentName || !newAttachmentUrl) return;
        lessonForm.attachments.push({
            name: newAttachmentName,
            url: newAttachmentUrl,
            size: newAttachmentSize || '1.0 MB',
            type: newAttachmentName.endsWith('.pdf') ? 'pdf' : (newAttachmentName.endsWith('.xlsx') ? 'excel' : 'file'),
        });
        newAttachmentName = '';
        newAttachmentUrl = '';
        newAttachmentSize = '';
    }

    function removeAttachment(index: number) {
        lessonForm.attachments.splice(index, 1);
    }

    function openCreateLesson() {
        editingLesson = null;
        lessonForm = {
            title: '',
            description: '',
            duration_minutes: 15,
            content_type: 'video',
            video_url: '',
            audio_url: '',
            content_text: '',
            pdf_url: '',
            attachments: [],
            is_preview: false,
        };
        isLessonModalOpen = true;
    }

    function openEditLesson(les: any) {
        editingLesson = les;
        lessonForm = {
            title: les.title,
            description: les.description || '',
            duration_minutes: les.duration_minutes || 15,
            content_type: les.content_type || 'video',
            video_url: les.video_url || '',
            audio_url: les.audio_url || '',
            content_text: les.content_text || '',
            pdf_url: les.pdf_url || '',
            attachments: les.attachments ? [...les.attachments] : [],
            is_preview: !!les.is_preview,
        };
        isLessonModalOpen = true;
    }

    let isSubmittingLesson = $state(false);

    function submitLesson(e: SubmitEvent) {
        e.preventDefault();
        isSubmittingLesson = true;

        if (editingLesson) {
            router.put(`/admin/courses/${course.id}/lessons/${editingLesson.id}`, lessonForm, {
                onSuccess: () => (isLessonModalOpen = false),
                onFinish: () => (isSubmittingLesson = false),
            });
        } else {
            router.post(`/admin/courses/${course.id}/lessons`, lessonForm, {
                onSuccess: () => (isLessonModalOpen = false),
                onFinish: () => (isSubmittingLesson = false),
            });
        }
    }

    function deleteLesson(les: any) {
        if (confirm(`Hapus pelajaran '${les.title}'?`)) {
            router.delete(`/admin/courses/${course.id}/lessons/${les.id}`);
        }
    }

    // Direct Assign Employees Modal
    let isAssignModalOpen = $state(false);
    let selectedUserIds = $state<number[]>([]);

    function toggleSelectUser(userId: number) {
        if (selectedUserIds.includes(userId)) {
            selectedUserIds = selectedUserIds.filter((id) => id !== userId);
        } else {
            selectedUserIds = [...selectedUserIds, userId];
        }
    }

    let isAssigning = $state(false);

    function submitAssignEmployees() {
        if (selectedUserIds.length === 0) return;
        isAssigning = true;
        router.post(`/admin/courses/${course.id}/assign`, { user_ids: selectedUserIds }, {
            onSuccess: () => {
                isAssignModalOpen = false;
                selectedUserIds = [];
            },
            onFinish: () => (isAssigning = false),
        });
    }

    function removeEnrolledEmployee(emp: any) {
        if (confirm(`Cabut akses kelas untuk '${emp.name}'?`)) {
            router.delete(`/admin/courses/${course.id}/remove-user/${emp.id}`);
        }
    }

    // Invite Employee Email
    let inviteEmail = $state('');
    let isInviting = $state(false);

    function submitInvite(e: SubmitEvent) {
        e.preventDefault();
        if (!inviteEmail) return;
        isInviting = true;
        router.post(`/admin/courses/${course.id}/invite`, { email: inviteEmail }, {
            onSuccess: () => (inviteEmail = ''),
            onFinish: () => (isInviting = false),
        });
    }
</script>

<AppHead title="{course.title} - Kelola Kelas & Modul" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-border/80">
        <div class="space-y-1">
            <Link
                href="/admin/courses"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors"
            >
                <ArrowLeft class="size-3.5" />
                <span>Kembali ke Daftar Kelas</span>
            </Link>
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <h1 class="text-2xl font-extrabold text-foreground tracking-tight">
                    {course.title}
                </h1>
                {#if course.class_group}
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                        📁 {course.class_group.name}
                    </span>
                {/if}
            </div>
            <p class="text-xs text-muted-foreground">
                Tutor: <span class="font-medium text-foreground">{course.tutor?.name || 'Belum ditentukan'}</span> • Durasi: {course.estimated_duration_hours} Jam
            </p>
        </div>

        <!-- Tab Switcher -->
        <div class="flex items-center p-1 rounded-xl bg-muted/60 border border-border shrink-0 text-xs font-semibold">
            <button
                type="button"
                onclick={() => (activeTab = 'lessons')}
                class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg transition-all {activeTab === 'lessons' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'}"
            >
                <BookOpen class="size-3.5 text-indigo-600" />
                <span>Modul Pelajaran ({course.lessons.length})</span>
            </button>
            <button
                type="button"
                onclick={() => (activeTab = 'access')}
                class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg transition-all {activeTab === 'access' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'}"
            >
                <Users class="size-3.5 text-emerald-600" />
                <span>Akses & Karyawan ({course.enrolled_users.length})</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: MODUL PELAJARAN (LESSONS) -->
    {#if activeTab === 'lessons'}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                        <BookOpen class="size-4.5 text-indigo-600" />
                        <span>Daftar Pelajaran / Kurikulum</span>
                    </h2>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Dukung video (YouTube embed), audio podcast, teks rangkuman, embed file PDF, dan berkas lampiran.
                    </p>
                </div>
                <button
                    type="button"
                    onclick={openCreateLesson}
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
                >
                    <Plus class="size-4" />
                    <span>Tambah Pelajaran</span>
                </button>
            </div>

            <!-- Lessons List -->
            <div class="space-y-3">
                {#each course.lessons as lesson, idx (lesson.id)}
                    <div class="rounded-2xl border border-border bg-card p-4 hover:border-indigo-400/60 shadow-xs transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="size-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 font-mono font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                #{idx + 1}
                            </div>
                            <div class="space-y-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="font-bold text-sm text-foreground">{lesson.title}</span>
                                    <span class="px-2 py-0.2 rounded text-[10px] uppercase font-semibold bg-muted text-muted-foreground">
                                        {lesson.content_type}
                                    </span>
                                    <span class="text-[11px] text-muted-foreground">⏱️ {lesson.duration_minutes} menit</span>
                                </div>
                                {#if lesson.description}
                                    <p class="text-xs text-muted-foreground line-clamp-1">{lesson.description}</p>
                                {/if}
                                <div class="flex flex-wrap items-center gap-3 pt-1 text-[11px] text-muted-foreground">
                                    {#if lesson.video_url}
                                        <span class="inline-flex items-center gap-1 text-indigo-600">
                                            <Video class="size-3" /> Video YouTube
                                        </span>
                                    {/if}
                                    {#if lesson.audio_url}
                                        <span class="inline-flex items-center gap-1 text-violet-600">
                                            <Headphones class="size-3" /> Audio MP3
                                        </span>
                                    {/if}
                                    {#if lesson.pdf_url}
                                        <span class="inline-flex items-center gap-1 text-rose-600">
                                            <FileText class="size-3" /> PDF Embed
                                        </span>
                                    {/if}
                                    {#if lesson.attachments && lesson.attachments.length > 0}
                                        <span class="inline-flex items-center gap-1 text-emerald-600">
                                            <Download class="size-3" /> {lesson.attachments.length} Lampiran
                                        </span>
                                    {/if}
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                            <button
                                type="button"
                                onclick={() => openEditLesson(lesson)}
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground transition-colors"
                            >
                                <Edit3 class="size-3.5" />
                                <span>Edit</span>
                            </button>
                            <button
                                type="button"
                                onclick={() => deleteLesson(lesson)}
                                class="p-1.5 rounded-lg text-muted-foreground hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 transition-colors"
                                title="Hapus Pelajaran"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </div>
                {/each}

                {#if course.lessons.length === 0}
                    <div class="rounded-2xl border border-dashed border-border p-12 text-center bg-card">
                        <BookOpen class="size-10 mx-auto text-muted-foreground/40 mb-3" />
                        <h3 class="text-sm font-semibold text-foreground">Belum ada modul pelajaran</h3>
                        <p class="text-xs text-muted-foreground mt-1">Mulai buat modul pertama untuk kelas ini.</p>
                        <button
                            type="button"
                            onclick={openCreateLesson}
                            class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500"
                        >
                            <Plus class="size-4" /> Tambah Pelajaran
                        </button>
                    </div>
                {/if}
            </div>
        </div>
    {/if}

    <!-- TAB 2: AKSES & KARYAWAN (INVITE & ASSIGN) -->
    {#if activeTab === 'access'}
        <div class="space-y-6">
            <!-- Group Auto-Access Callout -->
            {#if course.class_group}
                <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-900/60 flex items-start gap-3">
                    <Layers class="size-5 text-indigo-600 mt-0.5 shrink-0" />
                    <div class="text-xs text-indigo-950 dark:text-indigo-200 leading-relaxed">
                        <strong class="font-bold">Akses Otomatis Berbasis Group:</strong> Kelas ini terdaftar di group <strong>"{course.class_group.name}"</strong>. Semua karyawan yang di-assign ke group ini otomatis memiliki hak akses belajar ke kelas ini tanpa perlu di-assign manual satu per satu.
                    </div>
                </div>
            {/if}

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Invite via Email -->
                <div class="p-5 rounded-2xl border border-border bg-card shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <Mail class="size-4 text-indigo-600" />
                        <h3 class="text-xs font-bold text-foreground uppercase tracking-wider">Invite Employee Masuk Kelas</h3>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Kirim undangan akses kelas langsung ke email karyawan korporasi.
                    </p>
                    <form onsubmit={submitInvite} class="flex items-center gap-2">
                        <input
                            type="email"
                            required
                            placeholder="nama.karyawan@perusahaan.co.id"
                            bind:value={inviteEmail}
                            class="flex-1 px-3 py-2 rounded-xl text-xs border border-border bg-muted/30 focus:border-indigo-500"
                        />
                        <button
                            type="submit"
                            disabled={isInviting}
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs transition-colors shrink-0"
                        >
                            {isInviting ? 'Mengirim...' : 'Kirim Undangan'}
                        </button>
                    </form>
                </div>

                <!-- Direct Assign from Directory -->
                <div class="p-5 rounded-2xl border border-border bg-card shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <Users class="size-4 text-emerald-600" />
                        <h3 class="text-xs font-bold text-foreground uppercase tracking-wider">Assign Karyawan Terdaftar</h3>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Pilih karyawan dari direktori perusahaan untuk diberi akses kelas ini.
                    </p>
                    <button
                        type="button"
                        onclick={() => (isAssignModalOpen = true)}
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition-colors"
                    >
                        <Plus class="size-4" />
                        <span>Pilih & Assign Karyawan</span>
                    </button>
                </div>
            </div>

            <!-- Enrolled Employees Table -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-foreground">
                    Karyawan dengan Hak Akses Langsung ({course.enrolled_users.length})
                </h3>

                <div class="rounded-2xl border border-border bg-card overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-muted/50 border-b border-border/80 text-muted-foreground font-semibold">
                                <tr>
                                    <th class="p-3 pl-4">Karyawan</th>
                                    <th class="p-3">Posisi</th>
                                    <th class="p-3">Sumber Akses</th>
                                    <th class="p-3">Progress</th>
                                    <th class="p-3 pr-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/60">
                                {#each course.enrolled_users as emp (emp.id)}
                                    <tr class="hover:bg-muted/30">
                                        <td class="p-3 pl-4">
                                            <div class="font-semibold text-foreground">{emp.name}</div>
                                            <div class="text-[10px] text-muted-foreground">{emp.email}</div>
                                        </td>
                                        <td class="p-3 text-muted-foreground">
                                            {emp.position || 'Employee'}
                                        </td>
                                        <td class="p-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {emp.pivot?.enrollment_source === 'group' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'bg-muted text-muted-foreground'}">
                                                {emp.pivot?.enrollment_source?.toUpperCase() || 'DIRECT'}
                                            </span>
                                        </td>
                                        <td class="p-3 min-w-[130px]">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 h-1.5 rounded-full bg-muted overflow-hidden">
                                                    <div
                                                        class="h-full rounded-full {emp.pivot?.progress_percent === 100 ? 'bg-emerald-500' : 'bg-indigo-600'}"
                                                        style="width: {emp.pivot?.progress_percent || 0}%"
                                                    ></div>
                                                </div>
                                                <span class="font-mono font-bold text-[11px] text-foreground">{emp.pivot?.progress_percent || 0}%</span>
                                            </div>
                                        </td>
                                        <td class="p-3 pr-4 text-right">
                                            <button
                                                type="button"
                                                onclick={() => removeEnrolledEmployee(emp)}
                                                class="p-1.5 rounded-lg text-muted-foreground hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                title="Cabut Akses"
                                            >
                                                <Trash2 class="size-4" />
                                            </button>
                                        </td>
                                    </tr>
                                {/each}
                                {#if course.enrolled_users.length === 0}
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-muted-foreground">
                                            Belum ada karyawan yang di-assign secara langsung ke kelas ini.
                                        </td>
                                    </tr>
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>

<!-- Modal: Tambah / Edit Pelajaran -->
{#if isLessonModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-card border border-border rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    {editingLesson ? 'Edit Pelajaran' : 'Tambah Pelajaran Baru'}
                </h3>
                <button
                    type="button"
                    onclick={() => (isLessonModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitLesson} class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-foreground mb-1">Judul Pelajaran *</label>
                    <input
                        type="text"
                        required
                        bind:value={lessonForm.title}
                        placeholder="Contoh: Mastering Prompt Engineering C.R.A.F.T."
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 focus:border-indigo-500"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Tipe Materi Utama *</label>
                        <select
                            bind:value={lessonForm.content_type}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 text-foreground"
                        >
                            <option value="video">Video (YouTube Embed)</option>
                            <option value="audio">Audio / Podcast</option>
                            <option value="text">Teks Bacaan</option>
                            <option value="pdf">Dokumen PDF Tersemat (Embed)</option>
                            <option value="mixed">Kombinasi (Mixed Media)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Estimasi Durasi (Menit)</label>
                        <input
                            type="number"
                            min="1"
                            bind:value={lessonForm.duration_minutes}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                </div>

                <!-- Video YouTube URL -->
                {#if lessonForm.content_type === 'video' || lessonForm.content_type === 'mixed'}
                    <div>
                        <label class="block font-semibold text-foreground mb-1 flex items-center gap-1.5">
                            <Video class="size-3.5 text-indigo-600" />
                            <span>Link Video YouTube (Embed)</span>
                        </label>
                        <input
                            type="url"
                            placeholder="https://www.youtube.com/watch?v=..."
                            bind:value={lessonForm.video_url}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                {/if}

                <!-- Audio URL -->
                {#if lessonForm.content_type === 'audio' || lessonForm.content_type === 'mixed'}
                    <div>
                        <label class="block font-semibold text-foreground mb-1 flex items-center gap-1.5">
                            <Headphones class="size-3.5 text-violet-600" />
                            <span>URL File Audio / Podcast (MP3)</span>
                        </label>
                        <input
                            type="url"
                            placeholder="https://example.com/podcast-lesson.mp3"
                            bind:value={lessonForm.audio_url}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                {/if}

                <!-- PDF URL -->
                {#if lessonForm.content_type === 'pdf' || lessonForm.content_type === 'mixed'}
                    <div>
                        <label class="block font-semibold text-foreground mb-1 flex items-center gap-1.5">
                            <FileText class="size-3.5 text-rose-600" />
                            <span>URL File PDF untuk Embed</span>
                        </label>
                        <input
                            type="url"
                            placeholder="https://example.com/modul-pembelajaran.pdf"
                            bind:value={lessonForm.pdf_url}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30"
                        />
                    </div>
                {/if}

                <!-- Text Content -->
                <div>
                    <label class="block font-semibold text-foreground mb-1">Materi Teks / Catatan Pembelajaran</label>
                    <textarea
                        rows="4"
                        placeholder="Tuliskan catatan, rangkuman, instruksi latihan mandiri..."
                        bind:value={lessonForm.content_text}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-muted/30 font-mono text-xs"
                    ></textarea>
                </div>

                <!-- Attachments Manager -->
                <div class="space-y-2 p-3 rounded-xl border border-border bg-muted/20">
                    <span class="block font-semibold text-foreground">Lampiran Berkas (Attachments)</span>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input
                            type="text"
                            placeholder="Nama file (e.g. Lembar_Latihan.xlsx)"
                            bind:value={newAttachmentName}
                            class="flex-1 px-3 py-1.5 rounded-lg border border-border bg-background"
                        />
                        <input
                            type="url"
                            placeholder="URL File Download"
                            bind:value={newAttachmentUrl}
                            class="flex-1 px-3 py-1.5 rounded-lg border border-border bg-background"
                        />
                        <button
                            type="button"
                            onclick={addAttachment}
                            class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold shrink-0 hover:bg-indigo-500"
                        >
                            + Tambah
                        </button>
                    </div>

                    {#if lessonForm.attachments.length > 0}
                        <div class="space-y-1.5 pt-2">
                            {#each lessonForm.attachments as att, idx}
                                <div class="flex items-center justify-between p-2 rounded-lg bg-background border border-border text-xs">
                                    <span class="truncate">{att.name}</span>
                                    <button
                                        type="button"
                                        onclick={() => removeAttachment(idx)}
                                        class="text-rose-600 hover:underline text-[11px] ml-2"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isLessonModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isSubmittingLesson}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs"
                    >
                        {isSubmittingLesson ? 'Menyimpan...' : 'Simpan Pelajaran'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Modal: Assign Karyawan Terdaftar -->
{#if isAssignModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    Assign Karyawan ke Kelas Ini
                </h3>
                <button
                    type="button"
                    onclick={() => (isAssignModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <p class="text-xs text-muted-foreground">
                Pilih karyawan yang akan langsung diberikan hak akses ke kelas <strong>{course.title}</strong>:
            </p>

            <div class="space-y-1.5 max-h-[300px] overflow-y-auto pr-1">
                {#each allEmployees as emp (emp.id)}
                    {@const isEnrolled = course.enrolled_users.some((u: any) => u.id === emp.id)}
                    <label class="flex items-center gap-3 p-2.5 rounded-xl border border-border/70 hover:bg-muted/40 cursor-pointer {isEnrolled ? 'opacity-50 cursor-not-allowed bg-muted/20' : ''}">
                        <input
                            type="checkbox"
                            disabled={isEnrolled}
                            checked={selectedUserIds.includes(emp.id) || isEnrolled}
                            onchange={() => toggleSelectUser(emp.id)}
                            class="rounded border-border text-indigo-600 size-4"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-xs text-foreground truncate">{emp.name}</div>
                            <div class="text-[10px] text-muted-foreground truncate">{emp.email} • {emp.company?.name || 'Perusahaan'}</div>
                        </div>
                        {#if isEnrolled}
                            <span class="text-[10px] font-semibold text-emerald-600">Sudah Terdaftar</span>
                        {/if}
                    </label>
                {/each}
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-border/80">
                <span class="text-xs text-muted-foreground">
                    {selectedUserIds.length} dipilih
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
                        {isAssigning ? 'Memproses...' : 'Assign Terpilih'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}
