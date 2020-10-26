<template>
    <b-navbar
        toggleable="lg"
        class="navbar-fpt"
    >

        <b-navbar-brand href="#">
            <span class="logo"></span>
            <span style="font-weight:500;">{{ config.app_name }}</span>
        </b-navbar-brand>

        <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

        <b-collapse id="nav-collapse" is-nav>
            <b-navbar-nav>
                <template v-for="nav_item in nav_items">
                    <b-nav-item v-if="nav_item.constructor !== Array" :to="nav_item.path">{{nav_item.name}}</b-nav-item>
                    <b-nav-item-dropdown v-else :text="nav_item[0].meta.menu_group" right>
                        <b-dropdown-item v-for="(sub_menu_item, key) in nav_item" :key="key"
                                         :to="sub_menu_item.path" >
                            {{sub_menu_item.name}}
                        </b-dropdown-item>
                    </b-nav-item-dropdown>
                </template>
            </b-navbar-nav>

            <!-- Right aligned nav items -->
            <b-navbar-nav class="ml-auto">

                <b-nav-item-dropdown right>
                    <template v-slot:button-content>
                        <i class="fad fa-question-circle text-primary"></i> Help
                    </template>
                    <b-dropdown-item href="#" target="_blank">Access Groups</b-dropdown-item>
                    <b-dropdown-item href="https://gbs-support.corp.apple.com/new-request/fdt-request/58">Support Requests</b-dropdown-item>
                </b-nav-item-dropdown>

                <b-nav-item-dropdown right>
                    <!-- Using 'button-content' slot -->
                    <template v-slot:button-content>
                        <i class="fad fa-user-circle text-primary"></i>
                        <span>
							{{ user.name }}
						</span>
                    </template>
                    <b-dropdown-text style="width: 400px;">
                        <div class="d-flex">
                            <div class="border" :style="'width:50px; height:50px; border-radius: 25px;  background: url(\'https://avatars.apple.com/a?mail=' + user.email + '\') no-repeat center; background-size: cover'"></div>
                            <div class="d-flex align-items-center ml-2">
                                <div>
                                    <div>{{ user.name }}</div>
                                    <div class="text-gray small">{{ user.email }} | {{ user.dsid }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="mb-1">Roles</div>
                            <div>
                                <span v-for="role in user.roles" class="badge badge-pill badge-primary m-1">{{role.region}} - {{role.role}}</span>
                            </div>
                        </div>
                        <div class="text-right mt-3">
                            <a href="/logout"><i class="far fa-sign-out"></i> Sign Out</a>
                        </div>
                    </b-dropdown-text>
                </b-nav-item-dropdown>
            </b-navbar-nav>
        </b-collapse>
    </b-navbar>
</template>

<script>



    import store from "../store";

    export default {
        methods:{
            logout(){
                axios.post('/logout', {}).then(resp => {
                    alert('logged out')
                }).catch(err => {
                    this.showErr(err)
                });
            }
        },
        computed: {
            user () {
                return this.$store.state.user;
            },
            config(){
                return this.$store.state.config
            },
            nav_items(){
                let nav_items = {};
                this.$router.options.routes.forEach(route => {

                    // if (route.meta && route.meta.roles && !this.$store.getters.roles.some(i => route.meta.roles.includes(i))) {
                    //     return
                    // }

                    if(route.meta && route.meta.menu_group){
                        if(nav_items[route.meta.menu_group]){
                            nav_items[route.meta.menu_group].push(route);
                        }else{
                            nav_items[route.meta.menu_group] = [route]
                        }
                    }else{
                        if(route.meta && (route.meta.showInMenu || true)){
                            nav_items[route.path] = route
                        }
                    }


                });
                return nav_items;
            }
        }
    };
</script>

<style lang="scss">

    // @import "./../../../sass/_variables.scss";

    .nav-item.active{
        .nav-link{
            font-weight: 500 !important;
        }
    }
</style>
