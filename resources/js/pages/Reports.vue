<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-2">
                <p class="text-sm uppercase tracking-[0.35em] text-sky-300">Reports</p>
                <h1 class="font-display text-4xl font-semibold tracking-tight text-white">
                    Trend view
                </h1>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <article class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Monthly sales</p>
                    <div class="mt-4 space-y-3">
                        <div v-for="item in monthlyData" :key="item.month" class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                            <span class="text-sm text-slate-300">{{ item.month }}</span>
                            <span class="font-medium text-white">{{ formatCurrency(item.total) }}</span>
                        </div>
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
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Profit</p>
                    <div class="mt-4 space-y-3">
                        <div v-for="item in profitData" :key="item.month" class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3">
                            <span class="text-sm text-slate-300">{{ item.month }}</span>
                            <span class="font-medium text-white">{{ formatCurrency(item.profit) }}</span>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
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
    monthlyData: MonthlyTotal[];
    buyVsSell: BuySellTotal[];
    profitData: ProfitTotal[];
}>();
</script>
