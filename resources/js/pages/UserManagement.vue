<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button"
import { route } from 'ziggy-js';
import {useForm} from '@inertiajs/vue3';
import { Label } from 'reka-ui';
import { ref, watch, onMounted } from 'vue'

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
import { Alert } from '@/components/ui/alert';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Users',
        href: '/manage-users',
    },
];

const page = usePage();

const props = defineProps({
    users: Array,
});

const csrfToken = document.querySelector('meta[name=csrf-token]').getAttribute('content');

const show = ref(false);

function toggleAdmin(user_id) {
    const form = useForm({ user_id })

    form.post(route('toggle-admin'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            console.log('User modified successfully.')
        },
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
        onSuccess: () => {
            console.log('User modified successfully.')
        },
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
        onSuccess: () => {
            console.log('User modified successfully.')
        },
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
            <div v-for="(error) in page.props.errors">
                <span class="text-red-600">{{ error }}</span>
            </div>
        </div>
        <div v-if="show && $page.props.flash.success">
            <transition name="fade">
                <div
                    class="fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded shadow-lg"
                >
                    {{ page.props.flash.success }}
                </div>
            </transition>
        </div>
        <Label>Users</Label>
        <div
            class="relative overflow-y-auto max-h-[35vh] rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border overflow-x-auto"
        >
            <Table class="w-full text-left border-collapse">
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
                        <TableCell>{{ user.status === 0 ? "Active" : user.status === 1 ? "Inactive" : "Banned" }}</TableCell>
                        <TableCell>
                            <DropdownMenu>
                                <DropdownMenuTrigger>
                                    <button
                                        class="p-2 text-lg font-black cursor-pointer dark:text-white text-black rounded hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                        aria-label="Open menu"
                                        :disabled="page.props.auth.user.id === user.id"
                                    >
                                        ⋮
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
    </AppLayout>
</template>

<style scoped>

</style>
