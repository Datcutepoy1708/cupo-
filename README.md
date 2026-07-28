# Cupo — Multi-vendor E-commerce Platform

## Overview
Cupo is a multi-vendor e-commerce platform inspired by Shopee, built by a team
of 3 as a portfolio / capstone project.

## Tech Stack
- **Backend:** Laravel 11 (PHP 8.3)
- **Database:** MySQL
- **Cache / Queue / Flash Sale Lock:** Redis
- **Search:** Laravel Scout + Meilisearch
- **Real-time Chat:** Laravel Echo + Soketi
- **Payment Gateways:** VNPay / Momo (Sandbox)

## Key Features
- 3 roles: Customer / Seller / Admin
- Shopping cart, checkout, online payment
- Order splitting: parent order split into per-seller sub-orders
- Flash sale with anti-oversell handling via Redis atomic lock
- Advanced product search & filtering
- Seller wallet & payout reconciliation

## Installation

\`\`\`bash
git clone <repo-url>
cd cupo
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
\`\`\`

## Project Structure
See [AGENT.md](./AGENT.md) for detailed architecture guidelines.

## Documentation
- [Database Schema](docs/database-schema.md)
- [Contributing Guide](CONTRIBUTING.MD)

## Team
| Name | Module |
|---|---|
| [Member A] | Admin module |
| [Member B] | Seller module |
| [Your name] | Customer module |

## License
MIT