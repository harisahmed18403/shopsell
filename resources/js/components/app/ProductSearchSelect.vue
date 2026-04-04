<template>
    <div class="space-y-3">
        <FormField label="Search catalog product" :description="selectedProduct ? `Selected: ${selectedProduct.name}` : 'Type at least 2 characters to search the catalog.'">
            <div class="relative">
                <Input
                    v-model="query"
                    type="text"
                    placeholder="Search products..."
                    @focus="showDropdown = results.length > 0"
                />
                <div
                    v-if="showDropdown && query.length >= 2"
                    class="absolute z-20 mt-2 max-h-72 w-full overflow-y-auto rounded-2xl border border-white/10 bg-slate-900/95 p-2 shadow-2xl shadow-black/40"
                >
                    <button
                        v-for="product in results"
                        :key="product.id"
                        type="button"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left transition hover:bg-white/5"
                        @click="selectProduct(product)"
                    >
                        <div class="h-10 w-10 overflow-hidden rounded-xl border border-white/10 bg-white">
                            <img :src="product.image_url" :alt="product.name" class="h-full w-full object-contain" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-white">{{ product.name }}</p>
                            <p class="text-xs text-slate-500">ID {{ product.id }}</p>
                        </div>
                    </button>

                    <div v-if="results.length === 0 && !loading" class="px-3 py-6 text-sm text-slate-500">
                        No results found.
                    </div>
                </div>
            </div>
        </FormField>
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

import FormField from '@/components/app/FormField.vue';
import { appPath } from '@/lib/app-path';
import { Input } from '@/components/ui/input';
import type { SharedPageProps } from '@/types';

export interface SearchProduct {
    id: number;
    name: string;
    image_url: string;
    variants?: Array<{
        sale?: number;
    }>;
}

const props = defineProps<{
    modelValue: SearchProduct | null;
}>();
const page = usePage<SharedPageProps>();

const emit = defineEmits<{
    (e: 'update:modelValue', product: SearchProduct | null): void;
}>();

const query = ref(props.modelValue?.name ?? '');
const results = ref<SearchProduct[]>([]);
const loading = ref(false);
const showDropdown = ref(false);
const selectedProduct = computed(() => props.modelValue);
let controller: AbortController | null = null;
let timeoutId: number | null = null;
let suppressNextSearch = false;

watch(query, (value) => {
    if (suppressNextSearch) {
        suppressNextSearch = false;
        return;
    }

    if (timeoutId) {
        window.clearTimeout(timeoutId);
    }

    if (value.length < 2) {
        results.value = [];
        showDropdown.value = false;
        return;
    }

    timeoutId = window.setTimeout(async () => {
        controller?.abort();
        controller = new AbortController();
        loading.value = true;

        try {
            const response = await fetch(appPath(page.props.app.base_path, `/products/search?q=${encodeURIComponent(value)}`), {
                signal: controller.signal,
                headers: {
                    Accept: 'application/json',
                },
            });

            results.value = await response.json();
            showDropdown.value = true;
        } catch (error) {
            if (!(error instanceof DOMException && error.name === 'AbortError')) {
                console.error('Product search failed', error);
            }
        } finally {
            loading.value = false;
        }
    }, 250);
});

function selectProduct(product: SearchProduct) {
    emit('update:modelValue', product);
    suppressNextSearch = true;
    query.value = product.name;
    showDropdown.value = false;
}

onBeforeUnmount(() => {
    controller?.abort();
    if (timeoutId) {
        window.clearTimeout(timeoutId);
    }
});
</script>
