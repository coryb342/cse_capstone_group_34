<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Soft Sensors',
        href: dashboard().url,
    },
];

const page = usePage();


const showForm = ref(false);


const mqttBroker = ref('');
const mqttTopic = ref('');
const modelBroker = ref('');
const modelTopic = ref('');
const predictionInterval = ref(30);
</script>

<template>
    <Head title="Soft Sensors" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Error Display -->
        <div v-if="page.props.errors">
            <div v-for="(index, error) in page.props.errors" :key="index">
                <span class="text-red-600">{{ error }}</span>
            </div>
        </div>

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header Card -->
            <div
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-semibold">Soft Sensors</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Manage and configure soft sensor models.
                        </p>
                    </div>

                    <button
                        @click="showForm = !showForm"
                        class="rounded-lg border px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        Create Soft Sensor
                    </button>
                </div>
            </div>

            <!-- Form Card -->
            <div
                v-if="showForm"
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-xl font-semibold">New Soft Sensor</h2>

                <!-- MQTT Connection -->
                <div class="mb-6">
                    <h3 class="mb-2 font-semibold">MQTT Connection</h3>

                    <div class="mb-4">
                        <label class="mb-1 block text-sm">MQTT Broker</label>
                        <input
                            v-model="mqttBroker"
                            type="text"
                            class="w-full rounded border bg-transparent px-2 py-1"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm">MQTT Topic</label>
                        <input
                            v-model="mqttTopic"
                            type="text"
                            class="w-full rounded border bg-transparent px-2 py-1"
                        />
                    </div>
                </div>

                <!-- Model Settings -->
                <div>
                    <h3 class="mb-2 font-semibold">Model Settings</h3>

                    <div class="mb-4">
                        <label class="mb-1 block text-sm">MQTT Broker</label>
                        <input
                            v-model="modelBroker"
                            type="text"
                            class="w-full rounded border bg-transparent px-2 py-1"
                        />
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-sm">MQTT Topic</label>
                        <input
                            v-model="modelTopic"
                            type="text"
                            class="w-full rounded border bg-transparent px-2 py-1"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm"
                            >Time Between Predictions</label
                        >
                        <select
                            v-model="predictionInterval"
                            class="w-full rounded border px-2 py-1"
                        >
                            <option value="30">30 seconds</option>
                            <option value="60">60 seconds</option>
                            <option value="120">120 seconds</option>
                            <option value="300">300 seconds</option>
                        </select>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        @click="showForm = false"
                        class="rounded-lg border px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        Cancel
                    </button>

                    <button
                        class="rounded-lg border bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        Save Soft Sensor
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
