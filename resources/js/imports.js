import Vue from 'vue'

// import the bootstrap goodies
import BootstrapVue from 'bootstrap-vue';
Vue.use(BootstrapVue);

// dropzone
import 'vue2-dropzone/dist/vue2Dropzone.min.css';

window.axios = require('axios');
window.moment = require('moment');

let token = document.head.querySelector('meta[name="csrf-token"]');
// window.axios.defaults.baseURL = 'https://subdomain';
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
