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
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import SpiderChart from '@/components/SpiderChart.svelte';
    import CertificateModal from '@/components/CertificateModal.svelte';
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
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import { formatDate } from '@/lib/utils';

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
        completed_at?: string | null;
        certificate_code?: string;
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

    let isCertModalOpen = $state(false);
    let selectedCertCourse = $state<RecentClass | null>(null);

    function openCertificate(c: RecentClass) {
        selectedCertCourse = c;
        isCertModalOpen = true;
    }

    function formatLevel(lvl: string) {
        switch (lvl) {
            case 'beginner': return 'Pemula';
            case 'intermediate': return 'Menengah';
            case 'advanced': return 'Tingkat Mahir';
            default: return 'Semua Level';
        }
    }

</script>

<AppHead title="My Summary" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Minimalist Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border/80">
        <div>
            <Breadcrumbs
                breadcrumbs={[
                    { title: 'Portal Karyawan', href: '/my-summary' },
                    { title: 'My Summary', href: '#' },
                ]}
                class="mb-2"
            />
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
                <div class="text-[11px] text-muted-foreground mt-0.5 flex items-center gap-1">
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{widgets.completed_courses} Sertifikat</span> diperoleh
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
                    {#each recentClasses as course (course.id)}
                        <Link
                            href="/learn/{course.slug}"
                            class="group relative rounded-2xl border border-border bg-card p-3 sm:p-3.5 hover:border-indigo-400/60 hover:shadow-xs transition-all flex items-center gap-3 sm:gap-4"
                        >
                            <!-- Thumbnail ratio 1:1 dengan Progress Badge di pojok kanan -->
                            <div class="relative size-20 sm:size-22 shrink-0 overflow-hidden rounded-xl bg-muted border border-border/50 aspect-square">
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

                                <!-- Badge Progress di Pojok Kanan Image -->
                                <div class="absolute top-1.5 right-1.5">
                                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold shadow-xs backdrop-blur-xs {course.progress_percent === 100 ? 'bg-emerald-600 text-white' : 'bg-black/75 text-white'}">
                                        {course.progress_percent}%
                                    </span>
                                </div>
                            </div>

                            <!-- Di Kanannya: Kategori, Nama Kelas, dan Batas Tanggal (Exp.) -->
                            <div class="flex-1 min-w-0 flex flex-col justify-center space-y-1">
                                {#if course.class_group}
                                    <span class="text-[10px] sm:text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 truncate">
                                        {course.class_group.name}
                                    </span>
                                {/if}

                                <h3 class="text-xs sm:text-sm font-bold text-foreground leading-snug line-clamp-2 group-hover:text-indigo-600 transition-colors">
                                    {course.title}
                                </h3>

                                <div class="flex items-center gap-1 text-[10px] sm:text-[11px] text-muted-foreground font-medium pt-0.5">
                                    <Calendar class="size-3 text-muted-foreground/70 shrink-0" />
                                    <span>Exp. {course.due_date ? formatDate(course.due_date) : '-'}</span>
                                </div>
                            </div>

                            <ChevronRight class="size-4 text-muted-foreground/40 group-hover:text-foreground shrink-0 transition-transform group-hover:translate-x-0.5 ml-auto" />
                        </Link>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- Right: Spider Chart (Radar Kompetensi Employee) (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="rounded-xl border border-border bg-card p-4 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-border/50">
                    <div>
                        <h2 class="text-sm font-bold text-foreground flex items-center gap-2">
                            <Sparkles class="size-4 text-indigo-600" />
                            <span>Radar Kompetensi Karyawan</span>
                        </h2>
                        <p class="text-[11px] text-muted-foreground mt-0.5">Pemetaan 6 dimensi keahlian profesional</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shrink-0">
                        Rata-rata: {widgets.average_competency}
                    </span>
                </div>

                <!-- Spider Chart Visualization -->
                <div class="pt-3 pb-1 flex justify-center overflow-visible">
                    <SpiderChart {competencies} size={380} />
                </div>

                <!-- Detailed Breakdown List -->
                <div class="mt-2 pt-3 border-t border-border/50 space-y-2">
                    <div class="flex items-center justify-between text-[11px] font-semibold text-muted-foreground px-1">
                        <span>Dimensi Kompetensi</span>
                        <span>Level Penguasaan</span>
                    </div>
                    {#each competencies as comp}
                        <div class="p-2 rounded-lg bg-muted/20 border border-border/40 hover:bg-muted/40 transition-colors">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-semibold text-foreground">{comp.subject}</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold {comp.score >= 85 ? 'text-emerald-600 dark:text-emerald-400' : comp.score >= 75 ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400'}">
                                        {comp.score}
                                    </span>
                                    <span class="text-[10px] text-muted-foreground">/100</span>
                                </div>
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

<!-- Modal Sertifikat Kelulusan Resmi -->
<CertificateModal
    isOpen={isCertModalOpen}
    course={selectedCertCourse}
    user={employee}
    completedAt={selectedCertCourse?.completed_at}
    certificateCode={selectedCertCourse?.certificate_code}
    onClose={() => (isCertModalOpen = false)}
/>
