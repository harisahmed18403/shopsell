<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-2">
                <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Dashboard</p>
                <h1 class="font-display text-4xl font-semibold tracking-tight text-white">
                    Trading pulse
                </h1>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="metric in metrics"
                    :key="metric.label"
                    class="rounded-[1.75rem] border border-white/10 bg-slate-900/80 p-5 shadow-xl shadow-black/20"
                >
                    <p class="text-sm text-slate-400">{{ metric.label }}</p>
                    <p class="mt-3 font-display text-3xl font-semibold text-white">{{ formatCurrency(metric.value) }}</p>
                </article>
            </div>

            <section class="rounded-[2rem] border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-black/20">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Recent transactions</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white">Latest activity</h2>
                    </div>
                </div>

                <div v-if="recentTransactions.length" class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/10 text-left">
                        <thead class="text-xs uppercase tracking-[0.25em] text-slate-400">
                            <tr>
                                <th class="pb-3 pr-4">Type</th>
                                <th class="pb-3 pr-4">Customer</th>
                                <th class="pb-3 pr-4">Total</th>
                                <th class="pb-3 pr-4">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="transaction in recentTransactions" :key="transaction.id" class="text-sm text-slate-200">
                                <td class="py-4 pr-4 capitalize">{{ transaction.type }}</td>
                                <td class="py-4 pr-4">{{ transaction.customer_name }}</td>
                                <td class="py-4 pr-4">{{ formatCurrency(transaction.total_amount) }}</td>
                                <td class="py-4 pr-4">{{ formatDate(transaction.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="mt-6 rounded-2xl border border-dashed border-white/10 px-6 py-10 text-sm text-slate-400">
                    No transactions yet.
                </div>
            </section>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

interface DashboardTransaction {
    id: number;
    type: string;
    total_amount: number;
    customer_name: string;
    created_at: string;
}

const props = defineProps<SharedPageProps & {
    dailySales: number;
    weeklySales: number;
    monthlySales: number;
    recentTransactions: DashboardTransaction[];
}>();

const metrics = [
    { label: 'Today', value: props.dailySales },
    { label: 'This week', value: props.weeklySales },
    { label: 'This month', value: props.monthlySales },
];
</script>
