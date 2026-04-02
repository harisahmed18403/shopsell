<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
            <header class="rounded-[2rem] border border-white/10 bg-slate-900/75 px-6 py-5 shadow-2xl shadow-black/20 backdrop-blur">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <AppLogo :name="appName" />
                    <nav class="flex flex-wrap gap-2">
                        <Link
                            v-for="item in navigation"
                            :key="item.href"
                            :href="item.href"
                            :class="cn(
                                'rounded-full px-4 py-2 text-sm font-medium transition',
                                currentRoute === item.route
                                    ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/20'
                                    : 'bg-white/5 text-slate-300 hover:bg-white/10 hover:text-white'
                            )"
                        >
                            {{ item.label }}
                        </Link>
                    </nav>
                </div>
            </header>

            <main class="flex-1 py-6">
                <FlashMessages :flash="flash" />
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppLogo from '@/components/app/AppLogo.vue';
import FlashMessages from '@/components/app/FlashMessages.vue';
import { cn } from '@/lib/utils';
import type { FlashMessages as FlashMessageBag } from '@/types';

const props = defineProps<{
    appName: string;
    currentRoute?: string | null;
    flash: FlashMessageBag;
    isSuperAdmin?: boolean;
}>();

const navigation = computed(() => [
    { label: 'Dashboard', href: '/dashboard', route: 'dashboard' },
    { label: 'Reports', href: '/reports', route: 'reports' },
    { label: 'Products', href: '/products', route: 'products.index' },
    { label: 'Inventory', href: '/inventory', route: 'inventory.index' },
    { label: 'Transactions', href: '/transactions', route: 'transactions.index' },
    { label: 'Customers', href: '/customers', route: 'customers.index' },
    ...(props.isSuperAdmin ? [{ label: 'Admin', href: '/admin/users', route: 'admin.users.index' }] : []),
    { label: 'Profile', href: '/profile', route: 'profile.edit' },
]);
</script>
