<template>
	<div class="d-flex justify-content-center">
		<div div class="col-4">
			<div class="form-group">
				<label>Name</label>
				<input type="text" class="form-control" v-model="form.name" />
				<div class="text-danger">{{ errors.name?.[0] }}</div>
			</div>

			<div class="form-group pt-4">
				<label>Email address</label>
				<input type="email" class="form-control" v-model="form.email" />
				<div class="text-danger">{{ errors.email?.[0] }}</div>
			</div>

			<div class="form-group pt-4">
				<label>Password</label>
				<input type="password" class="form-control" v-model="form.password" />
				<div class="text-danger">{{ errors.password?.[0] }}</div>
			</div>

			<div class="form-group pt-4">
				<label>Confirm Password</label>
				<input
					type="password"
					class="form-control"
					v-model="form.password_confirmation"
				/>
			</div>

			<div class="form-group pt-4">
				<button
					type="submit"
					@click="submit"
					class="btn btn-primary"
					:disabled="loading"
				>
					{{ loading ? "Creating Account..." : "Register" }}
				</button>
			</div>

			<div class="pt-3">
				Already have an account?
				<router-link to="/">Login</router-link>
			</div>
		</div>
	</div>
</template>

<script>
	import { defineComponent, ref } from "vue";
	import { useRouter } from "vue-router";
	import User from "../../api/auth";

	export default defineComponent({
		setup() {
			const router = useRouter();

			const form = ref({
				name: "",
				email: "",
				password: "",
				password_confirmation: "",
			});

			const errors = ref({});

			const loading = ref(false);

			const submit = async () => {
				loading.value = true;

				errors.value = {};

				await User.register(form.value)
					.then(() => {
						router.push("/");
					})
					.catch((err) => {
						errors.value = err.response?.data?.errors || {};
					})
					.finally(() => {
						loading.value = false;
					});
			};

			return {
				form,
				errors,
				loading,
				submit,
			};
		},
	});
</script>
