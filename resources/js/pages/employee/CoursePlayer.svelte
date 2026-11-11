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
    }

    interface ActiveLesson extends LessonItem {
        video_url?: string;
        audio_url?: string;
        content_text?: string;
        pdf_url?: string;
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

    // Helper to format YouTube embed link
    function getYouTubeEmbedUrl(url?: string): string | null {
        if (!url) return null;
        try {
            // Already embed format
            if (url.includes('youtube.com/embed/')) {
                return url;
            }
            // youtu.be/ID
            const shortMatch = url.match(/youtu\.be\/([a-zA-Z0-9_-]+)/);
            if (shortMatch && shortMatch[1]) {
                return `https://www.youtube-nocookie.com/embed/${shortMatch[1]}?rel=0&modestbranding=1`;
            }
            // youtube.com/watch?v=ID
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
            {#if course.tutor}
                <p class="text-xs text-muted-foreground">
                    Instruktur: <span class="font-medium text-foreground">{course.tutor.name}</span>
                </p>
            {/if}
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
                                <div class="text-[10px] text-muted-foreground mt-1 flex items-center gap-2">
                                    <span>⏱️ {lesson.duration_minutes} mnt</span>
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
