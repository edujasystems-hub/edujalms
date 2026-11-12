<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Klasemen & Gamifikasi',
                href: '#',
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import Trophy from '@lucide/svelte/icons/trophy';
    import Medal from '@lucide/svelte/icons/medal';
    import Award from '@lucide/svelte/icons/award';
    import Crown from '@lucide/svelte/icons/crown';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Building2 from '@lucide/svelte/icons/building-2';
    import Search from '@lucide/svelte/icons/search';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Flame from '@lucide/svelte/icons/flame';
    import ArrowUpRight from '@lucide/svelte/icons/arrow-up-right';
    import Zap from '@lucide/svelte/icons/zap';

    let {
        isAdmin = false,
        currentCompany = null,
        companies = [],
        selectedCompanyId = 'all',
        leaderboard = [],
        podium = { first: null, second: null, third: null },
        currentUserStats = null,
    }: {
        isAdmin: boolean;
        currentCompany: any;
        companies: any[];
        selectedCompanyId: any;
        leaderboard: any[];
        podium: { first: any; second: any; third: any };
        currentUserStats: any;
    } = $props();

    let searchTerm = $state('');
    let selectedTierFilter = $state('all');

    function onCompanyChange(e: Event) {
        const val = (e.target as HTMLSelectElement).value;
        const targetUrl = isAdmin ? '/admin/leaderboard' : '/leaderboard';
        router.get(targetUrl, { company_id: val }, { preserveState: false, replace: true });
    }

    const filteredLeaderboard = $derived(
        leaderboard.filter((u) => {
            const matchesSearch =
                u.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                u.department.toLowerCase().includes(searchTerm.toLowerCase()) ||
                u.position.toLowerCase().includes(searchTerm.toLowerCase());
            const matchesTier =
                selectedTierFilter === 'all' ||
                String(u.tier?.level) === selectedTierFilter;
            return matchesSearch && matchesTier;
        })
    );

    function getInitials(name: string): string {
        return name
            ? name
                  .split(' ')
                  .map((n) => n[0])
                  .slice(0, 2)
                  .join('')
                  .toUpperCase()
            : 'U';
    }
</script>

<AppHead title="Klasemen Karyawan & Skor Gamifikasi" />

