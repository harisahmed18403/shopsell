<template>
    <InventoryEditor :app="app" :auth="auth" :flash="flash" :routing="routing" :errors="errors" :item="item" mode="edit" />
</template>

<script setup lang="ts">
import InventoryEditor from '@/pages/Inventory/Partials/InventoryEditor.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    item: {
        id: number;
        product_id: number | null;
        product: {
            id: number;
            name: string;
            image_url: string;
            variants?: Array<{ sale?: number }>;
        } | null;
        imei: string | null;
        condition: string | null;
        purchase_price: number;
        sale_price: number;
        status: string;
    };
}>();
</script>
