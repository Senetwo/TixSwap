import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * TixSwap design tokens.
 * brand  – TixSwap red, used for primary actions and the logo only
 * mpesa  – M-Pesa green, used only for "pay with M-Pesa" actions
 * Neutrals use Tailwind's gray scale; semantic colours (green/amber/
 * red/blue) are used for status only.
 */
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/View/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                    950: '#450a0a',
                },
                mpesa: {
                    50: '#f0fdf4',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                },
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(16 24 40 / 0.04), 0 1px 3px 0 rgb(16 24 40 / 0.06)',
                lift: '0 12px 24px -8px rgb(16 24 40 / 0.12), 0 4px 8px -4px rgb(16 24 40 / 0.06)',
            },
        },
    },

    plugins: [forms],
};
