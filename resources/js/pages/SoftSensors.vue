<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router, useForm } from '@inertiajs/vue3';
import { ref, reactive, onMounted, onUnmounted, watch } from 'vue';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogTrigger,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog/index';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { route } from 'ziggy-js';

import mqtt from 'mqtt';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Soft Sensors',
        href: dashboard().url,
    },
];

const page = usePage();

const viewingSession = useForm({});

const props = defineProps({
    sensors: Array,
    models: Array,
    stats: Object,
});

const isDialogOpen = ref(false);

const form = reactive({
    name: '',
    mqtt_broker: '',
    mqtt_topic: '',
    username: '',
    password: '',
    model_id: '',
    time_interval: 60,
});

const resetForm = () => {
    form.name = '';
    form.mqtt_broker = '';
    form.mqtt_topic = '';
    form.username = '';
    form.password = '';
    form.model_id = '';
    form.time_interval = 60;
};

const getModelName = (id) => {
    if (props.models.length < 1) {
        return "Model Not Found"
    }

    for (const model of props.models) {
        if (model.id == id) {
            return model.name
        }
    }
};

const clients = new Map()
const sensorActualValues = ref<number[]>([])
let heartbeat: string | number | NodeJS.Timeout | null | undefined = null

function submit() {
    router.post(
        '/soft-sensors',
        { ...form },
        {
            onSuccess: () => {
                resetForm();
                isDialogOpen.value = false;
            },
        },
    );
}

function confirmDelete(id) {
    if (!confirm('Are you sure you want to delete this soft sensor?')) return;

    router.delete(`/soft-sensors/${id}`);
}

function startHeartbeat() {
    heartbeat = setInterval(() => {
        viewingSession.post(route('soft-sensors.viewing-session-heartbeat'))
    }, 10000)
}

function establishDatastreamConnections() {
    for (const sensor of props.sensors) {
        establishDatastreamConnection(sensor)
    }
}

function establishDatastreamConnection(sensor) {
    const client = mqtt.connect(sensor.mqtt_broker)
    client.on("connect", () => {
        client.subscribe(sensor.mqtt_topic)
    })
    client.on("message", (topic, message) => {
        if (topic === sensor.mqtt_topic) {
            const dataPayload = JSON.parse(message.toString());
            sensorActualValues.value[sensor.id] = dataPayload.sensor_actual;
        }
    })

    clients.set(sensor.id, client)
}

onMounted(() => {
    establishDatastreamConnections()
    viewingSession.post(route('soft-sensors.initiate-viewing-session'))
    startHeartbeat()
})

onUnmounted(() => {
    clients.forEach(client => client.end())
    clearInterval(heartbeat)
    viewingSession.post(route('soft-sensors.terminate-viewing-session'))
})

setInterval(() => {
    router.reload({ only: ['sensors'] })
    let newest_sensor = props.sensors[props.sensors.length - 1]
    if (newest_sensor && !clients.has(newest_sensor.id)) {
        establishDatastreamConnection(newest_sensor);
    }
}, 1000)

