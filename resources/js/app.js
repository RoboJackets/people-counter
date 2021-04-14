require('./bootstrap');

window.Vue = require('vue');
import Bugsnag from '@bugsnag/js'
import BugsnagPluginVue from '@bugsnag/plugin-vue'

Vue.component('kiosk', require('./components/Kiosk.vue').default);
Vue.component('home-page', require('./components/HomePage.vue').default);

import VueSweetalert2 from 'vue-sweetalert2';
Vue.use(VueSweetalert2);
Vue.use(require('vue-moment'));

if (process.env.MIX_BUGSNAG_API_KEY !== undefined) {
    Bugsnag.start({
        apiKey: process.env.MIX_BUGSNAG_API_KEY,
        plugins: [new BugsnagPluginVue()],
        releaseStage: process.env.MIX_APP_ENV
    })
    Bugsnag.getPlugin('vue')
        .installVueErrorHandler(Vue)
} else {
    console.log('Bugsnag not loaded - API key not present')
}

var axios = require('axios');

const app = new Vue({
    el: '#app',
});
