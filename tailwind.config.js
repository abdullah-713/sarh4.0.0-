import defaultTheme from 'tailwindcss/defaultTheme';
import preset from './vendor/filament/filament/tailwind.config.preset';

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Filament/**/*.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Cairo', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Telegram Blue (Primary)
                'tg-blue': {
                    50:  '#EBF7FE',
                    100: '#D6EFFD',
                    200: '#ADDFFB',
                    300: '#84CFF9',
                    400: '#5BBFF7',
                    500: '#2AABEE',
                    600: '#229ED9',
                    700: '#1C96CC',
                    800: '#167EB0',
                    900: '#0D5E8A',
                    950: '#073D5C',
                },
                // Telegram Green
                'tg-green': {
                    50:  '#EDFBEF',
                    100: '#D4F5D9',
                    200: '#AAEDB5',
                    300: '#7FE492',
                    400: '#4DCD5E',
                    500: '#33B544',
                    600: '#2A9A39',
                    700: '#227F2F',
                    800: '#1A6425',
                    900: '#12491B',
                    950: '#0A2E11',
                },
                emerald: {
                    50:  '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                    950: '#022c22',
                },
            },
        },
    },
    plugins: [],
};
