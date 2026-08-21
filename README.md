# Splendid Caterery

**Smart Catering & Event Management Platform** — a world-class MVP for Splendid Caterery, built with PHP 8, MySQL, Bootstrap 5, and modern JavaScript.

![PHP 8](https://img.shields.io/badge/PHP-8+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8+-4479A1?style=flat&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)

## Features

### Public Website
- Home, About, Services, Packages, Menu, Gallery, Book Now, Testimonials, Contact
- Professional UI with high-quality food photography (Unsplash)
- Dietary filters (Vegetarian, Vegan, Halal, Gluten-Free)
- Live availability calendar
- Instant quotation calculator
- 4-step booking wizard with online payment

### User Roles (6 Dashboards)
| Role | Dashboard |
|------|-----------|
| Customer | Bookings, payments, invoices, reviews, loyalty points |
| Administrator | Full system management, reports, AI forecast |
| Event Manager | Event plans, checklists, staff assignments |
| Chef / Kitchen | Production orders, recipes, inventory |
| Delivery & Setup | GPS routes, proof of delivery, setup tracking |
| Finance Manager | Revenue reports, payment verification, refunds |

### AI Features (Rule-Based MVP)
- AI Menu Recommendation
- AI Budget Planner
- AI Guest Quantity Predictor
- AI Chat Assistant (live widget)
- AI Sales Dashboard & Forecast
- AI Inventory Prediction
- AI Customer Upsell Suggestions

### Payments
Simulated gateway supporting: MTN MoMo, Telecel Cash, AirtelTigo, Visa, Mastercard, Bank Transfer, Cash

## Quick Start

### Requirements
- PHP 8.0+
- MySQL 8.0+
- PDO MySQL extension

### Installation

1. **Clone / open the project**
   ```bash
   cd C:\Users\PROVENCAL\Projects\splendid-caterery
   ```

2. **Configure database** (optional — defaults work for local XAMPP/WAMP)
   ```bash
   copy .env.example .env
   ```
   Edit `.env` with your MySQL credentials.

3. **Start PHP development server**
   ```bash
   php -S localhost:8080 -t public public/router.php
   ```

4. **Run database setup**
   Open [http://localhost:8080/setup](http://localhost:8080/setup) and click **Install Database**.

5. **Visit the platform**
   Open [http://localhost:8080](http://localhost:8080)

### Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@splendidcaterery.com | password |
| Customer | customer@example.com | password |
| Event Manager | events@splendidcaterery.com | password |
| Chef | kitchen@splendidcaterery.com | password |
| Delivery | delivery@splendidcaterery.com | password |
| Finance | finance@splendidcaterery.com | password |

## Project Structure

```
splendid-caterery/
├── api/                  # REST API endpoints (AI, booking, payment)
├── auth/                 # Login, register, logout
├── config/               # App & database configuration
├── dashboards/           # Role-based dashboards
│   ├── admin/
│   ├── customer/
│   ├── event-manager/
│   ├── kitchen/
│   ├── delivery/
│   └── finance/
├── database/
│   ├── schema.sql        # Full database schema (30+ tables)
│   └── seed.sql          # Demo data
├── includes/             # Shared PHP (auth, functions, layouts)
├── pages/                # Public website pages
├── public/
│   ├── assets/           # CSS, JS
│   ├── index.php         # Front controller / router
│   └── router.php        # PHP dev server router
└── setup.php             # Database installer
```

## Database Schema Highlights

- **users** — Multi-role authentication
- **bookings** — Full event lifecycle tracking
- **payments** / **invoices** — Financial management
- **kitchen_orders** — Production workflow
- **delivery_tasks** — Logistics tracking
- **inventory_items** — Stock management
- **chat_messages** — AI assistant history
- **loyalty_transactions** — Rewards program
- **promotions** — Promo codes & discounts

## Production Deployment

For Apache, point DocumentRoot to `/public` and enable `mod_rewrite` (`.htaccess` included).

For Nginx:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Promo Codes (Demo)
- `WELCOME10` — 10% off first booking
- `GOLD500` — ₵500 off Gold packages
- `SUMMER15` — 15% off outdoor BBQ

## License

Proprietary — Splendid Caterery © 2026