window.addEventListener('beforeunload', () => {
    navigator.sendBeacon(route('soft-sensors.terminate-viewing-session'));
})

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        clearInterval(heartbeat)
    } else {
        startHeartbeat()
    }
})

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
                    <p class="mt-1 text-2xl font-semibold">
                        {{ props.stats?.activeSensors }}
                    </p>
                </div>

                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Total Sensors</p>
                    <p class="mt-1 text-2xl font-semibold">
                        {{ props.stats?.totalSensors }}
                    </p>
                </div>

                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Avg Model Accuracy</p>
                    <p class="mt-1 text-2xl font-semibold">
                        {{ props.stats?.avgAccuracy }}%
                    </p>
                </div>

                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Models Online</p>
                    <p class="mt-1 text-2xl font-semibold">
                        {{ props.stats?.modelsOnline }}
                    </p>
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

                    <form @submit.prevent="submit">
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
                                class="col-span-3 rounded border border-slate-900 bg-white px-2 py-1 text-slate-900 dark:border-slate-400 dark:bg-slate-800 dark:text-slate-100"
                            >
                                <option disabled value="">
                                    Select a model
                                </option>
                                <option
                                    v-for="model in props.models"
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
                                class="col-span-3 rounded border border-slate-900 bg-white px-2 py-1 text-slate-900 dark:border-slate-400 dark:bg-slate-800 dark:text-slate-100"
                            >
                                <option value="10">10 seconds</option>
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
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <!-- Sensor Cards -->
        <div class="min-h-screen px-4">
            <div class="grid grid-cols-[repeat(auto-fill,minmax(400px,400px))] gap-4 justify-start">
                <div
                    v-for="sensor in props.sensors"
                    :key="sensor.id"
                    class="transition-all duration-200 hover:-translate-y-1 hover:shadow-lg"
                >

                    <Card>
                        <CardHeader>
                            <CardTitle>{{ sensor.name }}</CardTitle>
                        </CardHeader>

                        <CardContent class="grid grid-cols-2 gap-2">
                            <div>
                                <CardHeader class="font-bold">
                                    Actual
                                </CardHeader>
                                <CardContent v-if="Math.abs(Number(Number((sensor.run_results[0]?.result / sensorActualValues[sensor.id]) * 100 - 100).toFixed(0))) < 20" class="bg-green-500/30 border-2 border-green-500 rounded-2xl px-4 py-12 text-center">
                                    <span class="text-5xl">{{ sensorActualValues[sensor.id] ?? "..." }}</span>
                                </CardContent>
                                <CardContent v-else-if="Math.abs(Number(Number((sensor.run_results[0]?.result / sensorActualValues[sensor.id]) * 100 - 100).toFixed(0))) >= 20 && Math.abs(Number(Number((sensor.run_results[0]?.result / sensorActualValues[sensor.id]) * 100 - 100).toFixed(0))) < 50" class="bg-yellow-500/30 border-2 border-yellow-500 rounded-2xl px-4 py-12 text-center">
                                    <span class="text-5xl">{{ sensorActualValues[sensor.id] ?? "..." }}</span>
                                </CardContent>
                                <CardContent v-else class="bg-red-500/30 border-2 border-red-500 rounded-2xl px-4 py-12 text-center">
                                    <span class="text-5xl">{{  sensorActualValues[sensor.id] ?? "..." }}</span>
                                </CardContent>
                                <CardFooter class="justify-between py-2">
                                    <Label>Deviation:</Label>
                                    <span>{{ Math.abs(Number(Number((sensor.run_results[0]?.result / sensorActualValues[sensor.id]) * 100 - 100).toFixed(0))) }}%</span>
                                </CardFooter>
                            </div>
                            <div>
                                <CardHeader>
                                    Predicted
                                </CardHeader>
                                <CardContent class="bg-slate-300 dark:bg-slate-900 border-2 border-slate-400 dark:border-slate-800 rounded-2xl px-4 py-12 text-center">
                                    <span class="text-5xl">{{ sensor.run_results[0] ? Number(sensor.run_results[0].result).toFixed(2) : '...' }}</span>
                                </CardContent>
                                <CardFooter class="justify-between py-2">
                                    <Label>Next:</Label>
                                    <span v-if="(Number((((new Date(sensor.last_prediction_time + 'Z').getTime()) + (sensor.time_interval * 1000)) - Date.now()) / 1000).toFixed(0)).includes('-')"
                                          class="inline-block w-4 h-4 border-2 rounded-full animate-spin border-black/30 border-t-black dark:border-white/30 dark:border-t-white">
                                    </span>
                                    <span v-else>{{ Number((((new Date(sensor.last_prediction_time + 'Z').getTime()) + (sensor.time_interval * 1000)) - Date.now()) / 1000).toFixed(0) }} sec(s)</span>
                                </CardFooter>
                            </div>
                        </CardContent>
                        <CardFooter class="items-end justify-between">
                            <div class="text-sm">
                                <p>
                                    <span class="font-semibold">MQTT Broker:</span>
                                    {{ sensor.mqtt_broker }}
                                </p>
                                <p>
                                    <span class="font-semibold">MQTT Topic:</span>
                                    {{ sensor.mqtt_topic }}
                                </p>
                                <p>
                                    <span class="font-semibold">Model:</span>
                                    {{ getModelName(sensor.model_id) }}
                                </p>
                                <p>
                                    <span class="font-semibold">Interval:</span>
                                    {{ sensor.time_interval }} sec
                                </p>
                            </div>
                            <button
                                @click="confirmDelete(sensor.id)"
                                class="rounded-lg border px-3 py-1 text-red-600 hover:bg-red-600 hover:text-white"
                            >
                                Delete
                            </button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>>
