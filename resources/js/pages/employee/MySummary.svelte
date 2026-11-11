<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Portal Karyawan',
                href: '/my-summary',
            },
            {
                title: 'My Summary',
                href: '/my-summary',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import SpiderChart from '@/components/SpiderChart.svelte';
    import Award from '@lucide/svelte/icons/award';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import Clock from '@lucide/svelte/icons/clock';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import PlayCircle from '@lucide/svelte/icons/play-circle';
    import Zap from '@lucide/svelte/icons/zap';
    import Building2 from '@lucide/svelte/icons/building-2';
    import User from '@lucide/svelte/icons/user';
    import Calendar from '@lucide/svelte/icons/calendar';

    interface TutorInfo {
        id: number;
        name: string;
        title?: string;
        avatar_url?: string;
    }

    interface ClassGroupInfo {
        id: number;
        name: string;
        badge_color: string;
    }

    interface RecentClass {
        id: number;
        title: string;
        slug: string;
        thumbnail_url?: string;
        level: string;
        estimated_duration_hours: number;
        due_date?: string | null;
        tutor?: TutorInfo;
        class_group?: ClassGroupInfo;
        company?: { id: number; name: string };
        progress_percent: number;
        status: string;
        total_lessons: number;
        completed_lessons: number;
        last_accessed_at?: string;
    }

    interface Widgets {
        total_enrolled: number;
        completed_courses: number;
        in_progress_courses: number;
        learning_hours: number;
        average_competency: number;
        top_competency?: { subject: string; score: number };
    }

    interface Competency {
        subject: string;
        score: number;
        fullMark: number;
    }

    let {
        recentClasses = [],
        widgets,
        competencies = [],
        employee,
    }: {
        recentClasses: RecentClass[];
        widgets: Widgets;
        competencies: Competency[];
        employee: any;
    } = $props();

    function formatLevel(lvl: string) {
        switch (lvl) {
            case 'beginner': return 'Pemula';
            case 'intermediate': return 'Menengah';
            case 'advanced': return 'Tingkat Mahir';
            default: return 'Semua Level';
        }
    }

    function formatDate(dateStr?: string | null) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    }
</script>

