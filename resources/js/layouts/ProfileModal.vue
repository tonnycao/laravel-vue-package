<template>
	<b-modal
		title="User Details"
		id="profile-modal"
	>
		<table class="tbl-user-details">
			<tr>
			    <td>Name:</td>
			    <td>{{ name }}</td>
			</tr>
			<tr>
			    <td>Email:</td>
			    <td>{{ email }}</td>
			</tr>
			<tr>
			    <td>Dsid:</td>
			    <td>{{ dsid }}</td>
			</tr>
			<tr>
			    <td>Roles:</td>
			    <td>
			    	<span v-for="role in roles">
			       		{{ role.role }} ({{ role.region }})
			    	</span>
			    </td>
			</tr>
		</table>
		<template slot="modal-footer">
			<b-btn 
				@click="$bvModal.hide('profile-modal')"
			>
				Close
			</b-btn>
		</template>
	</b-modal>
</template>

<script>
	export default {
		computed:{
			name(){
				return this.$store.state.user.name
			},
			email(){
				return this.$store.state.user.email
			},
			dsid(){
				return this.$store.state.user.dsid
			},
			roles(){
				return JSON.parse(this.$store.state.user.roles)
			}
		}
	}
</script>

<style lang="scss">
	#profile-modal{ 
		footer{
			display: none;
		}
		.tbl-user-details{
			border-bottom: 1px solid #e4e4e4;
		}
	}
</style>