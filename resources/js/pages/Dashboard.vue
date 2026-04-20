<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Icon } from '@iconify/vue';

import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import SimpleXYGraph from '@/components/charts/SimpleXYGraph.vue';
import SimpleBarGraph from '@/components/charts/SimpleBarGraph.vue';

const zip = ref('');
const gauge = ref('');
const selectedModel = ref('');

import ResidualScatterPlot from '@/components/charts/residualScatterPlot.vue';
import ActualVsPredScatterPlot from '@/components/charts/ActualVsPredScatterPlot.vue';
import MetricChart from '@/components/charts/MetricChart.vue';
const selectedGraph = ref('');

function updateDashboard() {
    router.get('/dashboard', {
        zip: zip.value,
        gauge: gauge.value,
    });
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const page = usePage();
const currentModel = computed(() => {
    return (
        page.props.models.find(
            (model: any) => String(model.id) === String(selectedModel.value),
        ) || null
    );
});

const residualScatter = computed(() => {
    return currentModel.value?.residualScatter ?? { points: [] };
});

const analytics = computed(() => {
    return currentModel.value?.analytics ?? null;
});

const actualPredPoints = computed(() => {
    return (residualScatter.value.points ?? []).map((p: any) => ({
        predicted: p.x,
        actual: p.x - p.y,
    }));
});
const modelAccuracyPoints = computed(() => {
    return (page.props.models ?? [])
        .filter(
            (model: any) =>
                model.analytics?.accuracy !== null &&
                model.analytics?.accuracy !== undefined,
        )
        .map((model: any) => ({
            label: model.name,
            value: Number(model.analytics.accuracy),
        }));
});
const totalModels = computed(() => {
    return (page.props.models ?? []).length;
});

const bestModel = computed(() => {
    const modelsWithAccuracy = (page.props.models ?? []).filter(
        (model: any) =>
            model.analytics?.accuracy !== null &&
            model.analytics?.accuracy !== undefined,
    );

    if (!modelsWithAccuracy.length) return null;

    return modelsWithAccuracy.reduce((best: any, current: any) => {
        return Number(current.analytics.accuracy) >
            Number(best.analytics.accuracy)
            ? current
            : best;
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Error Display -->
        <div v-for="(error, index) in page.props.errors" :key="index">
            <span class="text-red-600">{{ error }}</span>
        </div>

        <!-- Main Dash -->
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- Temperature Card -->
                <div
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div class="flex items-center gap-3">
                        <div>
                            <form
                                @submit.prevent="updateDashboard"
                                class="mt-2 flex items-center gap-2"
                            >
                                <!-- Zip Box -->
                                <input
                                    v-model="zip"
                                    type="text"
                                    placeholder="ZIP"
                                    class="w-20 rounded border px-2 py-1 text-xs"
                                />
                                <!-- Gauge Box -->
                                <input
                                    v-model="gauge"
                                    type="text"
                                    placeholder="Gauge ID (optional)"
                                    class="w-40 rounded border px-2 py-1 text-xs"
                                />
                                <button
                                    type="submit"
                                    class="rounded border px-2 py-1 text-xs"
                                >
                                    Go
                                </button>
                            </form>
                            <!-- City info -->
                            <p class="mt-2 text-sm">
                                <span
                                    v-if="page.props.city"
                                    class="flex items-center gap-1"
                                >
                                    <Icon icon="map:map-pin" />
                                    <strong>City:</strong>
                                    {{ page.props.city }},{{ page.props.state }}
                                </span>
                                <span v-else class="flex items-center gap-1">
                                    <Icon icon="map:map-pin" />
                                    <strong>City:</strong>
                                    Invalid ZIP
                                </span>
                            </p>

                            <!-- Temp Stuff -->
                            <p class="mt-2 text-sm">
                                <span
                                    v-if="page.props.weather"
                                    class="flex items-center gap-1"
                                >
                                    <Icon icon="mdi:thermometer" />
                                    <strong>Temperature:</strong>
                                    {{ page.props.weather.temperature_2m }}°C
                                </span>

                                <span v-else class="text-red-600">
                                    No Weather Data
                                </span>
                            </p>

                            <p class="mt-2 text-sm">
                                <span
                                    v-if="page.props.weather"
                                    class="flex items-center gap-1"
                                >
                                    <Icon icon="wi:rain" />
                                    <strong>Precipitation:</strong>
                                    {{ page.props.weather.precipitation }} mm
                                </span>

                                <span v-else class="text-red-600">
                                    No Precipitation Data
                                </span>
                            </p>

                            <!-- River Stuff -->
                            <p class="mt-2 text-sm">
                                <span
                                    class="inline-flex items-center gap-2 text-sm"
                                >
                                    <Icon
                                        icon="mdi:map-marker"
                                        class="align-middle"
                                    />
                                    <strong>River:</strong>
                                    {{ page.props.riverName }}
                                </span>
                            </p>
                            <p class="mt-2 text-sm">
                                <span
                                    class="inline-flex items-center gap-2 text-sm"
                                >
                                    <Icon
                                        icon="mdi:waves"
                                        class="align-middle"
                                    />
                                    <strong>Gauge Height:</strong>
                                    {{ page.props.gageHeight }} ft
                                </span>
                            </p>
                            <p class="mt-2 text-sm">
                                <span
                                    class="inline-flex items-center gap-2 text-sm"
                                >
                                    <Icon
                                        icon="mdi:water"
                                        class="align-middle"
                                    />
                                    <strong>Discharge:</strong>
                                    {{ page.props.discharge }} cfs
                                </span>
                            </p>
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-gray-600 dark:text-gray-300">
                        <template v-if="page.props.weather">
                            Updated: {{ page.props.weather.time }}
                        </template>

                        <template v-else> Updated: No data </template>
                    </p>

                    <PlaceholderPattern class="pointer-events-none" />
                </div>

                <!-- Placeholder Card 2 -->
                <div
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <div class="flex h-full flex-col justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Active Models
                            </p>
                            <h3
                                class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ totalModels }}
                            </h3>
                        </div>

                        <p
                            class="mt-4 text-xs text-gray-600 dark:text-gray-300"
                        >
                            Total active predictive models available
                        </p>
                    </div>
                </div>

                <!-- Placeholder Card 3 -->
                <div
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <div class="flex h-full flex-col justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Top Model
                            </p>

                            <template v-if="bestModel">
                                <h3
                                    class="mt-2 truncate text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{ bestModel.name }}
                                </h3>
                                <p
                                    class="mt-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    Accuracy:
                                    <span class="font-semibold">
                                        {{
                                            Number(
                                                bestModel.analytics.accuracy,
                                            ).toFixed(1)
                                        }}%
                                    </span>
                                </p>
                            </template>

                            <template v-else>
                                <h3
                                    class="mt-2 text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    No data
                                </h3>
                                <p
                                    class="mt-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    No model accuracy available yet
                                </p>
                            </template>
                        </div>

                        <p
                            class="mt-4 text-xs text-gray-600 dark:text-gray-300"
                        >
                            Highest accuracy among active models
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border"
            >
                <div class="mb-4 flex items-center justify-end gap-2">
                    <!-- Model -->
                    <select
                        v-model="selectedModel"
                        class="rounded border px-2 py-1 text-xs dark:bg-slate-800 dark:text-slate-100"
                    >
                        <option disabled value="">Model</option>
                        <option
                            v-for="model in page.props.models"
                            :key="model.id"
                            :value="String(model.id)"
                        >
                            {{ model.name }}
                        </option>
                    </select>

                    <!-- Graph -->
                    <select
                        v-model="selectedGraph"
                        class="rounded border px-2 py-1 text-xs dark:bg-slate-800 dark:text-slate-100"
                    >
                        <option disabled value="">Graph</option>
                        <option value="residual">Residual</option>
                        <option value="actualPred">Actual vs Pred</option>
                        <option value="modelAccuracy">
                            Model Accuracy Comparison
                        </option>
                    </select>
                </div>
                <!-- Show nothing until both dropdowns are selected -->
                <div
                    v-if="
                        !selectedGraph ||
                        (selectedGraph !== 'modelAccuracy' && !selectedModel)
                    "
                    class="py-20 text-center text-gray-500"
                >
                    Select a model and a graph to view analytics
                </div>

                <div
                    v-else-if="
                        selectedGraph === 'residual' &&
                        residualScatter.points?.length
                    "
                    class="w-full p-4"
                >
                    <SimpleXYGraph
                        :points="residualScatter.points"
                        xKey="x"
                        yKey="y"
                        :height="250"
                        title="Residual Scatter Plot"
                        xLabel="Predicted Value"
                        yLabel="Residual"
                    />
                </div>

                <div
                    v-else-if="
                        selectedGraph === 'actualPred' &&
                        actualPredPoints.length
                    "
                    class="w-full p-8"
                >
                    <SimpleXYGraph
                        :points="actualPredPoints"
                        xKey="predicted"
                        yKey="actual"
                        :height="250"
                        title="Actual vs Predicted"
                        xLabel="Predicted Value"
                        yLabel="Actual Value"
                    />
                </div>

                <div
                    v-else-if="
                        selectedGraph === 'modelAccuracy' &&
                        modelAccuracyPoints.length
                    "
                    class="w-full p-4"
                >
                    <SimpleBarGraph
                        :points="modelAccuracyPoints"
                        labelKey="label"
                        valueKey="value"
                        :height="260"
                        title="Accuracy Comparison Across Models"
                        yLabel="Accuracy (%)"
                    />
                </div>

                <div
                    v-else-if="selectedGraph === 'metric' && analytics"
                    class="w-full p-8"
                >
                    <MetricChart :analytics="analytics" />
                </div>

                <div v-else class="py-20 text-center text-gray-500">
                    No data available for this graph
                </div>
            </div>
        </div>
    </AppLayout>
</template>
