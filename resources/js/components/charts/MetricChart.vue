<script setup lang="ts">
import { computed } from "vue"
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    PointElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    TimeScale,
} from "chart.js"

import { Line, Bar, Doughnut } from "vue-chartjs"

// Register what you need (tree-shakable)
ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    PointElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    TimeScale
)

const props = defineProps({
    type: { type: String, default: "line" }, // "line" | "bar" | "doughnut"
    labels: { type: Array, default: () => [] },
    datasets: { type: Array, default: () => [] }, // [{ label, data, ... }]
    options: { type: Object, default: () => ({}) },
    height: { type: Number, default: 220 },
})

const chartData = computed(() => ({
    labels: props.labels,
    datasets: props.datasets,
}))

const mergedOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: true },
        tooltip: { enabled: true },
    },
    ...props.options,
}))

const ChartComponent = computed(() => {
    if (props.type === "bar") return Bar
    if (props.type === "doughnut") return Doughnut
    return Line
})
</script>

<template>
    <div :style="{ height: `${height}px` }" class="w-full">
        <component :is="ChartComponent" :data="chartData" :options="mergedOptions" />
    </div>
</template>
