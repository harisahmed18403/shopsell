<template>
    <div class="space-y-2">
        <Label v-if="label" :for="id" class="text-sm font-medium text-slate-200">
            {{ label }}
        </Label>
        <slot />
        <p v-if="description" class="text-xs text-slate-400">
            {{ description }}
        </p>
        <p v-if="error" class="text-sm text-rose-300">
            {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { Label } from '@/components/ui/label';

defineProps<{
    id?: string;
    label?: string;
    description?: string;
    error?: string;
}>();
</script>
