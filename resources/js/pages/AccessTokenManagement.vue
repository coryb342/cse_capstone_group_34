<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { usePage, useForm, Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Label } from 'reka-ui';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Trash2Icon } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Model Access Tokens',
        href: '/access-tokens',
    },
];

const page = usePage();

// expect: users[] each with access_tokens[], and models[]
const props = defineProps<{
    users: any[];
    models: any[];
}>();

const show = ref(false);

const userId = ref<string | number>('');
const modelId = ref<string | number>('');

const showActivateDialog = ref(false);
const selectedToken = ref<any | null>(null);
const tokenName = ref('');
const generatedToken = ref('');

// flash watcher
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
watch(
    () => page.props.flash.token,
    (newVal) => {
        if (newVal) {
            generatedToken.value = newVal as string;
        }
    },
    { immediate: true },
);

// grant access for selected user + model
function grantAccess() {
    if (!userId.value || !modelId.value) return;

    const form = useForm({
        user_id: userId.value,
        model_id: modelId.value,
    });

    form.post(route('access-token-grant-access'), {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            console.error(errors);
        },
    });
}

function getCurrentUserAccessTokens() {
    const currentId = page.props.auth.user.id;

    const user = props.users.find((u) => u.id === currentId);
    return user?.access_tokens ?? [];
}

function getAdminUsers() {
    const currentId = page.props.auth.user.id;
    return props.users.filter((u) => u.id !== currentId);
}

// activate dialog helpers
function openActivateDialog(token: any) {
    selectedToken.value = token;
    tokenName.value = token.token_name ?? '';
    generatedToken.value = '';
    showActivateDialog.value = true;
}

function generateToken() {
    if (!selectedToken.value) return;
    if (!tokenName.value) {
        alert('Please enter a name for this token.');
        return;
    }

    const form = useForm({
        token_name: tokenName.value,
    });

    form.post(route('access-tokens.activate', selectedToken.value.id), {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            console.error(errors);
        },
    });
}

function deleteToken(tokenId: number) {
    if (!confirm('Are you sure you want to delete this token?')) {
        return;
    }

    const form = useForm({});

    form.delete(route('access-tokens.destroy', tokenId), {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            console.error(errors);
        },
    });
}
</script>

