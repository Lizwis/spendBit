<template>
	<AuthLayout>
		<div class="container py-4">
			<div class="row justify-content-center">
				<div class="col-lg-6">
					<div class="card border-0 shadow-lg rounded-4">
						<div class="card-body p-4">
							<!-- HEADER -->
							<div class="text-center mb-4">
								<h2 class="fw-bold mb-2">Crypto Deposit</h2>

								<p class="text-muted mb-0">
									Deposit custom USDC token into treasury (Amoy)
								</p>
							</div>

							<!-- CONNECT BUTTON -->
							<div class="d-grid mb-4">
								<button
									@click="connectWallet"
									:disabled="connecting"
									class="btn btn-primary btn-lg rounded-3"
								>
									<span v-if="connecting">
										<span class="spinner-border spinner-border-sm me-2"></span>
										Connecting...
									</span>

									<span v-else>
										{{ wallet ? "Wallet Connected" : "Connect Wallet" }}
									</span>
								</button>
							</div>

							<!-- WALLET INFO -->
							<div v-if="wallet" class="bg-light border rounded-4 p-3 mb-4">
								<div
									class="d-flex justify-content-between align-items-center mb-2"
								>
									<span class="text-muted">Wallet</span>

									<span class="fw-semibold">
										{{ shortWallet }}
									</span>
								</div>

								<div class="d-flex justify-content-between align-items-center">
									<span class="text-muted">Balance</span>

									<span class="fw-bold text-success">
										{{ balance }}
									</span>
								</div>
							</div>

							<!-- DEPOSIT FORM -->
							<div v-if="wallet">
								<div class="mb-3">
									<label class="form-label fw-semibold"> Token Amount </label>

									<input
										v-model="amount"
										type="number"
										class="form-control form-control-lg rounded-3"
										placeholder="Enter amount"
									/>
								</div>

								<div class="d-grid">
									<button
										@click="deposit"
										:disabled="loading || !amount"
										class="btn btn-success btn-lg rounded-3"
									>
										<span v-if="loading">
											<span
												class="spinner-border spinner-border-sm me-2"
											></span>

											Processing Transaction...
										</span>

										<span v-else> Deposit Tokens </span>
									</button>
								</div>
							</div>

							<!-- SUCCESS -->
							<div
								v-if="txHash"
								class="alert alert-success mt-4 rounded-4 border-0"
							>
								<div class="fw-bold mb-2">✅ Transaction Successful</div>

								<div class="small mb-2">
									Hash:
									<span class="fw-semibold">
										{{ txHash }}
									</span>
								</div>

								<a
									:href="scanUrl"
									target="_blank"
									class="btn btn-sm btn-outline-success"
								>
									View on PolygonScan
								</a>
							</div>

							<!-- ERROR -->
							<div
								v-if="error"
								class="alert alert-danger mt-4 rounded-4 border-0"
							>
								{{ error }}
							</div>
						</div>
					</div>

					<!-- FOOTER -->
					<div class="text-center mt-3 text-muted small">
						Powered by Polygon Amoy Testnet
					</div>
				</div>
			</div>
		</div>
	</AuthLayout>
</template>

<script setup>
	import { ref, computed } from "vue";
	import { ethers } from "ethers";

	import DepositApi from "../api/crypto/deposit";
	import AuthLayout from "../layouts/AuthLayout.vue";

	/**
	 * =====================================
	 * ENV CONFIG
	 * =====================================
	 */
	const TOKEN_ADDRESS = import.meta.env.VITE_TOKEN_ADDRESS;

	const TREASURY_WALLET = import.meta.env.VITE_TREASURY_WALLET;

	const RPC_URL = import.meta.env.VITE_RPC_URL;

	/**
	 * =====================================
	 * ERC20 ABI
	 * =====================================
	 */
	const ABI = [
		"function balanceOf(address) view returns (uint256)",
		"function transfer(address to, uint256 amount) returns (bool)",
		"function decimals() view returns (uint8)",
	];

	/**
	 * =====================================
	 * STATE
	 * =====================================
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
	 * =====================================
	 * READ PROVIDER
	 * =====================================
	 */
	const readProvider = new ethers.JsonRpcProvider(RPC_URL);

	/**
	 * =====================================
	 * HELPERS
	 * =====================================
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
	 * =====================================
	 * CONNECT WALLET
	 * =====================================
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
	 * =====================================
	 * LOAD BALANCE
	 * =====================================
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
	 * =====================================
	 * DEPOSIT FLOW
	 * =====================================
	 */
	async function deposit() {
		try {
			loading.value = true;

			error.value = "";
			txHash.value = "";

			/**
			 * Ensure wallet connected
			 */
			if (!signer) {
				error.value = "Please connect wallet";
				return;
			}

			const contract = new ethers.Contract(TOKEN_ADDRESS, ABI, signer);

			/**
			 * TOKEN DECIMALS
			 */
			const decimals = await contract.decimals();

			/**
			 * CONVERT USER INPUT
			 */
			const value = ethers.parseUnits(amount.value.toString(), decimals);

			/**
			 * DIRECT TOKEN TRANSFER
			 * (NO APPROVE NEEDED)
			 */
			const tx = await contract.transfer(TREASURY_WALLET, value, {
				maxFeePerGas: ethers.parseUnits("50", "gwei"),

				maxPriorityFeePerGas: ethers.parseUnits("30", "gwei"),
			});

			/**
			 * WAIT FOR CONFIRMATION
			 */
			await tx.wait();

			txHash.value = tx.hash;

			/**
			 * SAVE VERIFIED TX TO BACKEND
			 */
			await DepositApi.create({
				tx_hash: tx.hash,
				wallet: wallet.value,
			});

			/**
			 * REFRESH BALANCE
			 */
			await loadBalance();

			/**
			 * RESET FORM
			 */
			amount.value = "";
		} catch (e) {
			console.error(e);

			if (e?.code === 4001) {
				error.value = "Transaction rejected";
			} else {
				error.value = e?.reason || e?.message || "Deposit failed";
			}
		} finally {
			loading.value = false;
		}
	}
</script>
