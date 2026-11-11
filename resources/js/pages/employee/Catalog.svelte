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
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import PlayCircle from '@lucide/svelte/icons/play-circle';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import Layers from '@lucide/svelte/icons/layers';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Clock from '@lucide/svelte/icons/clock';

    let {
        courses = [],
        assignedGroups = [],
    }: {
        courses: any[];
        assignedGroups: any[];
    } = $props();

    let selectedGroupId = $state<number | null>(null);

    const filteredCourses = $derived.by(() => {
        if (selectedGroupId === null) return courses;
        return courses.filter((c) => c.class_group?.id === selectedGroupId);
    });

    function formatLevel(lvl: string) {
        switch (lvl) {
            case 'beginner': return 'Pemula';
            case 'intermediate': return 'Menengah';
            case 'advanced': return 'Tingkat Mahir';
            default: return 'Semua Level';
        }
    }
</script>

<AppHead title="Kelas Saya & Track Akses Karyawan" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-foreground tracking-tight flex items-center gap-2">
                <BookOpen class="size-6 text-indigo-600" />
                <span>Kelas Saya & Program Akses</span>
            </h1>
            <p class="text-sm text-muted-foreground mt-1">
                Daftar seluruh kelas yang diberikan kepada Anda melalui penugasan langsung maupun grup track korporat.
            </p>
        </div>
    </div>

    <!-- Assigned Groups Cards / Filter Bar -->
    {#if assignedGroups.length > 0}
        <div class="space-y-2">
            <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider block">Group & Program Track Anda:</span>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    onclick={() => (selectedGroupId = null)}
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all {selectedGroupId === null ? 'bg-indigo-600 text-white shadow-xs' : 'bg-card border border-border text-foreground hover:bg-muted'}"
                >
                    Semua ({courses.length})
                </button>
                {#each assignedGroups as group}
                    <button
                        type="button"
                        onclick={() => (selectedGroupId = group.id)}
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all {selectedGroupId === group.id ? 'bg-indigo-600 text-white shadow-xs' : 'bg-card border border-border text-foreground hover:bg-muted'}"
                    >
                        <Layers class="size-3.5" />
                        <span>{group.name}</span>
                        <span class="size-4.5 rounded-full text-[10px] bg-black/10 dark:bg-white/10 flex items-center justify-center font-mono font-bold">
                            {group.courses_count}
                        </span>
                    </button>
                {/each}
            </div>
        </div>
    {/if}

    <!-- Courses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {#each filteredCourses as course (course.id)}
            <div class="group flex flex-col rounded-2xl border border-border bg-card overflow-hidden hover:border-indigo-400 hover:shadow-lg transition-all">
                <!-- Thumbnail -->
                <div class="relative aspect-video w-full overflow-hidden bg-muted">
                    {#if course.thumbnail_url}
                        <img
                            src={course.thumbnail_url}
                            alt={course.title}
                            class="size-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                    {:else}
                        <div class="size-full flex items-center justify-center bg-gradient-to-br from-indigo-100 to-indigo-50 dark:from-indigo-950 dark:to-slate-900 text-indigo-600">
                            <BookOpen class="size-12" />
                        </div>
                    {/if}

                    <div class="absolute top-2 left-2 flex gap-1.5">
                        {#if course.class_group}
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-900/80 text-white backdrop-blur-xs">
                                {course.class_group.name}
                            </span>
                        {/if}
                    </div>

                    <div class="absolute bottom-2 right-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-black/75 text-white backdrop-blur-xs flex items-center gap-1">
                            <Clock class="size-3" /> {course.estimated_duration_hours} Jam
                        </span>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span>{formatLevel(course.level)}</span>
                            <span>{course.lessons_count} Pelajaran</span>
                        </div>

                        <h3 class="text-base font-bold text-foreground leading-snug group-hover:text-indigo-600 transition-colors">
                            <Link href="/learn/{course.slug}">
                                {course.title}
                            </Link>
                        </h3>

                        {#if course.short_description}
                            <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed">
                                {course.short_description}
                            </p>
                        {/if}

                        {#if course.tutor}
                            <div class="flex items-center gap-2 pt-1 text-xs text-muted-foreground">
                                {#if course.tutor.avatar_url}
                                    <img
                                        src={course.tutor.avatar_url}
                                        alt={course.tutor.name}
                                        class="size-5 rounded-full object-cover border border-border"
                                    />
                                {/if}
                                <span class="truncate">{course.tutor.name}</span>
                            </div>
                        {/if}
                    </div>

                    <!-- Progress and CTA -->
                    <div class="mt-4 pt-4 border-t border-border/60">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-medium text-muted-foreground">Progress</span>
                            <span class="font-mono font-bold {course.progress_percent === 100 ? 'text-emerald-600' : 'text-indigo-600'}">
                                {course.progress_percent}%
                            </span>
                        </div>

                        <div class="w-full h-2 rounded-full bg-muted overflow-hidden mb-3">
                            <div
                                class="h-full rounded-full transition-all duration-500 {course.progress_percent === 100 ? 'bg-emerald-500' : 'bg-indigo-600'}"
                                style="width: {course.progress_percent}%"
                            ></div>
                        </div>

                        <Link
                            href="/learn/{course.slug}"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all shadow-xs {course.progress_percent === 100 ? 'bg-muted hover:bg-muted/80 text-foreground' : 'bg-indigo-600 hover:bg-indigo-500 text-white'}"
                        >
                            {#if course.progress_percent === 100}
                                <CheckCircle2 class="size-4 text-emerald-500" />
                                <span>Tinjau Ulang Kelas</span>
                            {:else}
                                <PlayCircle class="size-4" />
                                <span>{course.progress_percent > 0 ? 'Lanjutkan Belajar' : 'Mulai Belajar'}</span>
                            {/if}
                        </Link>
                    </div>
                </div>
            </div>
        {/each}
    </div>
</div>
