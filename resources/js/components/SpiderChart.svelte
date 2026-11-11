<script lang="ts">
    interface CompetencyItem {
        subject: string;
        score: number;
        fullMark?: number;
    }

    let {
        competencies = [],
        size = 360,
    }: {
        competencies: CompetencyItem[];
        size?: number;
    } = $props();

    const center = size / 2;
    const radius = (size / 2) - 50; // leave room for text labels
    const levels = [0.2, 0.4, 0.6, 0.8, 1.0];

    // Compute polygon coordinates for given ratio (0..1)
    function getPolygonPoints(scale: number): string {
        const count = competencies.length;
        if (count === 0) return '';
        const points: string[] = [];
        for (let i = 0; i < count; i++) {
            const angle = (Math.PI * 2 / count) * i - Math.PI / 2;
            const x = center + radius * scale * Math.cos(angle);
            const y = center + radius * scale * Math.sin(angle);
            points.push(`${x.toFixed(1)},${y.toFixed(1)}`);
        }
        return points.join(' ');
    }

    // Compute data polygon points
    const dataPoints = $derived.by(() => {
        const count = competencies.length;
        if (count === 0) return '';
        const points: string[] = [];
        for (let i = 0; i < count; i++) {
            const item = competencies[i];
            const max = item.fullMark || 100;
            const ratio = Math.min(Math.max(item.score / max, 0.05), 1);
            const angle = (Math.PI * 2 / count) * i - Math.PI / 2;
            const x = center + radius * ratio * Math.cos(angle);
            const y = center + radius * ratio * Math.sin(angle);
            points.push(`${x.toFixed(1)},${y.toFixed(1)}`);
        }
        return points.join(' ');
    });

    // Vertex points for dots and tooltips
    const vertexCoordinates = $derived.by(() => {
        const count = competencies.length;
        return competencies.map((item, i) => {
            const max = item.fullMark || 100;
            const ratio = Math.min(Math.max(item.score / max, 0.05), 1);
            const angle = (Math.PI * 2 / count) * i - Math.PI / 2;
            const x = center + radius * ratio * Math.cos(angle);
            const y = center + radius * ratio * Math.sin(angle);

            // Outer label position
            const labelRadius = radius + 28;
            const lx = center + labelRadius * Math.cos(angle);
            const ly = center + labelRadius * Math.sin(angle);

            return {
                ...item,
                x,
                y,
                lx,
                ly,
                angle,
            };
        });
    });

    let hoveredIndex = $state<number | null>(null);
</script>

<div class="relative flex flex-col items-center justify-center w-full">
    <svg
        viewBox="0 0 {size} {size}"
        class="w-full max-w-[420px] aspect-square overflow-visible select-none"
    >
        <defs>
            <linearGradient id="spiderGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#6366f1" stop-opacity="0.6" />
                <stop offset="50%" stop-color="#8b5cf6" stop-opacity="0.45" />
                <stop offset="100%" stop-color="#06b6d4" stop-opacity="0.3" />
            </linearGradient>
            <filter id="spiderGlow" x="-20%" y="-20%" width="140%" height="140%">
                <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="#6366f1" flood-opacity="0.3" />
            </filter>
        </defs>

        <!-- Concentric polygon web grid -->
        {#each levels as lvl, idx}
            <polygon
                points={getPolygonPoints(lvl)}
                fill={idx % 2 === 0 ? "rgba(99, 102, 241, 0.03)" : "none"}
                stroke="currentColor"
                stroke-dasharray={lvl < 1 ? "3,3" : "none"}
                class="text-border/60 transition-colors"
                stroke-width={lvl === 1 ? "1.5" : "1"}
            />
            <!-- Web level indicator (20, 40, 60, 80, 100) along top axis -->
            <text
                x={center}
                y={center - radius * lvl - 2}
                text-anchor="middle"
                class="text-[9px] font-mono fill-muted-foreground/60 select-none"
            >
                {Math.round(lvl * 100)}
            </text>
        {/each}

        <!-- Radial axis lines from center -->
        {#each competencies as _, i}
            {@const angle = (Math.PI * 2 / competencies.length) * i - Math.PI / 2}
            {@const x2 = center + radius * Math.cos(angle)}
            {@const y2 = center + radius * Math.sin(angle)}
            <line
                x1={center}
                y1={center}
                x2={x2}
                y2={y2}
                stroke="currentColor"
                class="text-border/60"
                stroke-width="1"
            />
        {/each}

        <!-- Plotted Data Polygon -->
        {#if dataPoints}
            <polygon
                points={dataPoints}
                fill="url(#spiderGradient)"
                stroke="#6366f1"
                stroke-width="2.5"
                filter="url(#spiderGlow)"
                class="transition-all duration-500 ease-out"
            />
        {/if}

        <!-- Vertex Dots & Interactive Points -->
        {#each vertexCoordinates as vertex, i}
            <g
                class="cursor-pointer group"
                onmouseenter={() => (hoveredIndex = i)}
                onmouseleave={() => (hoveredIndex = null)}
            >
                <circle
                    cx={vertex.x}
                    cy={vertex.y}
                    r={hoveredIndex === i ? 6 : 4.5}
                    class="transition-all duration-200 fill-white dark:fill-slate-900 stroke-indigo-600 dark:stroke-indigo-400 stroke-2"
                />
                {#if hoveredIndex === i}
                    <circle
                        cx={vertex.x}
                        cy={vertex.y}
                        r="10"
                        class="fill-indigo-500/20 animate-ping"
                    />
                {/if}
            </g>
        {/each}

        <!-- Labels for competencies -->
        {#each vertexCoordinates as vertex, i}
            {@const isLeft = vertex.lx < center - 10}
            {@const isRight = vertex.lx > center + 10}
            {@const textAnchor = isRight ? "start" : isLeft ? "end" : "middle"}
            <g
                class="cursor-pointer"
                onmouseenter={() => (hoveredIndex = i)}
                onmouseleave={() => (hoveredIndex = null)}
            >
                <text
                    x={vertex.lx}
                    y={vertex.ly - 4}
                    text-anchor={textAnchor}
                    class="text-[11px] font-semibold transition-colors {hoveredIndex === i ? 'fill-indigo-600 dark:fill-indigo-400 font-bold' : 'fill-foreground/90'}"
                >
                    {vertex.subject}
                </text>
                <text
                    x={vertex.lx}
                    y={vertex.ly + 10}
                    text-anchor={textAnchor}
                    class="text-[10px] font-mono {hoveredIndex === i ? 'fill-indigo-500 font-bold' : 'fill-muted-foreground'}"
                >
                    {vertex.score}/100
                </text>
            </g>
        {/each}
    </svg>

    <!-- Bottom Legend / Indicator Pill -->
    <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs">
        {#each competencies as comp, i}
            <button
                type="button"
                onmouseenter={() => (hoveredIndex = i)}
                onmouseleave={() => (hoveredIndex = null)}
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-all {hoveredIndex === i ? 'bg-indigo-600 text-white shadow-sm scale-105' : 'bg-muted/70 text-foreground/80 hover:bg-muted'}"
            >
                <span class="size-2 rounded-full {comp.score >= 85 ? 'bg-emerald-500' : comp.score >= 70 ? 'bg-indigo-500' : 'bg-amber-500'}"></span>
                <span>{comp.subject}</span>
                <span class="font-mono font-bold ml-0.5">{comp.score}</span>
            </button>
        {/each}
    </div>
</div>
