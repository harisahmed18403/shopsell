import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { InertiaProgress } from '@inertiajs/progress';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';

const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');

createInertiaApp({
    progress: false,
    resolve: async (name) => {
        const page = pages[`./pages/${name}.vue`];

        if (!page) {
            throw new Error(`Unknown Inertia page: ${name}`);
        }

        return page();
    },
    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .mount(el);
    },
});

InertiaProgress.init({
    color: '#fb7185',
    showSpinner: false,
});
