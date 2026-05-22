import { defineStore } from "pinia";
import User from "../api/auth";
import router from "../router/index";

export const useAuthStore = defineStore("auth", {
	state: () => ({
		authUser: null,
	}),
	getters: {
		user: (state) => state.authUser,
		isLoading: (state) => state.loading,
	},
	actions: {
		async getUser() {
			try {
				const user = await User.auth();
				this.authUser = user.data;
			} catch (error) {
				console.error("Error fetching user:", error);
				this.authUser = null;
			}
		},
		async logout() {
			await User.logout();
			this.authUser = null;
			localStorage.removeItem("auth");
			await router.push("/");
		},
	},
	persist: true,
});
