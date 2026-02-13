import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, require('daisyui')],

    daisyui: {
        themes: [
            {
                shopsell: {
                    "primary": "#1a73e8", // Google Blue
                    "secondary": "#4285f4",
                    "accent": "#34a853", // Google Green
                    "neutral": "#3d4451",
                    "base-100": "#ffffff",
                    "info": "#2094f3",
                    "success": "#009485",
                    "warning": "#ff9900",
                    "error": "#ff5724",
                },
            },
            "light",
            "dark",
        ],
    },
};
