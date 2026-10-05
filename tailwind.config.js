import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            // The "indigo" scale is re-pointed at a deep institutional blue, so
            // every existing indigo-* class picks up the new brand colour.
            colors: {
                indigo: {
                    50: '#f0f5ff',
                    100: '#e0eaff',
                    200: '#c2d5ff',
                    300: '#94b3fb',
                    400: '#6089f2',
                    500: '#3a63e0',
                    600: '#2a4cc2',
                    700: '#223e9c',
                    800: '#1f3680',
                    900: '#1c2f66',
                    950: '#111a3d',
                },
            },
            keyframes: {
                fadeUp: {
                    '0%': { opacity: '0', transform: 'translateY(24px)' },
                    '100%': { opacity: '1', transform: 'none' },
                },
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0) rotate(var(--r, 0deg))' },
                    '50%': { transform: 'translateY(-14px) rotate(calc(var(--r, 0deg) + 3deg))' },
                },
                blob: {
                    '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                    '50%': { transform: 'translate(28px, -22px) scale(1.12)' },
                },
            },
            animation: {
                'fade-up': 'fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both',
                'fade-in': 'fadeIn 0.6s ease-out both',
                float: 'float 6s ease-in-out infinite',
                'float-slow': 'float 9s ease-in-out infinite',
                blob: 'blob 14s ease-in-out infinite',
            },
            boxShadow: {
                soft: '0 1px 2px 0 rgb(15 23 42 / 0.04), 0 1px 3px 0 rgb(15 23 42 / 0.06)',
                glow: '0 1px 2px 0 rgb(15 23 42 / 0.18), 0 2px 6px -1px rgb(34 62 156 / 0.25)',
            },
            borderRadius: {
                xl: '0.75rem',
            },
        },
    },

    plugins: [forms],
};
