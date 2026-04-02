<template>
    <UserEditor
        :app="app"
        :auth="auth"
        :flash="flash"
        :routing="routing"
        :errors="errors"
        mode="create"
    />
</template>

<script setup lang="ts">
import UserEditor from '@/pages/Admin/Users/Partials/UserEditor.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps>();
</script>
