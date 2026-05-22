<template>
	<AuthLayout>
		<div class="container py-4">
			<h2 class="mb-4">Transaction History</h2>

			<div v-if="loading" class="text-muted">Loading...</div>

			<table v-else class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Tx Hash</th>
						<th>Amount</th>
						<th>Network</th>
						<th>Status</th>
						<th>Date</th>
					</tr>
				</thead>

				<tbody>
					<tr v-for="item in deposits" :key="item.id">
						<td>
							<a
								:href="`https://amoy.polygonscan.com/tx/${item.tx_hash}`"
								target="_blank"
							>
								{{ shortHash(item.tx_hash) }}
							</a>
						</td>

						<td>{{ item.amount }}</td>
						<td>{{ item.network }}</td>

						<td>
							<span class="badge bg-success">{{ item.status }}</span>
						</td>

						<td>{{ formatDate(item.created_at) }}</td>
					</tr>
				</tbody>
			</table>

			<div v-if="!loading && deposits.length === 0" class="text-muted">
				No transactions yet
			</div>
		</div>
	</AuthLayout>
</template>

<script setup>
	import { ref, onMounted } from "vue";
	import DepositApi from "../api/crypto/deposit";
	import AuthLayout from "../layouts/AuthLayout.vue";

	const deposits = ref([]);
	const loading = ref(false);

	async function fetchHistory() {
		loading.value = true;

		try {
			const res = await DepositApi.history();
			deposits.value = res.data.data;
		} catch (e) {
			console.error(e);
		} finally {
			loading.value = false;
		}
	}

	function shortHash(hash) {
		return hash ? hash.slice(0, 6) + "..." + hash.slice(-6) : "";
	}

	function formatDate(date) {
		return new Date(date).toLocaleString();
	}

	onMounted(() => {
		fetchHistory();
	});
</script>
