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

type Point = { x: number; y: number; run_id?: number; created_at?: string }

const props = defineProps<{
    points: Array<{ x: number; y: number; run_id?: number; created_at?: string }>
}>()

// Check if dark mode is active
const isDarkMode = computed(() => {
    return document.documentElement.classList.contains('dark')
})

// Dynamic colors based on mode
const colors = computed(() => {
    const isDark = isDarkMode.value
    return {
        // Chart area background
        chartBg: isDark ? '#0f172a' : '#f8fafc',

        // Grid and borders
        grid: isDark ? '#334155' : '#cbd5e1',
        border: isDark ? '#475569' : '#94a3b8',

        // Zero line
        zeroLine: isDark ? '#a78bfa' : '#8b5cf6',

        // Text colors
        text: isDark ? '#cbd5e1' : '#475569',
        titleText: isDark ? '#e2e8f0' : '#334155',

        // Tooltip
        tooltipBg: isDark ? '#1e293b' : '#ffffff',
        tooltipText: isDark ? '#f1f5f9' : '#0f172a',
        tooltipBorder: isDark ? '#475569' : '#cbd5e1',

        // Legend
        legendText: isDark ? '#cbd5e1' : '#475569',
    }
})

const zeroLinePlugin = {
    id: 'zeroLine',
    afterDraw(chart: any) {
        const { ctx, scales } = chart
        const yScale = scales.y
        if (!yScale) return

        const y = yScale.getPixelForValue(0)

        ctx.save()
        ctx.beginPath()
        ctx.moveTo(chart.chartArea.left, y)
        ctx.lineTo(chart.chartArea.right, y)
        ctx.lineWidth = 2
        ctx.strokeStyle = colors.value.zeroLine
        ctx.setLineDash([6, 4])
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

const overPoints = computed(() => (props.points ?? []).filter(p => p.y > 0))
const underPoints = computed(() => (props.points ?? []).filter(p => p.y < 0))

const chartData = computed(() => ({
    datasets: [
        {
            label: 'Under Predicted',
            data: overPoints.value,
            pointRadius: 5,
            backgroundColor: 'rgba(34,197,94,0.7)',
            borderColor: 'rgba(34,197,94,1)',
            borderWidth: 1.5,
        },
        {
            label: 'Over Predicted',
            data: underPoints.value,
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
            text: 'Residual Scatter',
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
                    const p = ctx.raw as Point
                    const extra = p.created_at ? ` • ${p.created_at}` : ''
                    return `Pred: ${ctx.parsed.x}, Residual: ${ctx.parsed.y}${extra}`
                },
            },
        },
    },
    scales: {
        x: {
            type: 'linear' as const,
            title: {
                display: true,
                text: 'Predicted',
                color: colors.value.text,
                font: {
                    size: 13,
                    weight: 'bold'
                }
            },
            min: 20,
            max: 50,
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
            }
        },
        y: {
            title: {
                display: true,
                text: 'Residual (Actual - Predicted)',
                color: colors.value.text,
                font: {
                    size: 13,
                    weight: 'bold'
                }
            },
            min: -14,
            max: 14,
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
            }
        },
    },
}))
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <div class="h-120 w-300">
            <Scatter :data="chartData" :options="chartOptions" :plugins="[zeroLinePlugin, chartAreaBackground]"/>
        </div>

        <p v-if="!points?.length" class="mt-2 text-sm text-slate-500 dark:text-slate-400">
        No residuals to plot yet (need runs with both predicted + actual).
        </p>
    </div>
</template>
