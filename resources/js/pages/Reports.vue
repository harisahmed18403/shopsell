<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-2">
                <p class="text-sm uppercase tracking-[0.35em] text-sky-300">Reports</p>
                <h1 class="font-display text-4xl font-semibold tracking-tight text-white">
                    Trend view
                </h1>
            </div>

            <div class="grid gap-4 lg:grid-cols-4">
                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Sales</p>
                    <p class="mt-3 font-display text-3xl font-semibold text-white">{{ formatCurrency(summary.sales) }}</p>
                </article>
                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Profit</p>
                    <p class="mt-3 font-display text-3xl font-semibold text-white">{{ formatCurrency(summary.profit) }}</p>
                </article>
                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Repairs</p>
                    <p class="mt-3 font-display text-3xl font-semibold text-white">{{ summary.repairs }}</p>
                </article>
                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Outstanding</p>
                    <p class="mt-3 font-display text-3xl font-semibold text-white">{{ formatCurrency(summary.outstanding_balance) }}</p>
                </article>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Monthly sales</p>
                    <div class="mt-4">
                        <TrendBars :items="monthlyData.map((item) => ({ label: item.month, value: item.total }))" :format="formatCurrency" />
                    </div>
                </article>

                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Buy vs sell</p>
                    <div class="mt-4 space-y-3">
                        <div v-for="item in buyVsSell" :key="`${item.month}-${item.type}`" class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                            <span class="text-sm text-slate-300">{{ item.month }} / {{ item.type }}</span>
                            <span class="font-medium text-white">{{ formatCurrency(item.total) }}</span>
                        </div>
                    </div>
                </article>

                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Top categories</p>
                    <div class="mt-4">
                        <MetricBars :items="topCategories" />
                    </div>
                </article>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Profit timeline</p>
                    <div class="mt-4">
                        <TrendBars :items="profitData.map((item) => ({ label: item.month, value: item.profit }))" :format="formatCurrency" />
                    </div>
                </article>

                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Payment methods</p>
                    <div class="mt-4 space-y-3">
                        <div v-for="item in paymentMethods" :key="item.label" class="rounded-2xl bg-white/5 px-4 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-300">{{ item.label }}</span>
                                <span class="text-sm text-white">{{ item.value }} txns</span>
                            </div>
                            <p class="mt-2 text-lg font-medium text-white">{{ formatCurrency(item.total_paid) }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Top devices</p>
                <div class="mt-4">
                    <MetricBars :items="deviceBreakdown" />
                </div>
            </article>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import MetricBars from '@/components/app/MetricBars.vue';
import TrendBars from '@/components/app/TrendBars.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

interface MonthlyTotal {
    month: string;
    total: number;
}

interface BuySellTotal extends MonthlyTotal {
    type: string;
}

interface ProfitTotal {
    month: string;
    profit: number;
}

defineProps<SharedPageProps & {
    summary: {
        sales: number;
        profit: number;
        repairs: number;
        outstanding_balance: number;
    };
    monthlyData: MonthlyTotal[];
    buyVsSell: BuySellTotal[];
    profitData: ProfitTotal[];
    topCategories: Array<{ label: string; value: number }>;
    paymentMethods: Array<{ label: string; value: number; total_paid: number }>;
    deviceBreakdown: Array<{ label: string; value: number }>;
}>();
</script>
