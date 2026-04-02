<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Customer</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">{{ customer.name }}</h1>
                </div>
                <div class="flex gap-3">
                    <Link :href="`/transactions/create?customer_id=${customer.id}`"><Button>New transaction</Button></Link>
                    <Link :href="`/customers/${customer.id}/edit`"><Button>Edit</Button></Link>
                    <Link href="/customers"><Button variant="ghost">Back</Button></Link>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                <Card class="border-white/10 bg-slate-900/80">
                    <CardContent class="space-y-4 pt-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Email</p>
                            <p class="mt-2 text-white">{{ customer.email || 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Phone</p>
                            <p class="mt-2 text-white">{{ customer.phone || 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Address</p>
                            <p class="mt-2 whitespace-pre-line text-white">{{ customer.address || 'Not provided' }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-white/10 bg-slate-900/80">
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Transactions</p>
                                <h2 class="mt-2 text-2xl font-semibold text-white">History</h2>
                            </div>
                        </div>
                        <div v-if="customer.transactions.length" class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10 text-left">
                                <thead class="text-xs uppercase tracking-[0.25em] text-slate-400">
                                    <tr>
                                        <th class="px-4 py-3">ID</th>
                                        <th class="px-4 py-3">Type</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Total</th>
                                        <th class="px-4 py-3">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="transaction in customer.transactions" :key="transaction.id" class="text-sm text-slate-200">
                                        <td class="px-4 py-4">
                                            <Link :href="`/transactions/${transaction.id}`" class="text-rose-300 hover:underline">#{{ transaction.id }}</Link>
                                        </td>
                                        <td class="px-4 py-4 capitalize">{{ transaction.type }}</td>
                                        <td class="px-4 py-4">{{ transaction.status }}</td>
                                        <td class="px-4 py-4">{{ formatCurrency(transaction.total_amount) }}</td>
                                        <td class="px-4 py-4">{{ formatDate(transaction.created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="mt-6 rounded-2xl border border-dashed border-white/10 px-6 py-10 text-sm text-slate-500">
                            No transactions recorded for this customer.
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    customer: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        transactions: Array<{
            id: number;
            type: string;
            status: string;
            total_amount: number;
            created_at: string;
        }>;
    };
}>();
</script>
