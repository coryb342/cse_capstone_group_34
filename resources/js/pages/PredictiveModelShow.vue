<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ResidualScatter from '@/components/charts/residualScatterPlot.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Settings,
    RotateCw,
    Play,
    HardDriveDownload,
    CalendarDays,
    ChartLine,
    TrendingUp,
    ChartScatter,
    Target,
} from 'lucide-vue-next';
import {  usePage, useForm } from '@inertiajs/vue3';
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
} from '@/components/ui/dialog'
import { route } from 'ziggy-js';

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
});

const getStatusColor = (status) => {
    switch(status) {
        case 'active': return 'bg-green-500';
        case 'inactive': return 'bg-gray-400';
        default: return 'bg-gray-400';
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
            if (value < 5) return { label: 'Needs Work', color: 'text-yellow-600' };
            return { label: 'Poor', color: 'text-red-600' };

        case 'MSE':
            if (value < 10) return { label: 'Great', color: 'text-green-600'};
            if (value < 25) return { label: 'Good', color: 'text-blue-600'};
            if (value < 50) return { label: 'Needs Work', color: 'text-yellow-600'};
            return { label: 'Poor', color: 'text-red-600' };

        case 'RMSE':
            if (value < 2) return { label: 'Great', color: 'text-green-600' };
            if (value < 3) return { label: 'Good', color: 'text-blue-600' };
            if (value < 5) return { label: 'Needs Work', color: 'text-yellow-600' };
            return { label: 'Poor', color: 'text-red-600' };

        case 'R2':
            if (value > 0.85) return { label: 'Excellent', color: 'text-green-600' };
            if (value > 0.7) return { label: 'Very Good', color: 'text-blue-600' };
            if (value > 0.5) return { label: 'Good', color: 'text-yellow-600' };
            if (value > 0.3) return { label: 'Moderate', color: 'text-orange-600' };
            return { label: 'Weak', color: 'text-red-600' };

        default:
            return { label: '', color: 'text-gray-600' };
    }
};

const form = useForm({
    parameters: [],
    actual: "",
    model_id: null,
});

function submit() {
    isLoadingResult.value = true;
    hasViewedResult.value = false;
    form.post(route('predictive-models.run'), {
        onSuccess: () => {
            isDialogOpen.value = true
            isLoadingResult.value = false
            form.reset()
        },
    })
}

function resetDialogue() {
    hasViewedResult.value = true;
    isDialogOpen.value = true;
}

