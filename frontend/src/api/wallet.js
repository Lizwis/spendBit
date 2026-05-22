import baseApi from "./baseApi";

export default {
	getBalance() {
		return baseApi().get("/wallet/balance");
	},
};
