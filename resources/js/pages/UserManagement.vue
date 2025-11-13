<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button"
import { route } from 'ziggy-js';
import {useForm} from '@inertiajs/vue3';
import { Label } from 'reka-ui';
import { ref, watch } from 'vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
} from '@/components/ui/dropdown-menu'
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
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
});

const show = ref(false);

function toggleAdmin(user_id) {
    const form = useForm({ user_id })

    form.post(route('toggle-admin'), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            console.error(errors)
        },
    })
}

function toggleStatus(user_id) {
    const form = useForm({ user_id })

    form.post(route('toggle-status'), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            console.error(errors)
        },
    })
}

function deleteUser(user_id, user_name) {
    const form = useForm({ user_id })
    if (!confirm(`Are you sure you want to delete ${user_name}?`)) {
        return
    }

    form.post(route('delete-user'), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            console.error(errors)
        },
    })
}
function generateAccessCode(user_id) {
    const form = useForm({ user_id })

    form.post(route('generate-access-code'), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            console.error(errors)
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
    <AppLayout :breadcrumbs="breadcrumbs">
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
        <div class="mx-auto ml-8 flex items-center justify-between mb-4 mt-8">
            <div>
                <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2">Users</h1>
                <p class="text-slate-600 dark:text-slate-400">Manage Users and Generate Access Codes</p>
            </div>
        </div>
        <div
            class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border ml-8 mr-8 flex items-center justify-center"
        >
            <Table class="w-full border-collapse">
                <TableHeader>
                    <TableHead>ID</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Is Admin?</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Action</TableHead>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in props.users" :key="user.id">
                        <TableCell class="font-medium"> {{ user.id }} </TableCell>
                        <TableCell>{{ user.name}}</TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                        <TableCell>{{ user.is_admin ? "Yes" : "No" }}</TableCell>
                        <TableCell :class="user.status === 0 ? 'text-green-500' : 'text-red-500'">{{ user.status === 0 ? "Active" : user.status === 1 ? "Inactive" : "Banned" }}</TableCell>
                        <TableCell>
                            <DropdownMenu>
                                <DropdownMenuTrigger>
                                    <button
                                        class="p-1 cursor-pointer dark:text-white text-black rounded hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                        aria-label="Open menu"
                                        :disabled="page.props.auth.user.id === user.id"
                                    >
                                        <EllipsisVerticalIcon/>
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem>
                                        <Button class="w-full" @click="toggleAdmin(user.id)">Toggle Admin</Button>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem>
                                        <Button class="w-full" @click="toggleStatus(user.id)">Toggle Status</Button>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem>
                                        <Button class="w-full" @click="deleteUser(user.id, user.name)">Delete User</Button>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
        <div class="mx-auto lg:flex items-center ml-8 mr-8 relative overflow-y-auto max-h-[40vh] md:min-h-min overflow-x-auto mt-5">
            <div class="max-w-[25vh] min-w-[25vh] mr-5">
                <Card>
                    <CardHeader>
                        <CardTitle>Usage</CardTitle>
                        <CardDescription>Seats Used/Total</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <span v-if="users.length / org_allowed_seats >= .50 && users.length / org_allowed_seats < .75" class="text-yellow"> {{ users.length }} / {{ org_allowed_seats }} </span>
                        <span v-else-if="users.length / org_allowed_seats >= .75" class="text-red-600"> {{ users.length }} / {{ org_allowed_seats }} </span>
                        <span v-else class="text-green-500"> {{ users.length }} / {{ org_allowed_seats }} </span>
                    </CardContent>
                    <CardFooter>
                        <Button>
                            Add More Seats
                        </Button>
                    </CardFooter>
                </Card>
            </div>
            <div class="relative max-h-[35vh] w-full rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-5">
                <Label class="font-bold">
                    Generate Organization Access Code
                </Label>
                <div class="lg:flex p-10">
                    <Button class="mr-5" @click="generateAccessCode(page.props.auth.user.id)">
                        Generate
                    </Button>
                    <div>
                        <Input disabled :value="page.props.flash.code ? page.props.flash.code : ''"/>
                        <Label>
                            <span v-if="page.props.flash.code" class="dark:text-white text-black">Provide new user with this code. It is valid for 24 hours.</span>
                        </Label>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
