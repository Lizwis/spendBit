<template>
	<div class="d-flex justify-content-center">
		<div div class="col-4">
			<div class="form-group">
				<label for="exampleInputEmail1">Email address</label>
				<input type="email" class="form-control" v-model="form.email" />
				<div class="text-danger">{{ errors.email[0] }}</div>
			</div>
			<div class="form-group pt-4">
				<label for="exampleInputPassword1">Password</label>
				<input type="password" class="form-control" v-model="form.password" />
				<div class="text-danger">{{ errors.password[0] }}</div>
			</div>
			<div class="form-group pt-4">
				<button type="submit" @click="submit" class="btn btn-primary">
					Submit
				</button>
			</div>

			<div class="pt-3">
				Don't have an account?
				<router-link to="/register">Register</router-link>
			</div>
		</div>
	</div>
</template>

<script>
	import { defineComponent, ref, onMounted } from "vue";
	import { useRouter } from "vue-router";
	import { useAuthStore } from "../../stores/auth";
	import User from "../../api/auth";

	export default defineComponent({
		components: {},

		setup() {
			const form = ref({
				email: "",
				password: "",
			});

			const errors = ref({
				email: "",
				password: "",
			});
			const loading = ref(false);
			const authStore = useAuthStore();

			const router = useRouter();

			const error = ref(null);
			const submit = async () => {
				loading.value = true;
				errors.value = {
					email: "",
					password: "",
				};

				await User.login(form.value)
					.then(async (response) => {
						localStorage.setItem("token", response.data.token);
						await authStore.getUser();
						loading.value = false;
						router.push("/crypto/deposit");
					})
					.catch((err) => {
						error.value = err;
						const res = err.response.data.errors;
						loading.value = false;
						errors.value = {
							email: res?.email || "",
							password: res?.password || "",
						};
					});
			};

			return {
				form,
				submit,
				errors,
				router,

				loading,
				error,
			};
		},
	});
</script>
