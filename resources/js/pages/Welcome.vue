<script setup lang="ts">
import { dashboard, login, register } from '@/routes'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Welcome" />

    <main class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-900">
        <div class="w-full max-w-md mx-auto p-8 bg-white dark:bg-slate-800 rounded-2xl shadow text-center">
            <img
                src="/img/FSF_Logo.png"
                alt="FSF logo"
                class="mx-auto mb-10 h-28 md:h-36 w-auto"
            />
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                Flow & Sensor Forecast
            </h1>
            <p class="mt-1 text-slate-600 dark:text-slate-300">
                Forecasts & soft-sensor estimates for wastewater plants
            </p>

            <nav class="mt-6 flex items-center justify-center gap-3">
                <Link
                    v-if="$page.props.auth?.user"
                    :href="dashboard()"
                    class="inline-flex rounded-lg px-4 py-2 bg-blue-600 text-white hover:bg-blue-700"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-flex rounded-lg px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-700"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-flex rounded-lg px-4 py-2 bg-blue-600 text-white hover:bg-blue-700"
                    >
                        Register Organization
                    </Link>
                    <a
                        href="/join-organization"
                        class="inline-flex rounded-lg px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-700"
                    >
                        Join Organization
                    </a>
                </template>
            </nav>
        </div>
    </main>
</template>

