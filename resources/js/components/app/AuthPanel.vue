<template>
    <div class="w-full max-w-md">
        <Card class="border-white/10 bg-slate-900/75 shadow-2xl shadow-black/20 backdrop-blur">
            <CardHeader class="space-y-3">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">
                    {{ eyebrow }}
                </p>
                <CardTitle class="font-display text-3xl font-semibold text-white">
                    {{ title }}
                </CardTitle>
                <CardDescription class="text-slate-400">
                    {{ description }}
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <slot />
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    eyebrow: string;
    title: string;
    description: string;
}>();
</script>
