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
        // Filament panel views & components
        './app/Filament/**/*.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Tajawal', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Brand Orange (Primary)
                'brand-orange': {
                    50:  '#FFF7ED',
                    100: '#FFEDD5',
                    200: '#FED7AA',
                    300: '#FDBA74',
                    400: '#FB923C',
                    500: '#F97316',
                    600: '#EA580C',
                    700: '#C2410C',
                    800: '#9A3412',
                    900: '#7C2D12',
                    950: '#431407',
                },
                // Brand Blue (Secondary)
                'brand-blue': {
                    50:  '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB',
                    700: '#1D4ED8',
                    800: '#1E3A5F',
                    900: '#0F1B2D',
                    950: '#0A1120',
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
