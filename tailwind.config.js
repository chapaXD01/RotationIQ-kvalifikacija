import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    // the app's theme is driven entirely by the [data-theme] toggle (see app.css /
    // app.blade.php), not the OS/browser's prefers-color-scheme — without this,
    // leftover `dark:` classes on the unmodified Breeze auth components (text-input,
    // primary-button, etc.) silently react to OS dark mode regardless of what the
    // in-app toggle says, since Tailwind's default darkMode strategy is 'media'.
    // 'class' requires an ancestor .dark class, which nothing in this app ever adds,
    // so it neutralizes those stray dark: variants instead of fighting the toggle.
    darkMode: 'class',

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

    plugins: [forms],
};
