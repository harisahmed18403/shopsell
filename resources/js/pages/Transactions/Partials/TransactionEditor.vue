<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Transactions</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">
                        {{ mode === 'create' ? 'New transaction' : `Edit #${transaction?.id}` }}
                    </h1>
                </div>
                <Link href="/transactions"><Button variant="ghost">Cancel</Button></Link>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr_0.8fr]">
                    <Card class="border-white/10 bg-slate-900/80">
                        <CardContent class="space-y-5 pt-6">
                            <FormField id="type" label="Transaction type" :error="errors.type as string">
                                <select id="type" v-model="form.type" class="h-10 w-full rounded-xl border border-white/10 bg-white/5 px-3 text-sm text-white">
                                    <option value="sell">Sell</option>
                                    <option value="buy">Buy</option>
                                    <option value="repair">Repair</option>
                                </select>
                            </FormField>
                        </CardContent>
                    </Card>

                    <Card class="border-white/10 bg-slate-900/80">
                        <CardContent class="space-y-5 pt-6">
                            <div class="grid gap-5 md:grid-cols-2">
                                <FormField id="customer_id" label="Existing customer" :error="errors.customer_id as string">
                                    <select id="customer_id" v-model="form.customer_id" class="h-10 w-full rounded-xl border border-white/10 bg-white/5 px-3 text-sm text-white" @change="applySelectedCustomer">
                                        <option value="">Walk-in / new</option>
                                        <option v-for="customer in customers" :key="customer.id" :value="String(customer.id)">
                                            {{ customer.name }}
                                        </option>
                                    </select>
                                </FormField>
                                <FormField id="customer_name" label="Full name" :error="errors.customer_name as string">
                                    <Input id="customer_name" v-model="form.customer_name" type="text" />
                                </FormField>
                                <FormField id="customer_email" label="Email" :error="errors.customer_email as string">
                                    <Input id="customer_email" v-model="form.customer_email" type="email" />
                                </FormField>
                                <FormField id="customer_phone" label="Phone" :error="errors.customer_phone as string">
                                    <Input id="customer_phone" v-model="form.customer_phone" type="text" />
                                </FormField>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="border-white/10 bg-slate-900/80">
                        <CardContent class="space-y-4 pt-6">
                            <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Summary</p>
                            <p class="font-display text-4xl font-semibold text-white">{{ formatCurrency(total) }}</p>
                            <Button type="submit" :disabled="form.processing" class="w-full">
                                {{ mode === 'create' ? 'Complete transaction' : 'Update transaction' }}
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <Card class="border-white/10 bg-slate-900/80">
                    <CardContent class="space-y-5 pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Items</p>
                                <h2 class="mt-2 text-2xl font-semibold text-white">Line items</h2>
                            </div>
                            <Button type="button" variant="outline" @click="addItem">Add item</Button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(item, index) in form.items" :key="index" class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <div class="grid gap-4 lg:grid-cols-[1.1fr_1fr_120px_160px_120px_auto]">
                                    <div class="lg:col-span-2">
                                        <FormField :label="`Catalog search ${index + 1}`">
                                            <div class="relative">
                                                <Input v-model="item.search" type="text" placeholder="Search product..." @input="debouncedSearch(index)" />
                                                <div v-if="item.showResults && item.searchResults.length" class="absolute z-20 mt-2 max-h-64 w-full overflow-y-auto rounded-2xl border border-white/10 bg-slate-900 p-2 shadow-2xl shadow-black/40">
                                                    <button
                                                        v-for="result in item.searchResults"
                                                        :key="result.id"
                                                        type="button"
                                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left transition hover:bg-white/5"
                                                        @click="selectProduct(index, result)"
                                                    >
                                                        <div class="h-10 w-10 overflow-hidden rounded-xl border border-white/10 bg-white">
                                                            <img :src="result.image_url" :alt="result.name" class="h-full w-full object-contain" />
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-medium text-white">{{ result.name }}</p>
                                                            <p class="text-xs text-slate-500">Choose product</p>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>
                                        </FormField>
                                    </div>
                                    <FormField :label="`Description ${index + 1}`" :error="errors[`items.${index}.description`] as string">
                                        <Input v-model="item.description" type="text" />
                                    </FormField>
                                    <FormField :label="`Qty ${index + 1}`" :error="errors[`items.${index}.quantity`] as string">
                                        <Input v-model="item.quantity" type="number" min="1" />
                                    </FormField>
                                    <FormField :label="`Price ${index + 1}`" :error="errors[`items.${index}.price`] as string">
                                        <Input v-model="item.price" type="number" step="0.01" min="0" />
                                    </FormField>
                                    <div class="flex items-end justify-between gap-3">
                                        <div class="pb-2 text-sm text-slate-300">{{ formatCurrency(Number(item.quantity) * Number(item.price || 0)) }}</div>
                                        <Button v-if="form.items.length > 1" type="button" variant="ghost" size="sm" @click="removeItem(index)">Remove</Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import FormField from '@/components/app/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

