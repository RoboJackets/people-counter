Nova.booting((Vue, router) => {
    Vue.component('makeawish', require('./components/nova/MakeAWishCard.vue').default);
    Vue.component('makeawish-link', require('./components/nova/MakeAWishLink.vue').default);
})
