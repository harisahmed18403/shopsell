<template>
    <div class="space-y-3">
        <div v-for="item in items" :key="item.label" class="space-y-2">
            <div class="flex items-center justify-between gap-4 text-sm">
                <span class="text-slate-300">{{ item.label }}</span>
                <span class="text-white">{{ item.value }}</span>
            </div>
            <div class="h-2 rounded-full bg-white/5">
                <div class="h-2 rounded-full bg-gradient-to-r from-rose-400 to-sky-400" :style="{ width: `${max === 0 ? 0 : (item.value / max) * 100}%` }" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    items: Array<{ label: string; value: number }>;
}>();

const max = computed(() => Math.max(0, ...props.items.map((item) => item.value)));
</script>
