require('./bootstrap');

window.Vue = require('vue');
import * as Sentry from "@sentry/vue";
import { Integrations } from "@sentry/tracing";

Vue.component('kiosk', require('./components/Kiosk.vue').default);
Vue.component('home-page', require('./components/HomePage.vue').default);

import VueSweetalert2 from 'vue-sweetalert2';
Vue.use(VueSweetalert2);

if (process.env.MIX_SENTRY_DSN !== undefined) {
    Sentry.init({
        Vue: Vue,
        dsn: process.env.MIX_SENTRY_DSN,
        environment: process.env.MIX_APP_ENV,
        attachProps: true,
        logErrors: true,
        integrations: [new Integrations.BrowserTracing()],
        tracesSampleRate: 1.0,
        tracingOptions: {
            trackComponents: true,
        },
    });
    window.Sentry = Sentry;
} else {
    console.log('Sentry not loaded - DSN not present')
}

var axios = require('axios');

const app = new Vue({
    el: '#app',
});
