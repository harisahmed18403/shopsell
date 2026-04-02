<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Admin</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">Users</h1>
                </div>
                <Link href="/admin/users/create">
                    <Button>New user</Button>
                </Link>
            </div>

            <Card class="border-white/10 bg-slate-900/80">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10 text-left">
                            <thead class="text-xs uppercase tracking-[0.25em] text-slate-400">
                                <tr>
                                    <th class="px-6 py-4">Name</th>
                                    <th class="px-6 py-4">Email</th>
                                    <th class="px-6 py-4">Role</th>
                                    <th class="px-6 py-4">Joined</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="user in users" :key="user.id" class="text-sm text-slate-200">
                                    <td class="px-6 py-4 font-medium text-white">{{ user.name }}</td>
                                    <td class="px-6 py-4">{{ user.email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs uppercase tracking-[0.2em] text-slate-300">
                                            {{ user.role.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-400">{{ formatDate(user.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/admin/users/${user.id}/edit`">
                                                <Button variant="outline" size="sm">Edit</Button>
                                            </Link>
                                            <Button v-if="user.can_delete" variant="ghost" size="sm" @click="removeUser(user.id)">
                                                Delete
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="users.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                        No users found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-400">{{ pagination.total }} users</p>
                <PaginationNav :links="pagination.links" />
            </div>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';

import PaginationNav from '@/components/app/PaginationNav.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        created_at: string;
        can_delete: boolean;
    }>;
    pagination: {
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}>();

function removeUser(id: number) {
    if (window.confirm('Are you sure you want to delete this user?')) {
        router.delete(`/admin/users/${id}`);
    }
}
</script>
