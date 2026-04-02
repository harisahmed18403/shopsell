<template>
    <TransactionEditor :app="app" :auth="auth" :flash="flash" :routing="routing" :errors="errors" :customers="customers" :initial-customer-id="initialCustomerId" mode="create" />
</template>

<script setup lang="ts">
import TransactionEditor from '@/pages/Transactions/Partials/TransactionEditor.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    customers: Array<{ id: number; name: string; email: string | null; phone: string | null }>;
    initialCustomerId: number | null;
}>();
</script>
