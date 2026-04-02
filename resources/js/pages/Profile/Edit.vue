<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash">
        <section class="space-y-6">
            <div class="flex flex-col gap-2">
                <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Profile</p>
                <h1 class="font-display text-4xl font-semibold tracking-tight text-white">
                    Account settings
                </h1>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
                <Card class="border-white/10 bg-slate-900/80 shadow-2xl shadow-black/20">
                    <CardHeader>
                        <CardTitle class="text-white">Profile information</CardTitle>
                        <CardDescription class="text-slate-400">
                            Update your account identity and email address.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form class="space-y-5" @submit.prevent="profileForm.patch('/profile')">
                            <FormField id="name" label="Name" :error="defaultErrors.name">
                                <Input id="name" v-model="profileForm.name" type="text" class="border-white/10 bg-white/5 text-white" />
                            </FormField>

                            <FormField id="email" label="Email" :error="defaultErrors.email">
                                <Input id="email" v-model="profileForm.email" type="email" class="border-white/10 bg-white/5 text-white" />
                            </FormField>

                            <div
                                v-if="mustVerifyEmail && auth.user?.email_verified_at === null"
                                class="rounded-2xl border border-sky-400/20 bg-sky-500/10 px-4 py-3 text-sm text-sky-100"
                            >
                                <p>Your email address is unverified.</p>
                                <button type="button" class="mt-2 font-medium text-sky-200 underline underline-offset-4" @click="sendVerification">
                                    Click here to re-send the verification email.
                                </button>
                            </div>

                            <div class="flex items-center gap-3">
                                <Button type="submit" :disabled="profileForm.processing">
                                    Save changes
                                </Button>
                                <span v-if="flash.status === 'profile-updated'" class="text-sm text-emerald-300">
                                    Saved.
                                </span>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card class="border-white/10 bg-slate-900/80 shadow-2xl shadow-black/20">
                    <CardHeader>
                        <CardTitle class="text-white">Password</CardTitle>
                        <CardDescription class="text-slate-400">
                            Use a strong, unique password for this account.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form class="space-y-5" @submit.prevent="passwordForm.put('/password', { errorBag: 'updatePassword' })">
                            <FormField id="current_password" label="Current password" :error="passwordErrors.current_password">
                                <Input id="current_password" v-model="passwordForm.current_password" type="password" class="border-white/10 bg-white/5 text-white" />
                            </FormField>

                            <FormField id="password" label="New password" :error="passwordErrors.password">
                                <Input id="password" v-model="passwordForm.password" type="password" class="border-white/10 bg-white/5 text-white" />
                            </FormField>

                            <FormField id="password_confirmation" label="Confirm password" :error="passwordErrors.password_confirmation">
                                <Input id="password_confirmation" v-model="passwordForm.password_confirmation" type="password" class="border-white/10 bg-white/5 text-white" />
                            </FormField>

                            <div class="flex items-center gap-3">
                                <Button type="submit" :disabled="passwordForm.processing">
                                    Update password
                                </Button>
                                <span v-if="flash.status === 'password-updated'" class="text-sm text-emerald-300">
                                    Saved.
                                </span>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <Card class="border-rose-400/20 bg-rose-500/10 shadow-2xl shadow-black/20">
                <CardHeader>
                    <CardTitle class="text-white">Delete account</CardTitle>
                    <CardDescription class="text-rose-100/80">
                        This permanently removes your account and associated data.
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <p class="max-w-2xl text-sm text-rose-100/80">
                        Enter your password to confirm deletion. This action cannot be undone.
                    </p>
                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive">Delete account</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent class="border-white/10 bg-slate-900 text-white">
                            <AlertDialogHeader>
                                <AlertDialogTitle>Delete account?</AlertDialogTitle>
                                <AlertDialogDescription class="text-slate-400">
                                    This will permanently remove your account and sign you out immediately.
                                </AlertDialogDescription>
                            </AlertDialogHeader>

                            <FormField id="delete_password" label="Password" :error="deleteErrors.password">
                                <Input id="delete_password" v-model="deleteForm.password" type="password" class="border-white/10 bg-white/5 text-white" />
                            </FormField>

                            <AlertDialogFooter>
                                <AlertDialogCancel class="border-white/10 bg-white/5 text-slate-100 hover:bg-white/10">
                                    Cancel
                                </AlertDialogCancel>
                                <AlertDialogAction
                                    class="bg-rose-500 text-white hover:bg-rose-400"
                                    @click.prevent="deleteForm.delete('/profile', { errorBag: 'userDeletion' })"
                                >
                                    Delete account
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </CardContent>
            </Card>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import FormField from '@/components/app/FormField.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SharedPageProps } from '@/types';

const props = defineProps<SharedPageProps & {
    mustVerifyEmail: boolean;
}>();

const profileForm = useForm({
    name: props.auth.user?.name ?? '',
    email: props.auth.user?.email ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({
    password: '',
});

const defaultErrors = computed(
    () => (props.errors as Record<string, string>).default ? (props.errors.default as Record<string, string>) : (props.errors as Record<string, string>),
);

const passwordErrors = computed(() => (props.errors.updatePassword ?? {}) as Record<string, string>);
const deleteErrors = computed(() => (props.errors.userDeletion ?? {}) as Record<string, string>);

function sendVerification() {
    useForm({}).post('/email/verification-notification');
}
</script>
