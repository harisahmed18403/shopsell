<template>
    <ProductEditor
        :app="app"
        :auth="auth"
        :flash="flash"
        :routing="routing"
        :errors="errors"
        :categories="categories"
        mode="create"
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
}>();
</script>
