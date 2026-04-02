<template>
    <GuestLayout :app-name="app.name">
        <AuthPanel eyebrow="Access" title="Sign in" description="Use your existing ShopSell account to continue.">
            <form class="space-y-5" @submit.prevent="form.post('/login')">
                <FormField id="email" label="Email" :error="errors.email as string">
                    <Input id="email" v-model="form.email" type="email" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <FormField id="password" label="Password" :error="errors.password as string">
                    <Input id="password" v-model="form.password" type="password" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <label class="flex items-center gap-3 text-sm text-slate-300">
                    <input v-model="form.remember" type="checkbox" class="rounded border-white/10 bg-white/5" />
                    Remember this device
                </label>

                <div class="flex items-center justify-between gap-4">
                    <Button type="submit" :disabled="form.processing">
                        Sign in
                    </Button>
                    <Link href="/forgot-password" class="text-sm text-slate-300 underline underline-offset-4">
                        Forgot password?
                    </Link>
                </div>
            </form>
        </AuthPanel>
    </GuestLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

import AuthPanel from '@/components/app/AuthPanel.vue';
import FormField from '@/components/app/FormField.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import GuestLayout from '@/layouts/GuestLayout.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});
</script>
