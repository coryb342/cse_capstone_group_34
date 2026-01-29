<script setup lang="ts">
import { computed } from 'vue'
import { Scatter } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    PointElement,
    LinearScale,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, PointElement, LinearScale)

type Point = { x: number; y: number; run_id?: number; created_at?: string }

const props = defineProps<{
    points: Point[]
    title?: string
}>()

const chartData = computed(() => ({
    datasets: [
        {
            label: 'Residuals (Actual − Predicted)',
            data: props.points,
        },
    ],
}))

function nicePad(maxAbs: number) {
    if (maxAbs === 0) return 1
    return maxAbs * 1.1
}

const yBounds = computed(() => {
    const ys = props.points.map(p => p.y).filter(v => Number.isFinite(v))
    const maxAbs = ys.length ? Math.max(...ys.map(v => Math.abs(v))) : 1
    const pad = nicePad(maxAbs)
    return { min: -pad, max: pad }
})

const options = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        title: {
            display: true,
            text: props.title ?? 'Residuals vs Predicted',
        },
        legend: { display: true },
        tooltip: {
            callbacks: {
                label: (ctx: any) => {
                    const x = ctx.raw?.x
                    const y = ctx.raw?.y
                    const runId = ctx.raw?.run_id
                    const ts = ctx.raw?.created_at
                    const parts = [
                        `Predicted: ${Number(x).toFixed(3)}`,
                        `Residual (A−P): ${Number(y).toFixed(3)}`,
                    ]
                    if (runId != null) parts.push(`Run #${runId}`)
                    if (ts) parts.push(ts)
                    return parts.join(' | ')
                },
            },
        },
    },
    scales: {
        x: {
            type: 'linear',
            title: { display: true, text: 'Predicted value' },
        },
        y: {
            type: 'linear',
            title: { display: true, text: 'Residual (Actual − Predicted)' },
            min: yBounds.value.min,
            max: yBounds.value.max,
        },
    },
}))
</script>

<template>
    <div class="h-80 w-full">
        <Scatter :data="chartData" :options="options" />
    </div>

    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
        Positive residuals = under-predicted. Negative residuals = over-predicted.
    </div>
</template>