const page = usePage();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-8">
            <Card>
                <CardHeader>
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <CardTitle>
                                <h1 class="mb-2 text-4xl font-bold text-slate-900 dark:text-white">
                                    {{ model.name }}
                                </h1>
                            </CardTitle>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button variant="outline">
                                <RotateCw class="w-4 h-4 mr-2" />
                                Retrain
                            </Button>
                            <Button variant="outline">
                                <HardDriveDownload class="w-4 h-4 mr-2" />
                                Export
                            </Button>
                            <Button variant="outline">
                                <Settings class="w-4 h-4 mr-2" />
                                Settings
                            </Button>
                            <Dialog v-model:open="isDialogOpen">
                                <DialogTrigger as-child>
                                    <Button @click="resetDialogue">
                                        <Play class="w-4 h-4 mr-2" />
                                        Run Prediction
                                    </Button>
                                </DialogTrigger>
                                <DialogContent
                                    v-if="((!page.props.flash.model_run_result && !page.props.flash.prediction_failed) || hasViewedResult) && !isLoadingResult"
                                    class="max-h-[90vh] overflow-y-auto"
                                >
                                    <DialogHeader>
                                        <DialogTitle>Run Prediction</DialogTitle>
                                        <DialogDescription>Use this form to run a prediction</DialogDescription>
                                    </DialogHeader>
                                        <Form
                                            @submit.prevent="submit"
                                        >
                                            <input
                                                type="hidden"
                                                name="model_id"
                                                :value="form.model_id = model.id"
                                            />
                                            <div v-for="(parameter, index) in JSON.parse(model.required_parameters)" :key="index">
                                                <div class="grid p-2">
                                                    <Label :for="'parameter_' + index" class="text-left mb-1">
                                                        {{ parameter.toString().toUpperCase() }}
                                                    </Label>
                                                    <Input required
                                                           :id="'parameter_' + index"
                                                           :name="'parameters[' + index + ']'"
                                                           v-model="form.parameters[index]"
                                                           class="col-span-3 border rounded dark:border-slate-400 border-slate-900 px-2 py-1"/>
                                                </div>
                                            </div>
                                            <div class="grid p-2 ">
                                                <Label for="actual" class="mb-1 flex flex-col items-start text-left">
                                                    <span class="text-left">Actual</span>
                                                    <span class="text-xs text-left">Entering an Actual value will update the Model Accuracy</span>
                                                </Label>
                                                <Input
                                                    id="actual"
                                                    name="actual"
                                                    v-model="form.actual"
                                                    class="col-span-3 border rounded dark:border-slate-400 border-slate-900 px-2 py-1"/>
                                            </div>
                                            <div class="flex justify-end mt-5 mb-5 mr-2">
                                                <Button type="submit">
                                                    Run
                                                </Button>
                                            </div>
                                        </Form>
                                </DialogContent>
                                <DialogContent
                                    v-else-if="isLoadingResult"
                                    class="max-h-[90vh] overflow-y-auto"
                                >
                                    <DialogHeader>
                                        <DialogTitle>Running Prediction...</DialogTitle>
                                        <DialogDescription>Doing some magic</DialogDescription>
                                    </DialogHeader>
                                    <div class="flex justify-center items-center h-24">
                                        <svg
                                            class="animate-spin h-8 w-8 text-blue-600"
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
                                    v-else-if="page.props.flash.prediction_failed"
                                    class="max-h-[90vh] overflow-y-auto"
                                >
                                    <DialogHeader>
                                        <DialogTitle class="text-red-600">
                                            Prediction Failed!
                                        </DialogTitle>
                                        <DialogDescription>Error Details Below</DialogDescription>
                                    </DialogHeader>
                                    <span class="text-lg font-bold">Inputs Given: </span>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm" v-for="(parameter, index) in page.props.flash.mapped_parameters" :key="index">
                                            {{index}}: {{parameter}}
                                        </span>
                                    </div>
                                    <span class="text-lg font-bold">Error:</span>
                                    <span>{{page.props.flash.prediction_failed}}</span>
                                </DialogContent>
                                <DialogContent
                                    v-else
                                    class="max-h-[90vh] overflow-y-auto"
                                >
                                    <DialogHeader>
                                        <DialogTitle class="text-green-500">
                                            Success!
                                        </DialogTitle>
                                        <DialogDescription>Result Details Below</DialogDescription>
                                    </DialogHeader>
                                    <span class="text-lg font-bold">Inputs Given: </span>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm" v-for="(parameter, index) in page.props.flash.mapped_parameters" :key="index">
                                            {{index}}: {{parameter}}
                                        </span>
                                    </div>
                                    <span class="text-lg font-bold">Result:</span>
                                    <span>Predicted {{model.target}}: {{Number(page.props.flash.model_run_result).toFixed(2)}}</span>
                                </DialogContent>
                            </Dialog>

                        </div>
                    </div>
                    <div>

                        <p class="text-sm text-slate-900 dark:text-white mb-6 size-5/12">{{model.description}} </p>
                    </div>
                    <div class="flex items-center gap-6 text-sm text-slate-600 dark:text-slate-400 ">
                        <div class="flex items-center gap-2">
                            <CalendarDays class="w-4 h-4" />
                            <span>Created: {{ props.modelCreatedDate}}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <CalendarDays class="w-4 h-4" />
                            <span>Last trained: {{ props.modelLastTrainedDate }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <ChartLine class="w-4 h-4" />
                            <span>Type: {{ model.type }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <div :class="getStatusColor(model.status)" class="w-2 h-2 rounded-full animate-pulse"/>
                            <Badge variant="secondary">{{ model.status }}</Badge>
                            <span class="text-sm text-slate-500">{{ model.version }}</span>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                        <Card>
                            <CardContent class="p-6">
                                <div class="flex items-start justify-between mb-2">
                                    <span class="text-2xl font-medium text-slate-600 dark:text-white">Accuracy</span>
                                    <TrendingUp class="w-10 h-10 text-green-500" />
                                </div>
                                <div class="text-4xl font-bold text-slate-900 dark:text-slate-400 mb-1">
                                    {{props.analytics?.accuracy ? props.analytics.accuracy + '%' : '--'}}
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
                                <div class="flex items-start justify-between mb-2">
                                    <span class="text-2xl font-medium text-slate-600 dark:text-white">Total Predictions</span>
                                    <ChartScatter class="w-10 h-10 text-blue-500" />
                                </div>
                                <div class="text-4xl font-bold text-slate-900 dark:text-slate-400 mb-1">
                                    {{props.totalPredictions}}
                                </div>
                                <div class="text-sm text-slate-500">
                                    Last 30 days
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <Card>
                                <CardHeader class="text-4xl font-bold text-slate-900 dark:text-slate-100 mb-1"> MSE </CardHeader>
                                <CardContent>
                                    <div class="flex items-start gap-6">
                                        <div>
                                            <div class="text-lg font-semibold text-slate-900 dark:text-slate-400">
                                                {{props.analytics?.mse ?? '--'}}
                                            </div>
                                            <div v-if="props.analytics?.mse" :class="['text-sm font-semibold', getMetricStatus('MSE', analytics.mse).color]">
                                                {{ getMetricStatus('MSE', analytics.mse).label }}
                                            </div>
                                        </div>

                                        <div class="text-sm text-slate-900 dark:text-slate-100">
                                            <strong>Low:</strong> No extreme failures<br>
                                            <strong>High:</strong> Occasionally makes bad predictions<br>
                                            <span class="text-slate-900 dark:text-slate-400">→ Highlights where model can fail badly</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader class="text-4xl font-bold text-slate-900 dark:text-slate-100 mb-1"> MAE </CardHeader>
                                <CardContent>
                                    <div class="flex items-start gap-6">
                                        <div>
                                            <div class="text-lg font-semibold text-slate-900 dark:text-slate-400">
                                                {{props.analytics?.mae ?? '--'}}
                                            </div>
                                            <div v-if="props.analytics?.mae" :class="['text-sm font-semibold', getMetricStatus('MAE', analytics.mae).color]">
                                                {{ getMetricStatus('MAE', analytics.mae).label }}
                                            </div>
                                        </div>

                                        <div class="text-sm text-slate-900 dark:text-slate-100">
                                            <strong>Low:</strong> Predictions are usually close<br>
                                            <strong>High:</strong> Predictions frequently off by a larger margin<br>
                                            <span class="text-slate-900 dark:text-slate-400">→ Tells us the average mistake size</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader class="text-4xl font-bold text-slate-900 dark:text-slate-100 mb-1"> RMSE </CardHeader>
                                <CardContent>
                                    <div class="flex items-start gap-6">
                                        <div>
                                            <div class="text-lg font-semibold text-slate-900 dark:text-slate-400">
                                                {{props.analytics?.rmse ?? '--'}}
                                            </div>
                                            <div v-if="props.analytics?.rmse" :class="['text-sm font-semibold', getMetricStatus('RMSE', analytics.rmse).color]">
                                                {{ getMetricStatus('RMSE', analytics.rmse).label }}
                                            </div>
                                        </div>

                                        <div class="text-sm text-slate-900 dark:text-slate-100">
                                            <strong>RMSE ≈ MAE:</strong> Errors are consistent <br>
                                            <strong>RMSE > MAE:</strong> Large failures occasionally<br>
                                            <span class="text-slate-900 dark:text-slate-400">→ Shows how bad predictions get when the model is wrong.</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader class="text-4xl font-bold text-slate-900 dark:text-slate-100 mb-1"> R² </CardHeader>
                                <CardContent>
                                    <div class="flex items-start gap-6">
                                        <div>
                                            <div class="text-lg font-semibold text-slate-900 dark:text-slate-400">
                                                {{props.analytics?.r2 ?? '--'}}
                                            </div>
                                            <div v-if="props.analytics?.r2" :class="['text-sm font-semibold', getMetricStatus('R2', analytics.r2).color]">
                                                {{ getMetricStatus('R2', analytics.r2).label }}
                                            </div>
                                        </div>

                                        <div class="text-sm text-slate-900 dark:text-slate-100">
                                            <strong>1.0: Perfect: </strong> <br>
                                            <strong>0.0: Equal to guessing the mean: </strong> <br>
                                            <strong>Less than 0 is worse than guessing:</strong> <br>
                                            <span class="text-slate-900 dark:text-slate-400">→ Shows how bad predictions get when the model is wrong.</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped></style>
