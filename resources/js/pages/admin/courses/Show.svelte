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
    import RichTextEditor from '@/components/RichTextEditor.svelte';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Users from '@lucide/svelte/icons/users';
    import Plus from '@lucide/svelte/icons/plus';
    import Edit3 from '@lucide/svelte/icons/edit-3';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Video from '@lucide/svelte/icons/video';
    import Headphones from '@lucide/svelte/icons/headphones';
    import FileText from '@lucide/svelte/icons/file-text';
    import Paperclip from '@lucide/svelte/icons/paperclip';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import Mail from '@lucide/svelte/icons/mail';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import Calendar from '@lucide/svelte/icons/calendar';
    import HelpCircle from '@lucide/svelte/icons/help-circle';
    import Layers from '@lucide/svelte/icons/layers';
    import Check from '@lucide/svelte/icons/check';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import ClipboardCheck from '@lucide/svelte/icons/clipboard-check';
    import X from '@lucide/svelte/icons/x';
    import { formatDate } from '@/lib/utils';

    interface TestQuestion {
        id?: string;
        question: string;
        options: string[];
        correct_answer: number;
        explanation?: string;
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

    // ==========================================
    // MODULE MANAGEMENT
    // ==========================================
    let isModuleModalOpen = $state(false);
    let editingModule = $state<any | null>(null);
    let moduleForm = $state({
        title: '',
        description: '',
        sort_order: 1,
    });
    let isSubmittingModule = $state(false);

    function openCreateModule() {
        editingModule = null;
        const maxSort = course.modules?.length
            ? Math.max(...course.modules.map((m: any) => m.sort_order || 0))
            : 0;
        moduleForm = {
            title: '',
            description: '',
            sort_order: maxSort + 1,
        };
        isModuleModalOpen = true;
    }

    function openEditModule(mod: any) {
        editingModule = mod;
        moduleForm = {
            title: mod.title,
            description: mod.description || '',
            sort_order: mod.sort_order || 1,
        };
        isModuleModalOpen = true;
    }

    function submitModule(e: SubmitEvent) {
        e.preventDefault();
        isSubmittingModule = true;

        if (editingModule) {
            router.put(`/admin/courses/${course.id}/modules/${editingModule.id}`, moduleForm, {
                onSuccess: () => (isModuleModalOpen = false),
                onFinish: () => (isSubmittingModule = false),
            });
        } else {
            router.post(`/admin/courses/${course.id}/modules`, moduleForm, {
                onSuccess: () => (isModuleModalOpen = false),
                onFinish: () => (isSubmittingModule = false),
            });
        }
    }

    function deleteModule(mod: any) {
        if (confirm(`Hapus modul '${mod.title}' beserta seluruh pelajaran dan evaluasi di dalamnya?`)) {
            router.delete(`/admin/courses/${course.id}/modules/${mod.id}`);
        }
    }

    // ==========================================
    // PRE-TEST & POST-TEST (MULTIPLE CHOICE)
    // ==========================================
    let isTestModalOpen = $state(false);
    let targetTestModule = $state<any | null>(null);
    let currentTestType = $state<'pre_test' | 'post_test'>('pre_test');
    let testForm = $state({
        is_active: true,
        title: '',
        instructions: '',
        passing_score: 70,
        duration_minutes: 15,
        points: 30,
        questions: [] as TestQuestion[],
    });
    let isSubmittingTest = $state(false);

    function openTestModal(mod: any, type: 'pre_test' | 'post_test') {
        targetTestModule = mod;
        currentTestType = type;
        const existingTest = mod.lessons?.find((l: any) => l.content_type === type);

        if (existingTest) {
            const rawQuestions = existingTest.quiz_questions?.length
                ? existingTest.quiz_questions
                : (type === 'pre_test' ? (existingTest.pre_test?.questions || []) : (existingTest.post_test?.questions || []));

            testForm = {
                is_active: true,
                title: existingTest.title,
                instructions: existingTest.description || '',
                passing_score: existingTest.passing_score ?? 70,
                duration_minutes: existingTest.duration_minutes ?? 15,
                points: existingTest.points ?? (type === 'pre_test' ? 30 : 50),
                questions: rawQuestions.length ? JSON.parse(JSON.stringify(rawQuestions)) : [
                    {
                        question: '',
                        options: ['', '', '', ''],
                        correct_answer: 0,
                        explanation: '',
                    },
                ],
            };
        } else {
            const defaultTitle = type === 'pre_test' ? `Pre-Test: ${mod.title}` : `Post-Test: ${mod.title}`;
            const defaultInst = type === 'pre_test'
                ? 'Uji pemahaman dasar Anda sebelum menyimak materi pada modul ini.'
                : 'Evaluasi akhir untuk menguji pemahaman materi pada modul ini.';

            testForm = {
                is_active: true,
                title: defaultTitle,
                instructions: defaultInst,
                passing_score: 70,
                duration_minutes: 15,
                points: type === 'pre_test' ? 30 : 50,
                questions: [
                    {
                        question: '',
                        options: ['', '', '', ''],
                        correct_answer: 0,
                        explanation: '',
                    },
                ],
            };
        }
        isTestModalOpen = true;
    }

    function addTestQuestion() {
        testForm.questions.push({
            question: '',
            options: ['', '', '', ''],
            correct_answer: 0,
            explanation: '',
        });
    }

    function removeTestQuestion(idx: number) {
        testForm.questions.splice(idx, 1);
    }

    function addOptionToQuestion(qIdx: number) {
        if (testForm.questions[qIdx].options.length < 6) {
            testForm.questions[qIdx].options.push('');
        }
    }

    function removeOptionFromQuestion(qIdx: number, optIdx: number) {
        if (testForm.questions[qIdx].options.length > 2) {
            testForm.questions[qIdx].options.splice(optIdx, 1);
            if (testForm.questions[qIdx].correct_answer >= testForm.questions[qIdx].options.length) {
                testForm.questions[qIdx].correct_answer = 0;
            }
        }
    }

    function submitTest(e: SubmitEvent) {
        e.preventDefault();
        if (!targetTestModule) return;
        isSubmittingTest = true;

        router.post(
            `/admin/courses/${course.id}/modules/${targetTestModule.id}/tests`,
            {
                test_type: currentTestType,
                ...testForm,
            },
            {
                onSuccess: () => (isTestModalOpen = false),
                onFinish: () => (isSubmittingTest = false),
            }
        );
    }

    function disableTest(mod: any, type: 'pre_test' | 'post_test') {
        const testLabel = type === 'pre_test' ? 'Pre-Test' : 'Post-Test';
        if (confirm(`Nonaktifkan dan hapus ${testLabel} dari modul '${mod.title}'?`)) {
            router.post(`/admin/courses/${course.id}/modules/${mod.id}/tests`, {
                test_type: type,
                is_active: false,
            });
        }
    }

    // ==========================================
    // REGULAR LESSON MANAGEMENT
    // ==========================================
    let isLessonModalOpen = $state(false);
    let editingLesson = $state<any | null>(null);

    let lessonForm = $state({
        course_module_id: null as number | null,
        title: '',
        description: '',
        duration_minutes: 15,
        points: 50,
        content_type: 'video',
        video_url: '',
        audio_url: '',
        content_text: '',
        pdf_url: '',
        due_date: '',
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

    function openCreateLesson(moduleId?: number) {
        editingLesson = null;
        saveSuccessMessage = '';
        keepOpenAfterSave = false;

        const targetModId = moduleId ?? (course.modules?.[0]?.id || null);

        lessonForm = {
            course_module_id: targetModId,
            title: '',
            description: '',
            duration_minutes: 15,
            points: 50,
            content_type: 'video',
            video_url: '',
            audio_url: '',
            content_text: '',
            pdf_url: '',
            due_date: '',
            attachments: [],
            is_preview: false,
        };
        isLessonModalOpen = true;
    }

    function openEditLesson(les: any) {
        editingLesson = les;
        saveSuccessMessage = '';
        keepOpenAfterSave = false;

        lessonForm = {
            course_module_id: les.course_module_id || (course.modules?.[0]?.id || null),
            title: les.title,
            description: les.description || '',
            duration_minutes: les.duration_minutes || 15,
            points: les.points ?? 50,
            content_type: les.content_type || 'video',
            video_url: les.video_url || '',
            audio_url: les.audio_url || '',
            content_text: les.content_text || '',
            pdf_url: les.pdf_url || '',
            due_date: les.due_date ? String(les.due_date).substring(0, 10) : '',
            attachments: les.attachments ? [...les.attachments] : [],
            is_preview: !!les.is_preview,
        };
        isLessonModalOpen = true;
    }

    let isSubmittingLesson = $state(false);
    let keepOpenAfterSave = $state(false);
    let saveSuccessMessage = $state('');
    let lessonModalContentEl: HTMLDivElement | null = $state(null);

    function submitLesson(e: SubmitEvent) {
        e.preventDefault();
        isSubmittingLesson = true;

        const payload = {
            course_module_id: lessonForm.course_module_id,
            title: lessonForm.title,
            description: lessonForm.description,
            duration_minutes: lessonForm.duration_minutes,
            points: lessonForm.points,
            content_type: lessonForm.content_type,
            video_url: lessonForm.video_url || null,
            audio_url: lessonForm.audio_url || null,
            content_text: lessonForm.content_text || null,
            pdf_url: lessonForm.pdf_url || null,
            due_date: lessonForm.due_date || null,
            attachments: lessonForm.attachments,
            is_preview: lessonForm.is_preview,
        };

        if (editingLesson) {
            router.put(`/admin/courses/${course.id}/lessons/${editingLesson.id}`, payload, {
                onSuccess: () => (isLessonModalOpen = false),
                onFinish: () => (isSubmittingLesson = false),
            });
        } else {
            router.post(`/admin/courses/${course.id}/lessons`, payload, {
                onSuccess: () => {
                    if (keepOpenAfterSave) {
                        const savedTitle = lessonForm.title;
                        const prevModId = lessonForm.course_module_id;
                        openCreateLesson(prevModId || undefined);
                        saveSuccessMessage = `Pelajaran "${savedTitle}" berhasil disimpan! Silakan tambah pelajaran berikutnya.`;
                        setTimeout(() => {
                            lessonModalContentEl?.scrollTo({ top: 0, behavior: 'smooth' });
                        }, 50);
                    } else {
                        isLessonModalOpen = false;
                    }
                },
                onFinish: () => (isSubmittingLesson = false),
            });
        }
    }

    function deleteLesson(les: any) {
        if (confirm(`Hapus pelajaran '${les.title}'?`)) {
            router.delete(`/admin/courses/${course.id}/lessons/${les.id}`);
        }
    }

    // ==========================================
    // DIRECT ASSIGN EMPLOYEES
    // ==========================================
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
        if (confirm(`Cabut akses '${emp.name}' dari kelas ini?`)) {
            router.delete(`/admin/courses/${course.id}/assign/${emp.id}`);
        }
    }

    // ==========================================
    // EDIT COURSE DATA (DUE DATE, STATUS, XP)
    // ==========================================
    let isEditCourseModalOpen = $state(false);
    let courseEditForm = $state({
        title: course.title,
        status: course.status,
        due_date: course.due_date ? String(course.due_date).substring(0, 10) : '',
        points: course.points ?? 200,
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

    // Total lesson count across all modules or flat lessons
    const totalLessonsCount = $derived(
        (course.modules?.reduce((acc: number, m: any) => acc + (m.lessons?.length || 0), 0) || 0) || course.lessons?.length || 0
    );
</script>

<AppHead title="{course.title} - Manajemen Kelas" />

<div class="space-y-6">
    <!-- Header with Breadcrumbs & Meta -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-border/80">
        <div class="space-y-1">
            <Breadcrumbs
                breadcrumbs={[
                    { title: 'Admin', href: '/admin/dashboard' },
                    { title: 'Kelas', href: '/admin/courses' },
                    { title: course.title, href: '#' },
                ]}
                class="mb-2"
            />
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
                        <span>Batas: {formatDate(course.due_date)}</span>
                    </span>
                {/if}
            </div>
            <p class="text-xs text-muted-foreground">
                Tutor: <span class="font-medium text-foreground">{course.tutor?.name || '-'}</span> • Durasi: {course.estimated_duration_hours} Jam • Bonus XP: <span class="font-bold text-amber-600">{course.points ?? 200} XP</span>
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
                    <Layers class="size-3.5 text-indigo-600" />
                    <span>Modul & Pelajaran ({course.modules?.length || 0})</span>
                </button>
                <button
                    type="button"
                    onclick={() => (activeTab = 'access')}
                    class="flex items-center gap-1.5 px-3 py-1 rounded-lg transition-all {activeTab === 'access' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'}"
                >
                    <Users class="size-3.5 text-emerald-600" />
                    <span>Akses ({course.enrolled_users?.length || 0})</span>
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 1: MODUL & PELAJARAN -->
    {#if activeTab === 'lessons'}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-foreground flex items-center gap-2">
                        <Layers class="size-4 text-indigo-600" />
                        <span>Struktur Modul ({course.modules?.length || 0} Modul, {totalLessonsCount} Pelajaran & Evaluasi)</span>
                    </h2>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Tiap kelas terdiri dari beberapa modul. Di dalam tiap modul, Anda dapat menambahkan pelajaran dan tes (Pre-Test & Post-Test pilihan ganda).
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button
                        type="button"
                        onclick={openCreateModule}
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
                    >
                        <Plus class="size-3.5" />
                        <span>Tambah Modul</span>
                    </button>
                </div>
            </div>

            <!-- Modules List -->
            <div class="space-y-4">
                {#each course.modules || [] as module, modIdx (module.id)}
                    {@const preTest = module.lessons?.find((l: any) => l.content_type === 'pre_test')}
                    {@const postTest = module.lessons?.find((l: any) => l.content_type === 'post_test')}
                    {@const regularLessons = (module.lessons || []).filter((l: any) => l.content_type !== 'pre_test' && l.content_type !== 'post_test')}

                    <div class="rounded-2xl border border-border bg-card shadow-xs overflow-hidden">
                        <!-- Module Header Bar -->
                        <div class="p-3.5 sm:p-4 bg-muted/30 border-b border-border/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <div class="size-7 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                    #{module.sort_order ?? modIdx + 1}
                                </div>
                                <div class="space-y-0.5 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-sm text-foreground truncate">
                                            {module.title}
                                        </h3>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-muted text-muted-foreground">
                                            {module.lessons?.length || 0} Item
                                        </span>
                                    </div>
                                    {#if module.description}
                                        <p class="text-xs text-muted-foreground line-clamp-1">{module.description}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Module Quick Actions -->
                            <div class="flex flex-wrap items-center gap-1.5 shrink-0 self-end md:self-center">
                                <button
                                    type="button"
                                    onclick={() => openCreateLesson(module.id)}
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-2xs transition-colors"
                                    title="Tambah Pelajaran ke Modul Ini"
                                >
                                    <Plus class="size-3" />
                                    <span>Pelajaran</span>
                                </button>

                                <button
                                    type="button"
                                    onclick={() => openTestModal(module, 'pre_test')}
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border transition-colors {preTest ? 'border-blue-400 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300' : 'border-border bg-card hover:bg-muted text-muted-foreground'}"
                                    title="Atur Pre-Test Modul"
                                >
                                    <ClipboardCheck class="size-3 text-blue-600" />
                                    <span>{preTest ? 'Pre-Test (Aktif)' : '+ Pre-Test'}</span>
                                </button>

                                <button
                                    type="button"
                                    onclick={() => openTestModal(module, 'post_test')}
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border transition-colors {postTest ? 'border-purple-400 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300' : 'border-border bg-card hover:bg-muted text-muted-foreground'}"
                                    title="Atur Post-Test Modul"
                                >
                                    <ClipboardCheck class="size-3 text-purple-600" />
                                    <span>{postTest ? 'Post-Test (Aktif)' : '+ Post-Test'}</span>
                                </button>

                                <div class="h-4 w-px bg-border mx-0.5"></div>

                                <button
                                    type="button"
                                    onclick={() => openEditModule(module)}
                                    class="p-1.5 rounded-lg border border-border hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
                                    title="Edit Judul & Deskripsi Modul"
                                >
                                    <Edit3 class="size-3.5" />
                                </button>

                                <button
                                    type="button"
                                    onclick={() => deleteModule(module)}
                                    class="p-1.5 rounded-lg border border-border hover:bg-rose-50 dark:hover:bg-rose-950 text-rose-600 transition-colors"
                                    title="Hapus Modul"
                                >
                                    <Trash2 class="size-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Lessons & Tests Inside Module -->
                        <div class="p-3 sm:p-3.5 space-y-2">
                            <!-- PRE-TEST ROW IF ACTIVE -->
                            {#if preTest}
                                <div class="rounded-xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/40 dark:bg-blue-950/20 p-2.5 flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-600 text-white shrink-0">
                                            Pre-Test
                                        </span>
                                        <div class="min-w-0">
                                            <div class="font-bold text-foreground truncate">{preTest.title}</div>
                                            <div class="text-[11px] text-muted-foreground flex flex-wrap items-center gap-2">
                                                <span>⏱️ {preTest.duration_minutes ?? 10}m</span>
                                                <span>•</span>
                                                <span>+{preTest.points ?? 30} XP</span>
                                                <span>•</span>
                                                <span>KKM: {preTest.passing_score ?? 70}%</span>
                                                <span>•</span>
                                                <span class="font-semibold text-blue-700 dark:text-blue-300">
                                                    {(preTest.quiz_questions?.length ?? preTest.pre_test?.questions?.length ?? 0)} Soal Pilihan Ganda
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button
                                            type="button"
                                            onclick={() => openTestModal(module, 'pre_test')}
                                            class="p-1.5 rounded-lg border border-blue-200 dark:border-blue-800 bg-card hover:bg-blue-100 dark:hover:bg-blue-900/40 text-blue-700 dark:text-blue-300 transition-colors"
                                            title="Edit Soal Pre-Test"
                                        >
                                            <Edit3 class="size-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            onclick={() => disableTest(module, 'pre_test')}
                                            class="p-1.5 rounded-lg border border-border hover:bg-rose-50 text-rose-600 transition-colors"
                                            title="Nonaktifkan Pre-Test"
                                        >
                                            <X class="size-3.5" />
                                        </button>
                                    </div>
                                </div>
                            {/if}

                            <!-- REGULAR LESSONS -->
                            {#each regularLessons as lesson, lesIdx (lesson.id)}
                                <div class="rounded-xl border border-border bg-card p-2.5 hover:border-indigo-400/50 transition-all flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="size-6 rounded-md bg-muted flex items-center justify-center shrink-0 text-muted-foreground">
                                            {#if lesson.content_type === 'video'}
                                                <Video class="size-3.5 text-rose-500" />
                                            {:else if lesson.content_type === 'audio'}
                                                <Headphones class="size-3.5 text-amber-500" />
                                            {:else if lesson.content_type === 'pdf'}
                                                <FileText class="size-3.5 text-blue-500" />
                                            {:else}
                                                <BookOpen class="size-3.5 text-indigo-500" />
                                            {/if}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-foreground truncate">{lesson.title}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] uppercase font-semibold bg-muted text-muted-foreground">
                                                    {lesson.content_type}
                                                </span>
                                                {#if lesson.is_preview}
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                                        Preview
                                                    </span>
                                                {/if}
                                            </div>
                                            <div class="text-[11px] text-muted-foreground flex flex-wrap items-center gap-2">
                                                <span>⏱️ {lesson.duration_minutes}m</span>
                                                <span>•</span>
                                                <span>+{lesson.points ?? 50} XP</span>
                                                {#if lesson.attachments?.length}
                                                    <span>•</span>
                                                    <span class="inline-flex items-center gap-0.5">
                                                        <Paperclip class="size-2.5" /> {lesson.attachments.length} Berkas
                                                    </span>
                                                {/if}
                                                {#if lesson.due_date}
                                                    <span>•</span>
                                                    <span class="text-amber-600">Exp. {formatDate(lesson.due_date)}</span>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
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
                                            class="p-1.5 rounded-lg border border-border hover:bg-rose-50 text-rose-600 transition-colors"
                                            title="Hapus Pelajaran"
                                        >
                                            <Trash2 class="size-3.5" />
                                        </button>
                                    </div>
                                </div>
                            {/each}

                            <!-- POST-TEST ROW IF ACTIVE -->
                            {#if postTest}
                                <div class="rounded-xl border border-purple-200 dark:border-purple-900/60 bg-purple-50/40 dark:bg-purple-950/20 p-2.5 flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-600 text-white shrink-0">
                                            Post-Test
                                        </span>
                                        <div class="min-w-0">
                                            <div class="font-bold text-foreground truncate">{postTest.title}</div>
                                            <div class="text-[11px] text-muted-foreground flex flex-wrap items-center gap-2">
                                                <span>⏱️ {postTest.duration_minutes ?? 15}m</span>
                                                <span>•</span>
                                                <span>+{postTest.points ?? 50} XP</span>
                                                <span>•</span>
                                                <span>KKM: {postTest.passing_score ?? 70}%</span>
                                                <span>•</span>
                                                <span class="font-semibold text-purple-700 dark:text-purple-300">
                                                    {(postTest.quiz_questions?.length ?? postTest.post_test?.questions?.length ?? 0)} Soal Pilihan Ganda
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button
                                            type="button"
                                            onclick={() => openTestModal(module, 'post_test')}
                                            class="p-1.5 rounded-lg border border-purple-200 dark:border-purple-800 bg-card hover:bg-purple-100 dark:hover:bg-purple-900/40 text-purple-700 dark:text-purple-300 transition-colors"
                                            title="Edit Soal Post-Test"
                                        >
                                            <Edit3 class="size-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            onclick={() => disableTest(module, 'post_test')}
                                            class="p-1.5 rounded-lg border border-border hover:bg-rose-50 text-rose-600 transition-colors"
                                            title="Nonaktifkan Post-Test"
                                        >
                                            <X class="size-3.5" />
                                        </button>
                                    </div>
                                </div>
                            {/if}

                            <!-- Empty Module State -->
                            {#if !preTest && regularLessons.length === 0 && !postTest}
                                <div class="py-6 px-4 rounded-xl border border-dashed border-border text-center text-xs text-muted-foreground">
                                    Modul ini belum memiliki materi atau tes.
                                    <div class="mt-2 flex items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            onclick={() => openCreateLesson(module.id)}
                                            class="text-indigo-600 hover:underline font-semibold"
                                        >
                                            + Tambah Pelajaran
                                        </button>
                                        <span>•</span>
                                        <button
                                            type="button"
                                            onclick={() => openTestModal(module, 'pre_test')}
                                            class="text-blue-600 hover:underline font-semibold"
                                        >
                                            Atur Pre-Test
                                        </button>
                                        <span>•</span>
                                        <button
                                            type="button"
                                            onclick={() => openTestModal(module, 'post_test')}
                                            class="text-purple-600 hover:underline font-semibold"
                                        >
                                            Atur Post-Test
                                        </button>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/each}

                {#if (!course.modules || course.modules.length === 0)}
                    <div class="p-8 rounded-2xl border border-dashed border-border text-center bg-card">
                        <Layers class="size-10 mx-auto text-muted-foreground/40 mb-3" />
                        <h3 class="text-sm font-semibold text-foreground">Belum ada modul di kelas ini</h3>
                        <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">
                            Buat modul pembelajaran terlebih dahulu untuk mengelompokkan materi, pre-test, dan post-test.
                        </p>
                        <button
                            type="button"
                            onclick={openCreateModule}
                            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
                        >
                            <Plus class="size-3.5" />
                            <span>Buat Modul Pertama</span>
                        </button>
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
                    <span>Karyawan Terdaftar Langsung ({course.enrolled_users?.length || 0})</span>
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
                {#each course.enrolled_users || [] as user (user.id)}
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

                {#if (!course.enrolled_users || course.enrolled_users.length === 0)}
                    <div class="p-6 text-center text-xs text-muted-foreground">
                        Belum ada karyawan yang terdaftar langsung pada kelas ini.
                    </div>
                {/if}
            </div>
        </div>
    {/if}
</div>

<!-- ======================================================== -->
<!-- MODAL: TAMBAH / EDIT MODUL -->
<!-- ======================================================== -->
{#if isModuleModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                    <Layers class="size-4.5 text-indigo-600" />
                    <span>{editingModule ? 'Edit Modul' : 'Tambah Modul Baru'}</span>
                </h3>
                <button
                    type="button"
                    onclick={() => (isModuleModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitModule} class="space-y-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-foreground mb-1">Judul Modul *</label>
                    <input
                        type="text"
                        required
                        bind:value={moduleForm.title}
                        placeholder="Contoh: Modul 1: Fondasi Keamanan & Regulasi AI"
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:border-indigo-500 focus:outline-hidden"
                    />
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Deskripsi Singkat (Opsional)</label>
                    <textarea
                        rows="3"
                        bind:value={moduleForm.description}
                        placeholder="Ringkasan kompetensi dan topik yang dibahas dalam modul ini..."
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                    ></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-foreground mb-1">Urutan Modul (Sort Order)</label>
                    <input
                        type="number"
                        min="1"
                        bind:value={moduleForm.sort_order}
                        class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                    />
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isModuleModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isSubmittingModule}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs transition-colors disabled:opacity-60"
                    >
                        {isSubmittingModule ? 'Menyimpan...' : editingModule ? 'Perbarui Modul' : 'Simpan Modul'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- ======================================================== -->
<!-- MODAL: ATUR PRE-TEST / POST-TEST (PILIHAN GANDA) -->
<!-- ======================================================== -->
{#if isTestModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-card border border-border rounded-2xl w-full max-w-[95vw] md:w-[75vw] md:max-w-3xl max-h-[92vh] overflow-y-auto p-6 md:p-7 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <div class="flex items-center gap-2">
                    <div class="size-7 rounded-lg {currentTestType === 'pre_test' ? 'bg-blue-600' : 'bg-purple-600'} text-white flex items-center justify-center shrink-0">
                        <ClipboardCheck class="size-4" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-foreground">
                            {currentTestType === 'pre_test' ? 'Atur Pre-Test' : 'Atur Post-Test'}
                        </h3>
                        <p class="text-xs text-muted-foreground">{targetTestModule?.title}</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick={() => (isTestModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            <form onsubmit={submitTest} class="space-y-4 text-xs">
                <!-- Toggle Status -->
                <div class="p-3 rounded-xl border border-border bg-muted/20 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-foreground block">
                            Aktifkan {currentTestType === 'pre_test' ? 'Pre-Test' : 'Post-Test'} pada Modul Ini
                        </span>
                        <span class="text-[11px] text-muted-foreground">
                            Jika dinonaktifkan, evaluasi ini tidak akan ditampilkan kepada karyawan.
                        </span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            bind:checked={testForm.is_active}
                            class="sr-only peer"
                        />
                        <div class="w-11 h-6 bg-muted peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                {#if testForm.is_active}
                    <div class="space-y-3.5">
                        <div>
                            <label class="block font-semibold text-foreground mb-1">Judul Evaluasi *</label>
                            <input
                                type="text"
                                required
                                bind:value={testForm.title}
                                class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                            />
                        </div>

                        <div>
                            <label class="block font-semibold text-foreground mb-1">Petunjuk / Instruksi</label>
                            <textarea
                                rows="2"
                                bind:value={testForm.instructions}
                                class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-semibold text-foreground mb-1">Target Kelulusan (KKM %)</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    bind:value={testForm.passing_score}
                                    class="w-full px-3 py-2 rounded-xl border border-border bg-card"
                                />
                            </div>
                            <div>
                                <label class="block font-semibold text-foreground mb-1">Durasi (Menit)</label>
                                <input
                                    type="number"
                                    min="1"
                                    bind:value={testForm.duration_minutes}
                                    class="w-full px-3 py-2 rounded-xl border border-border bg-card"
                                />
                            </div>
                            <div>
                                <label class="block font-semibold text-foreground mb-1">Reward XP</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="5"
                                    bind:value={testForm.points}
                                    class="w-full px-3 py-2 rounded-xl border border-border bg-card"
                                />
                            </div>
                        </div>

                        <!-- SOAL-SOAL PILIHAN GANDA -->
                        <div class="space-y-3 pt-2 border-t border-border/80">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-foreground flex items-center gap-1.5">
                                    <span>Daftar Soal Pilihan Ganda ({testForm.questions.length})</span>
                                </span>
                                <button
                                    type="button"
                                    onclick={addTestQuestion}
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-dashed border-indigo-500 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950 font-semibold"
                                >
                                    <Plus class="size-3.5" />
                                    <span>Tambah Soal</span>
                                </button>
                            </div>

                            <div class="space-y-3.5">
                                {#each testForm.questions as q, qIdx (qIdx)}
                                    <div class="p-3.5 rounded-2xl border border-border bg-muted/10 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-foreground flex items-center gap-1.5">
                                                <span class="size-5 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-[10px]">
                                                    {qIdx + 1}
                                                </span>
                                                <span>Soal #{qIdx + 1}</span>
                                            </span>
                                            <button
                                                type="button"
                                                onclick={() => removeTestQuestion(qIdx)}
                                                class="text-rose-600 hover:underline text-[11px] font-medium"
                                            >
                                                Hapus Soal
                                            </button>
                                        </div>

                                        <textarea
                                            rows="2"
                                            required
                                            placeholder="Tuliskan pertanyaan pilihan ganda..."
                                            bind:value={q.question}
                                            class="w-full px-3 py-2 rounded-xl border border-border bg-card focus:outline-hidden"
                                        ></textarea>

                                        <!-- Opsi Jawaban (A, B, C, D, dst) -->
                                        <div class="space-y-2">
                                            <span class="text-[11px] font-semibold text-muted-foreground block">
                                                Pilihan Jawaban (Pilih radio untuk jawaban yang benar):
                                            </span>
                                            <div class="space-y-1.5">
                                                {#each q.options as opt, optIdx}
                                                    <div class="flex items-center gap-2">
                                                        <label class="flex items-center gap-1.5 shrink-0 cursor-pointer">
                                                            <input
                                                                type="radio"
                                                                name="test_q_correct_{qIdx}"
                                                                checked={q.correct_answer === optIdx}
                                                                onchange={() => (q.correct_answer = optIdx)}
                                                                class="size-4 text-indigo-600 focus:ring-indigo-500"
                                                            />
                                                            <span class="font-bold w-4">{['A', 'B', 'C', 'D', 'E', 'F'][optIdx]}.</span>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            required
                                                            placeholder="Jawaban {['A', 'B', 'C', 'D', 'E', 'F'][optIdx]}"
                                                            bind:value={q.options[optIdx]}
                                                            class="flex-1 px-3 py-1.5 rounded-xl border border-border bg-card text-xs focus:outline-hidden"
                                                        />
                                                        {#if q.options.length > 2}
                                                            <button
                                                                type="button"
                                                                onclick={() => removeOptionFromQuestion(qIdx, optIdx)}
                                                                class="p-1 rounded text-muted-foreground hover:text-rose-600"
                                                                title="Hapus Opsi"
                                                            >
                                                                <X class="size-3.5" />
                                                            </button>
                                                        {/if}
                                                    </div>
                                                {/each}
                                            </div>

                                            {#if q.options.length < 6}
                                                <button
                                                    type="button"
                                                    onclick={() => addOptionToQuestion(qIdx)}
                                                    class="text-[11px] text-indigo-600 hover:underline pt-1 inline-block"
                                                >
                                                    + Tambah Pilihan Opsi
                                                </button>
                                            {/if}
                                        </div>

                                        <div>
                                            <label class="block font-semibold text-muted-foreground mb-1 text-[11px]">
                                                Penjelasan / Pembahasan (Opsional, tampil setelah karyawan menjawab):
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Contoh: Zero Data Retention menjamin kerahasiaan data internal..."
                                                bind:value={q.explanation}
                                                class="w-full px-3 py-1.5 rounded-xl border border-border bg-card text-xs focus:outline-hidden"
                                            />
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isTestModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        disabled={isSubmittingTest}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs disabled:opacity-60"
                    >
                        {isSubmittingTest ? 'Menyimpan...' : 'Simpan Evaluasi'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- ======================================================== -->
<!-- MODAL: TAMBAH / EDIT PELAJARAN REGULER -->
<!-- ======================================================== -->
{#if isLessonModalOpen}
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div bind:this={lessonModalContentEl} class="bg-card border border-border rounded-2xl w-full max-w-[95vw] md:w-[80vw] md:max-w-3xl max-h-[92vh] overflow-y-auto p-6 md:p-8 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-border/80">
                <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                    <BookOpen class="size-4.5 text-indigo-600" />
                    <span>{editingLesson ? 'Edit Pelajaran' : 'Tambah Pelajaran Baru'}</span>
                </h3>
                <button
                    type="button"
                    onclick={() => (isLessonModalOpen = false)}
                    class="p-1 rounded-lg hover:bg-muted text-muted-foreground"
                >
                    <X class="size-5" />
                </button>
            </div>

            {#if saveSuccessMessage}
                <div class="flex items-center gap-2 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-medium animate-in fade-in duration-200">
                    <CheckCircle2 class="size-4 shrink-0" />
                    <span class="flex-1">{saveSuccessMessage}</span>
                    <button
                        type="button"
                        onclick={() => (saveSuccessMessage = '')}
                        class="p-0.5 rounded text-muted-foreground hover:text-foreground"
                    >
                        <X class="size-3.5" />
                    </button>
                </div>
            {/if}

            <form onsubmit={submitLesson} class="space-y-4 text-xs">
                <!-- Modul Selector -->
                <div>
                    <label class="block font-semibold text-foreground mb-1">Pilih Modul Pembelajaran *</label>
                    <select
                        required
                        bind:value={lessonForm.course_module_id}
                        class="w-full pl-3 pr-8 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
                    >
                        {#each course.modules || [] as mod}
                            <option value={mod.id}>{mod.title}</option>
                        {/each}
                    </select>
                </div>

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

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Tipe Media *</label>
                        <select
                            bind:value={lessonForm.content_type}
                            class="w-full pl-3 pr-8 py-2 rounded-xl border border-border bg-card text-foreground focus:outline-hidden"
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
                        <label class="block font-semibold text-foreground mb-1">Poin / XP</label>
                        <input
                            type="number"
                            min="0"
                            step="5"
                            bind:value={lessonForm.points}
                            placeholder="50"
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

                <!-- Modul / Materi Pembelajaran (Rich-Text Editor) -->
                <div>
                    <RichTextEditor
                        bind:value={lessonForm.content_text}
                        label="Modul Bacaan & Materi Pelajaran"
                        placeholder="Tuliskan materi pembelajaran, modul bacaan, rangkuman, atau instruksi lengkap di sini..."
                        minHeight="220px"
                    />
                </div>

                <!-- Attachments Section -->
                <div class="p-3.5 rounded-xl border border-border bg-muted/20 space-y-3">
                    <span class="font-bold text-foreground flex items-center gap-1.5">
                        <Paperclip class="size-3.5" />
                        <span>Berkas Lampiran ({lessonForm.attachments.length})</span>
                    </span>

                    {#if lessonForm.attachments.length > 0}
                        <div class="space-y-1.5">
                            {#each lessonForm.attachments as att, attIdx}
                                <div class="flex items-center justify-between p-2 rounded-lg bg-card border border-border text-xs">
                                    <span class="font-medium truncate">{att.name} ({att.size})</span>
                                    <button
                                        type="button"
                                        onclick={() => removeAttachment(attIdx)}
                                        class="text-rose-600 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            {/each}
                        </div>
                    {/if}

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <input
                            type="text"
                            placeholder="Nama Berkas (e.g. Panduan.pdf)"
                            bind:value={newAttachmentName}
                            class="px-2.5 py-1.5 rounded-lg border border-border bg-card text-xs"
                        />
                        <input
                            type="url"
                            placeholder="URL Berkas"
                            bind:value={newAttachmentUrl}
                            class="px-2.5 py-1.5 rounded-lg border border-border bg-card text-xs"
                        />
                        <div class="flex gap-1.5">
                            <input
                                type="text"
                                placeholder="Ukuran (e.g. 1.2 MB)"
                                bind:value={newAttachmentSize}
                                class="w-full px-2.5 py-1.5 rounded-lg border border-border bg-card text-xs"
                            />
                            <button
                                type="button"
                                onclick={addAttachment}
                                class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold shrink-0"
                            >
                                Tambah
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            bind:checked={lessonForm.is_preview}
                            class="rounded border-border text-indigo-600 size-4"
                        />
                        <span class="font-semibold text-foreground">Izinkan Akses Pratinjau (Preview Bebas)</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-border/80">
                    <button
                        type="button"
                        onclick={() => (isLessonModalOpen = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground transition-colors"
                    >
                        Batal
                    </button>
                    {#if !editingLesson}
                        <button
                            type="submit"
                            disabled={isSubmittingLesson}
                            onclick={() => (keepOpenAfterSave = true)}
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold border border-indigo-600 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition-colors shadow-2xs disabled:opacity-60"
                        >
                            <Plus class="size-3.5" />
                            <span>{isSubmittingLesson && keepOpenAfterSave ? 'Menyimpan...' : 'Simpan & Tambah Lagi'}</span>
                        </button>
                    {/if}
                    <button
                        type="submit"
                        disabled={isSubmittingLesson}
                        onclick={() => (keepOpenAfterSave = false)}
                        class="px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs transition-colors disabled:opacity-60"
                    >
                        {isSubmittingLesson && !keepOpenAfterSave ? 'Menyimpan...' : editingLesson ? 'Perbarui Pelajaran' : 'Simpan Pelajaran'}
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- MODAL: UBAH DATA KELAS -->
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

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-foreground mb-1">Status</label>
                        <select
                            bind:value={courseEditForm.status}
                            class="w-full pl-3 pr-8 py-2 rounded-xl border border-border bg-card text-foreground"
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-foreground mb-1">Bonus Kelulusan (XP)</label>
                        <input
                            type="number"
                            min="0"
                            step="10"
                            bind:value={courseEditForm.points}
                            placeholder="200"
                            class="w-full px-3 py-2 rounded-xl border border-border bg-card text-foreground"
                        />
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

<!-- MODAL: ASSIGN KARYAWAN -->
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
                    {@const isEnrolled = course.enrolled_users?.some((u: any) => u.id === emp.id)}
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
