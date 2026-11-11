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
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Users from '@lucide/svelte/icons/users';
    import Plus from '@lucide/svelte/icons/plus';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Video from '@lucide/svelte/icons/video';
    import Headphones from '@lucide/svelte/icons/headphones';
    import FileText from '@lucide/svelte/icons/file-text';
    import Paperclip from '@lucide/svelte/icons/paperclip';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import Mail from '@lucide/svelte/icons/mail';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import Calendar from '@lucide/svelte/icons/calendar';
    import HelpCircle from '@lucide/svelte/icons/help-circle';
    import X from '@lucide/svelte/icons/x';

    interface TestQuestion {
        question: string;
        options: string[];
        correct_answer: number;
        explanation: string;
    }

    interface TestData {
        title: string;
        instructions: string;
        passing_score: number;
        questions: TestQuestion[];
    }

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
        due_date: '',
        attachments: [] as Array<{ name: string; url: string; size?: string; type?: string }>,
        is_preview: false,
        has_pre_test: false,
        pre_test: {
            title: 'Pre-Test Materi',
            instructions: 'Jawab pertanyaan sebelum memulai materi.',
            passing_score: 70,
            questions: [] as TestQuestion[],
        },
        has_post_test: false,
        post_test: {
            title: 'Post-Test Evaluasi',
            instructions: 'Uji pemahaman Anda setelah menyelesaikan materi.',
            passing_score: 70,
            questions: [] as TestQuestion[],
        },
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

    function addPreTestQuestion() {
        lessonForm.pre_test.questions.push({
            question: '',
            options: ['', '', '', ''],
            correct_answer: 0,
            explanation: '',
        });
    }

    function removePreTestQuestion(index: number) {
        lessonForm.pre_test.questions.splice(index, 1);
    }

    function addPostTestQuestion() {
        lessonForm.post_test.questions.push({
            question: '',
            options: ['', '', '', ''],
            correct_answer: 0,
            explanation: '',
        });
    }

    function removePostTestQuestion(index: number) {
        lessonForm.post_test.questions.splice(index, 1);
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
            due_date: '',
            attachments: [],
            is_preview: false,
            has_pre_test: false,
            pre_test: {
                title: 'Pre-Test Materi',
                instructions: 'Jawab pertanyaan sebelum menyimak materi.',
                passing_score: 70,
                questions: [],
            },
            has_post_test: false,
            post_test: {
                title: 'Post-Test Evaluasi',
                instructions: 'Uji pemahaman Anda setelah menyelesaikan materi.',
                passing_score: 70,
                questions: [],
            },
        };
        isLessonModalOpen = true;
    }

    function openEditLesson(les: any) {
        editingLesson = les;
        const pre = les.pre_test;
        const post = les.post_test;

        lessonForm = {
            title: les.title,
            description: les.description || '',
            duration_minutes: les.duration_minutes || 15,
            content_type: les.content_type || 'video',
            video_url: les.video_url || '',
            audio_url: les.audio_url || '',
            content_text: les.content_text || '',
            pdf_url: les.pdf_url || '',
            due_date: les.due_date ? String(les.due_date).substring(0, 10) : '',
            attachments: les.attachments ? [...les.attachments] : [],
            is_preview: !!les.is_preview,
            has_pre_test: !!pre && Array.isArray(pre.questions) && pre.questions.length > 0,
            pre_test: pre ? JSON.parse(JSON.stringify(pre)) : {
                title: 'Pre-Test Materi',
                instructions: 'Jawab pertanyaan sebelum menyimak materi.',
                passing_score: 70,
                questions: [],
            },
            has_post_test: !!post && Array.isArray(post.questions) && post.questions.length > 0,
            post_test: post ? JSON.parse(JSON.stringify(post)) : {
                title: 'Post-Test Evaluasi',
                instructions: 'Uji pemahaman Anda setelah menyelesaikan materi.',
                passing_score: 70,
                questions: [],
            },
        };
        isLessonModalOpen = true;
    }

    let isSubmittingLesson = $state(false);

    function submitLesson(e: SubmitEvent) {
        e.preventDefault();
        isSubmittingLesson = true;

        const payload = {
            title: lessonForm.title,
            description: lessonForm.description,
            duration_minutes: lessonForm.duration_minutes,
            content_type: lessonForm.content_type,
            video_url: lessonForm.video_url || null,
            audio_url: lessonForm.audio_url || null,
            content_text: lessonForm.content_text || null,
            pdf_url: lessonForm.pdf_url || null,
            due_date: lessonForm.due_date || null,
            attachments: lessonForm.attachments,
            is_preview: lessonForm.is_preview,
            pre_test: lessonForm.has_pre_test && lessonForm.pre_test.questions.length > 0 ? lessonForm.pre_test : null,
            post_test: lessonForm.has_post_test && lessonForm.post_test.questions.length > 0 ? lessonForm.post_test : null,
        };

        if (editingLesson) {
            router.put(`/admin/courses/${course.id}/lessons/${editingLesson.id}`, payload, {
                onSuccess: () => (isLessonModalOpen = false),
                onFinish: () => (isSubmittingLesson = false),
            });
        } else {
            router.post(`/admin/courses/${course.id}/lessons`, payload, {
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

    // Edit Course Modal
    let isEditCourseModalOpen = $state(false);
    let courseEditForm = $state({
        title: course.title,
        short_description: course.short_description || '',
        description: course.description || '',
        class_group_id: course.class_group_id || '',
        tutor_id: course.tutor_id || '',
        company_id: course.company_id || '',
        level: course.level || 'all_levels',
        estimated_duration_hours: course.estimated_duration_hours || 2.5,
        thumbnail_url: course.thumbnail_url || '',
        status: course.status || 'published',
        due_date: course.due_date ? String(course.due_date).substring(0, 10) : '',
    });

    let isUpdatingCourse = $state(false);

    function submitUpdateCourse(e: SubmitEvent) {
        e.preventDefault();
        isUpdatingCourse = true;
        router.put(`/admin/courses/${course.id}`, courseEditForm, {
            onSuccess: () => (isEditCourseModalOpen = false),
            onFinish: () => (isUpdatingCourse = false),
        });
    }
</script>

<AppHead title="{course.title} - Kelola Kelas" />

<div class="flex-1 space-y-5 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-border/80">
        <div class="space-y-1">
            <Link
                href="/admin/courses"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors"
            >
                <ArrowLeft class="size-3.5" />
                <span>Kembali ke Kelas</span>
            </Link>
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <h1 class="text-xl font-bold text-foreground tracking-tight">
                    {course.title}
                </h1>
                {#if course.class_group}
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                        {course.class_group.name}
                    </span>
                {/if}
                {#if course.due_date}
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                        <Calendar class="size-3" />
                        <span>Batas: {String(course.due_date).substring(0, 10)}</span>
                    </span>
                {/if}
            </div>
            <p class="text-xs text-muted-foreground">
                Tutor: <span class="font-medium text-foreground">{course.tutor?.name || '-'}</span> • Durasi: {course.estimated_duration_hours} Jam
            </p>
        </div>

        <!-- Tab Switcher & Edit Course CTA -->
        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick={() => (isEditCourseModalOpen = true)}
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-border bg-card hover:bg-muted text-xs font-semibold transition-colors"
            >
                <Edit3 class="size-3.5" />
                <span>Ubah Kelas</span>
            </button>

            <div class="flex items-center p-1 rounded-xl bg-muted/60 border border-border text-xs font-semibold">
                <button
                    type="button"
                    onclick={() => (activeTab = 'lessons')}
                    class="flex items-center gap-1.5 px-3 py-1 rounded-lg transition-all {activeTab === 'lessons' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'}"
                >
                    <BookOpen class="size-3.5 text-indigo-600" />
                    <span>Pelajaran ({course.lessons.length})</span>
                </button>
                <button
                    type="button"
                    onclick={() => (activeTab = 'access')}
                    class="flex items-center gap-1.5 px-3 py-1 rounded-lg transition-all {activeTab === 'access' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'}"
                >
                    <Users class="size-3.5 text-emerald-600" />
                    <span>Akses ({course.enrolled_users.length})</span>
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 1: MODUL PELAJARAN (LESSONS) -->
    {#if activeTab === 'lessons'}
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-foreground flex items-center gap-2">
                    <BookOpen class="size-4 text-indigo-600" />
                    <span>Daftar Pelajaran ({course.lessons.length})</span>
                </h2>
                <button
                    type="button"
                    onclick={openCreateLesson}
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
                >
                    <Plus class="size-3.5" />
                    <span>Tambah Pelajaran</span>
                </button>
            </div>

            <!-- Lessons List -->
            <div class="space-y-2.5">
                {#each course.lessons as lesson, idx (lesson.id)}
                    <div class="rounded-2xl border border-border bg-card p-3.5 hover:border-indigo-400/60 shadow-xs transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="size-7 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-600 font-mono font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                #{idx + 1}
                            </div>
                            <div class="space-y-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-xs text-foreground">{lesson.title}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] uppercase font-semibold bg-muted text-muted-foreground">
                                        {lesson.content_type}
                                    </span>
                                    <span class="text-[11px] text-muted-foreground">⏱️ {lesson.duration_minutes}m</span>

                                    {#if lesson.due_date}
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                            <Calendar class="size-2.5" />
                                            <span>Batas: {String(lesson.due_date).substring(0, 10)}</span>
                                        </span>
                                    {/if}

                                    {#if lesson.pre_test && lesson.pre_test.questions?.length > 0}
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                            Pre-Test ({lesson.pre_test.questions.length} Soal)
                                        </span>
                                    {/if}

                                    {#if lesson.post_test && lesson.post_test.questions?.length > 0}
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                            Post-Test ({lesson.post_test.questions.length} Soal)
                                        </span>
                                    {/if}
                                </div>

                                {#if lesson.description}
                                    <p class="text-xs text-muted-foreground line-clamp-1">{lesson.description}</p>
                                {/if}
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                            <button
                                type="button"
                                onclick={() => openEditLesson(lesson)}
                                class="p-1.5 rounded-lg border border-border hover:bg-muted text-foreground transition-colors"
                                title="Edit Pelajaran"
                            >
                                <Edit3 class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                onclick={() => deleteLesson(lesson)}
                                class="p-1.5 rounded-lg border border-border hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 transition-colors"
                                title="Hapus Pelajaran"
                            >
                                <Trash2 class="size-3.5" />
                            </button>
                        </div>
                    </div>
                {/each}

                {#if course.lessons.length === 0}
                    <div class="p-8 rounded-2xl border border-dashed border-border text-center">
                        <BookOpen class="size-8 mx-auto text-muted-foreground/40 mb-2" />
                        <h3 class="text-xs font-semibold text-foreground">Belum ada pelajaran</h3>
                    </div>
                {/if}
            </div>
        </div>
    {:else}
        <!-- TAB 2: AKSES KARYAWAN -->
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-foreground flex items-center gap-2">
                    <Users class="size-4 text-emerald-600" />
                    <span>Karyawan Terdaftar ({course.enrolled_users.length})</span>
                </h2>
                <button
                    type="button"
                    onclick={() => (isAssignModalOpen = true)}
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
                >
                    <UserPlus class="size-3.5" />
                    <span>Assign Karyawan</span>
                </button>
            </div>

            <div class="divide-y divide-border rounded-2xl border border-border bg-card overflow-hidden">
                {#each course.enrolled_users as user (user.id)}
                    <div class="p-3.5 flex items-center justify-between gap-3 hover:bg-muted/20 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="size-7 rounded-full bg-muted flex items-center justify-center font-bold text-xs shrink-0">
                                {user.name.charAt(0)}
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-xs text-foreground truncate">{user.name}</div>
                                <div class="text-[11px] text-muted-foreground truncate">{user.email} • {user.pivot?.enrollment_source === 'group' ? 'Via Kategori' : 'Penugasan Langsung'}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold {user.pivot?.progress_percent === 100 ? 'text-emerald-600' : 'text-indigo-600'}">
                                {user.pivot?.progress_percent ?? 0}%
                            </span>
                            <button
                                type="button"
                                onclick={() => removeEnrolledEmployee(user)}
                                class="text-rose-600 hover:underline text-xs"
                            >
                                Cabut
                            </button>
                        </div>
                    </div>
                {/each}

                {#if course.enrolled_users.length === 0}
                    <div class="p-6 text-center text-xs text-muted-foreground">
                        Belum ada karyawan yang terdaftar langsung pada kelas ini.
                    </div>
                {/if}
            </div>
        </div>
    {/if}
</div>

<!-- Modal: Tambah / Edit Pelajaran (dengan Pre-Test & Post-Test builder) -->
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
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:border-indigo-500 focus:outline-hidden"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Tipe Media *</label>
                        <select
                            bind:value={lessonForm.content_type}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                        >
                            <option value="video">Video (YouTube)</option>
                            <option value="audio">Audio / Podcast</option>
                            <option value="text">Teks Bacaan</option>
                            <option value="pdf">Dokumen PDF Embed</option>
                            <option value="mixed">Kombinasi Media</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Durasi (Menit)</label>
                        <input
                            type="number"
                            min="1"
                            bind:value={lessonForm.duration_minutes}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Tenggat (Due Date)</label>
                        <input
                            type="date"
                            bind:value={lessonForm.due_date}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                        />
                    </div>
                </div>

                <!-- Video YouTube URL -->
                {#if lessonForm.content_type === 'video' || lessonForm.content_type === 'mixed'}
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Link Video YouTube (Embed)</label>
                        <input
                            type="url"
                            placeholder="https://www.youtube.com/watch?v=..."
                            bind:value={lessonForm.video_url}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>
                {/if}

                <!-- Audio URL -->
                {#if lessonForm.content_type === 'audio' || lessonForm.content_type === 'mixed'}
                    <div>
                        <label class="block font-semibold text-foreground mb-1">URL File Audio (MP3)</label>
                        <input
                            type="url"
                            placeholder="https://example.com/audio.mp3"
                            bind:value={lessonForm.audio_url}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>
                {/if}

                <!-- PDF URL -->
                {#if lessonForm.content_type === 'pdf' || lessonForm.content_type === 'mixed'}
                    <div>
                        <label class="block font-semibold text-foreground mb-1">URL File PDF Embed</label>
                        <input
                            type="url"
                            placeholder="https://example.com/document.pdf"
                            bind:value={lessonForm.pdf_url}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                        />
                    </div>
                {/if}

                <!-- Text Content -->
                <div>
                    <label class="block font-semibold text-foreground mb-1">Catatan / Rangkuman Teks</label>
                    <textarea
                        rows="3"
                        placeholder="Materi ringkas atau catatan pembelajaran..."
                        bind:value={lessonForm.content_text}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                    ></textarea>
                </div>

                <!-- PRE-TEST BUILDER (Opsional) -->
                <div class="p-3.5 rounded-xl border border-border bg-muted/20 space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            bind:checked={lessonForm.has_pre_test}
                            class="rounded border-border text-indigo-600 size-4"
                        />
                        <span class="font-bold text-foreground">Sertakan Pre-Test (Opsional)</span>
                    </label>

                    {#if lessonForm.has_pre_test}
                        <div class="space-y-3 pt-2 border-t border-border/60">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-foreground mb-1">Judul Pre-Test</label>
                                    <input
                                        type="text"
                                        bind:value={lessonForm.pre_test.title}
                                        class="w-full px-3 py-1.5 rounded-lg border border-border bg-card"
                                    />
                                </div>
                                <div>
                                    <label class="block font-semibold text-foreground mb-1">Passing Score (%)</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        bind:value={lessonForm.pre_test.passing_score}
                                        class="w-full px-3 py-1.5 rounded-lg border border-border bg-card"
                                    />
                                </div>
                            </div>

                            <div class="space-y-3">
                                {#each lessonForm.pre_test.questions as q, qIdx}
                                    <div class="p-3 rounded-lg border border-border bg-card space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold">Soal #{qIdx + 1}</span>
                                            <button
                                                type="button"
                                                onclick={() => removePreTestQuestion(qIdx)}
                                                class="text-rose-600 text-[11px] hover:underline"
                                            >
                                                Hapus Soal
                                            </button>
                                        </div>
                                        <input
                                            type="text"
                                            required
                                            placeholder="Pertanyaan..."
                                            bind:value={q.question}
                                            class="w-full px-3 py-1.5 rounded-lg border border-border bg-background"
                                        />
                                        <div class="grid grid-cols-2 gap-2 pt-1">
                                            {#each q.options as opt, optIdx}
                                                <div class="flex items-center gap-1.5">
                                                    <input
                                                        type="radio"
                                                        name="pre_correct_{qIdx}"
                                                        checked={q.correct_answer === optIdx}
                                                        onchange={() => (q.correct_answer = optIdx)}
                                                    />
                                                    <input
                                                        type="text"
                                                        required
                                                        placeholder="Pilihan {['A', 'B', 'C', 'D'][optIdx]}"
                                                        bind:value={q.options[optIdx]}
                                                        class="w-full px-2 py-1 rounded border border-border bg-background text-xs"
                                                    />
                                                </div>
                                            {/each}
                                        </div>
                                    </div>
                                {/each}

                                <button
                                    type="button"
                                    onclick={addPreTestQuestion}
                                    class="px-3 py-1.5 rounded-lg border border-dashed border-indigo-500 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950 font-semibold"
                                >
                                    + Tambah Soal Pre-Test
                                </button>
                            </div>
                        </div>
                    {/if}
                </div>

                <!-- POST-TEST BUILDER (Opsional) -->
                <div class="p-3.5 rounded-xl border border-border bg-muted/20 space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            bind:checked={lessonForm.has_post_test}
                            class="rounded border-border text-emerald-600 size-4"
                        />
                        <span class="font-bold text-foreground">Sertakan Post-Test (Opsional)</span>
                    </label>

                    {#if lessonForm.has_post_test}
                        <div class="space-y-3 pt-2 border-t border-border/60">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-foreground mb-1">Judul Post-Test</label>
                                    <input
                                        type="text"
                                        bind:value={lessonForm.post_test.title}
                                        class="w-full px-3 py-1.5 rounded-lg border border-border bg-card"
                                    />
                                </div>
                                <div>
                                    <label class="block font-semibold text-foreground mb-1">Passing Score (%)</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        bind:value={lessonForm.post_test.passing_score}
                                        class="w-full px-3 py-1.5 rounded-lg border border-border bg-card"
                                    />
                                </div>
                            </div>

                            <div class="space-y-3">
                                {#each lessonForm.post_test.questions as q, qIdx}
                                    <div class="p-3 rounded-lg border border-border bg-card space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold">Soal #{qIdx + 1}</span>
                                            <button
                                                type="button"
                                                onclick={() => removePostTestQuestion(qIdx)}
                                                class="text-rose-600 text-[11px] hover:underline"
                                            >
                                                Hapus Soal
                                            </button>
                                        </div>
                                        <input
                                            type="text"
                                            required
                                            placeholder="Pertanyaan evaluasi..."
                                            bind:value={q.question}
                                            class="w-full px-3 py-1.5 rounded-lg border border-border bg-background"
                                        />
                                        <div class="grid grid-cols-2 gap-2 pt-1">
                                            {#each q.options as opt, optIdx}
                                                <div class="flex items-center gap-1.5">
                                                    <input
                                                        type="radio"
                                                        name="post_correct_{qIdx}"
                                                        checked={q.correct_answer === optIdx}
                                                        onchange={() => (q.correct_answer = optIdx)}
                                                    />
                                                    <input
                                                        type="text"
                                                        required
                                                        placeholder="Pilihan {['A', 'B', 'C', 'D'][optIdx]}"
                                                        bind:value={q.options[optIdx]}
                                                        class="w-full px-2 py-1 rounded border border-border bg-background text-xs"
                                                    />
                                                </div>
                                            {/each}
                                        </div>
                                    </div>
                                {/each}

                                <button
                                    type="button"
                                    onclick={addPostTestQuestion}
                                    class="px-3 py-1.5 rounded-lg border border-dashed border-emerald-500 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950 font-semibold"
                                >
                                    + Tambah Soal Post-Test
                                </button>
                            </div>
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

<!-- Modal: Ubah Data Kelas (termasuk Due Date) -->
{#if isEditCourseModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground">
                    Ubah Data Kelas
                </h3>
                <button
                    type="button"
                    onclick={() => (isEditCourseModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitUpdateCourse} class="space-y-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-foreground mb-1">Judul Kelas</label>
                    <input
                        type="text"
                        required
                        bind:value={courseEditForm.title}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Status</label>
                        <select
                            bind:value={courseEditForm.status}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card text-foreground"
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Tenggat (Due Date)</label>
                        <input
                            type="date"
                            bind:value={courseEditForm.due_date}
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card text-foreground"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isEditCourseModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isUpdatingCourse}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs"
                    >
                        {isUpdatingCourse ? 'Menyimpan...' : 'Simpan Perubahan'}
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
                            <span class="text-[10px] font-semibold text-emerald-600">Terdaftar</span>
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
                        {isAssigning ? 'Memproses...' : 'Simpan Akses'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}
