require('./bootstrap');

window.Vue = require('vue');

Vue.component('kiosk', require('./components/Kiosk.vue').default);

const app = new Vue({
    el: '#app',
});
