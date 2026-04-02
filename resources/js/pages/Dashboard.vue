<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-2">
                <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Dashboard</p>
                <h1 class="font-display text-4xl font-semibold tracking-tight text-white">
                    Trading pulse
                </h1>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <article
                    v-for="metric in metrics"
                    :key="metric.label"
                    class="rounded-[1.75rem] border border-white/10 bg-slate-900/80 p-5 shadow-xl shadow-black/20"
                >
                    <p class="text-sm text-slate-400">{{ metric.label }}</p>
                    <p class="mt-3 font-display text-3xl font-semibold text-white">{{ formatCurrency(metric.value) }}</p>
                    <p v-if="metric.detail" class="mt-2 text-xs text-slate-500">{{ metric.detail }}</p>
                </article>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <section class="rounded-[2rem] border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-black/20">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Sales trend</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white">Last 14 days</h2>
                    </div>
                    <div class="mt-6">
                        <TrendBars :items="salesTrend" :format="formatCurrency" />
                    </div>
                </section>

                <section class="rounded-[2rem] border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-black/20">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Mix</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white">Transaction breakdown</h2>
                    </div>
                    <div class="mt-6">
                        <MetricBars :items="typeBreakdown" />
                    </div>
                </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                <section class="rounded-[2rem] border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-black/20">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Payments</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white">Method mix</h2>
                    </div>
                    <div v-if="paymentMix.length" class="mt-6">
                        <MetricBars :items="paymentMix" />
                    </div>
                    <div v-else class="mt-6 rounded-2xl border border-dashed border-white/10 px-6 py-10 text-sm text-slate-400">
                        No payment method data yet.
                    </div>
                </section>

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
                                    <th class="pb-3 pr-4">Receipt</th>
                                    <th class="pb-3 pr-4">Type</th>
                                    <th class="pb-3 pr-4">Customer</th>
                                    <th class="pb-3 pr-4">Total</th>
                                    <th class="pb-3 pr-4">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="transaction in recentTransactions" :key="transaction.id" class="text-sm text-slate-200">
                                    <td class="py-4 pr-4 font-mono text-xs">{{ transaction.receipt_number || `#${transaction.id}` }}</td>
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
            </div>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import MetricBars from '@/components/app/MetricBars.vue';
import TrendBars from '@/components/app/TrendBars.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

interface DashboardTransaction {
    id: number;
    receipt_number: string | null;
    type: string;
    total_amount: number;
    customer_name: string;
    created_at: string;
}

const props = defineProps<SharedPageProps & {
    dailySales: number;
    weeklySales: number;
    monthlySales: number;
    transactionCount: number;
    averageTicket: number;
    outstandingBalance: number;
    salesTrend: Array<{ label: string; value: number }>;
    typeBreakdown: Array<{ label: string; value: number }>;
    paymentMix: Array<{ label: string; value: number }>;
    recentTransactions: DashboardTransaction[];
}>();

const metrics = [
    { label: 'Today', value: props.dailySales, detail: 'Revenue from sell and repair jobs completed today.' },
    { label: 'This week', value: props.weeklySales, detail: `${props.transactionCount} transactions recorded in the last 30 days.` },
    { label: 'This month', value: props.monthlySales, detail: `Average ticket ${formatCurrency(props.averageTicket || 0)}.` },
    { label: 'Outstanding', value: props.outstandingBalance, detail: 'Remaining balance still unpaid across recorded transactions.' },
];
</script>
