<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Icon } from '@iconify/vue';

import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const zip = ref('');
const gauge = ref('');

function updateDashboard() {
    router.get('/dashboard', {
        zip: zip.value,
        gauge: gauge.value
    })
}


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const page = usePage();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Error Display -->
        <div v-if="page.props.errors">
            <div v-for="(index, error) in page.props.errors" :key="index">
                <span class="text-red-600">{{ error }}</span>
            </div>
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
                            > <!-- Zip Box -->
                                <input
                                    v-model="zip"
                                    type="text"
                                    placeholder="ZIP"
                                    class="w-20 rounded border px-2 py-1 text-xs"
                                /> <!-- Gauge Box -->
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
                            <p class ="mt-2 text-sm">
                                <span class="flex items-center gap-1">
                                    <Icon icon="map:map-pin" />
                                    <strong>City:</strong>
                                    {{page.props.city}}, {{page.props.state}}
                                    </span>
                            </p>

                            <p class="mt-2 text-sm">
                                <span class="flex items-center gap-1">
                                    <Icon icon="mdi:thermometer" />
                                    <strong>Temperature:</strong>
                                    {{ page.props.weather.temperature_2m }}°C
                                </span>
                            </p>
                            <p class="mt-2 text-sm">
                                <span class="flex items-center gap-1">
                                    <Icon icon="wi:rain" />
                                    <strong>Precipitation:</strong>
                                    {{ page.props.weather.precipitation }} mm
                                </span>
                            </p>
                            <p class="mt-2 text-sm">
                                <span
                                    class="inline-flex items-center gap-2 text-sm"
                                >
                                    <Icon icon="mdi:map-marker" class="align-middle" />
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
                        Updated: {{ page.props.weather.time }}
                    </p>

                    <PlaceholderPattern class="pointer-events-none" />
                </div>

                <!-- Placeholder Card 2 -->
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>

                <!-- Placeholder Card 3 -->
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
            </div>

            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
