<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { usePage, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, watch } from 'vue';

import { Button } from "@/components/ui/button";
import { Label } from 'reka-ui';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Model Access Tokens',
        href: '/access-tokens',
    },
];

const page = usePage();

const props = defineProps({
    users: Array,
    models: Array
});

const show = ref(false);

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
function grantAccess(user_id, model_id) {
    const form = useForm({ user_id, model_id })

    form.post(route('access-token-grant-access'), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            console.error(errors)
        },
    })
}

function getCurrentUserAccessTokens(users) {
    for (const user of users) {
        if (user.id === page.props.auth.user.id) {
            return user.access_tokens;
        }
    }
    return;
}

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="page.props.errors">
            <div v-for="(error, index) in page.props.errors" :key="index">
                <span class="text-red-600">{{ error }}</span>
            </div>
        </div>
        <transition name="fade">
            <div
                v-if="show && $page.props.flash.success"
                class="fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded shadow-lg z-50"
            >
                {{ page.props.flash.success }}
            </div>
        </transition>

       <div class="min-h-screen p-8">
           <Card>
               <CardHeader>
                   <CardTitle>
                       <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2">
                           Model Access Tokens
                       </h1>
                       <p class="text-slate-600 dark:text-slate-400">
                           Manage access tokens for predictive models.
                       </p>
                   </CardTitle>
               </CardHeader>
               <div v-if="page.props.auth.user.is_admin" class="ml-8 mr-8 space-y-6 mb-10">
                   <Card>
                       <CardHeader>
                           <CardTitle>Create Access Token</CardTitle>
                           <CardDescription>
                               Select a user and model. Token will be auto generated.
                           </CardDescription>
                       </CardHeader>
                       <CardContent class="space-y-4">
                           <div class="grid gap-4 md:grid-cols-3">
                               <!-- Select User -->
                               <div>
                                   <Label for="user_id" class="mb-1 block">User</Label>
                                   <select
                                       id="user_id"
                                       v-model="user_id"
                                       class="w-full rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-background px-3 py-2 text-sm"
                                   >
                                       <option value="">Select user</option>
                                       <option v-for="user in props.users" :key="user.id" :value="user.id">
                                           {{ user.name }} ({{ user.email }})
                                       </option>
                                   </select>
                               </div>

                               <!-- Model select -->
                               <div>
                                   <Label for="model_id" class="mb-1 block">Model</Label>
                                   <select
                                       id="model_id"
                                       v-model="model_id"
                                       class="w-full rounded-md border border-sidebar-border/70 dark:border-sidebar-border bg-background px-3 py-2 text-sm"
                                   >
                                       <option value="">Select model</option>
                                       <option v-for="model in props.models" :key="model.id" :value="model.id">
                                           {{ model.name }}
                                       </option>
                                   </select>
                               </div>

                               <!-- Create button -->
                               <div class="flex items-end">
                                   <Button class="w-full"  @click="grantAccess(user_id, model_id)">
                                       Grant Access
                                   </Button>
                               </div>
                           </div>
                       </CardContent>
                   </Card>

                   <!-- Tokens Table -->
                   <div
                       class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-x-auto"
                   >
                       <Table class="w-full border-collapse">
                           <TableHeader>
                               <TableRow>
                                   <TableHead>User</TableHead>
                                   <TableHead>Email</TableHead>
                                   <TableHead>Model</TableHead>
                                   <TableHead>Access Token</TableHead>
                                   <TableHead>Created At</TableHead>
                                   <TableHead>Action</TableHead>
                               </TableRow>
                           </TableHeader>
                           <TableBody v-for="user in props.users" :key="user.id">
                               <TableRow  v-for="token in user.access_tokens" :key="token.id">
                                   <TableCell class="font-medium">{{ user.name }}</TableCell>
                                   <TableCell>{{ user.email }}</TableCell>
                                   <TableCell >{{ token.model_id }}</TableCell>
                                   <TableCell >{{ token.access_token }}</TableCell>
                                   <TableCell >{{ token.created_at }}</TableCell>
                                   <Button
                                       variant="destructive"
                                       size="sm"
                                       @click="deleteToken(token.id, token.model_name, token.user_name)"
                                   >
                                       Delete
                                   </Button>
                               </TableRow>
                               <TableRow v-if="!props.tokens || props.tokens.length === 0">
                                   <TableCell colspan="6" class="text-center py-4 text-slate-500">
                                       No access tokens found.
                                   </TableCell>
                               </TableRow>
                           </TableBody>
                       </Table>
                   </div>
               </div>

               <!-- Actual Tokens. Both users and admin shoule be able to see. I hope. -->
               <div class="ml-8 mr-8 mt-6 mb-10">
                   <Card>
                       <CardHeader>
                           <CardTitle>My Access Tokens</CardTitle>
                           <CardDescription>
                               Active access tokens assigned to you.
                           </CardDescription>
                       </CardHeader>
                       <CardContent>
                           <div
                               class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-x-auto"
                           >
                               <Table class="w-full border-collapse">
                                   <TableHeader>
                                       <TableRow>
                                           <TableHead>Model Name</TableHead>
                                           <TableHead>Access Token</TableHead>
                                           <TableHead>Created At</TableHead>
                                           <TableHead v-if="props.isAdmin">Action</TableHead>
                                       </TableRow>
                                   </TableHeader>
                                   <TableBody>
                                           <TableRow v-for="token in (getCurrentUserAccessTokens(props.users))" :key="token.id">
                                               <TableCell class="font-medium">{{ token.model_id }}</TableCell>
                                               <TableCell>{{ token.access_token }}}</TableCell>
                                               <TableCell>{{ token.created_at }}</TableCell>
                                               <TableCell>
                                                   <Button
                                                       variant="destructive"
                                                       size="sm"
                                                       @click="deleteToken(token.id, token.model_name)"
                                                   >
                                                       Delete
                                                   </Button>
                                               </TableCell>
                                           </TableRow>`
                                       <TableRow v-if="!props.myTokens || props.myTokens.length === 0">
                                           <TableCell colspan="4" class="text-center py-4 text-slate-500">
                                               You don't have any access tokens yet.
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
    </AppLayout>
</template>
<style scoped>

</style>
