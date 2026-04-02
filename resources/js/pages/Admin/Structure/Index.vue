<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Admin</p>
                <h1 class="font-display text-4xl font-semibold tracking-tight text-white">Product structure</h1>
            </div>

            <div class="grid gap-6">
                <Card v-for="superCategory in structure" :key="superCategory.id" class="border-white/10 bg-slate-900/80">
                    <CardContent class="space-y-5 pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Super category</p>
                                <h2 class="mt-2 text-2xl font-semibold text-white">{{ superCategory.name }}</h2>
                            </div>
                            <p class="text-sm text-slate-400">{{ superCategory.product_lines.length }} product lines</p>
                        </div>
                        <div class="grid gap-4 lg:grid-cols-2">
                            <div v-for="line in superCategory.product_lines" :key="line.id" class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <h3 class="font-semibold text-white">{{ line.name }}</h3>
                                    <span class="text-xs text-slate-400">{{ line.categories.length }} categories</span>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <div v-for="category in line.categories" :key="category.id" class="flex items-center justify-between rounded-xl bg-slate-950/60 px-3 py-2 text-sm">
                                        <span class="text-slate-200">{{ category.name }}</span>
                                        <span class="text-slate-500">{{ category.products_count }} products</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps & {
    structure: Array<{
        id: number;
        name: string;
        product_lines: Array<{
            id: number;
            name: string;
            categories: Array<{
                id: number;
                name: string;
                products_count: number;
            }>;
        }>;
    }>;
}>();
</script>
