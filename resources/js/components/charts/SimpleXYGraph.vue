<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    points: Array<any>;
    xKey: string;
    yKey: string;
    width?: number;
    height?: number;
    title?: string;
    xLabel?: string;
    yLabel?: string;
}>();

const width = props.width ?? 600;
const height = props.height ?? 260;
const padding = 50;

const hasPoints = computed(() => props.points && props.points.length > 0);

const xMin = computed(() =>
    hasPoints.value
        ? Math.min(...props.points.map((p) => Number(p[props.xKey])))
        : 0,
);

const xMax = computed(() =>
    hasPoints.value
        ? Math.max(...props.points.map((p) => Number(p[props.xKey])))
        : 1,
);

const yMin = computed(() =>
    hasPoints.value
        ? Math.min(...props.points.map((p) => Number(p[props.yKey])))
        : 0,
);

const yMax = computed(() =>
    hasPoints.value
        ? Math.max(...props.points.map((p) => Number(p[props.yKey])))
        : 1,
);

function scaleX(x: number) {
    return (
        padding +
        ((x - xMin.value) / (xMax.value - xMin.value || 1)) *
            (width - padding * 2)
    );
}

function scaleY(y: number) {
    return (
        height -
        padding -
        ((y - yMin.value) / (yMax.value - yMin.value || 1)) *
            (height - padding * 2)
    );
}

const yAxisX = padding;
const xAxisY = height - padding;

const xTicks = computed(() => {
    const count = 5;
    const range = xMax.value - xMin.value || 1;

    return Array.from({ length: count + 1 }, (_, i) => {
        const value = xMin.value + (range * i) / count;
        return {
            value,
            x: padding + ((width - padding * 2) * i) / count,
        };
    });
});

const yTicks = computed(() => {
    const count = 5;
    const range = yMax.value - yMin.value || 1;

    return Array.from({ length: count + 1 }, (_, i) => {
        const value = yMin.value + (range * i) / count;
        return {
            value,
            y: height - padding - ((height - padding * 2) * i) / count,
        };
    });
});

function formatTick(value: number) {
    return Number(value).toFixed(1);
}
</script>

<template>
    <div
        class="w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"
    >
        <div
            v-if="title"
            class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200"
        >
            {{ title }}
        </div>
        <div
            v-if="!hasPoints"
            class="flex h-[350px] items-center justify-center text-sm text-slate-500 dark:text-slate-400"
        >
            No data to display
        </div>

        <svg v-else :viewBox="`0 0 ${width} ${height}`" class="h-auto w-full">
            <!-- Grid lines -->
            <line
                v-for="(tick, i) in xTicks"
                :key="`x-grid-${i}`"
                :x1="tick.x"
                :y1="padding"
                :x2="tick.x"
                :y2="height - padding"
                class="stroke-slate-200 dark:stroke-slate-700"
                stroke-width="1"
            />
            <line
                v-for="(tick, i) in yTicks"
                :key="`y-grid-${i}`"
                :x1="padding"
                :y1="tick.y"
                :x2="width - padding"
                :y2="tick.y"
                class="stroke-slate-200 dark:stroke-slate-700"
                stroke-width="1"
            />

            <!-- Axes -->
            <line
                :x1="yAxisX"
                :y1="padding"
                :x2="yAxisX"
                :y2="height - padding"
                class="stroke-slate-500 dark:stroke-slate-300"
                stroke-width="2"
            />
            <line
                :x1="padding"
                :y1="xAxisY"
                :x2="width - padding"
                :y2="xAxisY"
                class="stroke-slate-500 dark:stroke-slate-300"
                stroke-width="2"
            />

            <!-- X tick labels -->
            <text
                v-for="(tick, i) in xTicks"
                :key="`x-label-${i}`"
                :x="tick.x"
                :y="height - padding + 20"
                text-anchor="middle"
                class="fill-slate-600 text-[11px] dark:fill-slate-300"
            >
                {{ formatTick(tick.value) }}
            </text>

            <!-- Y tick labels -->
            <text
                v-for="(tick, i) in yTicks"
                :key="`y-label-${i}`"
                :x="padding - 10"
                :y="tick.y + 4"
                text-anchor="end"
                class="fill-slate-600 text-[11px] dark:fill-slate-300"
            >
                {{ formatTick(tick.value) }}
            </text>

            <!-- Points -->
            <circle
                v-for="(p, i) in points"
                :key="i"
                :cx="scaleX(Number(p[xKey]))"
                :cy="scaleY(Number(p[yKey]))"
                r="4"
                class="fill-sky-600 dark:fill-sky-400"
            />
            <text
                :x="width / 2"
                :y="height - 8"
                text-anchor="middle"
                class="fill-slate-700 text-[12px] dark:fill-slate-200"
            >
                {{ xLabel }}
            </text>
            <text
                :x="16"
                :y="height / 2"
                text-anchor="middle"
                :transform="`rotate(-90 16 ${height / 2})`"
                class="fill-slate-700 text-[12px] dark:fill-slate-200"
            >
                {{ yLabel }}
            </text>
        </svg>
    </div>
</template>
