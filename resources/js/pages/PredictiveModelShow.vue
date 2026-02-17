<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ResidualScatterPlot from '@/components/charts/residualScatterPlot.vue';
import ActualVsPredScatterPlot from '@/components/charts/ActualVsPredScatterPlot.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Settings,
    Database,
    Play,
    HardDriveDownload,
    CalendarDays,
    ChartLine,
    TrendingUp,
    ChartScatter,
} from 'lucide-vue-next';
import { usePage, useForm } from '@inertiajs/vue3';
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';

import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: `Model Results`,
        href: '/predictive-model/${props.model.id}',
    },
];

const props = defineProps({
    model: Object,
    runResults: Array,
    totalPredictions: Number,
    analytics: Object,
    modelCreatedDate: String,
    modelLastTrainedDate: String,
    residualScatter: {
        points: Array<{
            x: number;
            y: number;
            run_id?: number;
            created_at?: string;
        }>(),
    },
});

const showRunDataModal = ref(false);

const getStatusColor = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-500';
        case 'inactive':
            return 'bg-gray-400';
        default:
            return 'bg-gray-400';
    }
};

const isDialogOpen = ref(false);
const isLoadingResult = ref(false);
const hasViewedResult = ref(false);

const getMetricStatus = (metric, value) => {
    switch (metric) {
        case 'MAE':
            if (value < 2) return { label: 'Great', color: 'text-green-600' };
            if (value < 3) return { label: 'Good', color: 'text-blue-600' };
            if (value < 5)
                return { label: 'Needs Work', color: 'text-yellow-600' };
            return { label: 'Poor', color: 'text-red-600' };

        case 'MSE':
            if (value < 10) return { label: 'Great', color: 'text-green-600' };
            if (value < 25) return { label: 'Good', color: 'text-blue-600' };
            if (value < 50)
                return { label: 'Needs Work', color: 'text-yellow-600' };
            return { label: 'Poor', color: 'text-red-600' };

        case 'RMSE':
            if (value < 2) return { label: 'Great', color: 'text-green-600' };
            if (value < 3) return { label: 'Good', color: 'text-blue-600' };
            if (value < 5)
                return { label: 'Needs Work', color: 'text-yellow-600' };
            return { label: 'Poor', color: 'text-red-600' };

        case 'R2':
            if (value > 0.85)
                return { label: 'Excellent', color: 'text-green-600' };
            if (value > 0.7)
                return { label: 'Very Good', color: 'text-blue-600' };
            if (value > 0.5) return { label: 'Good', color: 'text-yellow-600' };
            if (value > 0.3)
                return { label: 'Moderate', color: 'text-orange-600' };
            return { label: 'Weak', color: 'text-red-600' };

        default:
            return { label: '', color: 'text-gray-600' };
    }
};

const form = useForm({
    parameters: [],
    actual: '',
    model_id: null,
});

function submit() {
    isLoadingResult.value = true;
    hasViewedResult.value = false;
    form.post(route('predictive-models.run'), {
        onSuccess: () => {
            isDialogOpen.value = true;
            isLoadingResult.value = false;
            form.reset();
        },
    });
}

function resetDialogue() {
    hasViewedResult.value = true;
    isDialogOpen.value = true;
}

const page = usePage();

function parseJsonSafe(v: any) {
    try {
        return typeof v === 'string' ? JSON.parse(v) : v
    } catch {
        return v
    }
}

function toNumber(v: any): number | null {
    if (v === null || v === undefined) return null

    const decoded = parseJsonSafe(v)

    if (typeof decoded === 'number') return Number.isFinite(decoded) ? decoded : null
    if (typeof decoded === 'string') {
        const n = Number(decoded)
        return Number.isFinite(n) ? n : null
    }
    if (Array.isArray(decoded)) {
        const n = Number(decoded[0])
        return Number.isFinite(n) ? n : null
    }
    if (decoded && typeof decoded === 'object') {
        const maybe = (decoded.value ?? decoded.result ?? decoded.prediction ?? decoded.y ?? decoded.pred)
        const n = Number(maybe)
        return Number.isFinite(n) ? n : null
    }
    return null
}

function formatInputsPreview(inputs: any, maxPairs = 4) {
    const obj = parseJsonSafe(inputs)
    if (!obj || typeof obj !== 'object') return '—'

    const entries = Object.entries(obj).slice(0, maxPairs)
    const preview = entries.map(([k, v]) => `${k}: ${v}`).join(', ')
    const total = Object.keys(obj).length

    return total > maxPairs ? `${preview} … (+${total - maxPairs})` : preview
}

