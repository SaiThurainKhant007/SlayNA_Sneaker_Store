# SlayNa Sneaker Store

An e-commerce website for **SlayNa Sneaker Store**, a licensed sneaker shop in Yangon, Myanmar.
Customers can browse authentic sneakers by brand, category and size, order online and pay with
KBZPay, AYA Pay, a saved card or cash on delivery. Staff run stock, orders and payment checks,
and admins manage the catalogue, promotions, staff accounts and sales reports.

Built with **Laravel 13, PHP 8.3, MySQL and Bootstrap 5** as the Information Systems Project
(BSc (Hons) Business Computing and Information Systems) by Sai Thurein Khant.

## Features

### Customers
- Register, log in, and manage their profile and password
- Browse products, see new arrivals, and search or filter by brand, category and size
- View product details with a photo gallery, size availability, ratings and reviews
- Add sizes to the cart, update quantities and check out with a shipping address
- Pay by **KBZPay or AYA Pay** (scan the QR code, then upload the transfer screenshot), a saved card or cash on delivery
- Track order and payment status in their order history
- Write a star rating and review after an order containing the product is completed
- Save Visa or Mastercard cards (only the last four digits are stored)

### Staff
- Dashboard of pending orders, payments waiting for review and low-stock sizes
- Update stock for each size, with a low-stock filter
- Move orders through *pending → confirmed → processing → shipped → completed*. Cancelling an order puts its items back into stock.
- Check payment screenshots and confirm or reject them. A confirmed payment confirms the order.

### Admin
- Dashboard with orders, customers, products and sales, month-on-month change and a sales chart
- Manage products (multiple photos with cropping), sizes and stock, categories and brands (with logos)
- Run promotions with a discount from 1% to 90%, banner images and an end date
- Manage the home page gallery
- Create staff and admin accounts, and enable or disable them
- Sales, inventory and customer reports

## Tech stack

| Layer | Technology |
|---|---|
| Back end | Laravel 13 (PHP 8.3), Eloquent ORM, Blade templates |
| Database | MySQL |
| Front end | HTML5, CSS3, Bootstrap 5, Bootstrap Icons |
| Testing | PHPUnit feature tests |
| Local server | XAMPP or `php artisan serve` |

## Getting started

### Requirements
- PHP 8.3 or newer, with Composer
- MySQL (XAMPP works)

### Install

```bash
git clone https://github.com/<your-username>/SlayNa_Sneaker_Store.git
cd SlayNa_Sneaker_Store
composer install
cp .env.example .env
php artisan key:generate
```

If your MySQL login is not `root` with no password, edit the `DB_` settings in `.env`.

### Set up the database

Choose one option.

**Option A: fresh database with demo data**

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS slayna_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
```

**Option B: restore the included backup**

```bash
mysql -u root < database/backup/slayna_db.sql
php artisan migrate
```

The backup was taken before promotions, the gallery, product photos and the staff table were added. `php artisan migrate` adds them.

### Run

```bash
php artisan storage:link
php artisan serve
```

Open http://localhost:8000.

### Demo accounts

Every seeded account uses the password `Password123!`.

| Role | Email |
|---|---|
| Admin | admin@slayna.com |
| Staff | staff@slayna.com |
| Customer | customer@slayna.com |

### Mobile wallet settings

The KBZPay and AYA Pay phone numbers and account names live in `config/payments.php`. You can override them in `.env` with `KPAY_PHONE`, `KPAY_ACCOUNT_NAME`, `AYAPAY_PHONE` and `AYAPAY_ACCOUNT_NAME`. To change the QR codes, replace the images in `public/img/payments/`.

The store's address, phone, email and opening hours are in `config/shop.php`.

## Project structure

```
app/Http/Controllers/          Home, Product, Cart, Checkout, Order, Page
app/Http/Controllers/Auth/     Register and Login
app/Http/Controllers/Account/  Customer dashboard and saved cards
app/Http/Controllers/Staff/    Dashboard, Inventory, Orders, Payments
app/Http/Controllers/Admin/    Dashboard, Products, Categories, Brands, Promotions,
                               Gallery, Staff accounts, Reports
app/Http/Middleware/           EnsureUserHasRole (role-based access)
app/Models/                    Eloquent models for every table
config/payments.php            KBZPay / AYA Pay details
config/shop.php                Store contact details and opening hours
database/migrations/           Database schema
database/seeders/              Demo accounts and catalogue
database/backup/slayna_db.sql  MySQL backup with demo data
resources/views/               Blade templates (customer, staff and admin layouts)
routes/web.php                 Routes grouped by role
tests/Feature/                 Feature tests
```

## Use cases covered

| Use case | Where it is handled |
|---|---|
| UC_01 Customer Login Management | `Auth/RegisterController`, `Auth/LoginController` |
| UC_02 Review & Rating Management | `ProductController::storeReview` |
| UC_03 Product Management | `Admin/ProductController`, `Admin/CategoryController`, `Admin/BrandController` |
| UC_04 Product Browsing Management | `ProductController::index`, `show`, `newArrivals` |
| UC_05 Reports & Staff Management | `Admin/UserController` |
| UC_06 Reports Management | `Admin/ReportController` |
| UC_07 Inventory Management | `Staff/InventoryController` |
| UC_08 Order Processing Management | `Staff/OrderController` |
| UC_09 Payment Confirmation Management | `Staff/PaymentController` |

## Security

- Passwords are hashed with bcrypt.
- All database access goes through Eloquent with bound parameters, so there is no raw SQL built from user input.
- Every form is protected against CSRF.
- Staff and admin pages are restricted by role, and disabled accounts are logged out.
- Checkout re-checks stock inside a database transaction with row locks, so two customers cannot buy the last pair at once.
- Full card numbers are never stored, only the last four digits.

## Tests

```bash
php artisan test
```

The tests cover login and role access, checkout and stock, reviews, staff stock, order and payment actions, admin product and staff management, product photos, brand logos and profiles.

## Limitations

- This is a student project. It does **not** take real payments. Wallet payments are confirmed by staff from the uploaded screenshot.
- There is no email verification or password reset yet.
- Reports are shown on screen only, with no PDF or CSV export.

## Author

**Sai Thurein Khant**, Information Systems Project, BSc (Hons) Business Computing and Information Systems,
Strategy First International College (University of Lancashire / NCC Education), 2026.
