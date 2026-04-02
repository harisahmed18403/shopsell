<template>
    <ProductEditor
        :app="app"
        :auth="auth"
        :flash="flash"
        :routing="routing"
        :errors="errors"
        :categories="categories"
        :product="product"
        mode="edit"
    />
</template>

<script setup lang="ts">
import ProductEditor from '@/pages/Products/Partials/ProductEditor.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    categories: Array<{
        id: number;
        name: string;
    }>;
    product: {
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
}>();
</script>
