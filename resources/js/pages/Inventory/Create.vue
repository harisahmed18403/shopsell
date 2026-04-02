<template>
    <InventoryEditor :app="app" :auth="auth" :flash="flash" :routing="routing" :errors="errors" mode="create" />
</template>

<script setup lang="ts">
import InventoryEditor from '@/pages/Inventory/Partials/InventoryEditor.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps>();
</script>
