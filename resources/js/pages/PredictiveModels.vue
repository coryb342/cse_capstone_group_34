<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Calendar,
    ChevronRight,
    Plus,
    Bot,
    MonitorCheck,
    Target,
    Sparkle,
} from 'lucide-vue-next';
import { usePage, useForm } from '@inertiajs/vue3';
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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
import type { BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Models',
        href: '/predictive-models',
    },
];

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

function getAverageAccuracy(models) {
    if (models.length === 0){
        return '--';
    }
    // let sum_of_accuracies = 0;
    // for (const model of models){
    //     sum_of_accuracies += model.accuracy;
    // }
    // return sum_of_accuracies/models.length;
    let sumAccuracy = 0;
    for (const model of models){
        sumAccuracy += getAccuracyForModel(model.id);
    }
    return sumAccuracy/models.length;
}

function getAccuracyForModel(modelId){
    return props.modelData.find(m => m.model.id === modelId).accuracy;
}

const page = usePage();

const props = defineProps({
    models: Array,
    total_predictions: Number,
    modelData: Object,
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const isDialogOpen = ref(false);
const show = ref(false);

const form = useForm({
    model_name: '',
    model_description: '',
    required_parameters: '',
    target: '',
    model_type: '',
    model_accuracy: '',
    last_trained_on: '',
    model_file: null,
});

function submit() {
    form.post(route('predictive-models-upload'), {
        onSuccess: () => {
            isDialogOpen.value = false
            form.reset()
        },
    });
}

watch(
    () => page.props.flash.success,
    (newVal) => {
        if (newVal) {
            show.value = true;
            setTimeout(() => {
                show.value = false;
            }, 3000);
        }
    },
    { immediate: true },
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="page.props.errors">
            <div v-for="(index, error) in page.props.errors" :key="index">
                <span class="text-red-600">{{ error }}</span>
            </div>
        </div>
        <transition name="fade">
            <div
                v-if="show && $page.props.flash.success"
                class="fixed top-4 right-4 rounded bg-green-600 px-6 py-3 text-white shadow-lg"
            >
                {{ page.props.flash.success }}
            </div>
        </transition>
        <div class="min-h-screen p-8">
            <Card>
                <CardHeader class="flex justify-between">
                    <CardTitle>
                        <h1
                            class="mb-2 text-4xl font-bold text-slate-900 dark:text-white"
                        >
                            Predictive Models
                        </h1>
                        <p class="text-slate-600 dark:text-slate-400">
                            Manage and monitor your machine learning models
                        </p>
                    </CardTitle>
                    <Dialog
                        v-model:open="isDialogOpen"
                        v-if="page.props.auth.user.is_admin"
                    >
                        <DialogTrigger as-child>
                            <Button @click="isDialogOpen = true">
                                <Plus class="mr-2 h-4 w-4" />
                                Upload Model
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Upload a Model</DialogTitle>
                                <DialogDescription
                                >Use this form to add a new Predictive
                                    Model</DialogDescription
                                >
                                <Form
                                    @submit.prevent="submit"
                                    enctype="multipart/form-data"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        :value="csrfToken"
                                    />
                                    <div>
                                        <div class="grid p-2">
                                            <Label
                                                for="model_name"
                                                class="mb-1 text-left"
                                            >
                                                Model Name
                                            </Label>
                                            <Input
                                                v-model="form.model_name"
                                                required
                                                id="model_name"
                                                name="model_name"
                                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                                            />
                                        </div>
                                        <div class="grid p-2">
                                            <Label
                                                for="model_description"
                                                class="mb-1 text-left"
                                            >
                                                Model Description
                                            </Label>
                                            <textarea
                                                v-model="
                                                            form.model_description
                                                        "
                                                required
                                                type="text"
                                                id="model_description"
                                                name="model_description"
                                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                                            />
                                        </div>
                                        <div class="grid p-2">
                                            <Label
                                                for="required_parameters"
                                                class="mb-1 grid text-left"
                                            >
                                                Required Parameters
                                                <Label
                                                    required
                                                    class="text-sm text-slate-900 dark:text-slate-500"
                                                >Enter the inputs for
                                                    the model in the order
                                                    the model expects
                                                    separating by
                                                    commas.</Label
                                                >
                                            </Label>
                                            <Input
                                                v-model="
                                                            form.required_parameters
                                                        "
                                                id="required_parameters"
                                                name="required_parameters"
                                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                                                placeholder="ex. Flow, River Levels, Rainfall"
                                            />
                                        </div>
                                        <div class="grid p-2">
                                            <Label
                                                for="target"
                                                class="mb-1 grid text-left"
                                            >
                                                Target
                                                <Label
                                                    required
                                                    class="text-sm text-slate-900 dark:text-slate-500"
                                                >The name of the Target value this model predicts</Label
                                                >
                                            </Label>
                                            <Input
                                                v-model="
                                                            form.target
                                                        "
                                                id="target"
                                                name="target"
                                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                                                placeholder="ex. Influent Flow"
                                            />
                                        </div>
                                        <div class="grid p-2">
                                            <Label
                                                for="model_type"
                                                class="mb-1 text-left"
                                            >
                                                Model Type
                                            </Label>
                                            <Input
                                                v-model="form.model_type"
                                                required
                                                id="model_type"
                                                name="model_type"
                                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                                            />
                                        </div>
                                        <div class="grid p-2">
                                            <Label
                                                for="model_accuracy"
                                                class="mb-1 grid text-left"
                                            >
                                                Accuracy
                                                <Label
                                                    class="text-sm text-slate-900 dark:text-slate-500"
                                                >Enter the Accuracy of
                                                    the model if it is
                                                    known.</Label
                                                >
                                            </Label>
                                            <Input
                                                v-model="
                                                            form.model_accuracy
                                                        "
                                                type="number"
                                                id="model_accuracy"
                                                name="model_accuracy"
                                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400"
                                            />
                                        </div>
                                        <div class="grid p-2">
                                            <Label
                                                for="last_trained_on"
                                                class="mb-1 grid text-left"
                                            >
                                                Date Last Trained
                                                <Label
                                                    class="text-sm text-slate-900 dark:text-slate-500"
                                                >Default will be today
                                                    if no date is
                                                    selected.</Label
                                                >
                                            </Label>
                                            <Input
                                                v-model="
                                                            form.last_trained_on
                                                        "
                                                type="date"
                                                id="last_trained_on"
                                                name="last_trained_on"
                                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400 dark:bg-slate-700"
                                            />
                                        </div>
                                        <div class="grid p-2">
                                            <Label
                                                for="model_file"
                                                class="mb-1 text-left"
                                            >
                                                Model File
                                            </Label>
                                            <Input
                                                required
                                                type="file"
                                                @change="
                                                            (e) =>
                                                                (form.model_file =
                                                                    e.target.files[0])
                                                        "
                                                id="model_file"
                                                name="model_file"
                                                class="col-span-3 rounded border border-slate-900 px-2 py-1 dark:border-slate-400 dark:bg-slate-700"
                                                accept=".joblib,.pkl,.pickle"
                                            />
                                        </div>
                                        <div class="mt-5 mr-2 mb-5 flex justify-end">
                                            <Button type="submit">
                                                Upload Model
                                            </Button>
                                        </div>
                                    </div>
                                </Form>
                            </DialogHeader>
                        </DialogContent>
                    </Dialog>
                </CardHeader>
                <div class="p-8">
                    <div>
                        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                            <Card>
                                <CardContent class="pt-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="mb-1 text-sm text-slate-600 dark:text-white">
                                                Total Models
                                            </p>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-400">
                                                {{ models.length }}
                                            </p>
                                        </div>
                                        <Bot class="h-8 w-8 text-blue-500" />
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="pt-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="mb-1 text-sm text-slate-600 dark:text-white">
                                                Active Models
                                            </p>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-400">
                                                {{ models.filter((m) => m.status === 'active',).length }}
                                            </p>
                                        </div>
                                        <MonitorCheck class="h-8 w-8 text-green-500" />
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="pt-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="mb-1 text-sm text-slate-600 dark:text-white">
                                                Avg Accuracy
                                            </p>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-400">
                                                {{ models.length > 0 ? getAverageAccuracy(models).toFixed(2) + '%' : '--' }}
                                            </p>
                                        </div>
                                        <Target class="h-8 w-8 text-purple-500" />
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="pt-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="mb-1 text-sm text-slate-600 dark:text-white">
                                                Total Predictions
                                            </p>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-400">
                                                {{ total_predictions ? total_predictions : '--'  }}
                                            </p>
                                        </div>
                                        <Sparkle class="h-8 w-8 text-orange-500" />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <Card
                            class="hover:shadow-lg transition-shadow cursor-pointer group"
                            v-for="model in props.models" :key="model.id"
                            @click="router.visit(route('predictive-models.show', model.id))"
                        >
                            <CardHeader>
                                <div class="mb-2 flex items-start justify-between">
                                    <CardTitle class="text-lg transition-colors group-hover:text-blue-600">
                                        {{ model.name }}
                                    </CardTitle>
                                    <ChevronRight class="h-5 w-5 text-slate-400 transition-all group-hover:translate-x-1 group-hover:text-blue-600" />
                                </div>
                                <CardDescription class="text-sm">{{
                                    model.description
                                }}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <Badge variant="secondary">
                                            {{ model.type }}
                                        </Badge>
                                        <div class="flex items-center gap-2">
                                            <div :class="getStatusColor(model.status)" class="h-2 w-2 animate-pulse rounded-full" />
                                            <span class="text-sm text-slate-600 capitalize">
                                                {{ model.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 pt-2">
                                        <div>
                                            <p class="mb-1 text-xs text-slate-500">
                                                Accuracy
                                            </p>
                                            <p class="text-2xl font-semibold text-slate-900 dark:text-slate-400">
                                                {{ getAccuracyForModel(model.id) ? getAccuracyForModel(model.id).toFixed(2) + '%' : '--' }}
    <!--                                            {{model.accuracy ? model.accuracy + '%' : '&#45;&#45;'}}-->
                                            </p>
                                        </div>
                                        <div>
                                            <p class="mb-1 text-xs text-slate-500">
                                                Prediction
                                            </p>
                                            <p class="text-lg font-semibold text-slate-900 dark:text-slate-400">
                                                Need to Implement Results first
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center border-t pt-2 text-xs text-slate-500">
                                        <Calendar class="mr-1 h-3 w-3" />
                                        Last trained: {{ model.last_trained_on }}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped></style>
