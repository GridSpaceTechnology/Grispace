import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
         './resources/**/*.js',
        './resources/**/*.vue',
    ],

    safelist: [
       'fixed',
       'bottom-0',
       'left-0',
       'right-0',
       'z-50',
       'md:hidden',
       'md:flex',
       'hidden',
       'flex',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    primary: '#eb5333',
                    primaryHover: '#d94728',
                    secondary: '#052e5c',
                },
            },
        },
    },

    plugins: [forms],
};
