import { createRouter, createWebHistory } from "vue-router";
import CryptoDeposit from "../views/CryptoDeposit.vue";
import TransactionHistory from "../views/TransactionHistory.vue";

import { useAuthStore } from "../stores/auth";
import Login from "../views/auth/Login.vue";
import Register from "../views/auth/Register.vue";

const router = createRouter({
	history: createWebHistory(import.meta.env.BASE_URL),
	routes: [
		{
			path: "/",
			name: "Login",
			component: Login,
			meta: { guestOnly: true },
		},

		{
			path: "/register",
			name: "Register",
			component: Register,
			meta: { guestOnly: true },
		},
		{
			path: "/crypto/deposit",
			name: "deposit",
			meta: { requiresAuth: true },
			component: CryptoDeposit,
		},
		{
			path: "/transaction/history",
			name: "transactionHistory",
			meta: { requiresAuth: true },
			component: TransactionHistory,
		},
	],
});

router.beforeEach(async (to, from, next) => {
	const authStore = useAuthStore();

	// Ensure authStore is hydrated
	if (!authStore.user && localStorage.getItem("token")) {
		await authStore.getUser();
	}

	const isLoggedIn = !!authStore.user;

	if (to.meta.requiresAuth && !isLoggedIn) {
		// Redirect to login if not authenticated
		return next({ path: "/" });
	}

	if (to.meta.guestOnly && isLoggedIn) {
		// Redirect to dashboard if already logged in
		return next({ path: "/crypto/deposit" });
	}

	next();
});

export default router;
