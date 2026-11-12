<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Admin',
                href: '/admin/dashboard',
            },
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import Building2 from '@lucide/svelte/icons/building-2';
    import Users from '@lucide/svelte/icons/users';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Layers from '@lucide/svelte/icons/layers';
    import Award from '@lucide/svelte/icons/award';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import Plus from '@lucide/svelte/icons/plus';

    interface Stats {
        total_companies?: number;
        total_employees?: number;
        total_courses?: number;
        total_lessons?: number;
        total_groups?: number;
        total_enrollments?: number;
        completed_enrollments?: number;
        completion_rate?: number;
    }

    let {
        stats = {
            total_companies: 0,
            total_employees: 0,
            total_courses: 0,
            total_lessons: 0,
            total_groups: 0,
            total_enrollments: 0,
            completed_enrollments: 0,
            completion_rate: 0,
        },
        recentCourses = [],
        recentEnrollments = [],
        classGroups = [],
    }: {
        stats?: Stats;
        recentCourses?: any[];
        recentEnrollments?: any[];
        classGroups?: any[];
    } = $props();
</script>

<AppHead title="Dashboard - Corporate LMS" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header Admin -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <Breadcrumbs
                breadcrumbs={[
                    { title: 'Admin', href: '/admin/dashboard' },
                    { title: 'Dashboard', href: '#' },
                ]}
                class="mb-2"
            />
            <h1 class="text-xl font-bold text-foreground tracking-tight">
                Dashboard
            </h1>
        </div>

        <!-- Quick Action CTAs -->
        <div class="flex items-center gap-2">
            <Link
                href="/admin/courses"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
            >
                <Plus class="size-3.5" />
                <span>Tambah Kelas</span>
            </Link>
            <Link
                href="/admin/groups"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-border bg-card hover:bg-muted text-foreground text-xs font-semibold transition-colors"
            >
                <Layers class="size-3.5 text-indigo-600" />
                <span>Kategori</span>
            </Link>
        </div>
    </div>

    <!-- 4 Key Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- 1. Perusahaan -->
        <Link
            href="/admin/companies"
            class="group rounded-2xl border border-border bg-card p-5 shadow-xs hover:border-indigo-400 hover:shadow-md transition-all"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Klien Perusahaan</span>
                <div class="size-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <Building2 class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-foreground">{stats.total_companies}</div>
                <p class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                    Mitra korporasi aktif
                </p>
            </div>
        </Link>

        <!-- 2. Karyawan -->
        <Link
            href="/admin/employees"
            class="group rounded-2xl border border-border bg-card p-5 shadow-xs hover:border-indigo-400 hover:shadow-md transition-all"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Total Karyawan</span>
                <div class="size-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <Users class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-foreground">{stats.total_employees}</div>
                <p class="text-xs text-muted-foreground mt-1">
                    Terdaftar di sistem
                </p>
            </div>
        </Link>

        <!-- 3. Kelas & Pelajaran -->
        <Link
            href="/admin/courses"
            class="group rounded-2xl border border-border bg-card p-5 shadow-xs hover:border-indigo-400 hover:shadow-md transition-all"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Kelas & Modul</span>
                <div class="size-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <GraduationCap class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-foreground">{stats.total_courses}</div>
                <p class="text-xs text-muted-foreground mt-1">
                    {stats.total_lessons} total pelajaran aktif
                </p>
            </div>
        </Link>

        <!-- 4. Penyelesaian Belajar -->
        <div class="rounded-2xl border border-border bg-card p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground">Completion Rate</span>
                <div class="size-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center">
                    <CheckCircle2 class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-foreground">{stats.completion_rate}%</div>
                <p class="text-xs text-muted-foreground mt-1">
                    {stats.completed_enrollments} dari {stats.total_enrollments} kelulusan
                </p>
            </div>
        </div>
    </div>

    <!-- Main Section: Aktivitas Karyawan & Group Kelas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Recent Enrollments & Progress Activity (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                    <BookOpen class="size-5 text-indigo-600" />
                    <span>Aktivitas Belajar Karyawan Terkini</span>
                </h2>
                <Link
                    href="/admin/employees"
                    class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1"
                >
                    Semua Karyawan <ArrowRight class="size-3" />
                </Link>
            </div>

            <div class="rounded-2xl border border-border bg-card overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-muted/50 border-b border-border/80 text-muted-foreground font-semibold">
                            <tr>
                                <th class="p-3 pl-4">Karyawan</th>
                                <th class="p-3">Kelas</th>
                                <th class="p-3">Progress</th>
                                <th class="p-3">Sumber Akses</th>
                                <th class="p-3 pr-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            {#each recentEnrollments as enr (enr.id)}
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="p-3 pl-4">
                                        <div class="font-semibold text-foreground">{enr.user_name}</div>
                                        <div class="text-[10px] text-muted-foreground">{enr.user_email}</div>
                                    </td>
                                    <td class="p-3">
                                        <Link href="/admin/courses" class="font-medium text-foreground hover:text-indigo-600 line-clamp-1 max-w-[200px]">
                                            {enr.course_title}
                                        </Link>
                                    </td>
                                    <td class="p-3 min-w-[120px]">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 rounded-full bg-muted overflow-hidden">
                                                <div
                                                    class="h-full rounded-full {enr.progress_percent === 100 ? 'bg-emerald-500' : 'bg-indigo-600'}"
                                                    style="width: {enr.progress_percent}%"
                                                ></div>
                                            </div>
                                            <span class="font-mono font-bold text-[11px] text-foreground">{enr.progress_percent}%</span>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium {enr.enrollment_source === 'group' ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600' : 'bg-muted text-muted-foreground'}">
                                            {enr.enrollment_source === 'group' ? 'Group Track' : 'Direct'}
                                        </span>
                                    </td>
                                    <td class="p-3 pr-4">
                                        {#if enr.status === 'completed'}
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                                <CheckCircle2 class="size-3" /> Lulus
                                            </span>
                                        {:else if enr.progress_percent > 0}
                                            <span class="text-[11px] font-medium text-amber-600">
                                                Belajar
                                            </span>
                                        {:else}
                                            <span class="text-[11px] text-muted-foreground">
                                                Terdaftar
                                            </span>
                                        {/if}
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Group/Category Kelas Overview (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                    <Layers class="size-5 text-indigo-600" />
                    <span>Group Kelas ({classGroups.length})</span>
                </h2>
                <Link
                    href="/admin/groups"
                    class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1"
                >
                    Kelola Group <ArrowRight class="size-3" />
                </Link>
            </div>

            <div class="rounded-2xl border border-border bg-card p-4 shadow-xs space-y-3">
                <p class="text-xs text-muted-foreground">
                    💡 <em>Fitur Unggulan:</em> Admin dapat mengelompokkan kelas ke dalam Group/Category ini. Cukup assign karyawan ke grup, maka karyawan langsung mendapat akses ke seluruh kelas di dalamnya.
                </p>

                <div class="space-y-2.5 pt-2">
                    {#each classGroups as group (group.id)}
                        <div class="p-3 rounded-xl border border-border/80 bg-muted/20 hover:border-indigo-400/60 transition-all flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-xs text-foreground truncate">{group.name}</span>
                                </div>
                                <div class="text-[11px] text-muted-foreground mt-0.5">
                                    {group.courses_count} Kelas • {group.employees_count} Karyawan Terdaftar
                                </div>
                            </div>
                            <Link
                                href="/admin/groups"
                                class="shrink-0 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition-colors"
                            >
                                Assign
                            </Link>
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</div>
