<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Inventory</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">Add stock item</h1>
                </div>
                <Link href="/inventory">
                    <Button variant="ghost">Cancel</Button>
                </Link>
            </div>

            <Card class="mx-auto max-w-3xl border-white/10 bg-slate-900/80">
                <CardContent class="space-y-5 pt-6">
                    <ProductSearchSelect v-model="selectedProduct" />

                    <form class="space-y-5" @submit.prevent="submit">
                        <FormField label="Selected product" :error="errors.product_id as string">
                            <Input :model-value="selectedProduct?.name || ''" type="text" readonly />
                        </FormField>

                        <div class="grid gap-5 md:grid-cols-2">
                            <FormField id="imei" label="IMEI / Serial number" :error="errors.imei as string">
                                <Input id="imei" v-model="form.imei" type="text" />
                            </FormField>
                            <FormField id="condition" label="Condition" :error="errors.condition as string">
                                <Input id="condition" v-model="form.condition" type="text" />
                            </FormField>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <FormField id="purchase_price" label="Purchase price" :error="errors.purchase_price as string">
                                <Input id="purchase_price" v-model="form.purchase_price" type="number" step="0.01" />
                            </FormField>
                            <FormField id="sale_price" label="Listing sale price" :error="errors.sale_price as string">
                                <Input id="sale_price" v-model="form.sale_price" type="number" step="0.01" />
                            </FormField>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing || !selectedProduct">
                                Add to stock
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
import { ref, watch } from 'vue';

import FormField from '@/components/app/FormField.vue';
import ProductSearchSelect, { type SearchProduct } from '@/components/app/ProductSearchSelect.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps>();

const selectedProduct = ref<SearchProduct | null>(null);

const form = useForm({
    product_id: '',
    imei: '',
    condition: '',
    purchase_price: '',
    sale_price: '',
});

watch(selectedProduct, (product) => {
    form.product_id = product ? String(product.id) : '';
    form.sale_price = product?.variants?.[0]?.sale ? String(product.variants[0].sale) : '';
});

function submit() {
    form.post('/inventory');
}
</script>
