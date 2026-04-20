<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';
import { Label } from 'reka-ui';
import { ref, watch } from 'vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
} from '@/components/ui/dropdown-menu';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { EllipsisVerticalIcon } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Users',
        href: '/manage-users',
    },
];

const page = usePage();

const props = defineProps({
    users: Array,
    org_allowed_seats: Number,
    current_user_is_super: Boolean,
});

const show = ref(false);

function toggleAdmin(user_id) {
    const form = useForm({ user_id });

    form.post(route('toggle-admin'), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            console.error(errors);
        },
    });
}

function toggleStatus(user_id) {
    const form = useForm({ user_id });

    form.post(route('toggle-status'), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            console.error(errors);
        },
    });
}

function deleteUser(user_id, user_name) {
    const form = useForm({ user_id });
    if (!confirm(`Are you sure you want to delete ${user_name}?`)) {
        return;
    }

    form.post(route('delete-user'), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            console.error(errors);
        },
    });
}
function generateAccessCode(user_id) {
    const form = useForm({ user_id });

    form.post(route('generate-access-code'), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            console.error(errors);
        },
    });
}

function isAdmin(user) {
    return user.roles.some((role) => role.name === 'Admin');
}
function isSuper(user) {
    return user.roles.some((role) => role.name === 'Super');
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
        <title>Manage Users</title>
        <meta
            name="description"
            content="User page for organization management and access code generation."
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
                <CardHeader>
                    <CardTitle>
                        <h1
                            class="mb-2 text-4xl font-bold text-slate-900 dark:text-white"
                        >
                            Users
                        </h1>
                        <p class="text-slate-600 dark:text-slate-400">
                            Manage Users and Generate Access Codes
                        </p>
                    </CardTitle>
                </CardHeader>
                <div
                    class="mr-8 ml-8 flex items-center justify-center rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <Table class="w-full border-collapse">
                        <TableHeader>
                            <TableHead>ID</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Admin</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Action</TableHead>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="user in props.users"
                                :key="user.id"
                            >
                                <TableCell class="font-medium">
                                    {{ user.id }}
                                </TableCell>
                                <TableCell>{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>{{
                                    isSuper(user)
                                        ? 'Super'
                                        : isAdmin(user)
                                          ? 'Yes'
                                          : 'No'
                                }}</TableCell>
                                <TableCell
                                    :class="
                                        user.status === 0
                                            ? 'text-green-700 dark:text-green-400'
                                            : 'text-red-700 dark:text-red-400'
                                    "
                                    >{{
                                        user.status === 0
                                            ? 'Active'
                                            : user.status === 1
                                              ? 'Inactive'
                                              : 'Banned'
                                    }}</TableCell
                                >
                                <TableCell>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <button
                                                type="button"
                                                class="cursor-pointer rounded p-1 text-black hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:text-white dark:hover:bg-gray-800"
                                                aria-label="Open menu"
                                                :disabled="
                                                    page.props.auth.user.id ===
                                                        user.id ||
                                                    (isAdmin(user) &&
                                                        !current_user_is_super) ||
                                                    isSuper(user)
                                                "
                                            >
                                                <EllipsisVerticalIcon aria-hidden="true"/>
                                            </button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent>
                                            <DropdownMenuItem>
                                                <Button
                                                    type="button"
                                                    class="w-full"
                                                    @click="
                                                        toggleAdmin(user.id)
                                                    "
                                                    >Toggle Admin</Button
                                                >
                                            </DropdownMenuItem>
                                            <DropdownMenuItem>
                                                <Button
                                                    type="button"
                                                    class="w-full"
                                                    @click="
                                                        toggleStatus(user.id)
                                                    "
                                                    >Toggle Status</Button
                                                >
                                            </DropdownMenuItem>
                                            <DropdownMenuItem>
                                                <Button
                                                    type="button"
                                                    class="w-full"
                                                    @click="
                                                        deleteUser(
                                                            user.id,
                                                            user.name,
                                                        )
                                                    "
                                                    >Delete User</Button
                                                >
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div
                    class="relative mx-auto mt-5 mr-8 ml-8 max-h-[40vh] items-center overflow-x-auto overflow-y-auto md:min-h-min lg:flex"
                >
                    <div class="mr-5 max-w-[25vh] min-w-[25vh]">
                        <Card>
                            <CardHeader>
                                <h1>Usage</h1>
                                <CardDescription
                                    >Seats Used/Total</CardDescription
                                >
                            </CardHeader>
                            <CardContent>
                                <span
                                    v-if="
                                        users.length / org_allowed_seats >=
                                            0.5 &&
                                        users.length / org_allowed_seats < 0.75
                                    "
                                    class="text-yellow"
                                >
                                    {{ users.length }} / {{ org_allowed_seats }}
                                </span>
                                <span
                                    v-else-if="
                                        users.length / org_allowed_seats >= 0.75
                                    "
                                    class="text-red-700 dark:text-red-400"
                                >
                                    {{ users.length }} / {{ org_allowed_seats }}
                                </span>
                                <span v-else class="text-green-700 dark:text-green-400">
                                    {{ users.length }} / {{ org_allowed_seats }}
                                </span>
                            </CardContent>
                            <CardFooter>
                                <Button> Add More Seats </Button>
                            </CardFooter>
                        </Card>
                    </div>
                    <div
                        class="relative max-h-[35vh] w-full rounded-xl border border-sidebar-border/70 p-5 md:min-h-min dark:border-sidebar-border"
                    >
                        <p class="font-bold">
                            Generate Organization Access Code
                        </p>
                        <div class="p-10 lg:flex">
                            <Button
                                class="mr-5"
                                @click="
                                    generateAccessCode(page.props.auth.user.id)
                                "
                            >
                                Generate
                            </Button>
                            <div>
                                <Label for="invite-code" class="text-black dark:text-white">
                                    Invite Code
                                    <span v-if="page.props.flash.code" class="block text-sm font-normal">
                        Provide new user with this code. It is valid for 24 hours.
                    </span>
                                </Label>
                                <Input
                                    id="invite-code"
                                    disabled
                                    :value="page.props.flash.code ?? ''"
                                    :aria-describedby="page.props.flash.code ? 'invite-code-hint' : undefined"
                                />
                                <p
                                    v-if="page.props.flash.code"
                                    id="invite-code-hint"
                                    class="text-sm text-muted-foreground"
                                >
                                    Valid for 24 hours.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped></style>
