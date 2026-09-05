import './bootstrap';
import '../css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'FORGE';

createInertiaApp({
    title: (title) => `${title} — ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Provide robust global route helper to all Vue component templates
        const routeFn = (name?: string, params?: any, absolute?: boolean) => {
            const globalRoute = (window as any).route;
            if (typeof globalRoute === 'function' && globalRoute !== routeFn) {
                return name !== undefined ? globalRoute(name, params, absolute) : globalRoute();
            }
            if (name !== undefined) {
                return `/${name.replace(/\./g, '/')}`;
            }
            return {
                current: (pattern?: string) => {
                    if (!pattern) return window.location.pathname;
                    const path = window.location.pathname.replace(/^\//, '');
                    return path.startsWith(pattern.replace(/\.\*$/, ''));
                }
            };
        };

        app.config.globalProperties.route = (name?: string, params?: any, absolute?: boolean) => {
            const globalRoute = (window as any).route;
            if (typeof globalRoute === 'function') {
                return name !== undefined ? globalRoute(name, params, absolute) : globalRoute();
            }
            return routeFn(name, params, absolute);
        };

        if (typeof (window as any).route !== 'function') {
            (window as any).route = routeFn;
        }

        app.use(plugin).mount(el);
    },
    progress: {
        color: '#6366f1',
        showSpinner: true,
    },
});
