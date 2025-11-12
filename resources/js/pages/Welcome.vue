<script setup lang="ts">
import { dashboard, login, register } from '@/routes'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Welcome" />

    <main
        class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-900 px-4 sm:px-6"
    >
        <div
            class="w-full max-w-md mx-auto p-4 sm:p-6 md:p-8 bg-white dark:bg-slate-800 rounded-2xl shadow text-center"
        >
            <!-- Logo with white badge for dark mode -->
            <div class="mx-auto mb-8 sm:mb-10 inline-flex items-center justify-center">
                <div class="p-2 rounded-2xl bg-white dark:bg-white shadow-sm">
                    <img
                        src="/img/FSF_Logo.png"
                        alt="FSF logo"
                        class="h-20 sm:h-24 md:h-28 max-w-full object-contain"
                    />
                </div>
            </div>

            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                Flow & Sensor Forecast
            </h1>

            <p class="mt-1 text-slate-600 dark:text-slate-300">
                Forecasts &amp; soft-sensor estimates for wastewater plants
            </p>

            <nav
                class="mt-6 flex flex-col sm:flex-row flex-wrap items-center justify-center gap-3"
            >
                <!-- Logged-in user -->
                <Link
                    v-if="$page.props.auth?.user"
                    :href="dashboard()"
                    class="inline-flex justify-center w-full sm:w-auto rounded-lg px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 transition"
                >
                    Dashboard
                </Link>

                <!-- Guests -->
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-flex justify-center w-full sm:w-auto rounded-lg px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-700 transition"
                    >
                        Log in
                    </Link>

                    <Link
                        :href="register()"
                        class="inline-flex justify-center w-full sm:w-auto rounded-lg px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 transition"
                    >
                        Register Organization
                    </Link>

                    <a
                        href="/join-organization"
                        class="inline-flex justify-center w-full sm:w-auto rounded-lg px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-700 transition"
                    >
                        Join Organization
                    </a>
                </template>
            </nav>
        </div>
    </main>
</template>
