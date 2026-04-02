<template>
    <div v-if="items.length" class="space-y-3">
        <div
            v-for="item in items"
            :key="item.key"
            :class="cn(
                'rounded-2xl border px-4 py-3 text-sm shadow-lg shadow-black/10 backdrop-blur',
                item.kind === 'success' && 'border-emerald-400/30 bg-emerald-500/10 text-emerald-100',
                item.kind === 'error' && 'border-rose-400/30 bg-rose-500/10 text-rose-100',
                item.kind === 'status' && 'border-sky-400/30 bg-sky-500/10 text-sky-100',
            )"
        >
            {{ item.message }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

import { cn } from '@/lib/utils';
import type { FlashMessages } from '@/types';

const props = defineProps<{
    flash: FlashMessages;
}>();

const items = computed(() =>
    [
        props.flash.success ? { key: 'success', kind: 'success', message: props.flash.success } : null,
        props.flash.error ? { key: 'error', kind: 'error', message: props.flash.error } : null,
        props.flash.status ? { key: 'status', kind: 'status', message: props.flash.status } : null,
    ].filter((item): item is { key: string; kind: string; message: string } => item !== null),
);
</script>
