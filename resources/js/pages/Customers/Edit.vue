<template>
    <CustomerEditor :app="app" :auth="auth" :flash="flash" :routing="routing" :errors="errors" :customer="customer" mode="edit" />
</template>

<script setup lang="ts">
import CustomerEditor from '@/pages/Customers/Partials/CustomerEditor.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    customer: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
    };
}>();
</script>
