<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center gap-2">
        <Link
            v-for="link in links"
            :key="`${link.label}-${link.url}`"
            :href="link.url || '#'"
            :class="cn(
                'rounded-full border px-3 py-1.5 text-sm transition',
                link.active
                    ? 'border-rose-400/40 bg-rose-500 text-white'
                    : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10',
                !link.url && 'pointer-events-none opacity-40',
            )"
            preserve-scroll
        >
            {{ link.label }}
        </Link>
    </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { cn } from '@/lib/utils';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}>();
</script>
