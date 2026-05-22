<template>
	<div class="page">
		<div class="card">
			<h1>Crypto Deposit</h1>

			<p>Deposit custom USDC token into treasury (Amoy)</p>

			<button @click="connectWallet" :disabled="connecting">
				{{ wallet ? "Wallet Connected" : "Connect Wallet" }}
			</button>

			<div v-if="wallet" class="info">
				<p><strong>Wallet:</strong> {{ shortWallet }}</p>
				<p><strong>Balance:</strong> {{ balance }}</p>
			</div>

			<div v-if="wallet">
				<input v-model="amount" type="number" placeholder="Token Amount" />

				<button @click="deposit" :disabled="loading || !amount">
					{{ loading ? "Processing..." : "Deposit" }}
				</button>
			</div>

			<div v-if="txHash" class="success">
				<p>Transaction Successful</p>
				<a :href="scanUrl" target="_blank">View Tx</a>
			</div>

			<p v-if="error" class="error">{{ error }}</p>
		</div>
	</div>
</template>

<script setup>
	import { ref, computed } from "vue";
	import { ethers } from "ethers";
	import DepositApi from "../api/crypto/deposit";

	/**
	 * =========================
	 * CONFIG (AMOY TESTNET)
	 * =========================
	 */

	const TOKEN_ADDRESS = import.meta.env.VITE_TOKEN_ADDRESS;

	const TREASURY_WALLET = import.meta.env.VITE_TREASURY_WALLET;

	const RPC_URL = import.meta.env.VITE_RPC_URL;

	/**
	 * ERC20 ABI
	 */
	const ABI = [
		"function balanceOf(address) view returns (uint256)",
		"function transfer(address to, uint256 amount) returns (bool)",
		"function decimals() view returns (uint8)",
		"function approve(address spender, uint256 amount) returns (bool)",
	];

	/**
	 * STATE
	 */
	const wallet = ref("");
	const balance = ref("0");
	const amount = ref("");
	const txHash = ref("");

	const loading = ref(false);
	const connecting = ref(false);
	const error = ref("");

	let provider = null;
	let signer = null;

	/**
	 * READ PROVIDER
	 */
	const readProvider = new ethers.JsonRpcProvider(RPC_URL);

	/**
	 * HELPERS
	 */
	const shortWallet = computed(() =>
		wallet.value
			? wallet.value.slice(0, 6) + "..." + wallet.value.slice(-4)
			: "",
	);

	const scanUrl = computed(
		() => `https://amoy.polygonscan.com/tx/${txHash.value}`,
	);

	/**
	 * CONNECT WALLET
	 */
	async function connectWallet() {
		try {
			error.value = "";
			connecting.value = true;

			if (!window.ethereum) {
				error.value = "MetaMask not found";
				return;
			}

			const accounts = await window.ethereum.request({
				method: "eth_requestAccounts",
			});

			if (!accounts.length) {
				error.value = "No wallet found";
				return;
			}

			wallet.value = ethers.getAddress(accounts[0]);

			provider = new ethers.BrowserProvider(window.ethereum);
			signer = await provider.getSigner();

			await loadBalance();
		} catch (e) {
			console.error(e);
			error.value = "Wallet connection failed";
		} finally {
			connecting.value = false;
		}
	}

	/**
	 * LOAD BALANCE
	 */
	async function loadBalance() {
		try {
			const contract = new ethers.Contract(TOKEN_ADDRESS, ABI, readProvider);

			let decimals = 18;

			try {
				decimals = await contract.decimals();
			} catch {
				console.warn("Using fallback decimals = 18");
			}

			const raw = await contract.balanceOf(wallet.value);

			balance.value = ethers.formatUnits(raw, decimals);
		} catch (e) {
			console.error(e);
			error.value = "Balance load failed (RPC issue)";
		}
	}

	/**
	 * DEPOSIT FLOW (APPROVE + TRANSFER)
	 */
	async function deposit() {
		try {
			loading.value = true;
			error.value = "";
			txHash.value = "";

			const contract = new ethers.Contract(TOKEN_ADDRESS, ABI, signer);

			const decimals = await contract.decimals();
			const value = ethers.parseUnits(amount.value.toString(), decimals);

			/**
			 * STEP 1: APPROVE
			 */

			const approveTx = await contract.approve(TREASURY_WALLET, value, {
				maxFeePerGas: ethers.parseUnits("50", "gwei"),
				maxPriorityFeePerGas: ethers.parseUnits("30", "gwei"),
			});
			await approveTx.wait();

			/**
			 * STEP 2: TRANSFER
			 */
			const tx = await contract.transfer(TREASURY_WALLET, value);
			await tx.wait();

			txHash.value = tx.hash;

			/**
			 * BACKEND LOG
			 */

			await DepositApi.create({
				tx_hash: tx.hash,
				wallet: wallet.value,
			});

			await loadBalance();
			amount.value = "";
		} catch (e) {
			console.error(e);
			error.value =
				e?.code === 4001 ? "Transaction rejected" : "Deposit failed";
		} finally {
			loading.value = false;
		}
	}
</script>

<style scoped>
	.page {
		height: 100vh;
		display: flex;
		justify-content: center;
		align-items: center;
		background: #f3f4f6;
	}

	.card {
		width: 420px;
		padding: 30px;
		background: white;
		border-radius: 16px;
		box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
	}

	input {
		width: 100%;
		margin: 10px 0;
		padding: 10px;
	}

	button {
		width: 100%;
		margin-top: 10px;
		padding: 10px;
	}

	.info {
		margin-top: 10px;
		background: #f9fafb;
		padding: 10px;
		border-radius: 10px;
	}

	.success {
		margin-top: 15px;
		background: #ecfdf5;
		padding: 10px;
		border-radius: 10px;
	}

	.error {
		color: red;
		margin-top: 10px;
	}
</style>
