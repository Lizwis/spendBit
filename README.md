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


spendbit/
│
├── backend/     # Laravel API
│
├── frontend/    # Vue.js application
│
└── README.md

## ⚙️ Setup backend

- Clone this repository 
- navigate to the `backend` directory
- Copy `.env` file: `cp .env.example .env`
- Set the environment variables in `.env` file
- Install composer dependencies: `composer install`
- Generate key: `php artisan key:generate`
- Run migration and seeders: `php artisan migrate`
- serve app: `php artisan serve`

## setup Frontend

- navigate to the `frontend` directory
- Copy `.env` file: `cp .env.example .env`
- `npm install`
- `npm run dev`
- You can access the project at: `http://localhost:5173`

## Meta Mask Setup

Steps:
1. Connect your MetaMask wallet
2. Select **Polygon Amoy Testnet**
3. Request test MATIC
4. Wait for confirmation

## 🌐 Add Polygon Network (Amoy Testnet)

- Network Name: Polygon Amoy Testnet  
- RPC URL: https://rpc-amoy.polygon.technology  
- Chain ID: 80002  
- Explorer: https://amoy.polygonscan.com

## 🪙 USDT Token Setup (Polygon Mainnet)

- :contentReference[oaicite:0]{index=0}  

### USDT Contract Address (Polygon Mainnet):
0xc2132D05D31c914a87C6611C10748AaCB1f0B0c5

### Add USDT to MetaMask:
1. Open MetaMask
2. Go to **tokens**
3. Click **Import Tokens**
4. Paste contract address above
5. Confirm symbol (USDT)
6. Click **Add Token**

## Load test tokens
1. go to `https://faucet.polygon.technology/`
2. paste your wallet address and claim tokens



