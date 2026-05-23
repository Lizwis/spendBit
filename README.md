# SpendBit

SpendBit is a crypto-to-card infrastructure prototype built for South Africa.  
It enables users to deposit crypto and instantly convert it into spendable balances for future virtual card issuing integrations.

Built for the AlgoFest Hackathon 2026.

---

# 🚀 Problem

In South Africa, converting crypto into spendable money is still slow.

Most exchanges:
- require manual withdrawals
- take hours or days to settle
- force users through banking delays

SpendBit explores a faster alternative:
- deposit crypto
- verify on-chain instantly
- credit user balance
- enable future virtual card spending

---

# ✨ Features

## Frontend
- Vue 3 + Vite
- Wallet connection with MetaMask
- ERC20 token deposits
- Live balance fetching
- Transaction success states
- Deposit history UI
- Authentication with Pinia

## Backend
- Laravel API
- Polygon Amoy transaction verification
- ERC20 transfer validation
- Treasury wallet validation
- Secure deposit logging
- Sanctum authentication
- User-linked deposits

## Blockchain
- Polygon Amoy Testnet
- ERC20 token transfers
- On-chain transaction verification

---

# 🏗 Tech Stack

## Frontend
- Vue 3
- Vite
- Pinia
- Axios
- Bootstrap 5
- Ethers.js

## Backend
- Laravel
- Sanctum
- MySQL

## Blockchain
- Polygon Amoy
- ERC20

---

# 📁 Project Structure

```text
spendbit/
│
├── backend/     # Laravel API
│
├── frontend/    # Vue.js application
│
└── README.md