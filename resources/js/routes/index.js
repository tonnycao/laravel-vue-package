import Vue from 'vue'
import store from './../store'
import VueRouter from 'vue-router'
import routes from './routes.js';

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    routes,
    keepAlive: true
});

console.debug('[Vuex]   Initialising store');
const store_initialised = store.dispatch('initialiseStore').then(resp => {
    store.commit('storeInitialised');
    console.debug('[Vuex]   Store fully initialised');
});

router.beforeEach((to, from, next) => {

    store_initialised.then(resp => {

        if (to.matched.some(record => record.meta.requiresAuth)) {

            if (store.getters.isAuthenticated){

                if (to.matched.some(record => record.meta.roles)) {

                    if (store.getters.roles.some(i => to.meta.roles.includes(i))) next()
                    else next('/no-access')

                } else next()

            }else{
                next('/no-access')
            }
        }
        else next()

    })

});

export default router
