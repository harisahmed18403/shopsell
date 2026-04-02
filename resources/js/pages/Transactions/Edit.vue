<template>
    <TransactionEditor :app="app" :auth="auth" :flash="flash" :routing="routing" :errors="errors" :customers="customers" :transaction="transaction" mode="edit" />
</template>

<script setup lang="ts">
import TransactionEditor from '@/pages/Transactions/Partials/TransactionEditor.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    customers: Array<{ id: number; name: string; email: string | null; phone: string | null }>;
    transaction: {
        id: number;
        type: string;
        status: string;
        customer_id: number | null;
        customer_name: string | null;
        customer_email: string | null;
        customer_phone: string | null;
        items: Array<{ product_id: number | null; product_name: string | null; description: string | null; quantity: number; price: number }>;
    };
}>();
</script>
