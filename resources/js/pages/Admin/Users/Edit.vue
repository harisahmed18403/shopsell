<template>
    <UserEditor
        :app="app"
        :auth="auth"
        :flash="flash"
        :routing="routing"
        :errors="errors"
        :user="user"
        mode="edit"
    />
</template>

<script setup lang="ts">
import UserEditor from '@/pages/Admin/Users/Partials/UserEditor.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
    };
}>();
</script>
