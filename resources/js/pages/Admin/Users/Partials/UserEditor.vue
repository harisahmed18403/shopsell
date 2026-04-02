<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Admin</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">
                        {{ mode === 'create' ? 'Create user' : `Edit ${user?.name}` }}
                    </h1>
                </div>
                <Link href="/admin/users">
                    <Button variant="ghost">Cancel</Button>
                </Link>
            </div>

            <Card class="mx-auto max-w-3xl border-white/10 bg-slate-900/80">
                <CardContent class="pt-6">
                    <form class="space-y-5" @submit.prevent="submit">
                        <FormField id="name" label="Name" :error="errors.name as string">
                            <Input id="name" v-model="form.name" type="text" />
                        </FormField>

                        <FormField id="email" label="Email" :error="errors.email as string">
                            <Input id="email" v-model="form.email" type="email" />
                        </FormField>

                        <FormField id="role" label="Role" :error="errors.role as string">
                            <select id="role" v-model="form.role" class="h-10 w-full rounded-xl border border-white/10 bg-white/5 px-3 text-sm text-white">
                                <option value="user">User (Staff)</option>
                                <option value="admin">Admin (Branch Manager)</option>
                                <option value="super_admin">Super Admin (Full Access)</option>
                            </select>
                        </FormField>

                        <div class="grid gap-5 md:grid-cols-2">
                            <FormField id="password" :label="mode === 'create' ? 'Password' : 'New password'" :error="errors.password as string">
                                <Input id="password" v-model="form.password" type="password" />
                            </FormField>
                            <FormField id="password_confirmation" :label="mode === 'create' ? 'Confirm password' : 'Confirm new password'" :error="errors.password_confirmation as string">
                                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" />
                            </FormField>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing">
                                {{ mode === 'create' ? 'Create user' : 'Update user' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </section>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

import FormField from '@/components/app/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { SharedPageProps } from '@/types';

const props = defineProps<SharedPageProps & {
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
    };
    mode: 'create' | 'edit';
}>();

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    role: props.user?.role ?? 'user',
    password: '',
    password_confirmation: '',
});

function submit() {
    if (props.mode === 'create') {
        form.post('/admin/users');
        return;
    }

    form.patch(`/admin/users/${props.user?.id}`);
}
</script>
