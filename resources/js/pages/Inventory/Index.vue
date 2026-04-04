<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Inventory</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">Physical stock</h1>
                </div>
                <Link :href="appPath(app.base_path, '/inventory/create')">
                    <Button>Add new item</Button>
                </Link>
            </div>

            <Card class="border-white/10 bg-slate-900/80">
                <CardContent class="pt-6">
                    <form class="flex gap-3" @submit.prevent="submitFilters">
                        <Input v-model="searchForm.search" type="text" placeholder="Search by product name or IMEI..." />
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
                                    <th class="px-6 py-4">Product</th>
                                    <th class="px-6 py-4">IMEI</th>
                                    <th class="px-6 py-4">Condition</th>
                                    <th class="px-6 py-4">Purchase</th>
                                    <th class="px-6 py-4">Sale</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Added</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="item in items" :key="item.id" class="text-sm text-slate-200">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-white">{{ item.product_name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ item.category || 'Uncategorised' }}</p>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs">{{ item.imei || 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ item.condition || 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ formatCurrency(item.purchase_price) }}</td>
                                    <td class="px-6 py-4">{{ formatCurrency(item.sale_price) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-3 py-1 text-xs text-emerald-100">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-400">{{ formatDate(item.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="appPath(app.base_path, `/inventory/${item.id}/edit`)">
                                                <Button variant="outline" size="sm">
                                                    Edit
                                                </Button>
                                            </Link>
                                            <Button variant="ghost" size="sm" @click="removeItem(item.id)">
                                                Remove
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="items.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">
                                        No inventory items found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-400">{{ pagination.total }} items</p>
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
import { formatCurrency, formatDate } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

const props = defineProps<SharedPageProps & {
    filters: {
        search: string;
    };
    items: Array<{
        id: number;
        product_name: string;
        category: string | null;
        imei: string | null;
        condition: string | null;
        purchase_price: number;
        sale_price: number;
        status: string;
        created_at: string;
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

const searchForm = useForm({
    search: props.filters.search,
});

function submitFilters() {
    router.get(appPath(props.app.base_path, '/inventory'), {
        search: searchForm.search || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

function resetFilters() {
    searchForm.reset();
    router.get(appPath(props.app.base_path, '/inventory'));
}

function removeItem(id: number) {
    if (window.confirm('Remove this item from inventory?')) {
        router.delete(appPath(props.app.base_path, `/inventory/${id}`));
    }
}
</script>
