<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    points: Array<any>;
    labelKey: string;
    valueKey: string;
    width?: number;
    height?: number;
    title?: string;
    yLabel?: string;
}>();

const width = props.width ?? 700;
const height = props.height ?? 260;
const padding = 50;

const hasPoints = computed(() => props.points && props.points.length > 0);

const maxValue = computed(() => {
    if (!hasPoints.value) return 100;
    return Math.max(
        ...props.points.map((p) => Number(p[props.valueKey]) || 0),
        0,
    );
});

const yTicks = computed(() => {
    const count = 5;
    const top = maxValue.value || 1;

    return Array.from({ length: count + 1 }, (_, i) => {
        const value = (top * i) / count;
        const y = height - padding - ((height - padding * 2) * i) / count;

        return { value, y };
    });
});

const barWidth = computed(() => {
    if (!hasPoints.value) return 40;
    const usableWidth = width - padding * 2;
    return Math.max((usableWidth / props.points.length) * 0.6, 20);
});

function getBarX(index: number) {
    const usableWidth = width - padding * 2;
    const slotWidth = usableWidth / props.points.length;
    return padding + index * slotWidth + (slotWidth - barWidth.value) / 2;
}

function getBarHeight(value: number) {
    const top = maxValue.value || 1;
    return (Number(value) / top) * (height - padding * 2);
}

function getBarY(value: number) {
    return height - padding - getBarHeight(Number(value));
}

function formatTick(value: number) {
    return Number(value).toFixed(0);
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
            class="flex h-[260px] items-center justify-center text-sm text-slate-500 dark:text-slate-400"
        >
            No data to display
        </div>

        <svg v-else :viewBox="`0 0 ${width} ${height}`" class="h-auto w-full">
            <!-- Grid lines -->
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
                :x1="padding"
                :y1="padding"
                :x2="padding"
                :y2="height - padding"
                class="stroke-slate-500 dark:stroke-slate-300"
                stroke-width="2"
            />
            <line
                :x1="padding"
                :y1="height - padding"
                :x2="width - padding"
                :y2="height - padding"
                class="stroke-slate-500 dark:stroke-slate-300"
                stroke-width="2"
            />

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

            <!-- Bars -->
            <g v-for="(p, i) in points" :key="i">
                <rect
                    :x="getBarX(i)"
                    :y="getBarY(p[valueKey])"
                    :width="barWidth"
                    :height="getBarHeight(p[valueKey])"
                    rx="4"
                    class="fill-sky-600 dark:fill-sky-400"
                />

                <!-- value above bar -->
                <text
                    :x="getBarX(i) + barWidth / 2"
                    :y="getBarY(p[valueKey]) - 6"
                    text-anchor="middle"
                    class="fill-slate-700 text-[11px] dark:fill-slate-200"
                >
                    {{ Number(p[valueKey]).toFixed(1) }}
                </text>

                <!-- label below bar -->
                <text
                    :x="getBarX(i) + barWidth / 2"
                    :y="height - padding + 18"
                    text-anchor="middle"
                    class="fill-slate-600 text-[11px] dark:fill-slate-300"
                >
                    {{ p[labelKey] }}
                </text>
            </g>

            <!-- Y axis label -->
            <text
                v-if="yLabel"
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
