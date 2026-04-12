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
import RegisterOrganizationController from '@/actions/App/Http/Controllers/Auth/RegisterOrganizationController';
</script>

<template>
    <Head>
        <title>Register</title>
        <meta
            name="description"
            content="Register your organization and admin details to begin."
        />
    </Head>
    <main>
        <AuthBase
            title="Register your Organization"
            description="Enter Organization and Admin Details"
        >
            <Head title="Register" />

            <Form
                v-bind="RegisterOrganizationController.store.form()"
                :reset-on-success="['password', 'password_confirmation']"
                v-slot="{ errors, processing }"
                class="flex flex-col gap-6"
            >
                <div class="grid gap-6">

                    <h2>Organization Details</h2>

                    <div class="grid gap-2">
                        <Label for="org_name">Organization Name</Label>
                        <Input
                            id="org_name"
                            type="text"
                            required
                            autofocus
                            autocomplete="org_name"
                            name="org_name"
                            placeholder="Name of Organization"
                        />
                        <InputError :message="errors.org_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="num_seats">Number of Seats</Label>
                        <Input
                            id="num_seats"
                            type="number"
                            required
                            autofocus
                            autocomplete="num_seats"
                            name="num_seats"
                            placeholder="1-10"
                        />
                        <InputError :message="errors.num_seats" />
                    </div>
                </div>
                <div class="grid gap-6">

                    <h2>Admin Details</h2>

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
                        Create account
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
