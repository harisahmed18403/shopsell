<template>
    <GuestLayout :app-name="app.name">
        <section class="grid w-full gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
            <div class="space-y-6">
                <p class="text-sm uppercase tracking-[0.4em] text-rose-300">
                    Modern retail operations
                </p>
                <h1 class="max-w-3xl font-display text-5xl font-semibold tracking-tight text-white sm:text-6xl">
                    Run inventory, sales, repairs, and customers from one control room.
                </h1>
                <p class="max-w-2xl text-lg leading-8 text-slate-300">
                    ShopSell keeps the Laravel backend intact while delivering a faster, more focused operator interface for day-to-day trade.
                </p>
                <div class="flex flex-wrap gap-4">
                    <Link
                        v-if="auth.user"
                        href="/dashboard"
                        class="rounded-full bg-rose-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rose-400"
                    >
                        Open dashboard
                    </Link>
                    <template v-else>
                        <Link href="/register" class="rounded-full bg-rose-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rose-400">
                            Start account
                        </Link>
                        <Link href="/login" class="rounded-full border border-white/10 px-6 py-3 text-sm font-semibold text-slate-100 transition hover:border-white/20 hover:bg-white/5">
                            Sign in
                        </Link>
                    </template>
                </div>
            </div>

            <div class="grid gap-4">
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Core flows</p>
                    <div class="mt-4 grid gap-3">
                        <div v-for="feature in features" :key="feature.title" class="rounded-2xl border border-white/5 bg-slate-900/70 p-4">
                            <p class="font-medium text-white">{{ feature.title }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ feature.body }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import GuestLayout from '@/layouts/GuestLayout.vue';
import type { AuthUser } from '@/types';

defineProps<{
    app: {
        name: string;
    };
    auth: {
        user: AuthUser | null;
    };
}>();

const features = [
    {
        title: 'Inventory control',
        body: 'Track stock, conditions, and incoming devices without switching systems.',
    },
    {
        title: 'Sales and repairs',
        body: 'Capture buy, sell, and repair transactions while preserving server-side business rules.',
    },
    {
        title: 'Reporting clarity',
        body: 'Review daily, weekly, and monthly performance with clean summaries and trend views.',
    },
];
</script>
