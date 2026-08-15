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
                // Brand — reference these everywhere as bg-seal-navy, text-seal-ink, etc.
                // Never hardcode hex values in components. Change the business's look by editing only this block.
                seal: {
                    navy: '#1F2547',     // primary — headers, nav, primary buttons, trust/authority
                    'navy-light': '#2E3564', // hover/active state of navy
                    brass: '#B8863B',    // secondary — the "seal" accent: badges, seals, gold-foil cert elements
                    'brass-light': '#D3A75C', // hover/active state of brass
                    sage: '#3F6B52',     // tertiary — success / "completed" / active-student status
                    paper: '#F7F5F0',    // app background — warm off-white, evokes certificate stock
                    ink: '#1B1F2A',      // primary text
                    muted: '#7C8598',    // secondary text, placeholders, captions
                    line: '#E4E0D6',     // borders, dividers
                    danger: '#C0392B',   // errors, destructive actions
                },
            },

            fontFamily: {
                // font-serif -> certificates, section headers, anything that should feel official
                serif: ['Fraunces', 'ui-serif', 'Georgia', 'serif'],
                // font-sans -> default. All app UI: forms, lists, dashboard, nav
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                // font-mono -> certificate numbers, verification codes, anything meant to be read digit-by-digit
                mono: ['"IBM Plex Mono"', 'ui-monospace', 'monospace'],
            },

            borderRadius: {
                // Centralized so card/button rounding stays consistent without repeating a value everywhere
                card: '14px',
            },
        },
    },

    plugins: [],
};
