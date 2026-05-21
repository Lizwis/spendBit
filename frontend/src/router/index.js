import { createRouter, createWebHistory } from "vue-router";
import CryptoDeposit from "../views/CryptoDeposit.vue";

const routes = [
	{
		path: "/",
		name: "deposit",
		component: CryptoDeposit,
	},
];

const router = createRouter({
	history: createWebHistory(),
	routes,
});

export default router;
