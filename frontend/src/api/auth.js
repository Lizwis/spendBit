import BaseApi from "./baseApi";

export default {
	login(form) {
		return BaseApi().post("/login", form);
	},

	register(form) {
		return BaseApi().post("/register", form);
	},

	logout() {
		return BaseApi().post("/logout");
	},

	auth() {
		return BaseApi().get("/user");
	},
};
