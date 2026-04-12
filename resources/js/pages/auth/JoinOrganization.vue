<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import JoinOrganizationController from '@/actions/App/Http/Controllers/JoinOrganizationController';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <Head>
        <title>Join an Organization</title>
        <meta
            name="description"
            content="Join an organization with an access code given by an admin."
        />
    </Head>
    <main>
        <AuthBase
            title="Join your Organization"
            description="Enter Organization and User Details"
        >
            <Head title="Join Organization" />

            <Form
                v-bind="JoinOrganizationController.store.form()"
                :reset-on-success="['password', 'password_confirmation']"
                v-slot="{ errors, processing }"
                class="flex flex-col gap-6"
            >
                <div class="grid gap-6">
                    <div v-if="page.props.errors">
                        <div v-for="(index, error) in page.props.errors" :key="index">
                            <span class="text-red-600">{{ error }}</span>
                        </div>
                    </div>
                    <h2>Organization Details</h2>

                    <div class="grid gap-2">
                        <Label for="org_access_code">Organization Access Code</Label>
                        <Input
                            id="org_access_code"
                            type="number"
                            required
                            autofocus
                            autocomplete="org_access_code"
                            name="org_access_code"
                        />
                        <InputError :message="errors.org_access_code" />
                    </div>
                </div>
                <div class="grid gap-6">

                    <h2>User Details</h2>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            name="name"
                            placeholder="Full name"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autocomplete="email"
                            name="email"
                            placeholder="email@example.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            name="password"
                            placeholder="Password"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Confirm password"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <Button
                        type="submit"
                        class="mt-2 w-full"
                        :disabled="processing"
                        data-test="register-user-button"
                    >
                        <LoaderCircle
                            v-if="processing"
                            class="h-4 w-4 animate-spin"
                        />
                        Join Organization
                    </Button>
                </div>

                <div class="text-center text-sm text-muted-foreground">
                    Already have an account?
                    <TextLink
                        :href="login()"
                        class="underline underline-offset-4"
                    >Log in</TextLink
                    >
                </div>
            </Form>
        </AuthBase>
    </main>
</template>
