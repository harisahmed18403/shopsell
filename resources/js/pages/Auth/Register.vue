<template>
    <GuestLayout :app-name="app.name">
        <AuthPanel eyebrow="Create account" title="Open a workspace" description="Register a new operator account for ShopSell.">
            <form class="space-y-5" @submit.prevent="form.post('/register')">
                <FormField id="name" label="Name" :error="errors.name as string">
                    <Input id="name" v-model="form.name" type="text" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <FormField id="email" label="Email" :error="errors.email as string">
                    <Input id="email" v-model="form.email" type="email" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <FormField id="password" label="Password" :error="errors.password as string">
                    <Input id="password" v-model="form.password" type="password" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <FormField id="password_confirmation" label="Confirm password">
                    <Input id="password_confirmation" v-model="form.password_confirmation" type="password" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <div class="flex items-center justify-between gap-4">
                    <Link href="/login" class="text-sm text-slate-300 underline underline-offset-4">
                        Already registered?
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        Register
                    </Button>
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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
</script>
