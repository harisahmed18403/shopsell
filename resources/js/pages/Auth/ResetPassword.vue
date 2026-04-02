<template>
    <GuestLayout :app-name="app.name">
        <AuthPanel eyebrow="Recovery" title="Choose a new password" description="Complete the password reset with the token from your email.">
            <form class="space-y-5" @submit.prevent="form.post('/reset-password')">
                <FormField id="email" label="Email" :error="errors.email as string">
                    <Input id="email" v-model="form.email" type="email" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <FormField id="password" label="Password" :error="errors.password as string">
                    <Input id="password" v-model="form.password" type="password" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <FormField id="password_confirmation" label="Confirm password">
                    <Input id="password_confirmation" v-model="form.password_confirmation" type="password" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <Button type="submit" :disabled="form.processing">
                    Reset password
                </Button>
            </form>
        </AuthPanel>
    </GuestLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import AuthPanel from '@/components/app/AuthPanel.vue';
import FormField from '@/components/app/FormField.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import GuestLayout from '@/layouts/GuestLayout.vue';
import type { SharedPageProps } from '@/types';

const props = defineProps<SharedPageProps & {
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});
</script>
