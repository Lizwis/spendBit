import baseApi from "../baseApi";

export default {
	create(data) {
		return baseApi.post("/crypto/deposit", data);
	},
};
