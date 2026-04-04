<template>
    <GuestLayout :app-name="app.name" :base-path="app.base_path">
        <AuthPanel eyebrow="Security" title="Confirm password" description="Re-enter your password to continue to this secure area.">
            <form class="space-y-5" @submit.prevent="form.post(appPath(app.base_path, '/confirm-password'))">
                <FormField id="password" label="Password" :error="errors.password as string">
                    <Input id="password" v-model="form.password" type="password" class="border-white/10 bg-white/5 text-white" />
                </FormField>

                <Button type="submit" :disabled="form.processing">
                    Confirm
                </Button>
            </form>
        </AuthPanel>
    </GuestLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import AuthPanel from '@/components/app/AuthPanel.vue';
import FormField from '@/components/app/FormField.vue';
import { appPath } from '@/lib/app-path';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import GuestLayout from '@/layouts/GuestLayout.vue';
import type { SharedPageProps } from '@/types';

defineProps<SharedPageProps>();

const form = useForm({
    password: '',
});
</script>
