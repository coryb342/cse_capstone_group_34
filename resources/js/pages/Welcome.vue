<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome" />

    <main class="flex min-h-screen flex-col items-center justify-center">
        <div
            class="flex w-full max-w-sm flex-col items-center gap-1 text-center"
        >
            <!-- Logo -->
            <img src="/img/FSF_Logo.png" alt="FSF logo" />

            <!-- Links -->
            <div class="flex flex-col gap-1 text-sm text-muted-foreground">
                <Link
                    :href="login()"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-slate-900 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800"
                >
                    Log in
                </Link>

                <Link
                    :href="register()"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-slate-900 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800"
                >
                    Register Organization
                </Link>

                <Link
                    href="/join-organization"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-slate-900 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800"
                >
                    Join Organization
                </Link>

                <Link
                    v-if="$page.props.auth?.user"
                    :href="dashboard()"
                    class="underline underline-offset-4 hover:text-slate-900 dark:hover:text-slate-100"
                >
                    Go to Dashboard
                </Link>
            </div>
        </div>
    </main>
</template>
