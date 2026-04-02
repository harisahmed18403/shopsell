<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Products</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">
                        {{ mode === 'create' ? 'Add product' : `Edit ${product?.name}` }}
                    </h1>
                </div>
                <Link href="/products">
                    <Button variant="ghost">Cancel</Button>
                </Link>
            </div>

            <Card class="mx-auto max-w-3xl border-white/10 bg-slate-900/80">
                <CardContent class="pt-6">
                    <form class="space-y-5" @submit.prevent="submit">
                        <FormField id="name" label="Product name" :error="errors.name as string">
                            <Input id="name" v-model="form.name" type="text" />
                        </FormField>

                        <FormField id="category_id" label="Category" :error="errors.category_id as string">
                            <select id="category_id" v-model="form.category_id" class="h-10 w-full rounded-xl border border-white/10 bg-white/5 px-3 text-sm text-white">
                                <option value="">Select category</option>
                                <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                    {{ category.name }}
                                </option>
                            </select>
                        </FormField>

                        <div class="grid gap-5 md:grid-cols-3">
                            <FormField id="sale_price" label="Sale price" :error="errors.sale_price as string">
                                <Input id="sale_price" v-model="form.sale_price" type="number" step="0.01" />
                            </FormField>
                            <FormField id="cash_price" label="Cash price" :error="errors.cash_price as string">
                                <Input id="cash_price" v-model="form.cash_price" type="number" step="0.01" />
                            </FormField>
                            <FormField id="voucher_price" label="Voucher price" :error="errors.voucher_price as string">
                                <Input id="voucher_price" v-model="form.voucher_price" type="number" step="0.01" />
                            </FormField>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <FormField id="color" label="Color" :error="errors.color as string">
                                <Input id="color" v-model="form.color" type="text" />
                            </FormField>
                            <FormField id="grade" label="Grade" :error="errors.grade as string">
                                <Input id="grade" v-model="form.grade" type="text" />
                            </FormField>
                        </div>

                        <FormField id="description" label="Description" :error="errors.description as string">
                            <textarea id="description" v-model="form.description" class="min-h-28 w-full rounded-xl border border-white/10 bg-white/5 px-3 py-3 text-sm text-white" />
                        </FormField>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing">
                                {{ mode === 'create' ? 'Save product' : 'Update product' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

import FormField from '@/components/app/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SharedPageProps } from '@/types';

const props = defineProps<SharedPageProps & {
    categories: Array<{
        id: number;
        name: string;
    }>;
    product?: {
        id: number;
        name: string;
        category_id: number;
        sale_price: number | null;
        cash_price: number | null;
        voucher_price: number | null;
        color: string | null;
        grade: string | null;
        description: string | null;
    };
    mode: 'create' | 'edit';
}>();

const form = useForm({
    name: props.product?.name ?? '',
    category_id: props.product?.category_id ? String(props.product.category_id) : '',
    sale_price: props.product?.sale_price ?? '',
    cash_price: props.product?.cash_price ?? '',
    voucher_price: props.product?.voucher_price ?? '',
    color: props.product?.color ?? '',
    grade: props.product?.grade ?? '',
    description: props.product?.description ?? '',
});

function submit() {
    if (props.mode === 'create') {
        form.post('/products');
        return;
    }

    form.patch(`/products/${props.product?.id}`);
}
</script>
