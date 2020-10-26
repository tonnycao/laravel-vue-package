import Vue from 'vue';

import router from './routes'
import store from './store'
require('./imports');

// set our primary component
Vue.component('master-layout', require('./layouts/MasterLayout').default);

import global_mixin from './mixins/Global';
Vue.mixin(global_mixin);

// initialize
const app = new Vue({
    el: '#app',
    router,
    store
});