<AppHead title="My Summary" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Minimalist Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border/80">
        <div>
            <h1 class="text-xl md:text-2xl font-extrabold text-foreground tracking-tight">
                Halo, {employee?.name || 'Karyawan'} 👋
            </h1>
            <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-muted-foreground">
                {#if employee?.company}
                    <span class="inline-flex items-center gap-1 font-medium text-foreground">
                        <Building2 class="size-3.5 text-indigo-600" />
                        {employee.company.name}
                    </span>
                {/if}
                {#if employee?.department}
                    {#if employee?.company}<span>•</span>{/if}
                    <span>{employee.department} {employee?.position ? `(${employee.position})` : ''}</span>
                {/if}
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <Link
                href="/my-courses"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
            >
                <BookOpen class="size-3.5" />
                <span>Katalog Kelas</span>
                <ArrowRight class="size-3.5" />
            </Link>
        </div>
    </div>

    <!-- 4 Informative Widgets Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Widget 1: Total Kelas -->
        <div class="rounded-xl border border-border/80 bg-card p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Kelas Terdaftar</span>
                <div class="size-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <BookOpen class="size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-extrabold text-foreground">{widgets.total_enrolled}</div>
                <div class="text-[11px] text-muted-foreground mt-0.5">
                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{widgets.in_progress_courses}</span> berjalan
                </div>
            </div>
        </div>

        <!-- Widget 2: Kelas Selesai -->
        <div class="rounded-xl border border-border/80 bg-card p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Kelas Selesai</span>
                <div class="size-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <CheckCircle2 class="size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-extrabold text-foreground">{widgets.completed_courses}</div>
                <div class="text-[11px] text-muted-foreground mt-0.5">
                    Modul terselesaikan
                </div>
            </div>
        </div>

        <!-- Widget 3: Jam Belajar -->
        <div class="rounded-xl border border-border/80 bg-card p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Total Jam Belajar</span>
                <div class="size-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <Clock class="size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-extrabold text-foreground">{widgets.learning_hours} <span class="text-xs font-normal text-muted-foreground">jam</span></div>
                <div class="text-[11px] text-muted-foreground mt-0.5">
                    Waktu pembelajaran
                </div>
            </div>
        </div>

        <!-- Widget 4: Rata-rata Skor Kompetensi -->
        <div class="rounded-xl border border-border/80 bg-card p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Rata-rata Skor</span>
                <div class="size-8 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <Award class="size-4" />
                </div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-extrabold text-foreground">{widgets.average_competency} <span class="text-xs font-normal text-muted-foreground">/ 100</span></div>
                <div class="text-[11px] text-muted-foreground mt-0.5 truncate">
                    Top: <span class="font-semibold text-purple-600 dark:text-purple-400">{widgets.top_competency?.subject || 'Analisis'}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: 5 Kelas Terakhir & Spider Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: 5 Recent Classes & Progress (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                    <Zap class="size-4 text-indigo-600" />
                    <span>5 Kelas Terakhir</span>
                </h2>
                <Link
                    href="/my-courses"
                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 hover:underline flex items-center gap-1"
                >
                    Lihat Semua
                    <ArrowRight class="size-3" />
                </Link>
            </div>

            {#if recentClasses.length === 0}
                <div class="rounded-xl border border-dashed border-border p-8 text-center bg-card">
                    <BookOpen class="size-8 mx-auto text-muted-foreground/60 mb-2" />
                    <h3 class="text-xs font-semibold text-foreground">Belum ada kelas yang diakses</h3>
                    <Link
                        href="/my-courses"
                        class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500 transition-colors"
                    >
                        Mulai Belajar
                    </Link>
                </div>
            {:else}
                <div class="space-y-3">
                    {#each recentClasses as course, idx (course.id)}
                        <div class="group relative rounded-xl border border-border bg-card p-4 hover:border-indigo-400/50 hover:shadow-xs transition-all">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <!-- Thumbnail -->
                                <div class="relative sm:size-24 shrink-0 overflow-hidden rounded-lg bg-muted border border-border/50 aspect-video sm:aspect-square">
                                    {#if course.thumbnail_url}
                                        <img
                                            src={course.thumbnail_url}
                                            alt={course.title}
                                            class="size-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        />
                                    {:else}
                                        <div class="size-full flex items-center justify-center bg-gradient-to-br from-indigo-100 to-indigo-50 dark:from-indigo-950 dark:to-slate-900 text-indigo-600">
                                            <BookOpen class="size-6" />
                                        </div>
                                    {/if}
                                    <div class="absolute top-1 left-1">
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold tracking-wider uppercase bg-black/70 text-white">
                                            #{idx + 1}
                                        </span>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="flex-1 flex flex-col justify-between min-w-0">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                                            {#if course.class_group}
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300">
                                                    {course.class_group.name}
                                                </span>
                                            {/if}
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-muted text-muted-foreground">
                                                {formatLevel(course.level)}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-muted text-muted-foreground">
                                                ⏱️ {course.estimated_duration_hours} Jam
                                            </span>
                                            {#if course.due_date}
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">
                                                    <Calendar class="size-3" />
                                                    Tenggat: {formatDate(course.due_date)}
                                                </span>
                                            {/if}
                                        </div>

                                        <h3 class="text-sm font-bold text-foreground leading-snug line-clamp-1 group-hover:text-indigo-600 transition-colors">
                                            <Link href="/learn/{course.slug}">
                                                {course.title}
                                            </Link>
                                        </h3>

                                        {#if course.tutor}
                                            <div class="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground">
                                                {#if course.tutor.avatar_url}
                                                    <img
                                                        src={course.tutor.avatar_url}
                                                        alt={course.tutor.name}
                                                        class="size-4 rounded-full object-cover border border-border"
                                                    />
                                                {/if}
                                                <span class="truncate">Tutor: <span class="font-medium text-foreground/90">{course.tutor.name}</span></span>
                                            </div>
                                        {/if}
                                    </div>

                                    <!-- Progress Bar and Action Button -->
                                    <div class="mt-2.5 pt-2 border-t border-border/40">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-[11px] text-muted-foreground">
                                                {course.completed_lessons} / {course.total_lessons} Modul Selesai
                                            </span>
                                            <span class="font-mono font-bold {course.progress_percent === 100 ? 'text-emerald-600' : 'text-indigo-600'}">
                                                {course.progress_percent}%
                                            </span>
                                        </div>

                                        <div class="w-full h-1.5 rounded-full bg-muted overflow-hidden">
                                            <div
                                                class="h-full rounded-full transition-all duration-500 {course.progress_percent === 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-indigo-500 to-indigo-600'}"
                                                style="width: {course.progress_percent}%"
                                            ></div>
                                        </div>

                                        <div class="mt-2.5 flex items-center justify-between gap-2">
                                            <div class="text-[11px]">
                                                {#if course.progress_percent === 100}
                                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                                        <CheckCircle2 class="size-3.5" /> Selesai
                                                    </span>
                                                {:else if course.progress_percent > 0}
                                                    <span class="text-amber-600 dark:text-amber-400 font-medium">
                                                        Sedang Berjalan
                                                    </span>
                                                {:else}
                                                    <span class="text-muted-foreground font-medium">
                                                        Belum Dimulai
                                                    </span>
                                                {/if}
                                            </div>

                                            <Link
                                                href="/learn/{course.slug}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all {course.progress_percent === 100 ? 'bg-muted hover:bg-muted/80 text-foreground' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs'}"
                                            >
                                                <PlayCircle class="size-3.5" />
                                                <span>{course.progress_percent === 100 ? 'Review' : 'Lanjutkan'}</span>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- Right: Spider Chart (Radar Kompetensi Employee) (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-border/50">
                    <h2 class="text-sm font-bold text-foreground flex items-center gap-2">
                        <Sparkles class="size-4 text-indigo-600" />
                        <span>Radar Kompetensi</span>
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                        Skor: {widgets.average_competency}
                    </span>
                </div>

                <!-- Spider Chart Visualization -->
                <div class="pt-2 flex justify-center">
                    <SpiderChart {competencies} size={300} />
                </div>

                <!-- Detailed Breakdown List -->
                <div class="mt-3 pt-3 border-t border-border/50 space-y-2">
                    {#each competencies as comp}
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-medium text-foreground/80">{comp.subject}</span>
                                <span class="font-mono font-bold {comp.score >= 85 ? 'text-emerald-600' : comp.score >= 75 ? 'text-indigo-600' : 'text-amber-600'}">
                                    {comp.score}
                                </span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-muted overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500 {comp.score >= 85 ? 'bg-emerald-500' : comp.score >= 75 ? 'bg-indigo-500' : 'bg-amber-500'}"
                                    style="width: {comp.score}%"
                                ></div>
                            </div>
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</div>
