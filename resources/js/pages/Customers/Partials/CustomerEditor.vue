<template>
    <AppLayout :app-name="app.name" :current-route="routing.current" :flash="flash" :is-super-admin="auth.user?.is_super_admin">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-rose-300">Customers</p>
                    <h1 class="font-display text-4xl font-semibold tracking-tight text-white">{{ mode === 'create' ? 'Add customer' : `Edit ${customer?.name}` }}</h1>
                </div>
                <Link href="/customers"><Button variant="ghost">Cancel</Button></Link>
            </div>

            <Card class="mx-auto max-w-3xl border-white/10 bg-slate-900/80">
                <CardContent class="pt-6">
                    <form class="space-y-5" @submit.prevent="submit">
                        <FormField id="name" label="Name" :error="errors.name as string">
                            <Input id="name" v-model="form.name" type="text" />
                        </FormField>
                        <div class="grid gap-5 md:grid-cols-2">
                            <FormField id="email" label="Email" :error="errors.email as string">
                                <Input id="email" v-model="form.email" type="email" />
                            </FormField>
                            <FormField id="phone" label="Phone" :error="errors.phone as string">
                                <Input id="phone" v-model="form.phone" type="text" />
                            </FormField>
                        </div>
                        <FormField id="address" label="Address" :error="errors.address as string">
                            <textarea id="address" v-model="form.address" class="min-h-28 w-full rounded-xl border border-white/10 bg-white/5 px-3 py-3 text-sm text-white" />
                        </FormField>
                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing">{{ mode === 'create' ? 'Create customer' : 'Update customer' }}</Button>
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
    customer?: { id: number; name: string; email: string | null; phone: string | null; address: string | null };
    mode: 'create' | 'edit';
}>();

const form = useForm({
    name: props.customer?.name ?? '',
    email: props.customer?.email ?? '',
    phone: props.customer?.phone ?? '',
    address: props.customer?.address ?? '',
});

function submit() {
    if (props.mode === 'create') {
        form.post('/customers');
        return;
    }

    form.patch(`/customers/${props.customer?.id}`);
}
</script>
