<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Soft Sensors',
        href: dashboard().url,
    },
];

const page = usePage();
</script>

<template>
    <Head title="Soft Sensors" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="page.props.errors">
            <div v-for="(index, error) in page.props.errors" :key="index">
                <span class="text-red-600">{{ error }}</span>
            </div>
        </div>

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="relative flex min-h-[50vh] items-center justify-center rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <h1 class="text-3xl font-semibold">Soft Sensor</h1>
            </div>
        </div>
    </AppLayout>
</template>