interface SearchResult {
    id: number;
    name: string;
    image_url: string;
}

type ItemState = {
    product_id: string;
    description: string;
    quantity: number | string;
    price: number | string;
    search: string;
    searchResults: SearchResult[];
    showResults: boolean;
    searchTimer?: number | null;
};

const props = defineProps<SharedPageProps & {
    customers: Array<{ id: number; name: string; email: string | null; phone: string | null }>;
    transaction?: {
        id: number;
        type: string;
        status: string;
        customer_id: number | null;
        customer_name: string | null;
        customer_email: string | null;
        customer_phone: string | null;
        items: Array<{ product_id: number | null; product_name: string | null; description: string | null; quantity: number; price: number }>;
    };
    mode: 'create' | 'edit';
}>();

function makeItem(item?: { product_id: number | null; product_name: string | null; description: string | null; quantity: number; price: number }): ItemState {
    return {
        product_id: item?.product_id ? String(item.product_id) : '',
        description: item?.description ?? item?.product_name ?? '',
        quantity: item?.quantity ?? 1,
        price: item?.price ?? 0,
        search: item?.product_name ?? item?.description ?? '',
        searchResults: [],
        showResults: false,
        searchTimer: null,
    };
}

const form = useForm({
    type: props.transaction?.type ?? 'sell',
    customer_id: props.transaction?.customer_id ? String(props.transaction.customer_id) : '',
    customer_name: props.transaction?.customer_name ?? '',
    customer_email: props.transaction?.customer_email ?? '',
    customer_phone: props.transaction?.customer_phone ?? '',
    items: props.transaction?.items?.length ? props.transaction.items.map((item) => makeItem(item)) : [makeItem()],
});

const total = computed(() => form.items.reduce((sum, item) => sum + Number(item.quantity || 0) * Number(item.price || 0), 0));

function addItem() {
    form.items.push(makeItem());
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function applySelectedCustomer() {
    const customer = props.customers.find((entry) => String(entry.id) === form.customer_id);
    if (!customer) {
        return;
    }
    form.customer_name = customer.name;
    form.customer_email = customer.email ?? '';
    form.customer_phone = customer.phone ?? '';
}

async function search(index: number) {
    const item = form.items[index];
    if (item.search.length < 2) {
        item.searchResults = [];
        item.showResults = false;
        return;
    }

    const response = await fetch(`/products/search?q=${encodeURIComponent(item.search)}`, { headers: { Accept: 'application/json' } });
    item.searchResults = await response.json();
    item.showResults = true;
}

function debouncedSearch(index: number) {
    const item = form.items[index];
    if (item.searchTimer) {
        window.clearTimeout(item.searchTimer);
    }

    item.searchTimer = window.setTimeout(() => search(index), 250);
}

function selectProduct(index: number, result: SearchResult) {
    const item = form.items[index];
    item.product_id = String(result.id);
    item.search = result.name;
    item.description = result.name;
    item.showResults = false;
}

function submit() {
    const payload = {
        ...form.data(),
        items: form.items.map((item) => ({
            product_id: item.product_id || null,
            description: item.description || null,
            quantity: Number(item.quantity),
            price: Number(item.price),
        })),
    };

    if (props.mode === 'create') {
        form.transform(() => payload).post('/transactions');
        return;
    }

    form.transform(() => payload).put(`/transactions/${props.transaction?.id}`);
}
</script>