<template>
    <Head>
        <title>Model Access Token Management</title>
        <meta
            name="description"
            content="Manage model access tokens on specific models within the organization."
        />
    </Head>
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- errors -->
        <div v-if="page.props.errors">
            <div v-for="(error, index) in page.props.errors" :key="index">
                <span class="text-red-600">{{ error }}</span>
            </div>
        </div>

        <transition name="fade">
            <div
                v-if="show && $page.props.flash.success"
                class="fixed top-4 right-4 z-50 rounded bg-green-600 px-6 py-3 text-white shadow-lg"
            >
                {{ page.props.flash.success }}
            </div>
        </transition>

        <div class="min-h-screen p-8">
            <Card>
                <CardHeader>
                    <CardTitle>
                        <h1
                            class="mb-2 text-4xl font-bold text-slate-900 dark:text-white"
                        >
                            Model Access Tokens
                        </h1>
                        <p class="text-slate-600 dark:text-slate-400">
                            Manage access tokens for predictive models.
                        </p>
                    </CardTitle>
                </CardHeader>

                <!-- ADMIN SECTION -->
                <div
                    v-if="
                        page.props.auth.user_roles.some(
                            (role) => role.name === 'Admin',
                        )
                    "
                    class="mr-8 mb-10 ml-8 space-y-6"
                >
                    <!-- Create token card -->
                    <Card>
                        <CardHeader>
                            <h1 class="font-semibold">Create Access Token</h1>
                            <CardDescription>
                                Select a user and model. Token will be auto
                                generated.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-3">
                                <!-- Select User -->
                                <div>
                                    <Label for="user_id" class="mb-1 block"
                                        >User</Label
                                    >
                                    <select
                                        id="user_id"
                                        v-model="userId"
                                        class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 text-sm dark:border-sidebar-border"
                                    >
                                        <option value="">Select user</option>
                                        <option
                                            v-for="user in props.users"
                                            :key="user.id"
                                            :value="user.id"
                                        >
                                            {{ user.name }} ({{ user.email }})
                                        </option>
                                    </select>
                                </div>

                                <!-- Model select -->
                                <div>
                                    <Label for="model_id" class="mb-1 block"
                                        >Model</Label
                                    >
                                    <select
                                        id="model_id"
                                        v-model="modelId"
                                        class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 text-sm dark:border-sidebar-border"
                                    >
                                        <option value="">Select model</option>
                                        <option
                                            v-for="model in props.models"
                                            :key="model.id"
                                            :value="model.id"
                                        >
                                            {{ model.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Create button -->
                                <div class="flex items-end">
                                    <Button class="w-full" @click="grantAccess">
                                        Grant Access
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Admin tokens table (excluding current user) -->
                    <div
                        class="mt-6 overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                    >
                        <Table class="w-full border-collapse">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>User</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Model</TableHead>
                                    <TableHead>Token Name</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template
                                    v-for="user in getAdminUsers()"
                                    :key="user.id"
                                >
                                    <TableRow
                                        v-for="token in user.access_tokens"
                                        :key="token.id"
                                    >
                                        <TableCell class="font-medium">{{
                                            user.name
                                        }}</TableCell>
                                        <TableCell>{{ user.email }}</TableCell>
                                        <TableCell>{{
                                            models.find(
                                                (m) => m.id === token.model_id,
                                            ).name
                                        }}</TableCell>
                                        <TableCell>{{
                                            token.token_name ??
                                            `Token #${token.id}`
                                        }}</TableCell>
                                        <TableCell>{{
                                            token.status === 'inactive'
                                                ? 'Inactive'
                                                : 'Active'
                                        }}</TableCell>
                                        <TableCell>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                aria-label="Delete token"
                                                @click="deleteToken(token.id)"
                                            >
                                                <Trash2Icon class="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <div class="mt-6 mr-8 mb-10 ml-8">
                    <Card>
                        <CardHeader>
                            <h1 class="font-semibold">My Access Tokens</h1>
                            <CardDescription>
                                Active access tokens assigned to you.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                            >
                                <Table class="w-full border-collapse">
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Model</TableHead>
                                            <TableHead>Token Name</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Action</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="token in getCurrentUserAccessTokens()"
                                            :key="token.id"
                                        >
                                            <TableCell class="font-medium">
                                                {{
                                                    models.find(
                                                        (m) =>
                                                            m.id ===
                                                            token.model_id,
                                                    ).name
                                                }}
                                            </TableCell>
                                            <TableCell>
                                                {{
                                                    token.token_name ??
                                                    `Token #${token.id}`
                                                }}
                                            </TableCell>
                                            <TableCell>
                                                <Button
                                                    v-if="
                                                        token.status ===
                                                        'inactive'
                                                    "
                                                    size="sm"
                                                    @click="
                                                        openActivateDialog(
                                                            token,
                                                        )
                                                    "
                                                >
                                                    Activate
                                                </Button>
                                                <!-- otherwise just show status text -->
                                                <span v-else>
                                                    {{
                                                        token.status ===
                                                        'active'
                                                            ? 'Active'
                                                            : token.status
                                                    }}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label="Delete token"
                                                    @click="
                                                        deleteToken(token.id)
                                                    "
                                                >
                                                    <Trash2Icon
                                                        class="h-4 w-4"
                                                    />
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </Card>
        </div>

        <div
            v-if="showActivateDialog"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <Card
                class="w-full max-w-md border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <CardHeader>
                    <CardTitle>Activate Token</CardTitle>
                    <CardDescription>
                        Enter a name for this token and generate a new token.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div class="mb-4 space-y-2">
                        <Label for="token_name">Token Name</Label>
                        <input
                            id="token_name"
                            v-model="tokenName"
                            class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 text-sm dark:border-sidebar-border"
                            placeholder="e.g. My Sensor Model Token"
                        />
                        <Button @click="generateToken"> Generate </Button>
                    </div>

                    <div class="mb-4" v-if="page.props.flash.token">
                        <Label>Generated Token</Label>
                        <input
                            readonly
                            class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 font-mono text-sm dark:border-sidebar-border"
                            :value="generatedToken"
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            Copy this token now. It won't be shown again after
                            you close this window.
                        </p>
                    </div>
                </CardContent>

                <div class="flex justify-end gap-2 px-6 pb-4">
                    <Button
                        variant="outline"
                        @click="showActivateDialog = false"
                    >
                        Close
                    </Button>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
