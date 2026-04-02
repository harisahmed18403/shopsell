<template>
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-500/20 text-rose-300 ring-1 ring-rose-400/30">
            <Store class="h-5 w-5" />
        </div>
        <div>
            <p class="font-display text-base font-semibold tracking-tight text-white">
                {{ name }}
            </p>
            <p class="text-xs uppercase tracking-[0.28em] text-slate-400">
                Retail operations
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Store } from 'lucide-vue-next';

defineProps<{
    name: string;
}>();
</script>
