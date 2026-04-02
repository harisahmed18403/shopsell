<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Customers</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">CRM</h1>
                </div>
                <div class="flex gap-3">
                    <div class="w-full max-w-sm">
                        <Input v-model="form.search" type="text" placeholder="Search customers..." @keyup.enter="applyFilters" />
                    </div>
                    <Button @click="applyFilters">Search</Button>
                    <Button variant="ghost" @click="resetFilters">Reset</Button>
                    <Link href="/customers/create">
                        <Button>Add customer</Button>
                    </Link>
                </div>
            </div>

            <Card class="border-white/10 bg-slate-900/80">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10 text-left">
                            <thead class="text-xs uppercase tracking-[0.25em] text-slate-400">
                                <tr>
                                    <th class="px-6 py-4">Name</th>
                                    <th class="px-6 py-4">Contact</th>
                                    <th class="px-6 py-4">Address</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="customer in customers" :key="customer.id" class="text-sm text-slate-200">
                                    <td class="px-6 py-4 font-medium text-white">{{ customer.name }}</td>
                                    <td class="px-6 py-4">
                                        <p>{{ customer.phone || 'No phone' }}</p>
                                        <p class="text-xs text-slate-500">{{ customer.email || 'No email' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-400">
                                        <p>{{ customer.address || 'No address' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ customer.transactions_count }} transactions</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/customers/${customer.id}`"><Button variant="ghost" size="sm">View</Button></Link>
                                            <Link :href="`/customers/${customer.id}/edit`"><Button variant="outline" size="sm">Edit</Button></Link>
                                            <Button variant="ghost" size="sm" @click="destroyCustomer(customer.id)">Delete</Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="customers.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">No customers found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-between">
                <p class="text-sm text-slate-400">{{ pagination.total }} customers</p>
                <PaginationNav :links="pagination.links" />
            </div>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';

import PaginationNav from '@/components/app/PaginationNav.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SharedPageProps } from '@/types';

const props = defineProps<SharedPageProps & {
    filters: {
        search: string;
    };
    customers: Array<{
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        transactions_count: number;
        created_at: string | null;
    }>;
    pagination: {
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const form = useForm({
    search: props.filters.search,
});

function applyFilters() {
    router.get('/customers', { search: form.search || undefined }, { preserveState: true, replace: true });
}

function resetFilters() {
    form.reset();
    router.get('/customers');
}

function destroyCustomer(id: number) {
    if (window.confirm('Delete this customer record?')) {
        router.delete(`/customers/${id}`);
    }
}
</script>
