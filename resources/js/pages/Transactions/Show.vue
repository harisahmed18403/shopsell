<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Transaction</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">#{{ transaction.id }}</h1>
                </div>
                <div class="flex gap-3">
                    <a :href="`/transactions/${transaction.id}/invoice`" target="_blank"><Button>Invoice</Button></a>
                    <Link href="/transactions"><Button variant="ghost">Back</Button></Link>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <Card class="border-white/10 bg-slate-900/80">
                    <CardContent class="pt-6">
                        <table class="min-w-full divide-y divide-white/10 text-left">
                            <thead class="text-xs uppercase tracking-[0.25em] text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Item</th>
                                    <th class="px-4 py-3">Qty</th>
                                    <th class="px-4 py-3">Price</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="item in transaction.items" :key="item.id" class="text-sm text-slate-200">
                                    <td class="px-4 py-4">
                                        <p class="font-medium text-white">{{ item.product_name || 'Custom item' }}</p>
                                        <p class="text-xs text-slate-500">{{ item.description }}</p>
                                    </td>
                                    <td class="px-4 py-4">{{ item.quantity }}</td>
                                    <td class="px-4 py-4">{{ formatCurrency(item.price) }}</td>
                                    <td class="px-4 py-4 text-right">{{ formatCurrency(item.quantity * item.price) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </CardContent>
                </Card>

                <Card class="border-white/10 bg-slate-900/80">
                    <CardContent class="space-y-4 pt-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Type</p>
                            <p class="mt-2 capitalize text-white">{{ transaction.type }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Status</p>
                            <p class="mt-2 text-white">{{ transaction.status }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Date</p>
                            <p class="mt-2 text-white">{{ formatDate(transaction.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Served by</p>
                            <p class="mt-2 text-white">{{ transaction.user_name || 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Customer</p>
                            <p class="mt-2 text-white">{{ transaction.customer_name || 'Walk-in customer' }}</p>
                            <p class="text-sm text-slate-400">{{ transaction.customer_phone || '' }}</p>
                            <p class="text-sm text-slate-400">{{ transaction.customer_email || '' }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/5 px-4 py-3">
                            <p class="text-sm text-slate-400">Total</p>
                            <p class="mt-2 font-display text-3xl font-semibold text-white">{{ formatCurrency(transaction.total_amount) }}</p>
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
    transaction: {
        id: number;
        type: string;
        status: string;
        customer_name: string | null;
        customer_email: string | null;
        customer_phone: string | null;
        total_amount: number;
        created_at: string;
        user_name: string | null;
        items: Array<{ id: number; product_name: string | null; description: string | null; quantity: number; price: number }>;
    };
}>();
</script>
