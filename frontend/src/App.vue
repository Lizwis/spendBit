<template>
	<component :is="layout">
		<router-view />
	</component>
</template>

<script setup>
	import { computed } from "vue";
	import { useRoute } from "vue-router";
	import AuthLayout from "./layouts/AuthLayout.vue";
	import GuestLayout from "./layouts/GuestLayout.vue";

	const route = useRoute();

	const layout = computed(() => {
		const layoutType = route.matched[0]?.components?.default?.layout;
		return layoutType === "auth" ? AuthLayout : GuestLayout;
	});
</script>
