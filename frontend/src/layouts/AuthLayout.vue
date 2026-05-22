<template>
	<div class="d-flex flex-column flex-lg-row min-vh-100">
		<!-- Sidebar -->
		<nav
			id="sidebar"
			class="bg-dark text-white p-3 flex-shrink-0"
			:class="{ 'd-none': isSidebarCollapsed, 'd-lg-block': true }"
			style="min-width: 250px"
		>
			<h4 class="text-white fw-bold mb-4">SpendBit</h4>

			<ul class="nav nav-pills flex-column mb-auto">
				<li class="nav-item">
					<router-link
						class="nav-link text-white"
						active-class="active"
						to="/crypto/deposit"
					>
						<i class="bi bi-speedometer2 me-2"></i> Deposit
					</router-link>
				</li>
				<li class="nav-item">
					<router-link
						class="nav-link text-white"
						active-class="active"
						to="/transaction/history"
					>
						<i class="bi bi-person-circle me-2"></i> Transactions
					</router-link>
				</li>
			</ul>

			<div class="mt-auto small text-white-50 pt-5">&copy; 2026 Spendbit</div>
		</nav>

		<!-- Main content -->
		<div class="flex-grow-1 bg-light">
			<!-- Top Navbar -->
			<nav
				class="navbar navbar-light bg-white border-bottom shadow-sm px-3 px-lg-4"
			>
				<div class="container-fluid">
					<button
						class="btn btn-outline-primary d-lg-none"
						@click="toggleSidebar"
					>
						<i class="bi bi-list"></i>
					</button>

					<div class="ms-auto d-flex align-items-center">
						<span class="me-3 text-muted">Hi, {{ authStore.user?.name }}</span>
						<button
							class="btn btn-outline-danger btn-sm"
							@click="authStore.logout()"
						>
							Logout
						</button>
					</div>
				</div>
			</nav>

			<!-- Page Content -->
			<div class="col-12">
				<slot />
			</div>
		</div>
	</div>
</template>

<script setup>
	import { ref } from "vue";
	import { useAuthStore } from "../stores/auth";

	const authStore = useAuthStore();
	const isSidebarCollapsed = ref(false);

	function toggleSidebar() {
		isSidebarCollapsed.value = !isSidebarCollapsed.value;
	}
</script>

<style scoped>
	#sidebar .nav-link.active,
	#sidebar .nav-link:hover {
		background-color: rgba(255, 255, 255, 0.15);
		border-radius: 0.375rem;
	}

	#sidebar i {
		width: 20px;
	}
</style>
