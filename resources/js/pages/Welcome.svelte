<script lang="ts">
    import { Link, page, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Layers from '@lucide/svelte/icons/layers';
    import Award from '@lucide/svelte/icons/award';
    import Building2 from '@lucide/svelte/icons/building-2';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import User from '@lucide/svelte/icons/user';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Video from '@lucide/svelte/icons/video';
    import FileText from '@lucide/svelte/icons/file-text';
    import Headphones from '@lucide/svelte/icons/headphones';

    const auth = $derived(page.props.auth as any);

    let isLoggingIn = $state(false);

    function loginAs(email: string) {
        isLoggingIn = true;
        router.post('/login', {
            email,
            password: 'password',
            remember: true,
        }, {
            onFinish: () => (isLoggingIn = false),
        });
    }
</script>

<AppHead title="Eduja Corporate LMS - Solusi Pembelajaran & Kompetensi Perusahaan" />

<div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-foreground flex flex-col">
    <!-- Navbar -->
    <header class="w-full border-b border-border/80 bg-background/80 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <Link href="/" class="flex items-center gap-2">
                <img
                    src="/images/logo.png"
                    alt="Eduja Learning Media"
                    class="h-9 w-auto object-contain"
                />
            </Link>

            <nav class="flex items-center gap-3">
                {#if auth?.user}
                    <Link
                        href="/dashboard"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
                    >
                        <span>Buka Dashboard</span>
                        <ArrowRight class="size-3.5" />
                    </Link>
                {:else}
                    <Link
                        href="/login"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-foreground hover:bg-muted transition-colors"
                    >
                        Masuk (Login)
                    </Link>
                    <Link
                        href="/register"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
                    >
                        Daftar Baru
                    </Link>
                {/if}
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 py-12 md:py-20 flex flex-col items-center text-center space-y-8">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-semibold">
            <Sparkles class="size-3.5" />
            <span>Sistem LMS Enterprise Modern dengan Laravel 13 & Svelte 5</span>
        </div>

        <h1 class="text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-foreground max-w-4xl leading-tight">
            Tingkatkan Talenta Perusahaan dengan <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-500">Corporate LMS Cerdas</span>
        </h1>

        <p class="text-base sm:text-lg text-muted-foreground max-w-2xl leading-relaxed">
            Platform komprehensif untuk pengelolaan kelas, materi multi-format (YouTube, Audio, PDF & Lampiran), sistem group/kategori akses otomatis, serta spider chart kompetensi karyawan secara real-time.
        </p>

        <!-- Quick Demo Login Cards -->
        <div class="w-full max-w-3xl pt-4">
            <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3">
                🚀 Coba Langsung dengan Akun Demo (Satu Klik):
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                <!-- Demo Card: Admin -->
                <div class="p-5 rounded-2xl border border-indigo-200 dark:border-indigo-900/60 bg-card hover:shadow-lg transition-all space-y-3 relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                            <ShieldCheck class="size-3.5" /> Role: Admin
                        </span>
                        <span class="text-[11px] text-muted-foreground font-mono">password</span>
                    </div>
                    <div>
                        <div class="font-bold text-foreground text-sm">Eduja System (Super Admin)</div>
                        <div class="text-xs text-muted-foreground">eduja.systems@gmail.com</div>
                    </div>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Kelola data kelas, materi video/audio/PDF, instruktur, perusahaan klien, serta undang karyawan dan atur group akses.
                    </p>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            onclick={() => loginAs('eduja.systems@gmail.com')}
                            disabled={isLoggingIn}
                            class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-xs transition-colors"
                        >
                            <span>{isLoggingIn ? 'Masuk...' : 'Masuk sebagai Eduja Admin'}</span>
                            <ArrowRight class="size-3.5" />
                        </button>
                        <button
                            type="button"
                            onclick={() => loginAs('admin@corporate.lms')}
                            disabled={isLoggingIn}
                            title="Atau masuk sebagai Alexander Pratama (admin@corporate.lms)"
                            class="px-2.5 py-2 rounded-xl bg-muted hover:bg-muted/80 text-foreground text-xs font-medium transition-colors"
                        >
                            Alt Admin
                        </button>
                    </div>
                </div>

                <!-- Demo Card: Employee -->
                <div class="p-5 rounded-2xl border border-emerald-200 dark:border-emerald-900/60 bg-card hover:shadow-lg transition-all space-y-3 relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                            <User class="size-3.5" /> Role: Employee
                        </span>
                        <span class="text-[11px] text-muted-foreground font-mono">password</span>
                    </div>
                    <div>
                        <div class="font-bold text-foreground text-sm">John Hendrawan</div>
                        <div class="text-xs text-muted-foreground">employee@corporate.lms</div>
                    </div>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Lihat <strong>My Summary</strong> (5 kelas terkini & progress), widget informatif, Spider Chart radar kompetensi, dan ruang belajar materi.
                    </p>
                    <button
                        type="button"
                        onclick={() => loginAs('employee@corporate.lms')}
                        disabled={isLoggingIn}
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-xs transition-colors"
                    >
                        <span>{isLoggingIn ? 'Masuk...' : 'Masuk sebagai Karyawan'}</span>
                        <ArrowRight class="size-3.5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Features Matrix -->
        <div class="w-full pt-12 text-left">
            <h2 class="text-xl font-bold text-foreground text-center mb-6">
                Fitur Utama Sistem LMS Corporate
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <!-- 1. Pengelolaan Kelas -->
                <div class="p-5 rounded-2xl border border-border bg-card shadow-xs space-y-2">
                    <div class="size-9 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 flex items-center justify-center">
                        <GraduationCap class="size-5" />
                    </div>
                    <h3 class="font-bold text-sm text-foreground">Pengelolaan Kelas</h3>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Manajemen kurikulum kelas, silabus, tingkat kesulitan, durasi, dan penilaian dampak kompetensi.
                    </p>
                </div>

                <!-- 2. Pelajaran Multi-format -->
                <div class="p-5 rounded-2xl border border-border bg-card shadow-xs space-y-2">
                    <div class="size-9 rounded-xl bg-violet-50 dark:bg-violet-950 text-violet-600 flex items-center justify-center">
                        <Video class="size-5" />
                    </div>
                    <h3 class="font-bold text-sm text-foreground">Pelajaran Multi-format</h3>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Embed video YouTube, pemutar audio/podcast, materi teks panduan, embed file PDF interaktif, serta berkas lampiran.
                    </p>
                </div>

                <!-- 3. Group & Kategori Akses -->
                <div class="p-5 rounded-2xl border border-border bg-card shadow-xs space-y-2">
                    <div class="size-9 rounded-xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 flex items-center justify-center">
                        <Layers class="size-5" />
                    </div>
                    <h3 class="font-bold text-sm text-foreground">Group Track & Auto-Assign</h3>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Cukup assign karyawan ke group/category kelas, otomatis dapat mengakses seluruh kelas di dalam group tersebut.
                    </p>
                </div>

                <!-- 4. Spider Chart Kompetensi -->
                <div class="p-5 rounded-2xl border border-border bg-card shadow-xs space-y-2">
                    <div class="size-9 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-600 flex items-center justify-center">
                        <Award class="size-5" />
                    </div>
                    <h3 class="font-bold text-sm text-foreground">Spider Chart Kompetensi</h3>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Halaman <em>My Summary</em> dengan visualisasi radar 6 pilar kecakapan, 5 kelas terakhir, serta widget performa.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-border/80 py-6 text-center text-xs text-muted-foreground">
        © 2026 Eduja Corporate LMS. Ditenagai oleh Laravel 13, Inertia.js & Svelte 5.
    </footer>
</div>
