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
import { usePage, useForm, Head } from '@inertiajs/vue3';
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
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
    if (models.length === 0) {
        return '--';
    }
    // let sum_of_accuracies = 0;
    // for (const model of models){
    //     sum_of_accuracies += model.accuracy;
    // }
    // return sum_of_accuracies/models.length;
    let sumAccuracy = 0;
    let models_considered = 0;
    for (const model of models) {
        if (
            getAccuracyForModel(model.id) != null &&
            getAccuracyForModel(model.id) != 0
        ) {
            sumAccuracy += getAccuracyForModel(model.id);
            models_considered += 1;
        }
    }
    return sumAccuracy / models_considered;
}

function getAccuracyForModel(modelId) {
    const m = props.models?.find((x) => x.id === modelId);
    return m?.analytics?.accuracy ?? null;
}

const page = usePage();

const props = defineProps({
    uploaded_model: Object,
    models: Array,
    total_predictions: String,
    modelData: Object,
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const isDialogOpen = ref(false);
const isLoading = ref(false);
const showConfirmation = ref(false);
const uploadedModel = ref(null);
const show = ref(false);
const confirmingDelete = ref(false);

const form = useForm({
    model_name: '',
    model_description: '',
    model_accuracy: '',
    last_trained_on: '',
    model_file: null,
});

function submit() {
    isLoading.value = true;
    form.post(route('predictive-models-upload'), {
        onSuccess: (page) => {
            uploadedModel.value = page.props.flash.uploaded_model;
            showConfirmation.value = true;
            form.reset();
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
}

function acceptModel() {
    isDialogOpen.value = false;
    showConfirmation.value = false;
}

function discardModel() {
    router.delete(route('predictive-models.destroy', uploadedModel.value.id), {
        onSuccess: () => {
            isDialogOpen.value = false;
            showConfirmation.value = false;
            confirmingDelete.value = false;
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
    <Head>
        <title>Predictive Models</title>
        <meta
            name="description"
            content="Organizations uploaded models page where you can select to view a models analytics, run predictions, and export results."
        />
    </Head>
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
                        v-if="
                            page.props.auth.user_roles.some(
                                (role: { name: string }) =>
                                    role.name === 'Admin',
                            )
                        "
                    >
                        <DialogTrigger as-child>
                            <Button @click="isDialogOpen = true">
                                <Plus class="mr-2 h-4 w-4" />
                                Upload Model
                            </Button>
                        </DialogTrigger>
                        <DialogContent
                            v-if="isLoading"
                            class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden overflow-y-auto rounded-2xl border bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                            role="dialog"
                            aria-modal="true"
                        >
                            <DialogHeader>
                                <DialogTitle
                                    >Gathering Model Info...</DialogTitle
                                >
                                <DialogDescription
                                    >Doing some magic</DialogDescription
                                >
                            </DialogHeader>
                            <div class="flex h-24 items-center justify-center">
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
                            v-else-if="showConfirmation"
                            class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden overflow-y-auto rounded-2xl border bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                            role="dialog"
                            aria-modal="true"
                        >
                            <DialogHeader>
                                <DialogTitle
                                    >Model Uploaded Successfully</DialogTitle
                                >
                                <DialogDescription
                                    >Please Verify Model Info</DialogDescription
                                >
                            </DialogHeader>
                            <div
                                v-if="showConfirmation"
                                class="rounded-2xl border px-3 py-4 dark:border-slate-700"
                            >
                                <Label>Name</Label>
                                <Input
                                    :model-value="uploadedModel.name"
                                    :disabled="true"
                                ></Input>

                                <Label>Description</Label>
                                <textarea
                                    :value="uploadedModel.description"
                                    :disabled="true"
                                    class="w-full rounded bg-slate-700/10 px-2 py-1 text-sm disabled:opacity-50"
                                />

                                <Label>Target</Label>
                                <Input
                                    :model-value="uploadedModel.target"
                                    :disabled="true"
                                ></Input>

                                <Label>Required Parameters</Label>
                                <Input
                                    v-for="(param, key) in JSON.parse(
                                        uploadedModel.required_parameters,
                                    )"
                                    :key="key"
                                    :model-value="param"
                                    :disabled="true"
                                    class="border-transparent"
                                ></Input>

                                <Label>Type</Label>
                                <Input
                                    :model-value="uploadedModel.type"
                                    :disabled="true"
                                ></Input>

                                <Label>Accuracy</Label>
                                <Input
                                    :model-value="
                                        uploadedModel.accuracy ?? 'Not Provided'
                                    "
                                    :disabled="true"
                                ></Input>

                                <Label>Last Trained On</Label>
                                <Input
                                    :model-value="
                                        uploadedModel.last_trained_on ??
                                        'Not Provided'
                                    "
                                    :disabled="true"
                                ></Input>
                            </div>
                            <DialogFooter>
                                <div class="flex justify-end gap-3">
                                    <Button
                                        @click="acceptModel"
                                        class="mt-3 rounded-lg border border-green-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 transition-colors hover:bg-green-50 dark:border-green-800 dark:bg-transparent dark:text-gray-400 dark:hover:bg-green-900/30"
                                    >
                                        Accept
                                    </Button>

                                    <button
                                        v-if="!confirmingDelete"
                                        @click="confirmingDelete = true"
                                        class="mt-3 rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-sm font-medium text-rose-600 transition-colors hover:bg-rose-50 dark:border-rose-800 dark:bg-transparent dark:text-rose-400 dark:hover:bg-rose-900/30"
                                    >
                                        Discard
                                    </button>
                                    <div
                                        v-else
                                        class="mt-3 flex items-center gap-2"
                                    >
                                        <span
                                            class="text-xs text-rose-600 dark:text-rose-400"
                                            >Are you sure?</span
                                        >
                                        <button
                                            @click="discardModel"
                                            class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-rose-700"
                                        >
                                            Yes, Discard
                                        </button>
                                        <button
                                            @click="confirmingDelete = false"
                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </DialogFooter>
                        </DialogContent>
                        <DialogContent
                            class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden overflow-y-auto rounded-2xl border bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
                            role="dialog"
                            aria-modal="true"
                            v-else
                        >
                            <DialogHeader>
                                <DialogTitle>Upload a Model</DialogTitle>
                                <DialogDescription
                                    >Use this form to add a new Predictive
                                    Model</DialogDescription
                                >
                            </DialogHeader>
                            <Form
                                v-if="
                                    !isLoading &&
                                    !showConfirmation &&
                                    isDialogOpen
                                "
                                @submit.prevent="submit"
                                enctype="multipart/form-data"
                            >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    :value="csrfToken"
                                />
                                <div
                                    class="rounded-2xl border px-3 py-3 dark:border-slate-700"
                                >
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
                                            v-model="form.model_description"
                                            required
                                            type="text"
                                            id="model_description"
                                            name="model_description"
                                            class="col-span-3 rounded border border-slate-900 px-2 py-1 text-sm dark:border-slate-400"
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
                                                >Enter the Accuracy of the model
                                                if it is known.</Label
                                            >
                                        </Label>
                                        <Input
                                            v-model="form.model_accuracy"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
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
                                                >Default will be today if no
                                                date is selected.</Label
                                            >
                                        </Label>
                                        <Input
                                            v-model="form.last_trained_on"
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
                                </div>
                                <DialogFooter class="pt-2">
                                    <Button
                                        type="submit"
                                        class="mt-3 rounded-lg border border-green-300 bg-white font-medium text-gray-600 transition-colors hover:bg-green-50 dark:border-green-800 dark:bg-transparent dark:text-gray-400 dark:hover:bg-green-900/30"
                                    >
                                        Submit
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>
                </CardHeader>
                <div class="p-8">
                    <div>
                        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                            <Card>
                                <CardContent class="pt-6">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <h1
                                                class="mb-1 text-sm text-slate-600 dark:text-white"
                                            >
                                                Total Models
                                            </h1>
                                            <h2
                                                class="text-2xl font-bold text-slate-900 dark:text-slate-400"
                                            >
                                                {{ models.length }}
                                            </h2>
                                        </div>
                                        <Bot class="h-8 w-8 text-blue-500" />
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="pt-6">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <h1
                                                class="mb-1 text-sm text-slate-600 dark:text-white"
                                            >
                                                Active Models
                                            </h1>
                                            <h2
                                                class="text-2xl font-bold text-slate-900 dark:text-slate-400"
                                            >
                                                {{
                                                    models.filter(
                                                        (m) =>
                                                            m.status ===
                                                            'active',
                                                    ).length
                                                }}
                                            </h2>
                                        </div>
                                        <MonitorCheck
                                            class="h-8 w-8 text-green-500"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="pt-6">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <h1
                                                class="mb-1 text-sm text-slate-600 dark:text-white"
                                            >
                                                Avg Accuracy
                                            </h1>
                                            <h2
                                                class="text-2xl font-bold text-slate-900 dark:text-slate-400"
                                            >
                                                {{
                                                    total_predictions > 0
                                                        ? getAverageAccuracy(
                                                              models,
                                                          ).toFixed(2) + '%'
                                                        : '--'
                                                }}
                                            </h2>
                                        </div>
                                        <Target
                                            class="h-8 w-8 text-purple-500"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardContent class="pt-6">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <h1
                                                class="mb-1 text-sm text-slate-600 dark:text-white"
                                            >
                                                Total Predictions
                                            </h1>
                                            <h2
                                                class="text-2xl font-bold text-slate-900 dark:text-slate-400"
                                            >
                                                {{
                                                    total_predictions
                                                        ? total_predictions
                                                        : '--'
                                                }}
                                            </h2>
                                        </div>
                                        <Sparkle
                                            class="h-8 w-8 text-orange-500"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <Card
                            class="group cursor-pointer transition-shadow hover:shadow-lg"
                            v-for="model in props.models"
                            :key="model.id"
                            role="link"
                            tabindex="0"
                            :aria-label="model.name"
                            @click="
                                router.visit(
                                    route('predictive-models.show', model.id),
                                )
                            "
                            @keydown.enter="
                                router.visit(
                                    route('predictive-models.show', model.id),
                                )
                            "
                            @keydown.space.prevent="
                                router.visit(
                                    route('predictive-models.show', model.id),
                                )
                            "

                        >
                            <CardHeader>
                                <div
                                    class="mb-2 flex items-start justify-between"
                                >
                                    <h1
                                        class="text-lg font-semibold transition-colors group-hover:text-blue-600 group-hover:dark:text-blue-400"
                                    >
                                        {{ model.name }}
                                    </h1>
                                    <ChevronRight
                                        class="h-5 w-5 text-slate-400 transition-all group-hover:translate-x-1 group-hover:text-blue-600 group-hover:dark:text-blue-400"
                                    />
                                </div>
                                <CardDescription class="text-sm">{{
                                    model.description
                                }}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-3">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <Badge variant="secondary">
                                            {{ model.type }}
                                        </Badge>
                                        <div class="flex items-center gap-2">
                                            <div
                                                :class="
                                                    getStatusColor(model.status)
                                                "
                                                class="h-2 w-2 animate-pulse rounded-full"
                                            />
                                            <span
                                                class="text-sm text-slate-900 dark:text-slate-400 capitalize"
                                            >
                                                {{ model.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 pt-2">
                                        <div>
                                            <h1
                                                class="mb-1 text-xs text-slate-900 dark:text-slate-400"
                                            >
                                                Accuracy
                                            </h1>
                                            <h2
                                                class="text-2xl font-semibold text-slate-900 dark:text-slate-400"
                                            >
                                                {{
                                                    model.analytics?.accuracy !=
                                                    null
                                                        ? Number(
                                                              model.analytics
                                                                  .accuracy,
                                                          ).toFixed(2) + '%'
                                                        : '--'
                                                }}
                                            </h2>
                                        </div>
                                        <div>
                                            <h1
                                                class="mb-1 text-xs text-slate-900 dark:text-slate-400"
                                            >
                                                Latest Prediction
                                            </h1>
                                            <h2
                                                class="text-lg font-semibold text-slate-900 dark:text-slate-400"
                                                v-if="model.latest_run_result"
                                            >
                                                {{
                                                    parseFloat(
                                                        JSON.parse(
                                                            model
                                                                .latest_run_result
                                                                .result,
                                                        ),
                                                    ).toFixed(2)
                                                }}
                                                <!--                                                    {{ parseFloat(JSON.parse(model.run_results.at(-1).result)).toFixed(2) }}-->
                                            </h2>
                                            <p
                                                class="text-lg font-semibold text-slate-900 dark:text-slate-400"
                                                v-else
                                            >
                                                --
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center border-t pt-2 text-xs text-slate-900 dark:text-slate-400"
                                    >
                                        <Calendar class="mr-1 h-3 w-3" />
                                        Last trained:
                                        {{ model.last_trained_on }}
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
