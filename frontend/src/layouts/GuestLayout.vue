<template>
	<div
		class="min-vh-100 d-flex align-items-center justify-content-center bg-light"
	>
		<div class="p-4 rounded w-100">
			<slot />
		</div>
	</div>
</template>
<script>
	import { defineComponent } from "vue";

	export default defineComponent({
		components: {},

		setup() {
			return {};
		},
	});
</script>
