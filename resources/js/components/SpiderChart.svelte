<script lang="ts">
    interface CompetencyItem {
        subject: string;
        score: number;
        fullMark?: number;
    }

    interface Props {
        competencies?: CompetencyItem[];
        size?: number;
        showLegend?: boolean;
    }

    let {
        competencies = [],
        size = 380,
        showLegend = false,
    }: Props = $props();

    // Responsive coordinate system centered at (0, 0)
    // viewBox: -230 -185 460 370 gives generous margins on all 4 sides for long text labels
    const radius = 110;
    const levels = [0.2, 0.4, 0.6, 0.8, 1.0];

    // Compute polygon coordinates for a given scale (0..1)
    function getPolygonPoints(scale: number): string {
        const count = competencies.length;
        if (count === 0) return '';
        const points: string[] = [];
        for (let i = 0; i < count; i++) {
            const angle = (Math.PI * 2 / count) * i - Math.PI / 2;
            const x = radius * scale * Math.cos(angle);
            const y = radius * scale * Math.sin(angle);
            points.push(`${x.toFixed(1)},${y.toFixed(1)}`);
        }
        return points.join(' ');
    }

    // Plotted employee competency data polygon
    const dataPoints = $derived.by(() => {
        const count = competencies.length;
        if (count === 0) return '';
        const points: string[] = [];
        for (let i = 0; i < count; i++) {
            const item = competencies[i];
            const max = item.fullMark || 100;
            const ratio = Math.min(Math.max(item.score / max, 0.08), 1);
            const angle = (Math.PI * 2 / count) * i - Math.PI / 2;
            const x = radius * ratio * Math.cos(angle);
            const y = radius * ratio * Math.sin(angle);
            points.push(`${x.toFixed(1)},${y.toFixed(1)}`);
        }
        return points.join(' ');
    });

    // Vertex points for dots, hover targets, and outer labels
    const vertexCoordinates = $derived.by(() => {
        const count = competencies.length;
        return competencies.map((item, i) => {
            const max = item.fullMark || 100;
            const ratio = Math.min(Math.max(item.score / max, 0.08), 1);
            const angle = (Math.PI * 2 / count) * i - Math.PI / 2;
            const x = radius * ratio * Math.cos(angle);
            const y = radius * ratio * Math.sin(angle);

            // Outer label placement with clearance
            const labelDist = radius + 26;
            const lx = labelDist * Math.cos(angle);
            const ly = labelDist * Math.sin(angle);

            // Determine text anchor based on angle
            const cos = Math.cos(angle);
            const sin = Math.sin(angle);
            let textAnchor: 'start' | 'middle' | 'end' = 'middle';
            let dy = 4;

            if (cos > 0.3) {
                textAnchor = 'start';
            } else if (cos < -0.3) {
                textAnchor = 'end';
            } else {
                textAnchor = 'middle';
                dy = sin < 0 ? -6 : 14;
            }

            return {
                ...item,
                x,
                y,
                lx,
                ly,
                textAnchor,
                dy,
                angle,
            };
        });
    });

    let hoveredIndex = $state<number | null>(null);

    const averageScore = $derived.by(() => {
        if (!competencies.length) return 0;
        const total = competencies.reduce((acc, curr) => acc + curr.score, 0);
        return Math.round(total / competencies.length);
    });

    const activeItem = $derived.by(() => {
        if (hoveredIndex !== null && competencies[hoveredIndex]) {
            return competencies[hoveredIndex];
        }
        return null;
    });

    function getLevelBadge(score: number): { text: string; color: string } {
        if (score >= 88) return { text: 'Sangat Unggul', color: 'text-emerald-600 dark:text-emerald-400' };
        if (score >= 78) return { text: 'Kompeten', color: 'text-indigo-600 dark:text-indigo-400' };
        if (score >= 70) return { text: 'Standar Baik', color: 'text-sky-600 dark:text-sky-400' };
        return { text: 'Perlu Asah', color: 'text-amber-600 dark:text-amber-400' };
    }
</script>