<div class="flex-1 space-y-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
    <!-- Header with Office Context & Admin Switcher -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-border/80">
        <div>
            <Breadcrumbs
                breadcrumbs={[
                    { title: isAdmin ? 'Admin' : 'Portal Karyawan', href: isAdmin ? '/admin/dashboard' : '/my-summary' },
                    { title: 'Klasemen & Gamifikasi', href: '#' },
                ]}
                class="mb-2"
            />
            <h1 class="text-2xl font-extrabold text-foreground tracking-tight flex items-center gap-2">
                <span>Klasemen Prestasi Karyawan</span>
            </h1>
            <p class="text-xs text-muted-foreground mt-1 flex items-center gap-2">
                <Building2 class="size-3.5 text-muted-foreground" />
                <span>
                    {#if isAdmin}
                        {#if currentCompany}
                            Kantor Cabang: <strong class="text-foreground">{currentCompany.name} ({currentCompany.code})</strong>
                        {:else}
                            Menampilkan seluruh kantor cabang perusahaan
                        {/if}
                    {:else if currentCompany}
                        Klasemen resmi <strong class="text-foreground">{currentCompany.name}</strong>
                    {:else}
                        Klasemen internal seluruh divisi
                    {/if}
                </span>
            </p>
        </div>

        {#if isAdmin && companies.length > 0}
            <div class="flex items-center gap-2.5 bg-card border border-border p-2 rounded-2xl shadow-xs shrink-0">
                <Building2 class="size-4 text-indigo-600 ml-2" />
                <span class="text-xs font-semibold text-foreground whitespace-nowrap">Filter Kantor:</span>
                <select
                    value={selectedCompanyId}
                    onchange={onCompanyChange}
                    class="py-1.5 pl-3 pr-8 rounded-xl text-xs bg-muted/40 border border-border text-foreground font-medium focus:outline-hidden focus:border-indigo-500"
                >
                    <option value="all">🌐 Seluruh Kantor ({companies.length} Cabang)</option>
                    {#each companies as comp}
                        <option value={comp.id}>🏢 {comp.name} ({comp.code})</option>
                    {/each}
                </select>
            </div>
        {/if}
    </div>

    <!-- Current User Standings Pill (if employee has ranking) -->
    {#if currentUserStats}
        <div class="rounded-2xl border border-indigo-500/30 bg-gradient-to-r from-indigo-500/10 via-purple-500/5 to-transparent p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center gap-3.5">
                <div class="size-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shrink-0">
                    #{currentUserStats.rank}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Peringkat Anda</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center gap-1">
                            <span>{currentUserStats.badge}</span>
                            <span>{currentUserStats.tier?.name}</span>
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-foreground">
                        {currentUserStats.name} — <span class="font-mono text-amber-500">{currentUserStats.total_points} XP</span>
                    </h3>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Anda telah menyelesaikan {currentUserStats.completed_courses_count} kelas di {currentUserStats.company_name}.
                        {#if currentUserStats.tier?.next_points}
                            Dibutuhkan <strong class="text-foreground">{currentUserStats.tier.next_points - currentUserStats.total_points} XP</strong> lagi untuk naik level!
                        {/if}
                    </p>
                </div>
            </div>

            {#if currentUserStats.tier?.next_points}
                <div class="w-full sm:w-56 space-y-1">
                    <div class="flex justify-between text-[11px] font-medium text-muted-foreground">
                        <span>Progress Level</span>
                        <span class="font-mono font-bold text-indigo-600">{currentUserStats.tier.progress_percent}%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-muted overflow-hidden">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all duration-500"
                            style="width: {currentUserStats.tier.progress_percent}%"
                        ></div>
                    </div>
                </div>
            {/if}
        </div>
    {/if}

    <!-- Top 3 Podium (Visual Gamification Standings) -->
    {#if podium.first || podium.second || podium.third}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end pt-4 pb-2">
            <!-- Rank 2: Silver (Podium step 2) -->
            {#if podium.second}
                <div class="order-2 md:order-1 rounded-2xl border border-slate-300 dark:border-slate-700 bg-gradient-to-b from-slate-100/80 to-card dark:from-slate-900/60 p-5 text-center space-y-3 relative overflow-hidden shadow-xs">
                    <div class="absolute top-3 left-3 text-2xl font-black text-slate-400 font-mono">#2</div>
                    <div class="size-16 rounded-full mx-auto bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-xl border-4 border-slate-300 dark:border-slate-700 shadow-md">
                        {getInitials(podium.second.name)}
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-600 dark:text-slate-400 flex items-center justify-center gap-1">
                            <span>🥈</span>
                            <span>Silver Champion</span>
                        </div>
                        <h3 class="text-base font-extrabold text-foreground truncate mt-0.5">
                            {podium.second.name}
                        </h3>
                        <p class="text-xs text-muted-foreground truncate">{podium.second.position} • {podium.second.department}</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-card/80 border border-border/80 flex items-center justify-around">
                        <div>
                            <div class="text-[10px] text-muted-foreground uppercase font-semibold">Skor</div>
                            <div class="text-sm font-black text-amber-500 font-mono">{podium.second.total_points} XP</div>
                        </div>
                        <div class="w-px h-6 bg-border"></div>
                        <div>
                            <div class="text-[10px] text-muted-foreground uppercase font-semibold">Kelas</div>
                            <div class="text-sm font-bold text-foreground font-mono">{podium.second.completed_courses_count}</div>
                        </div>
                    </div>
                </div>
            {:else}
                <div class="order-2 md:order-1 hidden md:block"></div>
            {/if}

            <!-- Rank 1: Gold (Center elevated podium) -->
            {#if podium.first}
                <div class="order-1 md:order-2 rounded-2xl border-2 border-amber-400 dark:border-amber-500/80 bg-gradient-to-b from-amber-100/90 via-amber-50/40 to-card dark:from-amber-950/40 dark:to-card p-6 text-center space-y-3.5 relative overflow-hidden shadow-lg md:-translate-y-2">
                    <div class="absolute -top-1 -right-1 bg-amber-500 text-white text-[10px] font-black uppercase tracking-wider py-1 px-3 rounded-bl-xl shadow-xs flex items-center gap-1">
                        <Crown class="size-3" />
                        <span>Juara 1</span>
                    </div>
                    <div class="absolute top-3 left-3 text-2xl font-black text-amber-500 font-mono">#1</div>
                    <div class="relative size-20 rounded-full mx-auto bg-amber-500 text-white flex items-center justify-center font-extrabold text-2xl border-4 border-amber-300 dark:border-amber-400 shadow-xl shadow-amber-500/20">
                        {getInitials(podium.first.name)}
                        <span class="absolute -top-3 text-xl">👑</span>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-amber-600 dark:text-amber-400 flex items-center justify-center gap-1">
                            <span>🥇</span>
                            <span>Gold Champion</span>
                        </div>
                        <h3 class="text-lg font-black text-foreground truncate mt-0.5">
                            {podium.first.name}
                        </h3>
                        <p class="text-xs text-muted-foreground truncate">{podium.first.position} • {podium.first.department}</p>
                        <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 mt-1 flex items-center justify-center gap-1">
                            <span>{podium.first.badge}</span>
                            <span>{podium.first.tier?.name}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-xl bg-card border border-amber-300/60 dark:border-amber-700/60 flex items-center justify-around shadow-xs">
                        <div>
                            <div class="text-[10px] text-muted-foreground uppercase font-bold">Total Skor</div>
                            <div class="text-base font-black text-amber-500 font-mono">{podium.first.total_points} XP</div>
                        </div>
                        <div class="w-px h-7 bg-border"></div>
                        <div>
                            <div class="text-[10px] text-muted-foreground uppercase font-bold">Kelas Selesai</div>
                            <div class="text-base font-bold text-emerald-600 font-mono">{podium.first.completed_courses_count}</div>
                        </div>
                    </div>
                </div>
            {/if}

            <!-- Rank 3: Bronze (Podium step 3) -->
            {#if podium.third}
                <div class="order-3 md:order-3 rounded-2xl border border-amber-700/30 bg-gradient-to-b from-amber-900/10 to-card p-5 text-center space-y-3 relative overflow-hidden shadow-xs">
                    <div class="absolute top-3 left-3 text-2xl font-black text-amber-700/60 font-mono">#3</div>
                    <div class="size-16 rounded-full mx-auto bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-200 flex items-center justify-center font-bold text-xl border-4 border-amber-700/30 shadow-md">
                        {getInitials(podium.third.name)}
                    </div>
                    <div>
                        <div class="text-xs font-bold text-amber-700 dark:text-amber-500 flex items-center justify-center gap-1">
                            <span>🥉</span>
                            <span>Bronze Champion</span>
                        </div>
                        <h3 class="text-base font-extrabold text-foreground truncate mt-0.5">
                            {podium.third.name}
                        </h3>
                        <p class="text-xs text-muted-foreground truncate">{podium.third.position} • {podium.third.department}</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-card/80 border border-border/80 flex items-center justify-around">
                        <div>
                            <div class="text-[10px] text-muted-foreground uppercase font-semibold">Skor</div>
                            <div class="text-sm font-black text-amber-500 font-mono">{podium.third.total_points} XP</div>
                        </div>
                        <div class="w-px h-6 bg-border"></div>
                        <div>
                            <div class="text-[10px] text-muted-foreground uppercase font-semibold">Kelas</div>
                            <div class="text-sm font-bold text-foreground font-mono">{podium.third.completed_courses_count}</div>
                        </div>
                    </div>
                </div>
            {:else}
                <div class="order-3 md:order-3 hidden md:block"></div>
            {/if}
        </div>
    {/if}

    <!-- Search & Filter Controls -->
    <div class="p-3 rounded-2xl border border-border bg-card shadow-xs flex flex-wrap gap-2.5 items-center justify-between">
        <div class="relative flex-1 min-w-[220px]">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
            <input
                type="text"
                placeholder="Cari nama karyawan, jabatan, atau divisi..."
                bind:value={searchTerm}
                class="w-full pl-9 pr-3 py-1.5 rounded-xl text-xs bg-muted/30 border border-border focus:outline-hidden focus:border-indigo-500"
            />
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-muted-foreground font-medium hidden sm:inline">Tingkat Level:</span>
            <select
                bind:value={selectedTierFilter}
                class="py-1.5 pl-3 pr-8 rounded-xl text-xs bg-card border border-border text-foreground"
            >
                <option value="all">Semua Level</option>
                <option value="5">👑 Level 5 - Mastermind</option>
                <option value="4">💎 Level 4 - Expert</option>
                <option value="3">🥇 Level 3 - Pro</option>
                <option value="2">🥈 Level 2 - Rising</option>
                <option value="1">🥉 Level 1 - Novice</option>
            </select>
        </div>
    </div>

    <!-- Standings Table -->
    <div class="rounded-2xl border border-border bg-card overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-border bg-muted/40 text-muted-foreground font-semibold">
                        <th class="py-3 px-4 w-14 text-center">Rank</th>
                        <th class="py-3 px-4">Karyawan</th>
                        <th class="py-3 px-4">Kantor / Cabang</th>
                        <th class="py-3 px-4">Divisi & Jabatan</th>
                        <th class="py-3 px-4">Level Gamifikasi</th>
                        <th class="py-3 px-4 text-center">Kelas Selesai</th>
                        <th class="py-3 px-4 text-right">Total XP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60">
                    {#if filteredLeaderboard.length === 0}
                        <tr>
                            <td colspan="7" class="py-12 text-center text-muted-foreground">
                                <Trophy class="size-8 mx-auto text-muted-foreground/40 mb-2" />
                                <p class="font-medium">Tidak ada data karyawan yang cocok dengan pencarian.</p>
                            </td>
                        </tr>
                    {:else}
                        {#each filteredLeaderboard as item (item.id)}
                            {@const isTop1 = item.rank === 1}
                            {@const isTop2 = item.rank === 2}
                            {@const isTop3 = item.rank === 3}
                            <tr class="transition-colors hover:bg-muted/40 {item.is_current_user ? 'bg-indigo-50/60 dark:bg-indigo-950/40 font-semibold' : ''}">
                                <!-- Rank -->
                                <td class="py-3.5 px-4 text-center font-bold">
                                    {#if isTop1}
                                        <span class="inline-flex items-center justify-center size-7 rounded-full bg-amber-500 text-white shadow-xs font-mono">1</span>
                                    {:else if isTop2}
                                        <span class="inline-flex items-center justify-center size-7 rounded-full bg-slate-300 dark:bg-slate-700 text-slate-800 dark:text-slate-100 shadow-xs font-mono">2</span>
                                    {:else if isTop3}
                                        <span class="inline-flex items-center justify-center size-7 rounded-full bg-amber-700/20 text-amber-800 dark:text-amber-200 border border-amber-700/30 font-mono">3</span>
                                    {:else}
                                        <span class="text-muted-foreground font-mono">#{item.rank}</span>
                                    {/if}
                                </td>

                                <!-- User Name & Avatar -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="size-9 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-300 font-bold flex items-center justify-center shrink-0 text-xs">
                                            {getInitials(item.name)}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-foreground truncate flex items-center gap-1.5">
                                                <span>{item.name}</span>
                                                {#if item.is_current_user}
                                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-indigo-600 text-white">Anda</span>
                                                {/if}
                                            </div>
                                            <div class="text-[11px] text-muted-foreground truncate">{item.email}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Office -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 text-foreground font-medium">
                                        <Building2 class="size-3.5 text-muted-foreground shrink-0" />
                                        <span class="truncate">{item.company_name}</span>
                                    </div>
                                </td>

                                <!-- Department & Position -->
                                <td class="py-3.5 px-4">
                                    <div class="text-foreground font-medium truncate">{item.position}</div>
                                    <div class="text-[11px] text-muted-foreground truncate">{item.department}</div>
                                </td>

                                <!-- Tier Badge -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-muted/80 text-foreground border border-border">
                                        <span>{item.badge}</span>
                                        <span>{item.tier?.name}</span>
                                    </span>
                                </td>

                                <!-- Completed Courses -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-mono font-bold text-foreground">
                                        {item.completed_courses_count}
                                    </span>
                                </td>

                                <!-- Points -->
                                <td class="py-3.5 px-4 text-right">
                                    <span class="font-mono font-extrabold text-amber-500 text-sm">
                                        {item.total_points} XP
                                    </span>
                                </td>
                            </tr>
                        {/each}
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>
