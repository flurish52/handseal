import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import GuestLayout from "@/Layouts/GuestLayout.vue"
import AppLayout from "@/Layouts/AppLayout.vue"
import AuthLayout from "@/Layouts/AuthLayout.vue";

const pages = import.meta.glob('./pages/**/*.vue')
const appEl = document.getElementById('app')
const initialPage = JSON.parse(appEl.dataset.page)

    if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((err) => {
            console.error('SW registration failed:', err);
        });
    });
}



createInertiaApp({
    page: initialPage,

    resolve: async (name) => {
        const page = pages[`./pages/${name}.vue`]

        if (!page) {
            throw new Error(`Page not found: ./pages/${name}.vue`)
        }

        const module = await page()

        module.default.layout = module.default.layout || (() => {
            switch (true) {
                case name.startsWith('Welcome'):
                    return GuestLayout
                case name.startsWith('Auth/'):
                    return AuthLayout
                case name.startsWith('Business/Edit'):
                    return AppLayout
                case name.startsWith('Directory/'):
                    return GuestLayout
                case name.startsWith('Verify/'):
                    return GuestLayout
                case name.startsWith('Certificates/'):
                    return AppLayout
                case name.startsWith('CertificateTemplates/'):
                    return AppLayout
                case name.startsWith('Dashboard/'):
                    return AppLayout
                case name.startsWith('Programmes/'):
                    return AppLayout
                case name.startsWith('Referrals/'):
                    return AppLayout
                case name.startsWith('Students/'):
                    return AppLayout
                case name.startsWith('Billing/'):
                    return AppLayout
                case name.startsWith('Plans/'):
                    return GuestLayout
                default:
                    return null
            }
        })()

        return module
    },

    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el)
    },
})
