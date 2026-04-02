<template>
    <CustomerEditor :app="app" :auth="auth" :flash="flash" :routing="routing" :errors="errors" mode="create" />
</template>

<script setup lang="ts">
import CustomerEditor from '@/pages/Customers/Partials/CustomerEditor.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps>();
</script>
