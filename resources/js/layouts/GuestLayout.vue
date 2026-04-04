<template>
    <div class="relative min-h-screen overflow-hidden bg-slate-950 text-slate-100">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(244,63,94,0.22),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(56,189,248,0.18),_transparent_26%)]" />
        <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-4 py-6 sm:px-6 lg:px-8">
            <header class="flex items-center justify-between py-4">
                <AppLogo :name="appName" />
                <div class="flex gap-3">
                    <Link :href="appPath(basePath, '/login')" class="rounded-full border border-white/10 px-4 py-2 text-sm text-slate-200 transition hover:border-white/20 hover:bg-white/5">
                        Log in
                    </Link>
                    <Link :href="appPath(basePath, '/register')" class="rounded-full bg-rose-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-400">
                        Register
                    </Link>
                </div>
            </header>

            <main class="flex flex-1 items-center">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import AppLogo from '@/components/app/AppLogo.vue';
import { appPath } from '@/lib/app-path';

defineProps<{
    appName: string;
    basePath: string;
}>();
</script>
