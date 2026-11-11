<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Portal',
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
    import Clock from '@lucide/svelte/icons/clock';
    import Calendar from '@lucide/svelte/icons/calendar';

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
            case 'advanced': return 'Mahir';
            default: return 'Semua Level';
        }
    }
</script>

<AppHead title="Kelas Saya - Corporate LMS" />

<div class="flex-1 space-y-5 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Minimalist Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-foreground tracking-tight flex items-center gap-2">
                <BookOpen class="size-5 text-indigo-600" />
                <span>Kelas Saya</span>
            </h1>
        </div>
    </div>

    <!-- Assigned Groups Filter Bar -->
    {#if assignedGroups.length > 0}
        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                onclick={() => (selectedGroupId = null)}
                class="px-3 py-1 rounded-full text-xs font-semibold transition-all {selectedGroupId === null ? 'bg-indigo-600 text-white shadow-xs' : 'bg-card border border-border text-foreground hover:bg-muted'}"
            >
                Semua ({courses.length})
            </button>
            {#each assignedGroups as group}
                <button
                    type="button"
                    onclick={() => (selectedGroupId = group.id)}
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all {selectedGroupId === group.id ? 'bg-indigo-600 text-white shadow-xs' : 'bg-card border border-border text-foreground hover:bg-muted'}"
                >
                    <Layers class="size-3.5" />
                    <span>{group.name}</span>
                    <span class="size-4 rounded-full text-[10px] bg-black/10 dark:bg-white/10 flex items-center justify-center font-mono">
                        {group.courses_count}
                    </span>
                </button>
            {/each}
        </div>
    {/if}

    <!-- 4-Kolom Courses Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        {#each filteredCourses as course (course.id)}
            <div class="group flex flex-col rounded-2xl border border-border bg-card overflow-hidden hover:border-indigo-400/60 hover:shadow-md transition-all">
                <!-- Thumbnail -->
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

                    <div class="absolute bottom-2 right-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-black/75 text-white backdrop-blur-xs flex items-center gap-1">
                            <Clock class="size-3" /> {course.estimated_duration_hours}j
                        </span>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-3.5 flex-1 flex flex-col justify-between space-y-3">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] text-muted-foreground">
                            <span>{formatLevel(course.level)}</span>
                            <span>{course.lessons_count} Pelajaran</span>
                        </div>

                        <h3 class="text-xs font-bold text-foreground leading-snug group-hover:text-indigo-600 transition-colors line-clamp-2">
                            <Link href="/learn/{course.slug}">
                                {course.title}
                            </Link>
                        </h3>

                        {#if course.due_date}
                            <div class="flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-medium">
                                <Calendar class="size-3" />
                                <span>Batas: {course.due_date}</span>
                            </div>
                        {/if}
                    </div>

                    <!-- Progress Bar & CTA -->
                    <div class="space-y-2 pt-2 border-t border-border/60">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-muted-foreground font-medium">Progress</span>
                            <span class="font-bold {course.progress_percent === 100 ? 'text-emerald-600' : 'text-indigo-600'}">
                                {course.progress_percent}%
                            </span>
                        </div>

                        <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-500 {course.progress_percent === 100 ? 'bg-emerald-500' : 'bg-indigo-600'}"
                                style="width: {course.progress_percent}%"
                            ></div>
                        </div>

                        <Link
                            href="/learn/{course.slug}"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {course.progress_percent === 100 ? 'bg-muted hover:bg-muted/80 text-foreground' : 'bg-indigo-600 hover:bg-indigo-500 text-white'}"
                        >
                            {#if course.progress_percent === 100}
                                <CheckCircle2 class="size-3.5 text-emerald-500" />
                                <span>Ulas Materi</span>
                            {:else if course.progress_percent > 0}
                                <PlayCircle class="size-3.5" />
                                <span>Lanjutkan</span>
                            {:else}
                                <PlayCircle class="size-3.5" />
                                <span>Mulai Belajar</span>
                            {/if}
                        </Link>
                    </div>
                </div>
            </div>
        {/each}
    </div>

    {#if filteredCourses.length === 0}
        <div class="p-8 rounded-2xl border border-dashed border-border text-center">
            <BookOpen class="size-10 mx-auto text-muted-foreground/40 mb-2" />
            <h3 class="text-xs font-semibold text-foreground">Tidak ada kelas pada kategori ini</h3>
        </div>
    {/if}
</div>
