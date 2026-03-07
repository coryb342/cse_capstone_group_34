<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

import {
    Dialog,
    DialogTrigger,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/Components/ui/dialog/index';

import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Soft Sensors',
        href: dashboard().url,
    },
];

const page = usePage();

const isDialogOpen = ref(false);

//const csrfToken = page.props.csrf_token;

const form = reactive({
    name: '',
    mqtt_broker: '',
    mqtt_topic: '',
    username: '',
    password: '',
    model_id: '',
    time_interval: 60,
});

function submit() {
    router.post(
        '/soft-sensors',
        { ...form },
        {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
        },
    );
}

const getModelName = (id) => {
    const models = page.props.models;

    if (!models) return 'Unknown Model';

    const list = Array.isArray(models)
        ? models
        : Array.isArray(models.data)
          ? models.data
          : [];

    const model = list.find((m) => Number(m.id) === Number(id));

    return model?.name ?? 'Unknown Model';
};

function confirmDelete(id) {
    if (!confirm('Are you sure you want to delete this soft sensor?')) return;

    router.delete(`/soft-sensors/${id}`);
}
</script>

<template>
    <Head title="Soft Sensors" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Error Display -->
        <div v-if="page.props.errors">
            <div v-for="(error, index) in page.props.errors" :key="index">
                <span class="text-red-600">{{ error }}</span>
            </div>
        </div>

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Stats Bar -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Active Sensors</p>
                    <p class="mt-1 text-2xl font-semibold">--</p>
                </div>

                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Total Sensors</p>
                    <p class="mt-1 text-2xl font-semibold">--</p>
                </div>

                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Avg Model Accuracy</p>
                    <p class="mt-1 text-2xl font-semibold">--</p>
                </div>

                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Models Online</p>
                    <p class="mt-1 text-2xl font-semibold">--</p>
                </div>
            </div>

            <Dialog v-model:open="isDialogOpen">
                <div class="rounded-xl border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-semibold">Soft Sensors</h1>
                            <p class="mt-1 text-sm text-gray-500">
                                Manage and configure soft sensor models.
                            </p>
                        </div>

                        <!-- Trigger MUST be inside Dialog -->
                        <DialogTrigger as-child>
                            <Button
                                @click="isDialogOpen = true"
                                class="flex justify-center"
                            >
                                <Plus />
                                Create Soft Sensor
                            </Button>
                        </DialogTrigger>
                    </div>
                </div>

                <DialogContent class="max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Create Soft Sensor</DialogTitle>
                        <DialogDescription>
                            Configure MQTT settings and link this sensor to a
                            predictive model.
                        </DialogDescription>
                    </DialogHeader>

                    <Form @submit.prevent="submit">
                        <div class="grid p-2">
                            <Label for="name" class="mb-1 text-left">
                                Sensor Name
                            </Label>
                            <input
                                v-model="form.name"
                                id="name"
                                name="name"
                                required
                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                            />
                        </div>

                        <div class="grid p-2">
                            <Label for="description" class="mb-1 text-left">
                                Description
                            </Label>
                            <textarea
                                v-model="form.description"
                                id="description"
                                name="description"
                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                            ></textarea>
                        </div>
                        <!-- MQTT Broker -->
                        <div class="grid p-2">
                            <Label for="mqtt_broker" class="mb-1 text-left">
                                MQTT Broker
                            </Label>
                            <Input
                                v-model="form.mqtt_broker"
                                id="mqtt_broker"
                                name="mqtt_broker"
                                required
                                placeholder="tcp://localhost:1883"
                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                            />
                        </div>

                        <div class="grid p-2">
                            <Label for="mqtt_topic" class="mb-1 text-left">
                                MQTT Topic
                            </Label>
                            <Input
                                v-model="form.mqtt_topic"
                                id="mqtt_topic"
                                name="mqtt_topic"
                                required
                                placeholder="sensors/level"
                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                            />
                        </div>

                        <div class="grid p-2">
                            <Label for="username" class="mb-1 text-left">
                                Username (optional)
                            </Label>
                            <Input
                                v-model="form.username"
                                id="username"
                                name="username"
                                placeholder="MQTT Username"
                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                            />
                        </div>

                        <div class="grid p-2">
                            <Label for="password" class="mb-1 text-left">
                                Password (optional)
                            </Label>
                            <Input
                                v-model="form.password"
                                id="password"
                                name="password"
                                type="password"
                                placeholder="MQTT Password"
                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                            />
                        </div>

                        <!-- Model Dropdown -->
                        <div class="grid p-2">
                            <Label for="model_id" class="mb-1 text-left">
                                Select Predictive Model
                            </Label>
                            <select
                                v-model="form.model_id"
                                id="model_id"
                                name="model_id"
                                required
                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                            >
                                <option disabled value="">
                                    Select a model
                                </option>
                                <option
                                    v-for="model in page.props.models"
                                    :key="model.id"
                                    :value="model.id"
                                >
                                    {{ model.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Prediction Interval -->
                        <div class="grid p-2">
                            <Label for="time_interval" class="mb-1 text-left">
                                Prediction Interval (seconds)
                            </Label>
                            <select
                                v-model="form.time_interval"
                                id="time_interval"
                                name="time_interval"
                                required
                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                            >
                                <option value="30">30 seconds</option>
                                <option value="60">60 seconds</option>
                                <option value="120">120 seconds</option>
                                <option value="300">300 seconds</option>
                            </select>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                @click="isDialogOpen = false"
                            >
                                Cancel
                            </Button>
                            <Button type="submit">Create Soft Sensor</Button>
                        </div>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>

        <!-- Sensor Cards -->
        <div
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <div
                v-for="sensor in page.props.sensors"
                :key="sensor.id"
                class="group w-full rounded-xl border p-5 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg"
            >
                <!-- Header -->
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">
                        {{ sensor.name }}
                    </h3>

                    <div
                        class="text-slate-400 transition group-hover:text-slate-200"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 6v6l4 2"
                            />
                        </svg>
                    </div>
                </div>

                <div
                    class="h-1 w-full rounded-full bg-gradient-to-r from-slate-300 via-slate-400 to-slate-300 transition-transform group-hover:scale-x-110 dark:from-slate-700 dark:via-slate-600 dark:to-slate-700"
                ></div>

                <!-- Stats -->
                <div class="mt-4 space-y-1 text-sm">
                    <p>
                        <span class="font-semibold">MQTT Broker:</span>
                        {{ sensor.mqtt_broker }}
                    </p>

                    <p>
                        <span class="font-semibold">MQTT Topic:</span
                        >{{ sensor.mqtt_topic }}
                    </p>

                    <p>
                        <span class="font-semibold">Model:</span
                        >{{ getModelName(sensor.model_id) }}
                    </p>

                    <p>
                        <span class="font-semibold">Interval:</span
                        >{{ sensor.time_interval }} sec
                    </p>

                    <p>
                        <span class="font-semibold">Actual Value:</span
                        >{{ sensor.actual_value }}
                    </p>

                    <p>
                        <span class="font-semibold">Predicted Value:</span>
                        {{ sensor.predicted_value }}
                    </p>

                    <p>
                        <span class="font-semibold">Last Prediction:</span>
                        {{ sensor.time_since_last_prediction }}
                    </p>
                </div>

                <!-- Footer -->
                <div class="mt-5 flex justify-end">
                    <button
                        @click="confirmDelete(sensor.id)"
                        class="mt-4 rounded-lg border px-3 py-1 text-red-600 hover:bg-red-600 hover:text-white"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
