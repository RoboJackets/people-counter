require('./bootstrap');

window.Vue = require('vue');

Vue.component('kiosk', require('./components/Kiosk.vue').default);

import VueSweetalert2 from 'vue-sweetalert2';
Vue.use(VueSweetalert2);

var axios = require('axios');

const app = new Vue({
    el: '#app',
});
