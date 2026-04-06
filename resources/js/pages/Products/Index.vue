<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Products</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">Catalog</h1>
                </div>
                <Link :href="appPath(app.base_path, '/products/create')">
                    <Button>Add product</Button>
                </Link>
            </div>

            <Card class="border-white/10 bg-slate-900/80">
                <CardContent class="pt-6">
                    <form class="grid gap-4 md:grid-cols-[1fr_220px_auto]" @submit.prevent="submitFilters">
                        <Input v-model="filtersForm.search" type="text" placeholder="Search products..." />
                        <select v-model="filtersForm.category_id" class="h-10 rounded-xl border border-white/10 bg-white/5 px-3 text-sm text-white">
                            <option value="">All categories</option>
                            <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                {{ category.name }}
                            </option>
                        </select>
                        <div class="flex gap-2">
                            <Button type="submit">Filter</Button>
                            <Button type="button" variant="ghost" @click="resetFilters">Reset</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card class="border-white/10 bg-slate-900/80">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10 text-left">
                            <thead class="text-xs uppercase tracking-[0.25em] text-slate-400">
                                <tr>
                                    <th class="px-6 py-4">Product</th>
                                    <th class="px-6 py-4">Category</th>
                                    <th class="px-6 py-4">Condition</th>
                                    <th class="px-6 py-4">Color</th>
                                    <th class="px-6 py-4">Price</th>
                                    <th class="px-6 py-4">CeX market</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="product in products" :key="product.id" class="text-sm text-slate-200">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-12 w-12 overflow-hidden rounded-2xl border border-white/10 bg-white">
                                                <img :src="product.image_url || 'https://via.placeholder.com/150'" :alt="product.name" class="h-full w-full object-contain" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-white">{{ product.name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">{{ product.category || 'Uncategorised' }}</td>
                                    <td class="px-6 py-4">{{ product.grade || 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ product.color || 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ formatCurrency(product.sale_price) }}</td>
                                    <td class="px-6 py-4">
                                        <div v-if="product.cex_variants.length" class="flex flex-wrap gap-2 text-xs">
                                            <div
                                                v-for="variant in product.cex_variants"
                                                :key="variant.grade"
                                                class="min-w-[88px] rounded-xl border border-white/10 bg-white/5 px-3 py-2"
                                            >
                                                <p class="font-semibold uppercase tracking-[0.2em] text-slate-400">{{ variant.grade }}</p>
                                                <p class="mt-1 text-sky-300">S: {{ formatCurrency(variant.sale_price) }}</p>
                                                <p class="text-rose-300">C: {{ formatCurrency(variant.cash_price) }}</p>
                                                <p class="text-amber-300">V: {{ formatCurrency(variant.voucher_price) }}</p>
                                            </div>
                                        </div>
                                        <span v-else class="text-xs text-slate-500">No market data</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="appPath(app.base_path, `/products/${product.id}`)">
                                                <Button variant="ghost" size="sm">View</Button>
                                            </Link>
                                            <Link :href="appPath(app.base_path, `/products/${product.id}/edit`)">
                                                <Button variant="outline" size="sm">Edit</Button>
                                            </Link>
                                            <Button variant="ghost" size="sm" @click="destroyProduct(product.id)">Delete</Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="products.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                        No products matched the current filters.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-400">
                    {{ pagination.total }} products
                </p>
                <PaginationNav :links="pagination.links" />
            </div>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';

import PaginationNav from '@/components/app/PaginationNav.vue';
import { appPath } from '@/lib/app-path';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

const props = defineProps<SharedPageProps & {
    filters: {
        search: string;
        category_id: string;
    };
    categories: Array<{
        id: number;
        name: string;
    }>;
    products: Array<{
        id: number;
        name: string;
        category: string | null;
        grade: string | null;
        color: string | null;
        sale_price: number;
        image_url: string | null;
        cex_variants: Array<{
            grade: string;
            sale_price: number;
            cash_price: number;
            voucher_price: number;
        }>;
    }>;
    pagination: {
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}>();

const filtersForm = useForm({
    search: props.filters.search,
    category_id: props.filters.category_id,
});

function submitFilters() {
    router.get(appPath(props.app.base_path, '/products'), {
        search: filtersForm.search || undefined,
        category_id: filtersForm.category_id || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

function resetFilters() {
    filtersForm.reset();
    router.get(appPath(props.app.base_path, '/products'));
}

function destroyProduct(id: number) {
    if (window.confirm('Delete this product?')) {
        router.delete(appPath(props.app.base_path, `/products/${id}`));
    }
}
</script>
