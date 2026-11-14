<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Portal Karyawan',
                href: '/my-summary',
            },
            {
                title: 'Kelas Saya',
                href: '/my-courses',
            },
            {
                title: 'Ruang Belajar',
                href: '#',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import CertificateModal from '@/components/CertificateModal.svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import Circle from '@lucide/svelte/icons/circle';
    import FileText from '@lucide/svelte/icons/file-text';
    import Video from '@lucide/svelte/icons/video';
    import Headphones from '@lucide/svelte/icons/headphones';
    import FileSpreadsheet from '@lucide/svelte/icons/file-spreadsheet';
    import Download from '@lucide/svelte/icons/download';
    import ExternalLink from '@lucide/svelte/icons/external-link';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Award from '@lucide/svelte/icons/award';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Calendar from '@lucide/svelte/icons/calendar';
    import AlertCircle from '@lucide/svelte/icons/alert-circle';
    import RotateCcw from '@lucide/svelte/icons/rotate-ccw';
    import X from '@lucide/svelte/icons/x';
    import ClipboardCheck from '@lucide/svelte/icons/clipboard-check';
    import Layers from '@lucide/svelte/icons/layers';
    import Check from '@lucide/svelte/icons/check';
    import { formatDate } from '@/lib/utils';

    interface QuizQuestion {
        id: string;
        question: string;
        options: string[];
        correct_answer: number;
        explanation?: string;
    }

    interface LessonItem {
        id: number;
        course_module_id?: number | null;
        title: string;
        slug: string;
        description?: string;
        sort_order: number;
        duration_minutes: number;
        points?: number;
        content_type: string;
        is_completed: boolean;
        is_preview?: boolean;
        due_date?: string | null;
    }

    interface ModuleItem {
        id: number;
        title: string;
        slug: string;
        description?: string;
        sort_order: number;
        completed_count: number;
        total_lessons: number;
        lessons: LessonItem[];
    }

    interface ActiveLesson extends LessonItem {
        module_title?: string;
        video_url?: string;
        audio_url?: string;
        content_text?: string;
        pdf_url?: string;
        quiz_questions?: QuizQuestion[];
        passing_score?: number;
        pre_test?: any;
        post_test?: any;
        attachments?: Array<{
            name: string;
            url: string;
            size?: string;
            type?: string;
        }>;
    }

    let {
        course,
        modules = [],
        lessons = [],
        activeLesson,
        user = null,
        quizResult = null,
    }: {
        course: any;
        modules?: ModuleItem[];
        lessons: LessonItem[];
        activeLesson: ActiveLesson | null;
        user?: any;
        quizResult?: any;
    } = $props();

    let isCertificateOpen = $state(false);
    let isCompletingCourse = $state(false);
    let isCurriculumSheetOpen = $state(false);

    function completeEntireCourse() {
        if (!confirm(`Tandai seluruh kelas '${course.title}' sebagai selesai dan buka Sertifikat Kelulusan Resmi?`)) {
            return;
        }
        isCompletingCourse = true;
        router.post(`/learn/${course.id}/complete`, {}, {
            onSuccess: () => {
                isCertificateOpen = true;
            },
            onFinish: () => {
                isCompletingCourse = false;
            },
        });
    }

    // Helper to format YouTube embed link
    function getYouTubeEmbedUrl(url?: string): string | null {
        if (!url) return null;
        try {
            if (url.includes('youtube.com/embed/')) {
                return url;
            }
            const shortMatch = url.match(/youtu\.be\/([a-zA-Z0-9_-]+)/);
            if (shortMatch && shortMatch[1]) {
                return `https://www.youtube-nocookie.com/embed/${shortMatch[1]}?rel=0&modestbranding=1`;
            }
            const longMatch = url.match(/[?&]v=([a-zA-Z0-9_-]+)/);
            if (longMatch && longMatch[1]) {
                return `https://www.youtube-nocookie.com/embed/${longMatch[1]}?rel=0&modestbranding=1`;
            }
        } catch (e) {
            console.error('Invalid youtube URL', e);
        }
        return url;
    }

    const embedVideoUrl = $derived(getYouTubeEmbedUrl(activeLesson?.video_url));

    function formatLessonContent(raw: string | null | undefined): string {
        if (!raw) return '';
        if (/<[a-z][\s\S]*>/i.test(raw)) {
            return raw;
        }
        return raw
            .split('\n')
            .map((line) => {
                const trimmed = line.trim();
                if (trimmed.startsWith('### ')) return `<h3>${trimmed.slice(4)}</h3>`;
                if (trimmed.startsWith('## ')) return `<h2>${trimmed.slice(3)}</h2>`;
                if (trimmed.startsWith('# ')) return `<h1>${trimmed.slice(2)}</h1>`;
                if (trimmed === '') return '<p><br></p>';
                return `<p>${line}</p>`;
            })
            .join('');
    }

    let isSubmitting = $state(false);

    // ==========================================
    // MULTIPLE CHOICE QUIZ INTERACTIVITY (Pre-Test / Post-Test)
    // ==========================================
    let quizAnswers = $state<Record<number, number>>({});
    let isSubmittingQuiz = $state(false);
    let localQuizResult = $state<{
        score: number;
        passed: boolean;
        correct: number;
        total: number;
    } | null>(null);

    // Reset quiz state when active lesson changes
    $effect(() => {
        if (activeLesson) {
            quizAnswers = {};
            localQuizResult = null;
        }
    });

    const isQuizLesson = $derived(
        activeLesson?.content_type === 'pre_test' || activeLesson?.content_type === 'post_test'
    );

    const questionsList = $derived(
        activeLesson?.quiz_questions?.length
            ? activeLesson.quiz_questions
            : (activeLesson?.pre_test?.questions || activeLesson?.post_test?.questions || [])
    );

    function submitQuiz() {
        if (!activeLesson || !questionsList.length) return;

        const answeredCount = Object.keys(quizAnswers).length;
        if (answeredCount < questionsList.length) {
            if (!confirm(`Anda baru menjawab ${answeredCount} dari ${questionsList.length} soal. Kirim jawaban sekarang?`)) {
                return;
            }
        }

        isSubmittingQuiz = true;

        // Calculate score locally for instant visual feedback
        let correctCount = 0;
        questionsList.forEach((q: QuizQuestion, idx: number) => {
            if (quizAnswers[idx] === q.correct_answer) {
                correctCount++;
            }
        });
        const score = Math.round((correctCount / questionsList.length) * 100);
        const passingScore = activeLesson.passing_score ?? 70;
        const passed = score >= passingScore;

        localQuizResult = {
            score,
            passed,
            correct: correctCount,
            total: questionsList.length,
        };

        // Submit to backend to persist XP, lesson_user & course_user progress
        router.post(
            `/learn/${course.id}/lessons/${activeLesson.id}/quiz`,
            { answers: quizAnswers },
            {
                preserveScroll: true,
                onFinish: () => {
                    isSubmittingQuiz = false;
                },
            }
        );
    }

    function resetQuiz() {
        quizAnswers = {};
        localQuizResult = null;
    }

    function toggleLessonCompletion() {
        if (!activeLesson || isSubmitting) return;
        isSubmitting = true;
        router.post(`/learn/${course.id}/lessons/${activeLesson.id}/toggle`, {}, {
            preserveScroll: true,
            onFinish: () => {
                isSubmitting = false;
            },
        });
    }

    // Determine next and previous lessons sequentially
    const currentIndex = $derived(lessons.findIndex((l) => l.id === activeLesson?.id));
    const prevLesson = $derived(currentIndex > 0 ? lessons[currentIndex - 1] : null);
    const nextLesson = $derived(currentIndex < lessons.length - 1 ? lessons[currentIndex + 1] : null);

    // Grouping fallback if modules prop is empty
    const displayModules = $derived(
        modules.length > 0
            ? modules
            : [
                {
                    id: 0,
                    title: 'Modul Pelajaran',
                    slug: 'modul-pelajaran',
                    description: '',
                    sort_order: 1,
                    completed_count: course.completed_lessons,
                    total_lessons: course.total_lessons,
                    lessons: lessons,
                },
            ]
    );
</script>

<AppHead title="{activeLesson ? activeLesson.title : 'Belajar'} - {course.title}" />

<div class="flex-1 flex flex-col p-3 sm:p-5 md:p-6 max-w-7xl mx-auto w-full space-y-4 sm:space-y-5">
    <!-- Top Bar: Minimalist Course Title, Progress & Action -->
    <div class="flex items-center justify-between gap-3 pb-3 border-b border-border/70">
        <div class="min-w-0 space-y-0.5">
            <Breadcrumbs
                breadcrumbs={[
                    { title: 'Kelas Saya', href: '/my-courses' },
                    { title: course.title, href: '#' },
                ]}
                class="mb-1 text-xs hidden sm:flex"
            />
            <h1 class="text-base sm:text-xl font-bold text-foreground tracking-tight truncate">
                {course.title}
            </h1>
            {#if course.tutor}
                <p class="text-[11px] text-muted-foreground truncate hidden sm:block">
                    Instruktur: <span class="font-medium text-foreground">{course.tutor.name}</span>
                </p>
            {/if}
        </div>

        <!-- Course Progress & Action Pill -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <div class="flex items-center gap-2 bg-card border border-border/80 px-2.5 py-1.5 sm:px-3 sm:py-2 rounded-xl shadow-xs">
                <div class="w-14 sm:w-20 h-1.5 rounded-full bg-muted overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500 {course.is_completed || course.progress_percent === 100 ? 'bg-emerald-500' : 'bg-indigo-600'}"
                        style="width: {course.progress_percent}%"
                    ></div>
                </div>
                <span class="text-xs font-bold {course.is_completed || course.progress_percent === 100 ? 'text-emerald-600' : 'text-indigo-600'}">
                    {course.progress_percent}%
                </span>
            </div>

            {#if course.is_completed || course.progress_percent === 100}
                <button
                    type="button"
                    onclick={() => (isCertificateOpen = true)}
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold bg-amber-500 hover:bg-amber-400 text-white shadow-xs transition-colors shrink-0 cursor-pointer"
                >
                    <Award class="size-3.5" />
                    <span class="hidden sm:inline">Sertifikat</span>
                </button>
            {:else}
                <button
                    type="button"
                    onclick={completeEntireCourse}
                    disabled={isCompletingCourse}
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold border border-emerald-600 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950 transition-colors shrink-0 disabled:opacity-50 cursor-pointer"
                >
                    <CheckCircle2 class="size-3.5" />
                    <span>{isCompletingCourse ? 'Menyimpan...' : 'Selesai'}</span>
                </button>
            {/if}
        </div>
    </div>

    <!-- Minimal Celebratory Banner when Completed -->
    {#if course.is_completed || course.progress_percent === 100}
        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-950/20 p-3 sm:p-3.5 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="size-7 sm:size-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                    <Award class="size-4" />
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-bold text-foreground truncate">
                        Selamat! Kelas Telah Selesai (100%)
                    </h3>
                    <p class="text-[11px] text-muted-foreground truncate hidden sm:block">
                        Seluruh modul telah diselesaikan. Sertifikat resmi Anda siap dibuka.
                    </p>
                </div>
            </div>
            <button
                type="button"
                onclick={() => (isCertificateOpen = true)}
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition-all shrink-0 cursor-pointer"
            >
                <Award class="size-3.5" />
                <span>Buka Sertifikat</span>
            </button>
        </div>
    {/if}

    <!-- Mobile Curriculum Quick Trigger (lg:hidden) -->
    <button
        type="button"
        onclick={() => (isCurriculumSheetOpen = true)}
        class="lg:hidden flex items-center justify-between p-2.5 rounded-xl bg-card border border-border shadow-xs text-xs active:scale-[0.99] transition-all text-left"
    >
        <div class="flex items-center gap-2 min-w-0 pr-2">
            <BookOpen class="size-4 text-indigo-600 shrink-0" />
            <div class="min-w-0">
                {#if activeLesson?.module_title}
                    <span class="text-[10px] text-muted-foreground block truncate">{activeLesson.module_title}</span>
                {/if}
                <span class="font-semibold text-foreground truncate block">
                    {activeLesson?.title ?? 'Pilih Pelajaran'}
                </span>
            </div>
        </div>
        <div class="flex items-center gap-1 text-[11px] font-medium text-muted-foreground shrink-0">
            <span>{course.completed_lessons}/{course.total_lessons} Selesai</span>
            <ArrowRight class="size-3 text-muted-foreground/70" />
        </div>
    </button>

    <!-- Main Layout: Content Player (Left) + Curriculum Sidebar (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Lesson / Test Content Viewer (8 cols) -->
        <div class="lg:col-span-8 space-y-5">
            {#if activeLesson}
                <!-- Lesson / Test Header -->
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground">
                        <div class="flex items-center gap-2">
                            {#if activeLesson.module_title}
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                    {activeLesson.module_title}
                                </span>
                                <span>•</span>
                            {/if}
                            {#if activeLesson.content_type === 'pre_test'}
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-600 text-white">
                                    Pre-Test
                                </span>
                            {:else if activeLesson.content_type === 'post_test'}
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-600 text-white">
                                    Post-Test
                                </span>
                            {:else}
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-muted text-muted-foreground">
                                    {activeLesson.content_type}
                                </span>
                            {/if}
                            <span>•</span>
                            <span>{activeLesson.duration_minutes} Menit</span>
                            <span>•</span>
                            <span class="text-amber-600 font-semibold">+{activeLesson.points ?? 50} XP</span>
                            {#if activeLesson.due_date}
                                <span>•</span>
                                <span class="text-amber-600 dark:text-amber-400">
                                    Exp. {formatDate(activeLesson.due_date)}
                                </span>
                            {/if}
                        </div>
                        <div class="flex items-center gap-2">
                            {#if activeLesson.is_completed}
                                <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                    <CheckCircle2 class="size-3.5" /> Selesai
                                </span>
                            {/if}
                        </div>
                    </div>

                    <h2 class="text-lg sm:text-2xl font-bold text-foreground">
                        {activeLesson.title}
                    </h2>

                    {#if activeLesson.description}
                        <p class="text-xs sm:text-sm text-muted-foreground leading-relaxed pt-0.5">
                            {activeLesson.description}
                        </p>
                    {/if}
                </div>

                <!-- ============================================== -->
                <!-- QUIZ PLAYER VIEW (Pre-Test / Post-Test) -->
                <!-- ============================================== -->
                {#if isQuizLesson}
                    <div class="rounded-2xl border border-border bg-card p-4 sm:p-6 shadow-xs space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-border/80">
                            <div class="flex items-center gap-2.5">
                                <div class="size-8 rounded-xl {activeLesson.content_type === 'pre_test' ? 'bg-blue-600' : 'bg-purple-600'} text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                    <ClipboardCheck class="size-4.5" />
                                </div>
                                <div>
                                    <h3 class="text-sm sm:text-base font-bold text-foreground">
                                        Evaluasi Pilihan Ganda ({questionsList.length} Soal)
                                    </h3>
                                    <p class="text-xs text-muted-foreground">
                                        Pilihlah salah satu jawaban yang paling tepat. Target kelulusan: {activeLesson.passing_score ?? 70}%
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-muted text-muted-foreground shrink-0">
                                KKM: {activeLesson.passing_score ?? 70}%
                            </span>
                        </div>

                        {#if !localQuizResult}
                            <!-- Quiz Question List -->
                            <div class="space-y-4">
                                {#each questionsList as q, qIdx (q.id || qIdx)}
                                    <div class="p-4 sm:p-5 rounded-2xl border border-border bg-muted/15 space-y-3">
                                        <div class="text-xs sm:text-sm font-semibold text-foreground flex items-start gap-2.5">
                                            <span class="size-6 rounded-full {activeLesson.content_type === 'pre_test' ? 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300' : 'bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300'} flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                                                {qIdx + 1}
                                            </span>
                                            <span class="leading-relaxed flex-1">{q.question}</span>
                                        </div>

                                        <div class="space-y-2 pl-0 sm:pl-8 pt-1">
                                            {#each q.options as opt, optIdx}
                                                {@const isSelected = quizAnswers[qIdx] === optIdx}
                                                <label
                                                    class="flex items-center gap-3 p-3 rounded-xl border text-xs cursor-pointer transition-all {isSelected ? (activeLesson.content_type === 'pre_test' ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/40 text-foreground font-semibold shadow-xs' : 'border-purple-500 bg-purple-50/70 dark:bg-purple-950/40 text-foreground font-semibold shadow-xs') : 'border-border/70 hover:bg-card text-muted-foreground'}"
                                                >
                                                    <input
                                                        type="radio"
                                                        name="quiz_q_{qIdx}"
                                                        checked={isSelected}
                                                        onchange={() => (quizAnswers[qIdx] = optIdx)}
                                                        class="size-4 {activeLesson.content_type === 'pre_test' ? 'text-blue-600 focus:ring-blue-500' : 'text-purple-600 focus:ring-purple-500'}"
                                                    />
                                                    <span class="font-bold w-4">{['A', 'B', 'C', 'D', 'E', 'F'][optIdx]}.</span>
                                                    <span class="flex-1">{opt}</span>
                                                </label>
                                            {/each}
                                        </div>
                                    </div>
                                {/each}

                                <div class="flex items-center justify-between pt-2">
                                    <span class="text-xs text-muted-foreground">
                                        Terjawab: <strong class="text-foreground">{Object.keys(quizAnswers).length}</strong> dari {questionsList.length} soal
                                    </span>
                                    <button
                                        type="button"
                                        onclick={submitQuiz}
                                        disabled={isSubmittingQuiz || questionsList.length === 0}
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold {activeLesson.content_type === 'pre_test' ? 'bg-blue-600 hover:bg-blue-500' : 'bg-purple-600 hover:bg-purple-500'} text-white shadow-xs transition-colors disabled:opacity-50 cursor-pointer"
                                    >
                                        <ClipboardCheck class="size-4" />
                                        <span>{isSubmittingQuiz ? 'Memproses...' : 'Kirim Jawaban'}</span>
                                    </button>
                                </div>
                            </div>
                        {:else}
                            <!-- Result Screen -->
                            <div class="space-y-5">
                                <div class="p-4 sm:p-5 rounded-2xl border {localQuizResult.passed ? 'border-emerald-300 bg-emerald-50/80 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-100' : 'border-amber-300 bg-amber-50/80 dark:bg-amber-950/30 text-amber-900 dark:text-amber-100'} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5">
                                        {#if localQuizResult.passed}
                                            <div class="size-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                                <CheckCircle2 class="size-6" />
                                            </div>
                                            <div>
                                                <div class="text-sm sm:text-base font-bold">
                                                    Lulus! Skor Anda: {localQuizResult.score}%
                                                </div>
                                                <div class="text-xs opacity-90 mt-0.5">
                                                    Benar: {localQuizResult.correct} dari {localQuizResult.total} soal. Selamat! XP berhasil ditambahkan.
                                                </div>
                                            </div>
                                        {:else}
                                            <div class="size-10 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0">
                                                <AlertCircle class="size-6" />
                                            </div>
                                            <div>
                                                <div class="text-sm sm:text-base font-bold">
                                                    Skor Anda: {localQuizResult.score}% (Target: {activeLesson.passing_score ?? 70}%)
                                                </div>
                                                <div class="text-xs opacity-90 mt-0.5">
                                                    Benar: {localQuizResult.correct} dari {localQuizResult.total} soal.
                                                    {#if activeLesson.content_type === 'pre_test'}
                                                        Hasil awal Anda telah tercatat. Silakan lanjutkan menyimak materi modul.
                                                    {:else}
                                                        Skor belum mencapai target KKM. Anda dapat mengulanginya untuk lulus.
                                                    {/if}
                                                </div>
                                            </div>
                                        {/if}
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <button
                                            type="button"
                                            onclick={resetQuiz}
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-border bg-card text-xs font-semibold hover:bg-muted text-foreground transition-colors cursor-pointer"
                                        >
                                            <RotateCcw class="size-3.5" />
                                            <span>Ulangi Tes</span>
                                        </button>
                                        {#if nextLesson}
                                            <Link
                                                href="/learn/{course.slug}?lesson={nextLesson.slug}"
                                                class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
                                            >
                                                <span>Pelajaran Berikutnya</span>
                                                <ArrowRight class="size-3.5" />
                                            </Link>
                                        {/if}
                                    </div>
                                </div>

                                <!-- Review Jawaban & Pembahasan -->
                                <div class="space-y-3 pt-1">
                                    <h4 class="text-xs font-bold text-foreground">Pembahasan & Kunci Jawaban:</h4>
                                    {#each questionsList as q, qIdx}
                                        {@const isCorrect = quizAnswers[qIdx] === q.correct_answer}
                                        <div class="p-3.5 rounded-xl border {isCorrect ? 'border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/20' : 'border-rose-200 dark:border-rose-900/40 bg-rose-50/20'} space-y-2 text-xs">
                                            <div class="font-semibold text-foreground flex items-center justify-between">
                                                <span class="flex items-center gap-2">
                                                    {#if isCorrect}
                                                        <CheckCircle2 class="size-4 text-emerald-600 shrink-0" />
                                                    {:else}
                                                        <X class="size-4 text-rose-600 shrink-0" />
                                                    {/if}
                                                    <span>#{qIdx + 1}: {q.question}</span>
                                                </span>
                                                <span class="font-bold {isCorrect ? 'text-emerald-600' : 'text-rose-600'}">
                                                    {isCorrect ? 'Benar (+)' : 'Salah'}
                                                </span>
                                            </div>
                                            <div class="pl-6 space-y-1 text-muted-foreground">
                                                <div>
                                                    Pilihan Anda: <span class="font-medium {isCorrect ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'}">{q.options[quizAnswers[qIdx]] ?? 'Belum dijawab'}</span>
                                                </div>
                                                {#if !isCorrect}
                                                    <div>
                                                        Kunci Jawaban: <span class="font-semibold text-emerald-700 dark:text-emerald-300">{['A', 'B', 'C', 'D', 'E', 'F'][q.correct_answer]}. {q.options[q.correct_answer]}</span>
                                                    </div>
                                                {/if}
                                                {#if q.explanation}
                                                    <div class="p-2.5 rounded-lg bg-card border border-border/60 text-[11px] text-foreground/80 mt-1.5">
                                                        💡 <span class="font-semibold">Penjelasan:</span> {q.explanation}
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            </div>
                        {/if}
                    </div>
                {:else}
                    <!-- ============================================== -->
                    <!-- REGULAR LESSON PLAYER VIEW (Video, Audio, PDF, Text) -->
                    <!-- ============================================== -->
                    <!-- 1. Video Player Section (YouTube embed) -->
                    {#if embedVideoUrl}
                        <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-black shadow-xs border border-border/80">
                            <iframe
                                src={embedVideoUrl}
                                title={activeLesson.title}
                                class="absolute inset-0 size-full border-0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    {/if}

                    <!-- 2. Audio Player Section -->
                    {#if activeLesson.audio_url}
                        <div class="p-3 sm:p-4 rounded-2xl border border-border bg-card shadow-xs flex items-center gap-3">
                            <Headphones class="size-4 text-indigo-600 shrink-0" />
                            <audio
                                controls
                                class="w-full h-8"
                                src={activeLesson.audio_url}
                            >
                                Browser Anda tidak mendukung audio player.
                            </audio>
                        </div>
                    {/if}

                    <!-- 3. Formatted Text / Reading Content -->
                    {#if activeLesson.content_text}
                        <div class="rounded-2xl border border-border bg-card p-5 sm:p-6 shadow-xs">
                            <div class="prose prose-slate dark:prose-invert max-w-none text-sm leading-relaxed [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:my-3 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:my-2 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:my-2 [&_p]:my-2 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-2 [&_blockquote]:border-l-4 [&_blockquote]:border-indigo-500 [&_blockquote]:pl-3 [&_blockquote]:italic [&_blockquote]:my-2 [&_blockquote]:text-muted-foreground [&_code]:bg-muted [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded [&_code]:font-mono [&_code]:text-xs [&_code]:text-indigo-600 dark:[&_code]:text-indigo-400 [&_a]:text-indigo-600 [&_a]:underline">
                                {@html formatLessonContent(activeLesson.content_text)}
                            </div>
                        </div>
                    {/if}

                    <!-- 4. PDF Embed Option -->
                    {#if activeLesson.pdf_url}
                        <div class="rounded-2xl border border-border bg-card p-4 sm:p-5 shadow-xs space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-border/50 text-xs">
                                <span class="font-semibold text-foreground">Dokumen PDF Tersemat</span>
                                <a
                                    href={activeLesson.pdf_url}
                                    target="_blank"
                                    rel="noreferrer"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-muted hover:bg-muted/80 text-foreground transition-colors"
                                >
                                    <ExternalLink class="size-3.5" />
                                    <span>Fullscreen</span>
                                </a>
                            </div>

                            <div class="relative w-full h-[500px] rounded-xl overflow-hidden border border-border bg-muted/30">
                                <iframe
                                    src="{activeLesson.pdf_url}#toolbar=1"
                                    class="size-full border-0"
                                    title="PDF Viewer"
                                >
                                    <p class="p-6 text-sm text-center text-muted-foreground">
                                        Browser Anda tidak mendukung preview PDF langsung. Silakan
                                        <a href={activeLesson.pdf_url} target="_blank" class="text-indigo-600 underline">klik di sini untuk mengunduh</a>.
                                    </p>
                                </iframe>
                            </div>
                        </div>
                    {/if}

                    <!-- 5. Attachments Files Section -->
                    {#if activeLesson.attachments && activeLesson.attachments.length > 0}
                        <div class="rounded-2xl border border-border bg-card p-4 sm:p-5 shadow-xs space-y-2.5">
                            <div class="text-xs font-semibold text-muted-foreground">
                                Berkas Lampiran ({activeLesson.attachments.length})
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                {#each activeLesson.attachments as file}
                                    <a
                                        href={file.url}
                                        target="_blank"
                                        rel="noreferrer"
                                        class="flex items-center justify-between p-2.5 rounded-xl border border-border bg-muted/20 hover:bg-muted/50 hover:border-indigo-400/50 transition-all group text-xs"
                                    >
                                        <div class="flex items-center gap-2 min-w-0">
                                            {#if file.type === 'excel' || file.name.endsWith('.xlsx') || file.name.endsWith('.csv')}
                                                <FileSpreadsheet class="size-4 text-emerald-600 shrink-0" />
                                            {:else}
                                                <FileText class="size-4 text-indigo-600 shrink-0" />
                                            {/if}
                                            <div class="min-w-0">
                                                <div class="font-medium text-foreground truncate group-hover:text-indigo-600">
                                                    {file.name}
                                                </div>
                                                {#if file.size}
                                                    <div class="text-[10px] text-muted-foreground">
                                                        {file.size}
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                        <Download class="size-3.5 text-muted-foreground group-hover:text-indigo-600 shrink-0 ml-2" />
                                    </a>
                                {/each}
                            </div>
                        </div>
                    {/if}
                {/if}

                <!-- Bottom Navigation & Completion Action Bar -->
                <div class="p-2.5 sm:p-3.5 rounded-2xl border border-border bg-card shadow-xs flex items-center justify-between gap-2">
                    <div>
                        {#if prevLesson}
                            <Link
                                href="/learn/{course.slug}?lesson={prevLesson.slug}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border border-border bg-background hover:bg-muted transition-colors"
                            >
                                <ArrowLeft class="size-3.5" />
                                <span class="hidden sm:inline">Sebelumnya</span>
                            </Link>
                        {:else}
                            <div class="w-8 sm:w-24"></div>
                        {/if}
                    </div>

                    <!-- Mark As Complete Button (only for regular lessons; quizzes auto-complete on submission) -->
                    {#if !isQuizLesson}
                        <button
                            type="button"
                            onclick={toggleLessonCompletion}
                            disabled={isSubmitting}
                            class="inline-flex items-center justify-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-xs cursor-pointer {activeLesson.is_completed ? 'bg-muted hover:bg-muted/80 text-foreground' : 'bg-emerald-600 hover:bg-emerald-500 text-white active:scale-95'}"
                        >
                            <CheckCircle2 class="size-4 {activeLesson.is_completed ? 'text-emerald-500' : ''}" />
                            <span>
                                {#if isSubmitting}
                                    Menyimpan...
                                {:else if activeLesson.is_completed}
                                    Selesai
                                {:else}
                                    Tandai Selesai (+{activeLesson.points ?? 50} XP)
                                {/if}
                            </span>
                        </button>
                    {:else}
                        <div class="text-xs font-semibold text-muted-foreground">
                            {#if activeLesson.is_completed}
                                <span class="inline-flex items-center gap-1 text-emerald-600">
                                    <CheckCircle2 class="size-3.5" /> Evaluasi Selesai (+{activeLesson.points ?? 50} XP)
                                </span>
                            {:else}
                                Kerjakan tes di atas untuk menyelesaikan evaluasi
                            {/if}
                        </div>
                    {/if}

                    <div>
                        {#if nextLesson}
                            <Link
                                href="/learn/{course.slug}?lesson={nextLesson.slug}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition-colors"
                            >
                                <span class="hidden sm:inline">Selanjutnya</span>
                                <ArrowRight class="size-3.5" />
                            </Link>
                        {:else}
                            <div class="w-8 sm:w-24"></div>
                        {/if}
                    </div>
                </div>
            {:else}
                <div class="rounded-2xl border border-dashed border-border p-12 text-center bg-card">
                    <BookOpen class="size-12 mx-auto text-muted-foreground/50 mb-3" />
                    <h3 class="text-base font-bold text-foreground">Pelajaran Belum Tersedia</h3>
                    <p class="text-xs text-muted-foreground mt-1">Kelas ini belum memiliki modul pelajaran yang aktif.</p>
                </div>
            {/if}
        </div>

        <!-- Right: Curriculum Sidebar (4 cols) Grouped by Module -->
        <div class="lg:col-span-4 space-y-4">
            <div class="rounded-2xl border border-border bg-card p-4 shadow-xs sticky top-6">
                <div class="flex items-center justify-between pb-3 border-b border-border/60">
                    <div>
                        <h3 class="text-sm font-bold text-foreground">
                            Kurikulum Modul
                        </h3>
                        <p class="text-[11px] text-muted-foreground mt-0.5">
                            {course.completed_lessons} dari {course.total_lessons} selesai
                        </p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-muted text-muted-foreground">
                        {displayModules.length} Modul
                    </span>
                </div>

                <!-- Grouped Modules & Lessons List -->
                <div class="mt-3 space-y-3.5 max-h-[600px] overflow-y-auto pr-1">
                    {#each displayModules as mod, modIdx (mod.id)}
                        <div class="rounded-xl border border-border/70 bg-muted/10 overflow-hidden">
                            <!-- Module Group Header -->
                            <div class="px-3 py-2 bg-muted/40 border-b border-border/60 flex items-center justify-between text-xs">
                                <span class="font-bold text-foreground truncate pr-2">
                                    {mod.title}
                                </span>
                                <span class="text-[10px] text-muted-foreground font-semibold shrink-0">
                                    {mod.completed_count}/{mod.total_lessons}
                                </span>
                            </div>

                            <!-- Lessons Inside Module -->
                            <div class="p-1.5 space-y-1">
                                {#each mod.lessons as lesson (lesson.id)}
                                    {@const isActive = lesson.id === activeLesson?.id}
                                    <Link
                                        href="/learn/{course.slug}?lesson={lesson.slug}"
                                        class="flex items-center gap-2 p-2 rounded-lg text-xs transition-all {isActive ? 'bg-indigo-50/90 dark:bg-indigo-950/70 text-indigo-950 dark:text-indigo-200 font-semibold shadow-2xs' : 'hover:bg-muted/60 text-foreground/80 font-normal'}"
                                    >
                                        <div class="shrink-0">
                                            {#if lesson.is_completed}
                                                <CheckCircle2 class="size-3.5 text-emerald-500" />
                                            {:else if isActive}
                                                <div class="size-3.5 rounded-full border-2 border-indigo-600 flex items-center justify-center">
                                                    <div class="size-1 rounded-full bg-indigo-600"></div>
                                                </div>
                                            {:else}
                                                <Circle class="size-3.5 text-muted-foreground/30" />
                                            {/if}
                                        </div>

                                        {#if lesson.content_type === 'pre_test'}
                                            <span class="px-1.5 py-0.2 rounded text-[8px] font-bold uppercase bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 shrink-0">
                                                Pre
                                            </span>
                                        {:else if lesson.content_type === 'post_test'}
                                            <span class="px-1.5 py-0.2 rounded text-[8px] font-bold uppercase bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 shrink-0">
                                                Post
                                            </span>
                                        {/if}

                                        <div class="min-w-0 flex-1 truncate">
                                            <span class="truncate">{lesson.title}</span>
                                        </div>

                                        <span class="text-[10px] text-muted-foreground shrink-0">{lesson.duration_minutes}m</span>
                                    </Link>
                                {/each}
                            </div>
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sertifikat Kelulusan Resmi -->
<CertificateModal
    isOpen={isCertificateOpen}
    course={course}
    user={user}
    completedAt={course.completed_at}
    certificateCode={course.certificate_code}
    onClose={() => (isCertificateOpen = false)}
/>

<!-- Mobile Curriculum Bottom Sheet Grouped by Module -->
{#if isCurriculumSheetOpen}
    <div class="fixed inset-0 z-50 flex items-end justify-center p-0 lg:hidden">
        <button
            type="button"
            class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity cursor-default"
            aria-label="Tutup"
            onclick={() => (isCurriculumSheetOpen = false)}
        ></button>
        <div class="relative z-10 w-full max-h-[85vh] bg-card border border-b-0 border-border rounded-t-2xl shadow-2xl flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-300 pb-[max(1rem,env(safe-area-inset-bottom))]">
            <!-- Mobile Bottom Sheet Grab Handle -->
            <div class="flex justify-center pt-2.5 pb-1 shrink-0">
                <div class="h-1.5 w-12 rounded-full bg-muted-foreground/30"></div>
            </div>

            <!-- Sheet Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-border/80 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="size-7 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <Layers class="size-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-foreground">Kurikulum Kelas</h3>
                        <p class="text-[11px] text-muted-foreground">{course.completed_lessons} dari {course.total_lessons} selesai</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick={() => (isCurriculumSheetOpen = false)}
                    class="p-2 rounded-xl text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                    aria-label="Tutup"
                >
                    <X class="size-4.5" />
                </button>
            </div>

            <!-- Grouped Lessons List in Mobile Bottom Sheet -->
            <div class="p-3 space-y-3 overflow-y-auto max-h-[calc(85vh-120px)]">
                {#each displayModules as mod, modIdx (mod.id)}
                    <div class="rounded-xl border border-border/70 bg-muted/10 overflow-hidden">
                        <div class="px-3 py-2 bg-muted/40 border-b border-border/60 flex items-center justify-between text-xs">
                            <span class="font-bold text-foreground truncate pr-2">
                                {mod.title}
                            </span>
                            <span class="text-[10px] text-muted-foreground font-semibold shrink-0">
                                {mod.completed_count}/{mod.total_lessons}
                            </span>
                        </div>

                        <div class="p-1.5 space-y-1">
                            {#each mod.lessons as lesson (lesson.id)}
                                {@const isActive = lesson.id === activeLesson?.id}
                                <Link
                                    href="/learn/{course.slug}?lesson={lesson.slug}"
                                    onclick={() => (isCurriculumSheetOpen = false)}
                                    class="flex items-center gap-2 p-2 rounded-lg text-xs transition-all {isActive ? 'bg-indigo-50/90 dark:bg-indigo-950/70 text-indigo-950 dark:text-indigo-200 font-semibold' : 'hover:bg-muted/60 text-foreground/80 font-normal'}"
                                >
                                    <div class="shrink-0">
                                        {#if lesson.is_completed}
                                            <CheckCircle2 class="size-3.5 text-emerald-500" />
                                        {:else if isActive}
                                            <div class="size-3.5 rounded-full border-2 border-indigo-600 flex items-center justify-center">
                                                <div class="size-1 rounded-full bg-indigo-600"></div>
                                            </div>
                                        {:else}
                                            <Circle class="size-3.5 text-muted-foreground/30" />
                                        {/if}
                                    </div>

                                    {#if lesson.content_type === 'pre_test'}
                                        <span class="px-1.5 py-0.2 rounded text-[8px] font-bold uppercase bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 shrink-0">
                                            Pre
                                        </span>
                                    {:else if lesson.content_type === 'post_test'}
                                        <span class="px-1.5 py-0.2 rounded text-[8px] font-bold uppercase bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 shrink-0">
                                            Post
                                        </span>
                                    {/if}

                                    <div class="min-w-0 flex-1 truncate">
                                        <span class="truncate">{lesson.title}</span>
                                    </div>

                                    <span class="text-[10px] text-muted-foreground shrink-0">{lesson.duration_minutes}m</span>
                                </Link>
                            {/each}
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </div>
{/if}
