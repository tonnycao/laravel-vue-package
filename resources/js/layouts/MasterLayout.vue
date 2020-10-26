<template>
	<div v-if="$store.state.storeInitialised">
		<navbar />
		<side-bar />
		<router-view></router-view>
		<footer-bar />
	</div>
</template>

<script>

	import Navbar from './Navbar.vue'
	import FooterBar from './FooterBar.vue'
	import SideBar from './SideBar'
	export default {
		components: {
			Navbar,
			SideBar,
			FooterBar
		}
	}
</script>
