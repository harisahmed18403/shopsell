<template>
    <div class="flex items-end gap-3">
        <div v-for="item in items" :key="item.label" class="flex flex-1 flex-col items-center gap-2">
            <div class="flex h-40 w-full items-end rounded-t-2xl bg-white/5 px-1">
                <div class="w-full rounded-t-xl bg-gradient-to-t from-rose-500 to-sky-400" :style="{ height: `${max === 0 ? 0 : Math.max(8, (item.value / max) * 100)}%` }" />
            </div>
            <div class="text-center">
                <p class="text-xs text-slate-500">{{ item.label }}</p>
                <p class="text-xs text-slate-300">{{ format(item.value) }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    items: Array<{ label: string; value: number }>;
    format?: (value: number) => string;
}>();

const max = computed(() => Math.max(0, ...props.items.map((item) => item.value)));
const format = (value: number) => props.format ? props.format(value) : `${value}`;
</script>
