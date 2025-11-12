<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Calendar, ChevronRight, Plus, Bot, MonitorCheck, Target, Sparkle } from 'lucide-vue-next';
import {  usePage, useForm } from '@inertiajs/vue3';
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
} from '@/components/ui/dialog'
import { route } from 'ziggy-js';

const getStatusColor = (status) => {
    switch(status) {
        case 'active': return 'bg-green-500';
        case 'inactive': return 'bg-gray-400';
        default: return 'bg-gray-400';
    }
};

function getAverageAccuracy(models) {
    if (models.length === 0) {
        return '--'
    }
    let sum_of_accuracies = 0;
    for (const model of models) {
        sum_of_accuracies += model.accuracy;
    }
    return sum_of_accuracies / models.length;
}

const page = usePage();

const props = defineProps({
    models: Array,
    total_predictions: Number,
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const isDialogOpen = ref(false);
const show = ref(false);

const form = useForm({
    model_name: '',
    model_description: '',
    required_parameters: '',
    model_type: '',
    model_accuracy: '',
    last_trained_on: '',
    model_file: null,
})

function submit() {
    form.post(route('predictive-models-upload'), {
        onSuccess: () => {
            isDialogOpen.value = false  // Close dialog only if submission succeeds
            form.reset() // optionally reset form
        },
    })
}

watch(
    () => page.props.flash.success,
    (newVal) => {
        if (newVal) {
            show.value = true
            setTimeout(() => {
                show.value = false
            }, 3000)
        }
    },
    { immediate: true }
)

</script>

<template>
    <AppLayout>
        <div v-if="page.props.errors">
            <div v-for="(index, error) in page.props.errors" :key="index">
                <span class="text-red-600">{{ error }}</span>
            </div>
        </div>
        <transition name="fade">
            <div v-if="show && $page.props.flash.success"
                 class="fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded shadow-lg"
            >
                {{ page.props.flash.success }}
            </div>
        </transition>
        <div class="min-h-screen p-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2">Predictive Models</h1>
                            <p class="text-slate-600 dark:text-slate-400">Manage and monitor your machine learning models</p>
                        </div>
                        <Dialog v-model:open="isDialogOpen" v-if="page.props.auth.user.is_admin">
                            <DialogTrigger as-child>
                                <Button @click="isDialogOpen = true">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Upload Model
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Upload a Model</DialogTitle>
                                    <DialogDescription>Use this form to add a new Predictive Model</DialogDescription>
                                    <Form @submit.prevent="submit" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" :value="csrfToken" />
                                        <div>
                                            <div class="grid p-2">
                                                <Label for="model_name" class="text-left mb-1">
                                                    Model Name
                                                </Label>
                                                <Input v-model="form.model_name" required id="model_name" name="model_name" class="col-span-3 border rounded dark:border-slate-400 border-slate-900 px-2 py-1"/>
                                            </div>
                                            <div class="grid p-2">
                                                <Label for="model_description" class="text-left mb-1">
                                                    Model Description
                                                </Label>
                                                <textarea v-model="form.model_description" required type="text" id="model_description" name="model_description" class="col-span-3 border rounded dark:border-slate-400 border-slate-900 px-2 py-1" />
                                            </div>
                                            <div class="grid p-2">
                                                <Label for="required_parameters" class="text-left mb-1 grid">
                                                    Required Parameters
                                                    <Label required class="text-sm dark:text-slate-500 text-slate-900">Enter the inputs for the model in the order the model expects separating by commas.</Label>
                                                </Label>
                                                <Input v-model="form.required_parameters" id="required_parameters" name="required_parameters" class="col-span-3 border rounded dark:border-slate-400 border-slate-900 px-2 py-1" placeholder="ex. Flow, River Levels, Rainfall"/>
                                            </div>
                                            <div class="grid p-2">
                                                <Label for="model_type" class="text-left mb-1">
                                                    Model Type
                                                </Label>
                                                <Input v-model="form.model_type" required id="model_type" name="model_type" class="col-span-3 border rounded dark:border-slate-400 border-slate-900 px-2 py-1" />
                                            </div>
                                            <div class="grid p-2">
                                                <Label for="model_accuracy" class="text-left mb-1 grid">
                                                    Accuracy
                                                    <Label class="text-sm dark:text-slate-500 text-slate-900">Enter the Accuracy of the model if it is known.</Label>
                                                </Label>
                                                <Input v-model="form.model_accuracy" type="number" id="model_accuracy" name="model_accuracy" class="col-span-3 border rounded dark:border-slate-400 border-slate-900 px-2 py-1" />
                                            </div>
                                            <div class="grid p-2">
                                                <Label for="last_trained_on" class="text-left mb-1 grid">
                                                    Date Last Trained
                                                    <Label class="text-sm dark:text-slate-500 text-slate-900">Default will be today if no date is selected.</Label>
                                                </Label>
                                                <Input v-model="form.last_trained_on" type="date" id="last_trained_on" name="last_trained_on" class="col-span-3 border rounded dark:border-slate-400 border-slate-900 px-2 py-1 dark:bg-slate-700" />
                                            </div>
                                            <div class="grid p-2">
                                                <Label for="model_file" class="text-left mb-1">
                                                    Model File
                                                </Label>
                                                <Input required type="file" @change="e => form.model_file = e.target.files[0]" id="model_file" name="model_file" class="col-span-3 border rounded dark:border-slate-400 border-slate-900 px-2 py-1 dark:bg-slate-700" accept=".joblib,.pkl,.pickle"/>
                                            </div>
                                            <div class="flex justify-end mt-5 mb-5 mr-2">
                                                <Button type="submit">
                                                    Upload Model
                                                </Button>
                                            </div>
                                        </div>
                                    </Form>
                                </DialogHeader>
                            </DialogContent>
                        </Dialog>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <Card>
                            <CardContent class="pt-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-slate-600 dark:text-white mb-1">Total Models</p>
                                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-400">{{ models.length }}</p>
                                    </div>
                                    <Bot class="w-8 h-8 text-blue-500" />
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardContent class="pt-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-slate-600 dark:text-white mb-1">Active Models</p>
                                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-400">
                                            {{models.filter(m => m.status === 'active').length}}
                                        </p>
                                    </div>
                                    <MonitorCheck class="w-8 h-8 text-green-500" />
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardContent class="pt-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-slate-600 dark:text-white mb-1">Avg Accuracy</p>
                                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-400">{{getAverageAccuracy(models)}}</p>
                                    </div>
                                    <Target class="w-8 h-8 text-purple-500" />
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardContent class="pt-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-slate-600 dark:text-white mb-1">Total Predictions</p>
                                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-400">{{total_predictions ? total_predictions : '--'}}</p>
                                    </div>
                                    <Sparkle class="w-8 h-8 text-orange-500" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card
                        class="hover:shadow-lg transition-shadow cursor-pointer group"
                        v-for="model in props.models" :key="model.id"
                    >
                    <CardHeader>
                        <div class="flex items-start justify-between mb-2">
                            <CardTitle class="text-lg group-hover:text-blue-600 transition-colors">
                                {{model.name}}
                            </CardTitle>
                            <ChevronRight class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" />
                        </div>
                        <CardDescription class="text-sm">{{model.description}}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <Badge variant="secondary">{{model.type}}</Badge>
                                <div class="flex items-center gap-2">
                                    <div :class="getStatusColor(model.status)" class="w-2 h-2"/>
                                    <span class="text-sm text-slate-600 capitalize">{{model.status}}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-2">
                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Accuracy</p>
                                    <p class="text-lg font-semibold text-slate-900 dark:text-slate-400">{{model.accuracy}}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Predictions</p>
                                    <p class="text-lg font-semibold text-slate-900 dark:text-slate-400">
                                        Need to Implement Results first
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center text-xs text-slate-500 pt-2 border-t">
                                <Calendar class="w-3 h-3 mr-1" />
                                Last trained: {{model.last_trained_on}}
                            </div>
                        </div>
                    </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
