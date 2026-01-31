<script setup lang="ts">
import { dashboard, login, register } from '@/routes'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Welcome" />

    <main class="min-h-screen flex flex-col items-center justify-center ">

        <img
            src="/img/FSF_Logo.png"
            alt="FSF logo"
            class="h-28 md:h-36 w-auto mb-8"
        />

        <h1 class="text-3xl font-semibold text-center">
            Flow & Sensor Forecast
        </h1>

        <p class="mt-2  text-center">
            Forecasts & soft-sensor estimates for wastewater plants
        </p>

        <div class="mt-8 flex flex-col items-center gap-3">

            <Link
                v-if="$page.props.auth?.user"
                :href="dashboard()"
                class= "hover:underline text-sm"
            >
                Dashboard
            </Link>

            <template v-else>
                <Link
                    :href="login()"
                    class=" hover:underline text-sm"
                >
                    Log in
                </Link>

                <Link
                    :href="register()"
                    class= "hover:underline text-sm"
                >
                    Register Organization
                </Link>

                <a
                    href="/join-organization"
                    class="hover:underline text-sm"
                >
                    Join Organization
                </a>

                <a
                    href="/about"
                    class="hover:underline text-sm"
                >
                    About
                </a>
            </template>

        </div>
    </main>
</template>

