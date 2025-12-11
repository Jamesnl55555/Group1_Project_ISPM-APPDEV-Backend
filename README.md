## Team Information
- Jorilyn Pantallano
- Czarina Fombuena
- Ellissea Montes
- Maffia Khate Guaro
- Jimmy Lumaad
- Nathaniel Miguel


---


# Project Overview and Objectives

> Our system entitled “88Chocolates Inventory and Transaction System,” developed for our client, 88 Chocolates, a small retail store that sells both imported and local food products.

At present, the store manages its inventory and transaction records manually. This traditional method makes it difficult to monitor product availability, track sales accurately, and avoid errors in recording. Manual processes are also time-consuming and may lead to misplaced records, incorrect stock counts, and slow business operations.

To address these challenges, we proposed the 88Chocolates Inventory and Transaction System, a computerized system designed to help the business efficiently manage its product inventory and daily transactions. This system will allow the store to record sales accurately, monitor stock levels in real time, and generate necessary reports, making operations faster, more organized, and more reliable.

Through this system, we aim to help 88 Chocolate improve its overall business performance by providing a modern, efficient, and user-friendly solution for managing inventory and transactions.

---

## Tech Stack Used
**Frontend:**
- React.js
- React Router
- Axios
- Recharts
- TailwindCSS / CSS
- Mantine Tabler Icons
- Vite

**Backend:**
- Laravel (PHP)
- Laravel Sanctum or JWT (Authentication)
- MySQL / PostgreSQL
- Laravel Eloquent (ORM)
- Composer (Dependency Manager)

**DevOps / Hosting:**
- Git / GitHub
- Docker (optional)
- Cloud hosting (Vercel, Render)

---

## Setup & Installation

### Backend (Laravel)

cd Group1_Project_ISPM-APPDEV-Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

### Frontend (React)
cd Group1_Project_ISPM-APPDEV-Frontend
npm install
npm run build
npm run dev


Deployment Links
### Backend
- https://group1-project-ispm-appdev-backend.onrender.com

### Frontend
- https://ispmappdevfrontend.vercel.app/