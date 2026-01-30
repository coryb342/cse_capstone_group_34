<script setup lang="ts">
import { computed } from 'vue'
import { Scatter } from 'vue-chartjs'
import {
    Chart as ChartJS,
    LinearScale,
    PointElement,
    Tooltip,
    Legend,
    Title,
} from 'chart.js'

ChartJS.register(LinearScale, PointElement, Tooltip, Legend, Title)


const props = defineProps<{
    points: Array<{ predicted: number; actual: number; run_id?: number; created_at?: string }>
}>()

// Check if dark mode is active
const isDarkMode = computed(() => {
    return document.documentElement.classList.contains('dark')
})

// Dynamic colors based on mode
const colors = computed(() => {
    const isDark = isDarkMode.value
    return {
        chartBg: isDark ? '#0f172a' : '#f8fafc',
        grid: isDark ? '#334155' : '#cbd5e1',
        border: isDark ? '#475569' : '#94a3b8',
        perfectLine: isDark ? '#a78bfa' : '#8b5cf6',
        text: isDark ? '#cbd5e1' : '#475569',
        titleText: isDark ? '#e2e8f0' : '#334155',
        tooltipBg: isDark ? '#1e293b' : '#ffffff',
        tooltipText: isDark ? '#f1f5f9' : '#0f172a',
        tooltipBorder: isDark ? '#475569' : '#cbd5e1',
        legendText: isDark ? '#cbd5e1' : '#475569',
    }
})

const perfectPredictionLine = {
    id: 'perfectPredictionLine',
    afterDraw(chart: any) {
        const { ctx, scales } = chart
        const xScale = scales.x
        const yScale = scales.y
        if (!xScale || !yScale) return

        const minVal = Math.max(xScale.min, yScale.min)
        const maxVal = Math.min(xScale.max, yScale.max)

        const x1 = xScale.getPixelForValue(minVal)
        const y1 = yScale.getPixelForValue(minVal)
        const x2 = xScale.getPixelForValue(maxVal)
        const y2 = yScale.getPixelForValue(maxVal)

        ctx.save()
        ctx.beginPath()
        ctx.moveTo(x1, y1)
        ctx.lineTo(x2, y2)
        ctx.lineWidth = 2
        ctx.strokeStyle = colors.value.perfectLine
        ctx.setLineDash([8, 4])
        ctx.stroke()
        ctx.restore()
    },
}

const chartAreaBackground = {
    id: 'chartAreaBackground',
    beforeDraw(chart: any) {
        const { ctx, chartArea } = chart
        if (!chartArea) return

        ctx.save()
        ctx.fillStyle = colors.value.chartBg
        ctx.fillRect(
            chartArea.left,
            chartArea.top,
            chartArea.right - chartArea.left,
            chartArea.bottom - chartArea.top
        )
        ctx.restore()
    },
}

// Transform data to x,y format for scatter plot
const scatterPoints = computed(() => {
    return (props.points ?? []).map(p => ({
        x: p.predicted,
        y: p.actual,
        run_id: p.run_id,
        created_at: p.created_at
    }))
})

// Calculate how far points are from perfect line
const pointsWithError = computed(() => {
    return scatterPoints.value.map(p => ({
        ...p,
        error: Math.abs(p.y - p.x),
        errorPercent: p.x !== 0 ? Math.abs((p.y - p.x) / p.x) * 100 : 0
    }))
})

// Color points based on accuracy
const goodPoints = computed(() => pointsWithError.value.filter(p => p.errorPercent < 5))
const okPoints = computed(() => pointsWithError.value.filter(p => p.errorPercent >= 5 && p.errorPercent < 15))
const poorPoints = computed(() => pointsWithError.value.filter(p => p.errorPercent >= 15))

const chartData = computed(() => ({
    datasets: [
        {
            label: 'Excellent (<5% error)',
            data: goodPoints.value,
            pointRadius: 5,
            backgroundColor: 'rgba(34,197,94,0.7)',
            borderColor: 'rgba(34,197,94,1)',
            borderWidth: 1.5,
        },
        {
            label: 'Good (5-15% error)',
            data: okPoints.value,
            pointRadius: 5,
            backgroundColor: 'rgba(251,146,60,0.7)',
            borderColor: 'rgba(251,146,60,1)',
            borderWidth: 1.5,
        },
        {
            label: 'Poor (>15% error)',
            data: poorPoints.value,
            pointRadius: 5,
            backgroundColor: 'rgba(239,68,68,0.7)',
            borderColor: 'rgba(239,68,68,1)',
            borderWidth: 1.5,
        },
    ],
}))

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            labels: {
                color: colors.value.legendText,
                font: {
                    size: 12,
                    weight: '500'
                },
                padding: 10,
                usePointStyle: true,
            }
        },
        title: {
            display: true,
            text: 'Predicted vs Actual',
            color: colors.value.titleText,
            font: {
                size: 16,
                weight: 'bold'
            },
            padding: {
                top: 10,
                bottom: 20
            }
        },
        tooltip: {
            backgroundColor: colors.value.tooltipBg,
            titleColor: colors.value.tooltipText,
            bodyColor: colors.value.tooltipText,
            borderColor: colors.value.tooltipBorder,
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
                label: (ctx: any) => {
                    const p = ctx.raw as any
                    const error = Math.abs(p.y - p.x).toFixed(2)
                    const errorPercent = p.x !== 0 ? Math.abs((p.y - p.x) / p.x * 100).toFixed(1) : '0'
                    const extra = p.created_at ? ` • ${p.created_at}` : ''
                    return [
                        `Predicted: ${p.x.toFixed(2)}`,
                        `Actual: ${(((p.x - p.y) + p.x).toFixed(2))}`,
                        `Error: ${error} (${errorPercent}%)${extra}`
                    ]
                },
            },
        },
    },
    scales: {
        x: {
            type: 'linear' as const,
            title: {
                display: true,
                text: 'Predicted Values',
                color: colors.value.text,
                font: {
                    size: 13,
                    weight: 'bold'
                }
            },
            border: {
                color: colors.value.border
            },
            ticks: {
                color: colors.value.text,
                font: {
                    size: 11
                }
            },
            grid: {
                color: colors.value.grid,
                drawBorder: false
            },
            min: 20,
            max: 50,
        },
        y: {
            type: 'linear' as const,
            title: {
                display: true,
                text: 'Actual Values',
                color: colors.value.text,
                font: {
                    size: 13,
                    weight: 'bold'
                }
            },
            border: {
                color: colors.value.border
            },
            ticks: {
                color: colors.value.text,
                font: {
                    size: 11
                }
            },
            grid: {
                color: colors.value.grid,
                drawBorder: false
            },
            min: 20,
            max: 60,
        },
    },
}))
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <div class="h-120 w-300">
            <Scatter :data="chartData" :options="chartOptions" :plugins="[perfectPredictionLine, chartAreaBackground]"/>
        </div>

        <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-900 rounded-lg">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                <strong>Perfect model:</strong> All points on the diagonal line (predicted = actual)
                <br>
                <strong>Good model:</strong> Points clustered tightly around the line
                <br>
                <strong>Poor model:</strong> Points scattered far from the line
            </p>
        </div>

        <p v-if="!points?.length" class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            No predictions to plot yet (need runs with both predicted + actual values).
        </p>
    </div>
</template>
