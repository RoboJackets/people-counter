require('./bootstrap');

window.Vue = require('vue');

Vue.component('dashboard', require('./components/Dashboard.vue').default);

const app = new Vue({
	el: '#app',
});
