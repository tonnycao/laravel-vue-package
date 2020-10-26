<template>
	<footer class="footer-color">
        <a href="#top" title="Go to the top" class="mr-2 to-top">
        	<i class="fas fa-arrow-alt-to-top"></i>
        </a>
        <b>{{ app_name }}</b> - v1.0 - Copyright © {{ year }} 
        <span class="d-inline-block">Apple Inc. All rights reserved. This site is Apple Internal Use Only.</span>
	</footer>
</template>

<script>
	export default {
		computed: {
			year(){
				return new Date().getFullYear()
			},
			app_name(){
				return this.$store.state.config.app_name
			}
		}
	}
</script>