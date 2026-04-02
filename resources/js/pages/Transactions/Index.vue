<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Transactions</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">Ledger</h1>
                </div>
                <Link href="/transactions/create"><Button>New transaction</Button></Link>
            </div>

            <Card class="border-white/10 bg-slate-900/80">
                <CardContent class="pt-6">
                    <form class="flex flex-wrap gap-3" @submit.prevent="applyFilters">
                        <select v-model="form.type" class="h-10 rounded-xl border border-white/10 bg-white/5 px-3 text-sm text-white">
                            <option value="">All types</option>
                            <option value="sell">Sell</option>
                            <option value="buy">Buy</option>
                            <option value="repair">Repair</option>
                        </select>
                        <select v-model="form.status" class="h-10 rounded-xl border border-white/10 bg-white/5 px-3 text-sm text-white">
                            <option value="">All statuses</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                        </select>
                        <Button type="submit">Filter</Button>
                        <Button type="button" variant="ghost" @click="resetFilters">Reset</Button>
                    </form>
                </CardContent>
            </Card>

            <Card class="border-white/10 bg-slate-900/80">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10 text-left">
                            <thead class="text-xs uppercase tracking-[0.25em] text-slate-400">
                                <tr>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Type</th>
                                    <th class="px-6 py-4">Items</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="transaction in transactions" :key="transaction.id" class="text-sm text-slate-200">
                                    <td class="px-6 py-4">{{ formatDate(transaction.created_at) }}</td>
                                    <td class="px-6 py-4 capitalize">{{ transaction.type }}</td>
                                    <td class="px-6 py-4">{{ transaction.items.join(', ') }}</td>
                                    <td class="px-6 py-4">{{ transaction.customer_name }}</td>
                                    <td class="px-6 py-4">{{ formatCurrency(transaction.total_amount) }}</td>
                                    <td class="px-6 py-4">{{ transaction.status }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="`/transactions/${transaction.id}`"><Button variant="ghost" size="sm">Details</Button></Link>
                                            <Link :href="`/transactions/${transaction.id}/edit`"><Button variant="outline" size="sm">Edit</Button></Link>
                                            <a :href="`/transactions/${transaction.id}/invoice`" target="_blank"><Button variant="ghost" size="sm">Invoice</Button></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="transactions.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">No transactions found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-between">
                <p class="text-sm text-slate-400">{{ pagination.total }} transactions</p>
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
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

const props = defineProps<SharedPageProps & {
    filters: { type: string; status: string };
    transactions: Array<{
        id: number;
        type: string;
        status: string;
        created_at: string;
        customer_name: string;
        total_amount: number;
        items: string[];
    }>;
    pagination: { total: number; links: Array<{ url: string | null; label: string; active: boolean }> };
}>();

const form = useForm({ type: props.filters.type, status: props.filters.status });

function applyFilters() {
    router.get('/transactions', { type: form.type || undefined, status: form.status || undefined }, { preserveState: true, replace: true });
}

function resetFilters() {
    form.reset();
    router.get('/transactions');
}
</script>
