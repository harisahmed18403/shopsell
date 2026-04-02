<template>
    <GuestLayout :app-name="app.name">
        <AuthPanel eyebrow="Recovery" title="Reset password" description="Request a password reset link for your account.">
            <form class="space-y-5" @submit.prevent="form.post('/forgot-password')">
                <FormField
                    id="email"
                    label="Email"
                    :description="flash.status ? String(flash.status) : 'We will send a reset link if the account exists.'"
                    :error="errors.email as string"
                >
                    <Input id="email" v-model="form.email" type="email" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <div class="flex items-center justify-between gap-4">
                    <Link href="/login" class="text-sm text-slate-300 underline underline-offset-4">
                        Back to sign in
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        Email reset link
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

const props = defineProps<SharedPageProps>();

const form = useForm({
    email: '',
});
</script>