<div class="relative flex flex-col items-center justify-center w-full select-none">
    <svg
        viewBox="-230 -185 460 370"
        class="w-full max-w-[460px] h-auto overflow-visible"
    >
        <defs>
            <!-- Data polygon gradient -->
            <linearGradient id="spiderGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.65" />
                <stop offset="50%" stop-color="#7c3aed" stop-opacity="0.45" />
                <stop offset="100%" stop-color="#0284c7" stop-opacity="0.30" />
            </linearGradient>

            <!-- Glow drop-shadow filter -->
            <filter id="spiderGlow" x="-30%" y="-30%" width="160%" height="160%">
                <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="#4f46e5" flood-opacity="0.35" />
            </filter>

            <!-- Active point pulse filter -->
            <filter id="pointHalo" x="-50%" y="-50%" width="200%" height="200%">
                <feDropShadow dx="0" dy="0" stdDeviation="4" flood-color="#6366f1" flood-opacity="0.8" />
            </filter>
        </defs>

        <!-- Concentric polygon web grid -->
        {#each levels as lvl, idx}
            <polygon
                points={getPolygonPoints(lvl)}
                fill={idx % 2 === 0 ? "rgba(99, 102, 241, 0.035)" : "none"}
                stroke="currentColor"
                stroke-dasharray={lvl < 1 ? "3,3" : "none"}
                class="text-border/70 transition-colors"
                stroke-width={lvl === 1 ? "1.5" : "0.75"}
            />
            <!-- Web level indicator (20, 40, 60, 80, 100) along vertical axis -->
            <text
                x="0"
                y={-radius * lvl - 2}
                text-anchor="middle"
                class="text-[9px] font-mono fill-muted-foreground/60"
            >
                {Math.round(lvl * 100)}
            </text>
        {/each}

        <!-- Radial axis lines from center -->
        {#each competencies as _, i}
            {@const angle = (Math.PI * 2 / competencies.length) * i - Math.PI / 2}
            {@const x2 = radius * Math.cos(angle)}
            {@const y2 = radius * Math.sin(angle)}
            <line
                x1="0"
                y1="0"
                x2={x2}
                y2={y2}
                stroke="currentColor"
                class="text-border/70"
                stroke-width="1"
            />
        {/each}

        <!-- Plotted Data Polygon -->
        {#if dataPoints}
            <polygon
                points={dataPoints}
                fill="url(#spiderGradient)"
                stroke="#4f46e5"
                stroke-width="2.5"
                filter="url(#spiderGlow)"
                class="transition-all duration-300 ease-out"
            />
        {/if}

        <!-- Vertex Dots & Interactive Target Circles -->
        {#each vertexCoordinates as vertex, i}
            {@const isHovered = hoveredIndex === i}
            <g
                role="group"
                aria-label="{vertex.subject}: {vertex.score}"
                class="cursor-pointer group"
                onmouseenter={() => (hoveredIndex = i)}
                onmouseleave={() => (hoveredIndex = null)}
            >
                <!-- Invisible wider hit area for easy hover -->
                <circle
                    cx={vertex.x}
                    cy={vertex.y}
                    r="14"
                    fill="transparent"
                />

                <!-- Point halo on hover -->
                {#if isHovered}
                    <circle
                        cx={vertex.x}
                        cy={vertex.y}
                        r="11"
                        class="fill-indigo-500/25 animate-ping"
                    />
                    <circle
                        cx={vertex.x}
                        cy={vertex.y}
                        r="9"
                        class="fill-indigo-500/30"
                    />
                {/if}

                <!-- Inner and Outer Circle -->
                <circle
                    cx={vertex.x}
                    cy={vertex.y}
                    r={isHovered ? 6.5 : 4.5}
                    class="transition-all duration-200 fill-card stroke-indigo-600 dark:stroke-indigo-400 {isHovered ? 'stroke-[2.5px]' : 'stroke-2'}"
                />
            </g>
        {/each}

        <!-- Labels for competencies positioned around outer edge -->
        {#each vertexCoordinates as vertex, i}
            {@const isHovered = hoveredIndex === i}
            <g
                role="group"
                aria-label="{vertex.subject}: {vertex.score}"
                class="cursor-pointer transition-transform duration-200"
                onmouseenter={() => (hoveredIndex = i)}
                onmouseleave={() => (hoveredIndex = null)}
            >
                <text
                    x={vertex.lx}
                    y={vertex.ly + vertex.dy - 6}
                    text-anchor={vertex.textAnchor}
                    class="text-[11px] transition-all font-semibold {isHovered ? 'fill-indigo-600 dark:fill-indigo-400 font-bold scale-105' : 'fill-foreground/90'}"
                >
                    {vertex.subject}
                </text>
                <text
                    x={vertex.lx}
                    y={vertex.ly + vertex.dy + 8}
                    text-anchor={vertex.textAnchor}
                    class="text-[10px] font-mono {isHovered ? 'fill-indigo-600 font-bold' : 'fill-muted-foreground'}"
                >
                    {vertex.score} <tspan class="text-[8px] fill-muted-foreground/60">/ 100</tspan>
                </text>
            </g>
        {/each}
    </svg>

    <!-- Center Live Insight Badge (Shows either hovered competency or overall average) -->
    <div class="mt-2 text-center p-2 rounded-xl bg-muted/40 border border-border/60 w-full max-w-xs transition-all">
        {#if activeItem}
            <div class="flex items-center justify-between text-xs px-1">
                <span class="font-bold text-foreground truncate">{activeItem.subject}</span>
                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">{activeItem.score}</span>
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md bg-card border border-border {getLevelBadge(activeItem.score).color}">
                        {getLevelBadge(activeItem.score).text}
                    </span>
                </div>
            </div>
        {:else}
            <div class="flex items-center justify-between text-xs px-1 text-muted-foreground">
                <span class="text-[11px]">Rata-rata Kompetensi:</span>
                <div class="flex items-center gap-1.5 font-semibold text-foreground">
                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">{averageScore}</span>
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-md bg-card border border-border">
                        {getLevelBadge(averageScore).text}
                    </span>
                </div>
            </div>
        {/if}
    </div>

    {#if showLegend}
        <!-- Optional Bottom Legend -->
        <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5 text-xs">
            {#each competencies as comp, i}
                <button
                    type="button"
                    onmouseenter={() => (hoveredIndex = i)}
                    onmouseleave={() => (hoveredIndex = null)}
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition-all {hoveredIndex === i ? 'bg-indigo-600 text-white shadow-xs' : 'bg-muted/60 text-foreground/80 hover:bg-muted'}"
                >
                    <span class="size-1.5 rounded-full {comp.score >= 85 ? 'bg-emerald-500' : comp.score >= 75 ? 'bg-indigo-500' : 'bg-amber-500'}"></span>
                    <span>{comp.subject}</span>
                    <span class="font-mono font-bold text-[11px]">{comp.score}</span>
                </button>
            {/each}
        </div>
    {/if}
</div>
