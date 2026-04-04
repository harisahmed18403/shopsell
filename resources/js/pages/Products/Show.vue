<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Product detail</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">{{ product.name }}</h1>
                </div>
                <div class="flex gap-3">
                    <Link :href="appPath(app.base_path, `/products/${product.id}/edit`)">
                        <Button>Edit</Button>
                    </Link>
                    <Link :href="appPath(app.base_path, '/products')">
                        <Button variant="ghost">Back</Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <Card class="border-white/10 bg-slate-900/80">
                    <CardContent class="space-y-6 pt-6">
                        <div class="flex flex-col gap-6 md:flex-row">
                            <div class="flex-1 space-y-4">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Category</p>
                                        <p class="mt-2 text-white">{{ product.category || 'Uncategorised' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Grade</p>
                                        <p class="mt-2 text-white">{{ product.grade || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Color</p>
                                        <p class="mt-2 text-white">{{ product.color || 'N/A' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Description</p>
                                    <p class="mt-2 leading-7 text-slate-300">
                                        {{ product.description || 'No description provided.' }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="product.image_url" class="h-36 w-36 overflow-hidden rounded-[1.75rem] border border-white/10 bg-white">
                                <img :src="product.image_url" :alt="product.name" class="h-full w-full object-contain" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-white/10 bg-slate-900/80">
                    <CardContent class="space-y-4 pt-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Your pricing</p>
                        </div>
                        <div class="rounded-2xl bg-white/5 px-4 py-3">
                            <p class="text-sm text-slate-400">Sale price</p>
                            <p class="mt-2 text-3xl font-semibold text-white">{{ formatCurrency(product.sale_price) }}</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white/5 px-4 py-3">
                                <p class="text-sm text-slate-400">Cash</p>
                                <p class="mt-2 text-lg font-semibold text-rose-300">{{ formatCurrency(product.cash_price) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/5 px-4 py-3">
                                <p class="text-sm text-slate-400">Voucher</p>
                                <p class="mt-2 text-lg font-semibold text-sky-300">{{ formatCurrency(product.voucher_price) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-white/10 bg-slate-900/80">
                <CardContent class="pt-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">CeX market</p>
                            <p v-if="product.last_synced_at" class="mt-2 text-sm text-slate-400">
                                Synced {{ formatDate(product.last_synced_at) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="product.cex_products.length" class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10 text-left">
                            <thead class="text-xs uppercase tracking-[0.25em] text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Variant</th>
                                    <th class="px-4 py-3">Grade</th>
                                    <th class="px-4 py-3">Sale</th>
                                    <th class="px-4 py-3">Cash</th>
                                    <th class="px-4 py-3">Voucher</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="variant in product.cex_products" :key="variant.id" class="text-sm text-slate-200">
                                    <td class="px-4 py-4">{{ variant.name }}</td>
                                    <td class="px-4 py-4">{{ variant.grade || 'N/A' }}</td>
                                    <td class="px-4 py-4">{{ formatCurrency(variant.sale_price) }}</td>
                                    <td class="px-4 py-4 text-rose-300">{{ formatCurrency(variant.cash_price) }}</td>
                                    <td class="px-4 py-4 text-sky-300">{{ formatCurrency(variant.voucher_price) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="mt-6 rounded-2xl border border-dashed border-white/10 px-6 py-10 text-sm text-slate-500">
                        No CeX data available for this product.
                    </div>
                </CardContent>
            </Card>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { appPath } from '@/lib/app-path';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    product: {
        id: number;
        name: string;
        category: string | null;
        grade: string | null;
        color: string | null;
        description: string | null;
        sale_price: number;
        cash_price: number;
        voucher_price: number;
        image_url: string | null;
        last_synced_at: string | null;
        cex_products: Array<{
            id: number;
            name: string;
            grade: string | null;
            sale_price: number;
            cash_price: number;
            voucher_price: number;
        }>;
    };
}>();
</script>