const showSettingsModal = ref(false)
const confirmingDelete  = ref(false)
const settingsStatus    = ref(props.model.status)

function toggleStatus() {
    const newStatus = settingsStatus.value === 'active' ? 'inactive' : 'active'
    settingsStatus.value = newStatus

    router.patch(route('predictive-models.status', { model: props.model.id }), {
        status: newStatus,
    }, {
        preserveScroll: true,
        preserveState:  true,
    })
}

function deleteModel() {
    router.delete(route('predictive-models.destroy', { model: props.model.id }))
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-8">
            <Card>
                <CardHeader>
                    <div class="mb-2 flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <CardTitle>
                                <h1
                                    class="mb-2 text-4xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{ model.name }}
                                </h1>
                            </CardTitle>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button variant="outline">
                                <HardDriveDownload class="mr-2 h-4 w-4" />
                                Export
                            </Button>
                            <Button  @click="showSettingsModal = true">
                                <Settings class="mr-2 h-4 w-4" />
                                Settings
                            </Button>
                            <Button @click="showRunDataModal = true">
                                <Database class="mr-2 h-4 w-4" />
                                Run Data
                            </Button>
                            <Dialog v-model:open="isDialogOpen">
                                <DialogTrigger as-child>
                                    <Button @click="resetDialogue">
                                        <Play class="mr-2 h-4 w-4" />
                                        Run Prediction
                                    </Button>
                                </DialogTrigger>
                                <DialogContent
                                    v-if="
                                        ((!page.props.flash.model_run_result &&
                                            !page.props.flash
                                                .prediction_failed) ||
                                            hasViewedResult) &&
                                        !isLoadingResult
                                    "
                                    class="max-h-[90vh] overflow-y-auto"
                                >
                                    <DialogHeader>
                                        <DialogTitle
                                            >Run Prediction</DialogTitle
                                        >
                                        <DialogDescription
                                            >Use this form to run a
                                            prediction</DialogDescription
                                        >
                                    </DialogHeader>
                                    <Form @submit.prevent="submit">
                                        <input
                                            type="hidden"
                                            name="model_id"
                                            :value="form.model_id = model.id"
                                        />
                                        <div
                                            v-for="(
                                                parameter, index
                                            ) in JSON.parse(
                                                model.required_parameters,
                                            )"
                                            :key="index"
                                        >
                                            <div class="grid p-2">
                                                <Label
                                                    :for="'parameter_' + index"
                                                    class="mb-1 text-left"
                                                >
                                                    {{
                                                        parameter
                                                            .toString()
                                                            .toUpperCase()
                                                    }}
                                                </Label>
                                                <Input
                                                    required
                                                    :id="'parameter_' + index"
                                                    :name="
                                                        'parameters[' +
                                                        index +
                                                        ']'
                                                    "
                                                    v-model="
                                                        form.parameters[index]
                                                    "
                                                    class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                                                />
                                            </div>
                                        </div>
                                        <div class="grid p-2">
                                            <Label
                                                for="actual"
                                                class="mb-1 flex flex-col items-start text-left"
                                            >
                                                <span class="text-left"
                                                    >Actual</span
                                                >
                                                <span class="text-left text-xs"
                                                    >Entering an Actual value
                                                    will update the Model
                                                    Accuracy</span
                                                >
                                            </Label>
                                            <Input
                                                id="actual"
                                                name="actual"
                                                v-model="form.actual"
                                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                                            />
                                        </div>
                                        <div
                                            class="mt-5 mr-2 mb-5 flex justify-end"
                                        >
                                            <Button type="submit"> Run </Button>
                                        </div>
                                    </Form>
                                </DialogContent>
                                <DialogContent
                                    v-else-if="isLoadingResult"
                                    class="max-h-[90vh] overflow-y-auto"
                                >
                                    <DialogHeader>
                                        <DialogTitle
                                            >Running Prediction...</DialogTitle
                                        >
                                        <DialogDescription
                                            >Doing some magic</DialogDescription
                                        >
                                    </DialogHeader>
                                    <div
                                        class="flex h-24 items-center justify-center"
                                    >
                                        <svg
                                            class="h-8 w-8 animate-spin text-blue-600"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                            ></path>
                                        </svg>
                                    </div>
                                </DialogContent>
                                <DialogContent
                                    v-else-if="
                                        page.props.flash.prediction_failed
                                    "
                                    class="max-h-[90vh] overflow-y-auto"
                                >
                                    <DialogHeader>
                                        <DialogTitle class="text-red-600">
                                            Prediction Failed!
                                        </DialogTitle>
                                        <DialogDescription
                                            >Error Details
                                            Below</DialogDescription
                                        >
                                    </DialogHeader>
                                    <span class="text-lg font-bold"
                                        >Inputs Given:
                                    </span>
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="text-sm"
                                            v-for="(parameter, index) in page
                                                .props.flash.mapped_parameters"
                                            :key="index"
                                        >
                                            {{ index }}: {{ parameter }}
                                        </span>
                                    </div>
                                    <span class="text-lg font-bold"
                                        >Error:</span
                                    >
                                    <span>{{
                                        page.props.flash.prediction_failed
                                    }}</span>
                                </DialogContent>
                                <DialogContent
                                    v-else
                                    class="max-h-[90vh] overflow-y-auto"
                                >
                                    <DialogHeader>
                                        <DialogTitle class="text-green-500">
                                            Success!
                                        </DialogTitle>
                                        <DialogDescription
                                            >Result Details
                                            Below</DialogDescription
                                        >
                                    </DialogHeader>
                                    <span class="text-lg font-bold"
                                        >Inputs Given:
                                    </span>
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="text-sm"
                                            v-for="(parameter, index) in page
                                                .props.flash.mapped_parameters"
                                            :key="index"
                                        >
                                            {{ index }}: {{ parameter }}
                                        </span>
                                    </div>
                                    <span class="text-lg font-bold"
                                        >Result:</span
                                    >
                                    <span
                                        >Predicted {{ model.target }}:
                                        {{
                                            Number(
                                                page.props.flash
                                                    .model_run_result,
                                            ).toFixed(2)
                                        }}</span
                                    >
                                </DialogContent>
                            </Dialog>
                        </div>
                    </div>
                    <div>
                        <p
                            class="mb-6 size-5/12 text-sm text-slate-900 dark:text-white"
                        >
                            {{ model.description }}
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400"
                    >
                        <div class="flex items-center gap-2">
                            <CalendarDays class="h-4 w-4" />
                            <span>Created: {{ props.modelCreatedDate }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <CalendarDays class="h-4 w-4" />
                            <span
                                >Last trained:
                                {{ props.modelLastTrainedDate }}</span
                            >
                        </div>

                        <div class="flex items-center gap-2">
                            <ChartLine class="h-4 w-4" />
                            <span>Type: {{ model.type }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <div
                                :class="getStatusColor(model.status)"
                                class="h-2 w-2 animate-pulse rounded-full"
                            />
                            <Badge variant="secondary">{{
                                model.status
                            }}</Badge>
                            <span class="text-sm text-slate-500">{{
                                model.version
                            }}</span>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="pt-6">
                    <div
                        class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2"
                    >
                        <Card>
                            <CardContent class="p-6">
                                <div
                                    class="mb-2 flex items-start justify-between"
                                >
                                    <span
                                        class="text-2xl font-medium text-slate-600 dark:text-white"
                                        >Accuracy</span
                                    >
                                    <TrendingUp
                                        class="h-10 w-10 text-green-500"
                                    />
                                </div>
                                <div
                                    class="mb-1 text-4xl font-bold text-slate-900 dark:text-slate-400"
                                >
                                    {{
                                        props.analytics?.accuracy
                                            ? props.analytics.accuracy + '%'
                                            : '--'
                                    }}
                                </div>
                                <!--                                EDIT TO DETERMINE IF UP OR DOWN FROM PREVIOUS MONTH AND SHOW PROPER RESULT-->
                                <!--                            <div class="flex items-center text-sm text-green-600">-->
                                <!--                                <ArrowUp class="w-4 h-4 mr-1" />-->
                                <!--                                <span>2.1% from last month</span>-->
                                <!--                            </div>-->
                            </CardContent>
                        </Card>
                        <Card>
                            <CardContent class="p-6">
                                <div
                                    class="mb-2 flex items-start justify-between"
                                >
                                    <span
                                        class="text-2xl font-medium text-slate-600 dark:text-white"
                                        >Total Predictions</span
                                    >
                                    <ChartScatter
                                        class="h-10 w-10 text-blue-500"
                                    />
                                </div>
                                <div
                                    class="mb-1 text-4xl font-bold text-slate-900 dark:text-slate-400"
                                >
                                    {{ props.totalPredictions }}
                                </div>
                                <div class="text-sm text-slate-500">
                                    Last 30 days
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div
                        class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4"
                    >
                        <Card>
                            <CardHeader
                                class="mb-1 text-4xl font-bold text-slate-900 dark:text-slate-100"
                            >
                                MSE
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-start gap-6">
                                    <div>
                                        <div
                                            class="text-lg font-semibold text-slate-900 dark:text-slate-400"
                                        >
                                            {{ props.analytics?.mse ?? '--' }}
                                        </div>
                                        <div
                                            v-if="props.analytics?.mse"
                                            :class="[
                                                'text-sm font-semibold',
                                                getMetricStatus(
                                                    'MSE',
                                                    analytics.mse,
                                                ).color,
                                            ]"
                                        >
                                            {{
                                                getMetricStatus(
                                                    'MSE',
                                                    analytics.mse,
                                                ).label
                                            }}
                                        </div>
                                    </div>

                                    <div
                                        class="text-sm text-slate-900 dark:text-slate-100"
                                    >
                                        <strong>Low:</strong> No extreme
                                        failures<br />
                                        <strong>High:</strong> Occasionally
                                        makes bad predictions<br />
                                        <span
                                            class="text-slate-900 dark:text-slate-400"
                                            >→ Highlights where model can fail
                                            badly</span
                                        >
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader
                                class="mb-1 text-4xl font-bold text-slate-900 dark:text-slate-100"
                            >
                                MAE
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-start gap-6">
                                    <div>
                                        <div
                                            class="text-lg font-semibold text-slate-900 dark:text-slate-400"
                                        >
                                            {{ props.analytics?.mae ?? '--' }}
                                        </div>
                                        <div
                                            v-if="props.analytics?.mae"
                                            :class="[
                                                'text-sm font-semibold',
                                                getMetricStatus(
                                                    'MAE',
                                                    analytics.mae,
                                                ).color,
                                            ]"
                                        >
                                            {{
                                                getMetricStatus(
                                                    'MAE',
                                                    analytics.mae,
                                                ).label
                                            }}
                                        </div>
                                    </div>

                                    <div
                                        class="text-sm text-slate-900 dark:text-slate-100"
                                    >
                                        <strong>Low:</strong> Predictions are
                                        usually close<br />
                                        <strong>High:</strong> Predictions
                                        frequently off by a larger margin<br />
                                        <span
                                            class="text-slate-900 dark:text-slate-400"
                                            >→ Tells us the average mistake
                                            size</span
                                        >
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader
                                class="mb-1 text-4xl font-bold text-slate-900 dark:text-slate-100"
                            >
                                RMSE
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-start gap-6">
                                    <div>
                                        <div
                                            class="text-lg font-semibold text-slate-900 dark:text-slate-400"
                                        >
                                            {{ props.analytics?.rmse ?? '--' }}
                                        </div>
                                        <div
                                            v-if="props.analytics?.rmse"
                                            :class="[
                                                'text-sm font-semibold',
                                                getMetricStatus(
                                                    'RMSE',
                                                    analytics.rmse,
                                                ).color,
                                            ]"
                                        >
                                            {{
                                                getMetricStatus(
                                                    'RMSE',
                                                    analytics.rmse,
                                                ).label
                                            }}
                                        </div>
                                    </div>

                                    <div
                                        class="text-sm text-slate-900 dark:text-slate-100"
                                    >
                                        <strong>RMSE ≈ MAE:</strong> Errors are
                                        consistent <br />
                                        <strong>RMSE > MAE:</strong> Large
                                        failures occasionally<br />
                                        <span
                                            class="text-slate-900 dark:text-slate-400"
                                            >→ Shows how bad predictions get
                                            when the model is wrong.</span
                                        >
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader
                                class="mb-1 text-4xl font-bold text-slate-900 dark:text-slate-100"
                            >
                                R²
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-start gap-6">
                                    <div>
                                        <div
                                            class="text-lg font-semibold text-slate-900 dark:text-slate-400"
                                        >
                                            {{ props.analytics?.r2 ?? '--' }}
                                        </div>
                                        <div
                                            v-if="props.analytics?.r2"
                                            :class="[
                                                'text-sm font-semibold',
                                                getMetricStatus(
                                                    'R2',
                                                    analytics.r2,
                                                ).color,
                                            ]"
                                        >
                                            {{
                                                getMetricStatus(
                                                    'R2',
                                                    analytics.r2,
                                                ).label
                                            }}
                                        </div>
                                    </div>

                                    <div
                                        class="text-sm text-slate-900 dark:text-slate-100"
                                    >
                                        <strong>1.0: Perfect </strong> <br />
                                        <strong
                                            >0.0: Equal to guessing the mean
                                        </strong>
                                        <br />
                                        <strong
                                            >10 > Samples = R2 is
                                            unstable</strong
                                        >
                                        <br />
                                        <span
                                            class="text-slate-900 dark:text-slate-400"
                                            >→ Shows how bad predictions get
                                            when the model is wrong.</span
                                        >
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </CardContent>
            </Card>
        </div>
        <div class="flex justify-center p-4">
            <ResidualScatterPlot
                :points="props.residualScatter?.points ?? []"
            />
        </div>
        <div class="flex justify-center p-4">
            <ActualVsPredScatterPlot
                :points="
                    (props.residualScatter?.points ?? []).map((p) => ({
                        predicted: p.x,
                        actual: p.x - p.y, // Convert residual back to actual
                    }))
                "
            />
        </div>
        <div
            v-if="showRunDataModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="showRunDataModal = false"
        >
            <div
                class="flex max-h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border bg-white shadow-xl
           dark:border-slate-700 dark:bg-slate-900"
                role="dialog"
                aria-modal="true"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b px-6 py-4
             dark:border-slate-700"
                >
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Run Results
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Total runs: {{ totalPredictions }}
                        </p>
                    </div>
                    <button
                        @click="showRunDataModal = false"
                        class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200
               dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        X
                    </button>
                </div>
                <!-- Table -->
                <div class="flex-1 overflow-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead
                            class="sticky top-0 z-10 border-b bg-white/95 backdrop-blur
                 dark:border-slate-700 dark:bg-slate-900/95"
                        >
                        <tr>
                            <th class="px-6 py-3 font-medium text-slate-600 dark:text-slate-300">ID</th>
                            <th class="px-6 py-3 font-medium text-slate-600 dark:text-slate-300">Date</th>
                            <th class="px-6 py-3 font-medium text-slate-600 dark:text-slate-300">Inputs</th>
                            <th class="px-6 py-3 font-medium text-slate-600 dark:text-slate-300">Predicted</th>
                            <th class="px-6 py-3 font-medium text-slate-600 dark:text-slate-300">Actual</th>
                            <th class="px-6 py-3 font-medium text-slate-600 dark:text-slate-300">Residual</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="run in runResults"
                            :key="run.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/40"
                        >
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">
                                {{ run.id }}
                            </td>

                            <td class="px-6 py-3 whitespace-nowrap text-slate-700 dark:text-slate-200">
                                {{ new Date(run.created_at).toLocaleString() }}
                            </td>

                            <td class="px-6 py-3 max-w-[420px]">
                                <div
                                    class="truncate text-slate-600 dark:text-slate-300"
                                    :title="JSON.stringify(parseJsonSafe(run.inputs), null, 2)"
                                >
                                    {{ formatInputsPreview(run.inputs) }}
                                </div>
                            </td>

                            <td class="px-6 py-3 font-medium text-emerald-700 dark:text-emerald-400">
                                {{ toNumber(run.result) ?? '—' }}
                            </td>

                            <td class="px-6 py-3 font-medium text-rose-700 dark:text-rose-400">
                                {{ toNumber(run.actual) ?? '—' }}
                            </td>

                            <td class="px-6 py-3 font-medium text-slate-800 dark:text-slate-100">
                                {{
                                    (toNumber(run.actual) !== null && toNumber(run.result) !== null)
                                        ? (toNumber(run.actual)! - toNumber(run.result)!).toFixed(3)
                                        : '—'
                                }}
                            </td>
                        </tr>

                        <tr v-if="!runResults?.length">
                            <td colspan="6" class="px-6 py-6 text-center text-slate-500 dark:text-slate-400">
                                No run results yet.
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between border-t px-6 py-4 dark:border-slate-700">

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-slate-400 dark:text-slate-500">Export:</span>

                        <button
                            class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-sm
                   font-medium text-slate-700 transition-colors hover:bg-slate-50
                   disabled:cursor-not-allowed disabled:opacity-40
                   dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                       a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                            </svg>
                            CSV
                        </button>

                        <button
                            class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-sm
                   font-medium text-slate-700 transition-colors hover:bg-slate-50
                   disabled:cursor-not-allowed disabled:opacity-40
                   dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 10h18M3 14h18M10 3v18M3 3h18v18H3V3z"/>
                            </svg>
                            Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div
            v-if="showSettingsModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="showSettingsModal = false; confirmingDelete = false"
        >
            <div
                class="flex w-full max-w-lg flex-col overflow-hidden rounded-2xl border bg-white shadow-xl
               dark:border-slate-700 dark:bg-slate-900"
                role="dialog"
                aria-modal="true"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b px-6 py-4 dark:border-slate-700">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Model Settings
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ model.name }}</p>
                    </div>
                    <button
                        @click="showSettingsModal = false; confirmingDelete = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors dark:hover:text-slate-200"
                    >
                        ✕
                    </button>
                </div>

                <div class="flex flex-col gap-4 px-6 py-5">
                    <!-- Status toggle -->
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3
                        dark:border-slate-700">
                        <div>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Model Status</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Currently
                                <span :class="settingsStatus === 'active'
                            ? 'text-emerald-600 dark:text-emerald-400'
                            : 'text-slate-400'">
                            {{ settingsStatus }}
                        </span>
                            </p>
                        </div>
                        <button
                            @click="toggleStatus"
                            :class="[
                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2',
                        'border-transparent transition-colors duration-200 focus:outline-none',
                        settingsStatus === 'active'
                            ? 'bg-emerald-500'
                            : 'bg-slate-300 dark:bg-slate-600'
                    ]"
                        >
                    <span
                        :class="[
                            'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow',
                            'transform transition duration-200',
                            settingsStatus === 'active' ? 'translate-x-5' : 'translate-x-0'
                        ]"
                    />
                        </button>
                    </div>

                    <!-- Model info -->
                    <div class="divide-y rounded-xl border border-slate-200 dark:border-slate-700 dark:divide-slate-700">
                        <div class="flex items-start justify-between px-4 py-3">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Type</span>
                            <span class="text-xs text-slate-800 dark:text-slate-100 text-right">{{ model.type ?? '—' }}</span>
                        </div>
                        <div class="flex items-start justify-between px-4 py-3">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Target</span>
                            <span class="text-xs text-slate-800 dark:text-slate-100 text-right">{{ model.target ?? '—' }}</span>
                        </div>
                        <div class="flex items-start justify-between px-4 py-3">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Required Parameters</span>
                            <span class="text-xs text-slate-800 dark:text-slate-100 text-right max-w-[60%]">
                        {{ model.required_parameters ?? '—' }}</span>
                        </div>
                        <div class="flex items-start justify-between px-4 py-3">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Model File</span>
                            <span class="text-xs text-slate-800 dark:text-slate-100 text-right max-w-[60%] break-all">
                        {{ model.path ?? '—' }}</span>
                        </div>
                    </div>

                    <!-- Delete button -->
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3
                        dark:border-rose-900/50 dark:bg-rose-950/20">
                        <p class="text-sm font-medium text-rose-700 dark:text-rose-400">Danger Zone</p>
                        <p class="mt-0.5 text-xs text-rose-600/80 dark:text-rose-400/70">
                            Permanently deletes this model and all its run results. This cannot be undone.
                        </p>
                        <button
                            v-if="!confirmingDelete"
                            @click="confirmingDelete = true"
                            class="mt-3 rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-medium
                           text-rose-600 transition-colors hover:bg-rose-50
                           dark:border-rose-800 dark:bg-transparent dark:text-rose-400 dark:hover:bg-rose-900/30"
                        >
                            Delete Model
                        </button>
                        <div v-else class="mt-3 flex items-center gap-2">
                            <span class="text-xs text-rose-600 dark:text-rose-400">Are you sure?</span>
                            <button
                                @click="deleteModel"
                                class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white
                               hover:bg-rose-700 transition-colors"
                            >
                                Yes, delete
                            </button>
                            <button
                                @click="confirmingDelete = false"
                                class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium
                               text-slate-600 hover:bg-slate-50 transition-colors
                               dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped></style>
