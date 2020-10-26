import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

window.axios = require('axios');

export default new Vuex.Store({
    state: {
        user: null,
        config: null,
        access: null,
        storeInitialised: false
    },
    getters: {
        isAuthenticated: state => {
            return !!state.user;
        },
        roles: state => {
            return state.user ? state.user.roles.map(a => a.role) : [];
        },
    },
    mutations: {
        setUser(state, user) {
            state.user = user
        },
        setConfig(state, config) {
            state.config = config
        },
        storeInitialised(state) {
            state.storeInitialised = true
        }
    },
    actions: {
        setUser({commit, state}) {
            return new Promise((resolve, reject) => {
                axios.get('/user').then(resp => {
                    commit('setUser', resp.data.user);
                    resolve();
                }).catch(() => {
                    reject();
                });
            });
        },
        setConfig({commit, state}) {
            return new Promise((resolve, reject) => {
                axios.get('/config').then(resp => {
                    commit('setConfig', resp.data.config);
                    resolve();
                }).catch(() => {
                    reject();
                });
            });
        },
        initialiseStore({ dispatch }) {
            return Promise.all([
                dispatch('setConfig'),
                dispatch('setUser'),
            ])
        }
    }
});
