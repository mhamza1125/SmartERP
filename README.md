```markdown
# SmartERP

[![Laravel Version](https://img.shields.io/badge/Laravel-10.10-brightgreen)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2-brightgreen)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-blue)](LICENSE)

SmartERP is a comprehensive **Enterprise Resource Planning (ERP)** system built with **Laravel 10** and **PHP 8.1+**. It is designed to streamline business operations including inventory, orders, purchases, deliveries, payroll, and financial management. This system is suitable for manufacturing or trading companies with complex workflows.

---

## 🚀 Features

### Authentication & Authorization
- User login/logout
- Role-Based Access Control (RBAC)
- Permission management
- Admin dashboard

### Inventory Management
- Product creation with sizing & costing
- Material tracking and ledger
- Stock management: issuance, receiving, returns
- Stock types: Material Processing, Opening Stock, Delivery, Machine Material
- Stock status tracking (Not Received, Partially Received, Completely Received)

### Order Management
- Order creation and tracking
- Order items with pricing
- Order fulfillment workflow

### Purchase & Receiving
- Purchase orders
- Vendor management
- Material receiving and return management

### Delivery Management
- Delivery tracking and returns
- Packing lists and cartons

### Employee & Payroll
- Employee management
- Attendance tracking (integrates with `att2000.mdb`)
- Salary management
- Work hours and non-working days configuration

### Financial Management
- Bank management
- Transaction tracking (domestic & foreign bank charges)
- Multi-currency support
- Asset management

### Reporting
- Daily issuance & receiving reports
- Material and purchase ledger
- Product stock requirements
- Comprehensive reporting system

### Settings & Configuration
- Company information with REX number and Statement of Origin for invoices
- Accounting heads and categories
- Work hours and non-working days

---

## 🛠️ Technology Stack

- **Backend Framework:** Laravel 10.10
- **PHP Version:** 8.2.0 (minimum 8.1)
- **Frontend:** Blade templates + Vite
- **Database:** MySQL / MariaDB
- **Packages:**
  - Laravel Sanctum (API authentication)
  - Spatie Activity Log (audit trails)
  - Guzzle HTTP client
  - PHPUnit (testing)

---

## 📂 Project Structure

```

app/
├── Console/
├── Exceptions/
├── Helpers/
├── Http/
│   ├── Controllers/  # 40+ controllers
│   ├── Middleware/
│   └── Requests/
├── Models/           # 40+ Eloquent models
├── Policies/
├── Providers/
└── Repositories/

resources/
├── views/
├── css/
└── js/

database/
├── migrations/
├── factories/
└── seeders/

tests/

````

---

## ⚙️ Architecture & Key Concepts
- **Repository Pattern** for data access
- **Policies** for authorization
- Extensive **controllers and models** for modular functionality
- **Activity logging** for audit trails
- **Image management** for products and materials
- **Attendance system** integration with external MDB database

---

## 📦 Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/SmartERP.git
cd SmartERP
````

2. Install dependencies:

```bash
composer install
npm install
npm run dev
```

3. Configure `.env` file and set database credentials

4. Run migrations and seeders:

```bash
php artisan migrate --seed
```

5. Serve the application:

```bash
php artisan serve
```

---

## 🧪 Testing

Run unit and feature tests:

```bash
php artisan test
```

---

## 📄 License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

```
