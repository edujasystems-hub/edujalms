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

    interface QuizQuestion {
        id: string;
        question: string;
        options: string[];
        correct_answer: number;
        explanation?: string;
    }

    interface QuizTest {
        title: string;
        instructions?: string;
        passing_score?: number;
        questions: QuizQuestion[];
    }

    interface LessonItem {
        id: number;
        title: string;
        slug: string;
        description?: string;
        sort_order: number;
        duration_minutes: number;
        content_type: string;
        is_completed: boolean;
        is_preview?: boolean;
        has_pre_test?: boolean;
        has_post_test?: boolean;
        due_date?: string | null;
    }

    interface ActiveLesson extends LessonItem {
        video_url?: string;
        audio_url?: string;
        content_text?: string;
        pdf_url?: string;
        pre_test?: QuizTest | null;
        post_test?: QuizTest | null;
        attachments?: Array<{
            name: string;
            url: string;
            size?: string;
            type?: string;
        }>;
    }

    let {
        course,
        lessons = [],
        activeLesson,
    }: {
        course: any;
        lessons: LessonItem[];
        activeLesson: ActiveLesson | null;
    } = $props();

    function formatDate(dateStr?: string | null) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
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

    let isSubmitting = $state(false);

    // Pre-Test Interactivity State
    let preTestAnswers = $state<Record<number, number>>({});
    let preTestSubmitted = $state(false);
    let preTestScore = $state(0);

    // Post-Test Interactivity State
    let postTestAnswers = $state<Record<number, number>>({});
    let postTestSubmitted = $state(false);
    let postTestScore = $state(0);

    // Reset test state when active lesson changes
    $effect(() => {
        if (activeLesson) {
            preTestAnswers = {};
            preTestSubmitted = false;
            preTestScore = 0;

            postTestAnswers = {};
            postTestSubmitted = false;
            postTestScore = 0;
        }
    });

    function submitPreTest() {
        if (!activeLesson?.pre_test?.questions?.length) return;
        const questions = activeLesson.pre_test.questions;
        let correctCount = 0;
        questions.forEach((q, idx) => {
            if (preTestAnswers[idx] === q.correct_answer) {
                correctCount++;
            }
        });
        preTestScore = Math.round((correctCount / questions.length) * 100);
        preTestSubmitted = true;
    }

    function resetPreTest() {
        preTestAnswers = {};
        preTestSubmitted = false;
        preTestScore = 0;
    }

    function submitPostTest() {
        if (!activeLesson?.post_test?.questions?.length) return;
        const questions = activeLesson.post_test.questions;
        let correctCount = 0;
        questions.forEach((q, idx) => {
            if (postTestAnswers[idx] === q.correct_answer) {
                correctCount++;
            }
        });
        postTestScore = Math.round((correctCount / questions.length) * 100);
        postTestSubmitted = true;
    }

    function resetPostTest() {
        postTestAnswers = {};
        postTestSubmitted = false;
        postTestScore = 0;
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

    // Determine next and previous lessons
    const currentIndex = $derived(lessons.findIndex((l) => l.id === activeLesson?.id));
    const prevLesson = $derived(currentIndex > 0 ? lessons[currentIndex - 1] : null);
    const nextLesson = $derived(currentIndex < lessons.length - 1 ? lessons[currentIndex + 1] : null);
</script>

<AppHead title="{activeLesson ? activeLesson.title : 'Belajar'} - {course.title}" />

<div class="flex-1 flex flex-col p-4 md:p-6 max-w-7xl mx-auto w-full space-y-6">
    <!-- Top Bar: Course Title, Progress, and Back -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-border/80">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <Link href="/my-courses" class="hover:text-foreground inline-flex items-center gap-1 transition-colors">
                    <ArrowLeft class="size-3.5" />
                    <span>Semua Kelas</span>
                </Link>
                <span>/</span>
                <span class="text-foreground font-medium truncate max-w-xs">{course.title}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-extrabold text-foreground tracking-tight">
                {course.title}
            </h1>
            <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                {#if course.tutor}
                    <span>Instruktur: <strong class="font-medium text-foreground">{course.tutor.name}</strong></span>
                {/if}
                {#if course.due_date}
                    {#if course.tutor}<span>•</span>{/if}
                    <span class="inline-flex items-center gap-1 font-medium text-amber-600 dark:text-amber-400">
                        <Calendar class="size-3.5" />
                        Tenggat Kelas: {formatDate(course.due_date)}
                    </span>
                {/if}
            </div>
        </div>

        <!-- Course Progress Pill -->
        <div class="flex items-center gap-4 bg-card border border-border/80 px-4 py-2.5 rounded-xl shadow-xs shrink-0">
            <div class="space-y-1 min-w-[130px]">
                <div class="flex justify-between text-xs">
                    <span class="font-medium text-muted-foreground">Progress Kelas</span>
                    <span class="font-mono font-bold {course.progress_percent === 100 ? 'text-emerald-600' : 'text-indigo-600'}">
                        {course.progress_percent}%
                    </span>
                </div>
                <div class="w-full h-2 rounded-full bg-muted overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500 {course.progress_percent === 100 ? 'bg-emerald-500' : 'bg-indigo-600'}"
                        style="width: {course.progress_percent}%"
                    ></div>
                </div>
            </div>
            {#if course.progress_percent === 100}
                <div class="size-8 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 flex items-center justify-center shrink-0">
                    <Award class="size-4.5" />
                </div>
            {/if}
        </div>
    </div>

    <!-- Main Layout: Content Player (Left) + Curriculum Sidebar (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Lesson Content Viewer (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            {#if activeLesson}
                <!-- Lesson Header -->
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300">
                            Modul #{activeLesson.sort_order}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground uppercase tracking-wider">
                            Tipe: {activeLesson.content_type}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                            ⏱️ {activeLesson.duration_minutes} Menit
                        </span>
                        {#if activeLesson.due_date}
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50">
                                <Calendar class="size-3" />
                                Tenggat: {formatDate(activeLesson.due_date)}
                            </span>
                        {/if}
                        {#if activeLesson.pre_test?.questions?.length}
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                <ClipboardCheck class="size-3" />
                                Pre-Test Tersedia
                            </span>
                        {/if}
                        {#if activeLesson.post_test?.questions?.length}
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                <Award class="size-3" />
                                Post-Test Tersedia
                            </span>
                        {/if}
                        {#if activeLesson.is_completed}
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                <CheckCircle2 class="size-3.5" /> Selesai
                            </span>
                        {/if}
                    </div>

                    <h2 class="text-2xl font-bold text-foreground">
                        {activeLesson.title}
                    </h2>

                    {#if activeLesson.description}
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            {activeLesson.description}
                        </p>
                    {/if}
                </div>

                <!-- Pre-Test Section (Optional) -->
                {#if activeLesson.pre_test?.questions?.length}
                    <div class="rounded-2xl border border-indigo-200 dark:border-indigo-900/60 bg-gradient-to-b from-indigo-50/40 via-card to-card dark:from-indigo-950/20 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-indigo-100 dark:border-indigo-900/40">
                            <div class="flex items-center gap-2.5">
                                <div class="size-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    PRE
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-foreground">
                                        {activeLesson.pre_test.title || 'Pre-Test Pelajaran'}
                                    </h3>
                                    {#if activeLesson.pre_test.instructions}
                                        <p class="text-xs text-muted-foreground">
                                            {activeLesson.pre_test.instructions}
                                        </p>
                                    {/if}
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                                Target: {activeLesson.pre_test.passing_score ?? 70}%
                            </span>
                        </div>

                        {#if !preTestSubmitted}
                            <div class="space-y-4 pt-1">
                                {#each activeLesson.pre_test.questions as q, qIdx (q.id || qIdx)}
                                    <div class="p-4 rounded-xl border border-border bg-card space-y-3">
                                        <div class="text-xs font-semibold text-foreground flex items-start gap-2">
                                            <span class="size-5 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 flex items-center justify-center shrink-0 text-[11px] font-bold">
                                                {qIdx + 1}
                                            </span>
                                            <span class="leading-relaxed">{q.question}</span>
                                        </div>
                                        <div class="space-y-1.5 pl-7">
                                            {#each q.options as opt, optIdx}
                                                <label
                                                    class="flex items-center gap-2.5 p-2.5 rounded-lg border text-xs cursor-pointer transition-all {preTestAnswers[qIdx] === optIdx ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 text-foreground font-semibold shadow-xs' : 'border-border/60 hover:bg-muted/50 text-muted-foreground'}"
                                                >
                                                    <input
                                                        type="radio"
                                                        name="pre_q_{qIdx}"
                                                        checked={preTestAnswers[qIdx] === optIdx}
                                                        onchange={() => (preTestAnswers[qIdx] = optIdx)}
                                                        class="size-3.5 text-indigo-600 focus:ring-indigo-500"
                                                    />
                                                    <span>{opt}</span>
                                                </label>
                                            {/each}
                                        </div>
                                    </div>
                                {/each}

                                <div class="flex justify-end pt-1">
                                    <button
                                        type="button"
                                        onclick={submitPreTest}
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs transition-colors"
                                    >
                                        <ClipboardCheck class="size-4" />
                                        <span>Kirim Jawaban Pre-Test</span>
                                    </button>
                                </div>
                            </div>
                        {:else}
                            <!-- Result Screen -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 rounded-xl border {preTestScore >= (activeLesson.pre_test.passing_score ?? 70) ? 'border-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200' : 'border-amber-300 bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200'}">
                                    <div class="flex items-center gap-3">
                                        {#if preTestScore >= (activeLesson.pre_test.passing_score ?? 70)}
                                            <CheckCircle2 class="size-6 text-emerald-600 shrink-0" />
                                            <div>
                                                <div class="text-sm font-bold">Lulus Pre-Test! Skor: {preTestScore}%</div>
                                                <div class="text-xs opacity-90">Pemahaman awal Anda sangat baik. Silakan pelajari materi berikut.</div>
                                            </div>
                                        {:else}
                                            <AlertCircle class="size-6 text-amber-600 shrink-0" />
                                            <div>
                                                <div class="text-sm font-bold">Hasil Pre-Test: {preTestScore}%</div>
                                                <div class="text-xs opacity-90">Jangan khawatir, simak materi di bawah untuk memperdalam pemahaman Anda.</div>
                                            </div>
                                        {/if}
                                    </div>
                                    <button
                                        type="button"
                                        onclick={resetPreTest}
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-card text-xs font-semibold hover:bg-muted text-foreground transition-colors shrink-0"
                                    >
                                        <RotateCcw class="size-3.5" />
                                        <span>Ulangi Pre-Test</span>
                                    </button>
                                </div>

                                <div class="space-y-3 pt-1">
                                    {#each activeLesson.pre_test.questions as q, qIdx}
                                        {@const isCorrect = preTestAnswers[qIdx] === q.correct_answer}
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
                                                <div>Pilihan Anda: <span class="font-medium {isCorrect ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'}">{q.options[preTestAnswers[qIdx]] ?? 'Belum dijawab'}</span></div>
                                                {#if !isCorrect}
                                                    <div>Kunci Jawaban: <span class="font-semibold text-emerald-700 dark:text-emerald-300">{q.options[q.correct_answer]}</span></div>
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
                {/if}

                <!-- 1. Video Player Section (YouTube embed) -->
                {#if embedVideoUrl}
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-xs font-semibold text-foreground">
                            <Video class="size-4 text-indigo-600" />
                            <span>Materi Video Interaktif:</span>
                        </div>
                        <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-black shadow-lg border border-border">
                            <iframe
                                src={embedVideoUrl}
                                title={activeLesson.title}
                                class="absolute inset-0 size-full border-0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </div>
                {/if}

                <!-- 2. Audio Player Section -->
                {#if activeLesson.audio_url}
                    <div class="p-4 rounded-xl border border-border bg-card shadow-xs space-y-3">
                        <div class="flex items-center gap-2 text-xs font-semibold text-foreground">
                            <Headphones class="size-4 text-violet-600" />
                            <span>Rekaman Audio / Podcast Pelajaran:</span>
                        </div>
                        <audio
                            controls
                            class="w-full rounded-lg"
                            src={activeLesson.audio_url}
                        >
                            Browser Anda tidak mendukung audio player.
                        </audio>
                    </div>
                {/if}

                <!-- 3. Formatted Text / Reading Content -->
                {#if activeLesson.content_text}
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-xs space-y-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-foreground pb-2 border-b border-border/50">
                            <FileText class="size-4 text-indigo-600" />
                            <span>Modul Bacaan & Ringkasan Materi:</span>
                        </div>
                        <div class="prose prose-slate dark:prose-invert max-w-none text-sm leading-relaxed whitespace-pre-line">
                            {activeLesson.content_text}
                        </div>
                    </div>
                {/if}

                <!-- 4. PDF Embed Option -->
                {#if activeLesson.pdf_url}
                    <div class="rounded-2xl border border-border bg-card p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-border/50">
                            <div class="flex items-center gap-2 text-xs font-semibold text-foreground">
                                <FileText class="size-4 text-rose-600" />
                                <span>Dokumen PDF Tersemat (Embed):</span>
                            </div>
                            <a
                                href={activeLesson.pdf_url}
                                target="_blank"
                                rel="noreferrer"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-muted hover:bg-muted/80 text-foreground transition-colors"
                            >
                                <ExternalLink class="size-3.5" />
                                <span>Buka Fullscreen</span>
                            </a>
                        </div>

                        <!-- Embedded PDF Object / Iframe -->
                        <div class="relative w-full h-[550px] rounded-xl overflow-hidden border border-border bg-muted/30">
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
                    <div class="rounded-2xl border border-border bg-card p-5 shadow-xs space-y-3">
                        <div class="flex items-center gap-2 text-xs font-semibold text-foreground">
                            <Download class="size-4 text-indigo-600" />
                            <span>Lampiran Berkas & Lembar Kerja ({activeLesson.attachments.length}):</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {#each activeLesson.attachments as file}
                                <a
                                    href={file.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    class="flex items-center justify-between p-3 rounded-xl border border-border bg-muted/30 hover:bg-muted/70 hover:border-indigo-400/50 transition-all group"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="size-8 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center shrink-0">
                                            {#if file.type === 'excel' || file.name.endsWith('.xlsx') || file.name.endsWith('.csv')}
                                                <FileSpreadsheet class="size-4" />
                                            {:else}
                                                <FileText class="size-4" />
                                            {/if}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-semibold text-foreground truncate group-hover:text-indigo-600">
                                                {file.name}
                                            </div>
                                            {#if file.size}
                                                <div class="text-[10px] text-muted-foreground">
                                                    {file.size}
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                    <Download class="size-4 text-muted-foreground group-hover:text-indigo-600 shrink-0 ml-2" />
                                </a>
                            {/each}
                        </div>
                    </div>
                {/if}

                <!-- Post-Test Section (Optional) -->
                {#if activeLesson.post_test?.questions?.length}
                    <div class="rounded-2xl border border-purple-200 dark:border-purple-900/60 bg-gradient-to-b from-purple-50/40 via-card to-card dark:from-purple-950/20 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-purple-100 dark:border-purple-900/40">
                            <div class="flex items-center gap-2.5">
                                <div class="size-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    POST
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-foreground">
                                        {activeLesson.post_test.title || 'Post-Test Evaluasi Akhir'}
                                    </h3>
                                    {#if activeLesson.post_test.instructions}
                                        <p class="text-xs text-muted-foreground">
                                            {activeLesson.post_test.instructions}
                                        </p>
                                    {/if}
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">
                                Target: {activeLesson.post_test.passing_score ?? 70}%
                            </span>
                        </div>

                        {#if !postTestSubmitted}
                            <div class="space-y-4 pt-1">
                                {#each activeLesson.post_test.questions as q, qIdx (q.id || qIdx)}
                                    <div class="p-4 rounded-xl border border-border bg-card space-y-3">
                                        <div class="text-xs font-semibold text-foreground flex items-start gap-2">
                                            <span class="size-5 rounded-full bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center shrink-0 text-[11px] font-bold">
                                                {qIdx + 1}
                                            </span>
                                            <span class="leading-relaxed">{q.question}</span>
                                        </div>
                                        <div class="space-y-1.5 pl-7">
                                            {#each q.options as opt, optIdx}
                                                <label
                                                    class="flex items-center gap-2.5 p-2.5 rounded-lg border text-xs cursor-pointer transition-all {postTestAnswers[qIdx] === optIdx ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/40 text-foreground font-semibold shadow-xs' : 'border-border/60 hover:bg-muted/50 text-muted-foreground'}"
                                                >
                                                    <input
                                                        type="radio"
                                                        name="post_q_{qIdx}"
                                                        checked={postTestAnswers[qIdx] === optIdx}
                                                        onchange={() => (postTestAnswers[qIdx] = optIdx)}
                                                        class="size-3.5 text-purple-600 focus:ring-purple-500"
                                                    />
                                                    <span>{opt}</span>
                                                </label>
                                            {/each}
                                        </div>
                                    </div>
                                {/each}

                                <div class="flex justify-end pt-1">
                                    <button
                                        type="button"
                                        onclick={submitPostTest}
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-purple-600 hover:bg-purple-500 text-white shadow-xs transition-colors"
                                    >
                                        <ClipboardCheck class="size-4" />
                                        <span>Kirim Jawaban Post-Test</span>
                                    </button>
                                </div>
                            </div>
                        {:else}
                            <!-- Result Screen -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 rounded-xl border {postTestScore >= (activeLesson.post_test.passing_score ?? 70) ? 'border-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200' : 'border-amber-300 bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200'}">
                                    <div class="flex items-center gap-3">
                                        {#if postTestScore >= (activeLesson.post_test.passing_score ?? 70)}
                                            <CheckCircle2 class="size-6 text-emerald-600 shrink-0" />
                                            <div>
                                                <div class="text-sm font-bold">Lulus Post-Test! Skor: {postTestScore}%</div>
                                                <div class="text-xs opacity-90">Selamat! Anda telah memahami modul ini dengan sangat baik.</div>
                                            </div>
                                        {:else}
                                            <AlertCircle class="size-6 text-amber-600 shrink-0" />
                                            <div>
                                                <div class="text-sm font-bold">Hasil Post-Test: {postTestScore}%</div>
                                                <div class="text-xs opacity-90">Skor belum mencapai target ({activeLesson.post_test.passing_score ?? 70}%). Anda dapat mengulanginya.</div>
                                            </div>
                                        {/if}
                                    </div>
                                    <button
                                        type="button"
                                        onclick={resetPostTest}
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-card text-xs font-semibold hover:bg-muted text-foreground transition-colors shrink-0"
                                    >
                                        <RotateCcw class="size-3.5" />
                                        <span>Ulangi Post-Test</span>
                                    </button>
                                </div>

                                <div class="space-y-3 pt-1">
                                    {#each activeLesson.post_test.questions as q, qIdx}
                                        {@const isCorrect = postTestAnswers[qIdx] === q.correct_answer}
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
                                                <div>Pilihan Anda: <span class="font-medium {isCorrect ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300'}">{q.options[postTestAnswers[qIdx]] ?? 'Belum dijawab'}</span></div>
                                                {#if !isCorrect}
                                                    <div>Kunci Jawaban: <span class="font-semibold text-emerald-700 dark:text-emerald-300">{q.options[q.correct_answer]}</span></div>
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
                {/if}

                <!-- Bottom Navigation & Completion Action Bar -->
                <div class="p-4 rounded-2xl border border-border bg-card shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        {#if prevLesson}
                            <Link
                                href="/learn/{course.slug}?lesson={prevLesson.slug}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border border-border bg-background hover:bg-muted transition-colors"
                            >
                                <ArrowLeft class="size-3.5" />
                                <span>Sebelumnya</span>
                            </Link>
                        {/if}
                    </div>

                    <!-- Primary Mark As Complete Button -->
                    <button
                        type="button"
                        onclick={toggleLessonCompletion}
                        disabled={isSubmitting}
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-md {activeLesson.is_completed ? 'bg-muted hover:bg-muted/80 text-foreground' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/25 active:scale-95'}"
                    >
                        {#if activeLesson.is_completed}
                            <CheckCircle2 class="size-4.5 text-emerald-500" />
                            <span>Tandai Belum Selesai</span>
                        {:else}
                            <CheckCircle2 class="size-4.5" />
                            <span>{isSubmitting ? 'Menyimpan...' : 'Tandai Selesai & Tingkatkan Skor'}</span>
                        {/if}
                    </button>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        {#if nextLesson}
                            <Link
                                href="/learn/{course.slug}?lesson={nextLesson.slug}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition-colors"
                            >
                                <span>Selanjutnya</span>
                                <ArrowRight class="size-3.5" />
                            </Link>
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

        <!-- Right: Curriculum Sidebar (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="rounded-2xl border border-border bg-card p-4 shadow-xs sticky top-6">
                <div class="flex items-center justify-between pb-3 border-b border-border/60">
                    <div>
                        <h3 class="text-sm font-bold text-foreground flex items-center gap-1.5">
                            <BookOpen class="size-4 text-indigo-600" />
                            <span>Daftar Pelajaran</span>
                        </h3>
                        <p class="text-[11px] text-muted-foreground mt-0.5">
                            {course.completed_lessons} dari {course.total_lessons} selesai
                        </p>
                    </div>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-muted">
                        {lessons.length} Modul
                    </span>
                </div>

                <!-- Lessons List -->
                <div class="mt-3 space-y-1.5 max-h-[600px] overflow-y-auto pr-1">
                    {#each lessons as lesson (lesson.id)}
                        {@const isActive = lesson.id === activeLesson?.id}
                        <Link
                            href="/learn/{course.slug}?lesson={lesson.slug}"
                            class="flex items-start gap-3 p-3 rounded-xl border text-xs transition-all {isActive ? 'bg-indigo-50/80 dark:bg-indigo-950/60 border-indigo-400 text-foreground font-semibold shadow-xs' : 'border-border/60 hover:bg-muted/60 text-muted-foreground'}"
                        >
                            <!-- Completion Icon -->
                            <div class="shrink-0 mt-0.5">
                                {#if lesson.is_completed}
                                    <CheckCircle2 class="size-4.5 text-emerald-500 fill-emerald-100 dark:fill-emerald-950" />
                                {:else if isActive}
                                    <div class="size-4.5 rounded-full border-2 border-indigo-600 flex items-center justify-center">
                                        <div class="size-2 rounded-full bg-indigo-600"></div>
                                    </div>
                                {:else}
                                    <Circle class="size-4.5 text-muted-foreground/40" />
                                {/if}
                            </div>

                            <!-- Lesson Info -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <span class="text-[10px] font-mono text-muted-foreground">
                                        Modul #{lesson.sort_order}
                                    </span>
                                    <span class="text-[10px] uppercase font-medium px-1.5 py-0.2 rounded bg-muted/80 text-muted-foreground">
                                        {lesson.content_type}
                                    </span>
                                </div>
                                <div class="line-clamp-2 {isActive ? 'text-indigo-950 dark:text-indigo-200' : 'text-foreground/90'}">
                                    {lesson.title}
                                </div>
                                <div class="text-[10px] text-muted-foreground mt-1 flex flex-wrap items-center gap-1.5">
                                    <span>⏱️ {lesson.duration_minutes} mnt</span>
                                    {#if lesson.has_pre_test}
                                        <span class="px-1 py-0.2 rounded font-semibold bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">Pre</span>
                                    {/if}
                                    {#if lesson.has_post_test}
                                        <span class="px-1 py-0.2 rounded font-semibold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">Post</span>
                                    {/if}
                                    {#if lesson.is_completed}
                                        <span class="text-emerald-600 font-medium">✓ Selesai</span>
                                    {/if}
                                </div>
                            </div>
                        </Link>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</div>
