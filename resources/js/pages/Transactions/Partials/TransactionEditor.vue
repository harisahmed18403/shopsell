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
                <Link :href="appPath(app.base_path, '/transactions')"><Button variant="ghost">Cancel</Button></Link>
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
                            <FormField id="payment_method" label="Payment method" :error="errors.payment_method as string">
                                <select id="payment_method" v-model="form.payment_method" class="h-10 w-full rounded-xl border border-white/10 bg-white/5 px-3 text-sm text-white">
                                    <option value="Cash">Cash</option>
                                    <option value="Card">Card</option>
                                    <option value="Bank Transfer">Bank transfer</option>
                                    <option value="Voucher">Voucher</option>
                                    <option value="Mixed">Mixed</option>
                                </select>
                            </FormField>
                            <FormField id="amount_paid" label="Amount paid" :error="errors.amount_paid as string">
                                <Input id="amount_paid" v-model="form.amount_paid" type="number" step="0.01" min="0" />
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
                            <div class="rounded-2xl bg-white/5 px-4 py-3 text-sm text-slate-300">
                                <div class="flex items-center justify-between gap-4">
                                    <span>Amount paid</span>
                                    <span>{{ formatCurrency(amountPaid) }}</span>
                                </div>
                                <div class="mt-2 flex items-center justify-between gap-4">
                                    <span>Balance</span>
                                    <span>{{ formatCurrency(balance) }}</span>
                                </div>
                            </div>
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
                                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    <FormField :label="`Brand ${index + 1}`" :error="errors[`items.${index}.brand`] as string">
                                        <Input v-model="item.brand" type="text" placeholder="Apple" />
                                    </FormField>
                                    <FormField :label="`Model ${index + 1}`" :error="errors[`items.${index}.model`] as string">
                                        <Input v-model="item.model" type="text" placeholder="iPhone 16 Pro Max" />
                                    </FormField>
                                    <FormField :label="`Storage ${index + 1}`" :error="errors[`items.${index}.storage`] as string">
                                        <Input v-model="item.storage" type="text" placeholder="256 GB" />
                                    </FormField>
                                    <FormField :label="`Colour ${index + 1}`" :error="errors[`items.${index}.color`] as string">
                                        <Input v-model="item.color" type="text" placeholder="Black Titanium" />
                                    </FormField>
                                    <FormField :label="`IMEI 1 ${index + 1}`" :error="errors[`items.${index}.imei_1`] as string">
                                        <Input v-model="item.imei_1" type="text" />
                                    </FormField>
                                    <FormField :label="`IMEI 2 ${index + 1}`" :error="errors[`items.${index}.imei_2`] as string">
                                        <Input v-model="item.imei_2" type="text" />
                                    </FormField>
                                    <FormField :label="`Condition ${index + 1}`" :error="errors[`items.${index}.condition_grade`] as string">
                                        <Input v-model="item.condition_grade" type="text" placeholder="A" />
                                    </FormField>
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
import { appPath } from '@/lib/app-path';
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
    brand: string;
    model: string;
    storage: string;
    color: string;
    imei_1: string;
    imei_2: string;
    condition_grade: string;
    quantity: number | string;
    price: number | string;
    search: string;
    searchResults: SearchResult[];
    showResults: boolean;
    searchTimer?: number | null;
};

const props = defineProps<SharedPageProps & {
    customers: Array<{ id: number; name: string; email: string | null; phone: string | null }>;
    initialCustomerId?: number | null;
    transaction?: {
        id: number;
        type: string;
        status: string;
        customer_id: number | null;
        customer_name: string | null;
        customer_email: string | null;
        customer_phone: string | null;
        amount_paid: number;
        balance_amount: number;
        payment_method: string | null;
        items: Array<{
            product_id: number | null;
            product_name: string | null;
            description: string | null;
            brand: string | null;
            model: string | null;
            storage: string | null;
            color: string | null;
            imei_1: string | null;
            imei_2: string | null;
            condition_grade: string | null;
            quantity: number;
            price: number;
        }>;
    };
    mode: 'create' | 'edit';
}>();

function splitProductName(name: string | null | undefined): { brand: string; model: string } {
    const normalized = name?.trim() ?? '';
    if (!normalized) {
        return { brand: '', model: '' };
    }

    const [brand = '', ...modelParts] = normalized.split(/\s+/);

    return {
        brand,
        model: modelParts.join(' '),
    };
}

function makeItem(item?: {
    product_id: number | null;
    product_name: string | null;
    description: string | null;
    brand: string | null;
    model: string | null;
    storage: string | null;
    color: string | null;
    imei_1: string | null;
    imei_2: string | null;
    condition_grade: string | null;
    quantity: number;
    price: number;
}): ItemState {
    const parsedName = splitProductName(item?.product_name);

    return {
        product_id: item?.product_id ? String(item.product_id) : '',
        description: item?.description ?? item?.product_name ?? '',
        brand: item?.brand ?? parsedName.brand,
        model: item?.model ?? parsedName.model,
        storage: item?.storage ?? '',
        color: item?.color ?? '',
        imei_1: item?.imei_1 ?? '',
        imei_2: item?.imei_2 ?? '',
        condition_grade: item?.condition_grade ?? '',
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
    customer_id: props.transaction?.customer_id ? String(props.transaction.customer_id) : (props.initialCustomerId ? String(props.initialCustomerId) : ''),
    customer_name: props.transaction?.customer_name ?? '',
    customer_email: props.transaction?.customer_email ?? '',
    customer_phone: props.transaction?.customer_phone ?? '',
    amount_paid: props.transaction?.amount_paid ?? '',
    payment_method: props.transaction?.payment_method ?? 'Cash',
    items: props.transaction?.items?.length ? props.transaction.items.map((item) => makeItem(item)) : [makeItem()],
});

const total = computed(() => form.items.reduce((sum, item) => sum + Number(item.quantity || 0) * Number(item.price || 0), 0));
const amountPaid = computed(() => form.amount_paid === '' ? total.value : Number(form.amount_paid || 0));
const balance = computed(() => Math.max(0, total.value - amountPaid.value));

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

if (form.customer_id) {
    applySelectedCustomer();
}

async function search(index: number) {
    const item = form.items[index];
    if (item.search.length < 2) {
        item.searchResults = [];
        item.showResults = false;
        return;
    }

    const response = await fetch(appPath(props.app.base_path, `/products/search?q=${encodeURIComponent(item.search)}`), { headers: { Accept: 'application/json' } });
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
    const parsedName = splitProductName(result.name);
    item.product_id = String(result.id);
    item.search = result.name;
    item.description = result.name;
    if (!item.brand) {
        item.brand = parsedName.brand;
    }
    if (!item.model) {
        item.model = parsedName.model;
    }
    item.showResults = false;
}

function submit() {
    const payload = {
        ...form.data(),
        amount_paid: form.amount_paid === '' ? null : Number(form.amount_paid),
        items: form.items.map((item) => ({
            product_id: item.product_id || null,
            description: item.description || null,
            brand: item.brand || null,
            model: item.model || null,
            storage: item.storage || null,
            color: item.color || null,
            imei_1: item.imei_1 || null,
            imei_2: item.imei_2 || null,
            condition_grade: item.condition_grade || null,
            quantity: Number(item.quantity),
            price: Number(item.price),
        })),
    };

    if (props.mode === 'create') {
        form.transform(() => payload).post(appPath(props.app.base_path, '/transactions'));
        return;
    }

    form.transform(() => payload).put(appPath(props.app.base_path, `/transactions/${props.transaction?.id}`));
}
</script>
