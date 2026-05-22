<template>
	<AuthLayout>
		<div class="container py-4">
			<h2 class="mb-4">Virtual Card</h2>

			<div class="card shadow-sm p-4 mb-4">
				<h5 class="text-muted">Available Balance</h5>

				<h2 class="text-success">
					{{ balanceFormatted }}
				</h2>

				<button class="btn btn-primary mt-3" @click="loadBalance">
					Refresh Balance
				</button>
			</div>

			<div class="card p-4">
				<h5>Card Summary</h5>

				<ul class="list-group list-group-flush mt-3">
					<li class="list-group-item">
						Status: <span class="badge bg-success">Active</span>
					</li>

					<li class="list-group-item">Currency: USD (Virtual)</li>

					<li class="list-group-item">Source: Crypto Deposits</li>
				</ul>
			</div>
		</div>
	</AuthLayout>
</template>

<script setup>
	import { ref, computed, onMounted } from "vue";
	import WalletApi from "../api/wallet";
	import AuthLayout from "../layouts/AuthLayout.vue";

	const balance = ref(0);

	const balanceFormatted = computed(() => Number(balance.value).toFixed(2));

	async function loadBalance() {
		try {
			const res = await WalletApi.getBalance();
			balance.value = res.data.balance;
		} catch (e) {
			console.error("Balance error", e);
		}
	}

	onMounted(() => {
		loadBalance();
	});
</script>
