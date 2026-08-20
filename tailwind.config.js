/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                seal: {
                    navy: '#101a30',
                    'navy-light': '#2E3564',
                    'navy-2': '#16223e',
                    'navy-3': '#0b1324',

                    brass: '#c79a46',
                    'brass-light': '#e7c577',
                    'brass-dim': '#8a6a30',


                    sage: '#5f7a5b',
                    'sage-light': '#e7ede4',


                    paper: '#f7f1e1',
                    'paper-2': '#efe5cc',


                    ink: '#1b1810',


                    muted: '#75694f',
                    'muted-dark': '#a9b2c8',


                    danger: '#a8442e',
                    'danger-light': '#f4e3de',


                    line: 'rgba(27,24,16,.14)',
                    'line-dark': 'rgba(231,197,119,.18)',
                },
            },


            fontFamily: {
                serif: ['Fraunces', 'Georgia', 'ui-serif', 'serif'],
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                mono: ['"IBM Plex Mono"', 'ui-monospace', 'monospace'],
            },


            borderRadius: {
                card: '14px',
            },

        },
    },

    plugins: [],
};
