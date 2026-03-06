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
} from '@/Components/ui/dialog';

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

const csrfToken = page.props.csrf_token;


const form = reactive({
    mqtt_broker: '',
    mqtt_topic: '',
    username: '',
    password: '',
    model_id: '',
    time_interval: 60,
});

function submit() {

    router.post('/soft-sensors', form, {
        onSuccess: () => {
            isDialogOpen.value = false;
        },
    });
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

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

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
                            <Button @click="isDialogOpen = true">
                                <Plus class="mr-2 h-4 w-4" />
                                Create Soft Sensor
                            </Button>
                        </DialogTrigger>
                    </div>
                </div>

                <!-- Form Stuff-->
                <DialogContent class="max-h-[90vh] overflow-y-auto">

                    <div class="font-bold text-red-600">THIS IS THE REAL MODAL</div>

                    <DialogHeader>
                        <DialogTitle>Create Soft Sensor</DialogTitle>
                        <DialogDescription>
                            Configure MQTT settings and select a predictive model.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submit">
                        <input type="hidden" name="csrf_token" :value="csrfToken" />

                        <!-- MQTT Section -->
                        <div class="grid gap-4 p-2">
                            <h3 class="text-lg font-semibold">MQTT Connection</h3>

                            <div class="grid">
                                <Label for="mqtt_broker">MQTT Broker</Label>
                                <Input
                                    v-model="form.mqtt_broker"
                                    id="mqtt_broker"
                                    name="mqtt_broker"
                                    placeholder="tcp://localhost:1883"
                                    required
                                />
                            </div>

                            <div class="grid">
                                <Label for="mqtt_topic">MQTT Topic</Label>
                                <Input
                                    v-model="form.mqtt_topic"
                                    id="mqtt_topic"
                                    name="mqtt_topic"
                                    placeholder="sensors/level"
                                    required
                                />
                            </div>

                            <div class="grid">
                                <Label for="username">Username (optional)</Label>
                                <Input
                                    v-model="form.username"
                                    id="username"
                                    name="username"
                                    placeholder="MQTT Username"
                                />
                            </div>

                            <div class="grid">
                                <Label for="password">Password (optional)</Label>
                                <Input
                                    v-model="form.password"
                                    id="password"
                                    name="password"
                                    type="password"
                                    placeholder="MQTT Password"
                                />
                            </div>
                        </div>

                        <!-- Model Settings -->
                        <div class="grid gap-4 p-2">
                            <h3 class="text-lg font-semibold">Model Settings</h3>

                            <div class="grid">
                                <Label for="model_id">Select Model</Label>
                                <select
                                    v-model="form.model_id"
                                    id="model_id"
                                    name="model_id"
                                    class="rounded border p-2"
                                    required
                                >
                                    <option disabled value="">Select a model</option>

                                    <option
                                        v-for="model in page.props.models"
                                        :key="model.id"
                                        :value="model.id"
                                    >
                                        {{ model.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="grid">
                                <Label for="time_interval">Prediction Interval</Label>
                                <select
                                    v-model="form.time_interval"
                                    id="time_interval"
                                    name="time_interval"
                                    class="rounded border p-2"
                                    required
                                >
                                    <option value="30">30 seconds</option>
                                    <option value="60">60 seconds</option>
                                    <option value="120">120 seconds</option>
                                    <option value="300">300 seconds</option>
                                </select>
                            </div>
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
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <!-- Sensor Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="sensor in page.props.sensors"
                :key="sensor.id"
                class="rounded-xl border p-4"
            >
                <h3 class="mb-2 text-lg font-semibold">
                    Soft Sensor #{{ sensor.id }}
                </h3>

                <p><strong>MQTT Broker:</strong> {{ sensor.mqtt_broker }}</p>
                <p><strong>MQTT Topic:</strong> {{ sensor.mqtt_topic }}</p>
                <p><strong>Model ID:</strong> {{ sensor.model_id }}</p>
                <p>
                    <strong>Time Interval:</strong>
                    {{ sensor.time_interval }} sec
                </p>

                <button
                    @click="router.delete(`/soft-sensors/${sensor.id}`)"
                    class="mt-4 rounded-lg border px-3 py-1 text-red-600 hover:bg-red-600 hover:text-white"
                >
                    Delete
                </button>
            </div>
        </div>

    </AppLayout>
</template>
