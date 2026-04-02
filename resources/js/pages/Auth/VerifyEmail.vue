<template>
    <GuestLayout :app-name="app.name">
        <AuthPanel eyebrow="Verification" title="Confirm your email" description="Check your inbox before continuing into the application.">
            <div class="space-y-5 text-sm text-slate-300">
                <p>
                    A verification link has been sent to your email address. Open it to activate the account.
                </p>
                <p v-if="flash.status === 'verification-link-sent'" class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-emerald-100">
                    A fresh verification link has been sent.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <Button :disabled="verificationForm.processing" @click="verificationForm.post('/email/verification-notification')">
                        Resend verification link
                    </Button>
                    <Button variant="ghost" :disabled="logoutForm.processing" @click="logoutForm.post('/logout')">
                        Log out
                    </Button>
                </div>
            </div>
        </AuthPanel>
    </GuestLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import AuthPanel from '@/components/app/AuthPanel.vue';
import { Button } from '@/components/ui/button';
import GuestLayout from '@/layouts/GuestLayout.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps>();

const verificationForm = useForm({});
const logoutForm = useForm({});
</script>
