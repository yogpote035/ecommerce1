# Ecommerce Modernization Project

PHP/MySQL ecommerce application modernized incrementally while preserving the original procedural PHP structure.

## Main Entry Points

- Storefront: `Home.php`, `index.php?page=1`
- Unified auth: `auth.php`
- Cart: `view_cart.php`
- Checkout: `checkout.php`
- Order tracking: `Track.php`
- Admin dashboard: `admin/dashboard.php`
- Admin products: `admin_products.php`
- Admin orders: `admin_orders.php`
- Backup manager: `admin/backup-manager.php`

## Setup

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin or MySQL CLI.
3. Import the global schema file:
   - phpMyAdmin: import `ecommerce-global.sql`
   - CLI: `mysql -u root -p < ecommerce-global.sql`
   This file creates both the `ecommerce` database and the retailer database `retailler`.
4. Confirm database credentials in `config/Database.php` or create a `.env` file.
   The application loads environment variables from `.env` when present.

   Required database variables:
   - `DB_HOST=localhost`
   - `DB_USER=root`
   - `DB_PASS=your_db_password`
   - `DB_NAME=ecommerce`
   - `DB_CHARSET=utf8mb4`

   Required Razorpay variables:
   - `RAZORPAY_KEY_ID=rzp_test_xxxxxxxx`
   - `RAZORPAY_KEY_SECRET=xxxxxxxxxxxxxxxxxxxx`
   - `RAZORPAY_WEBHOOK_SECRET=xxxxxxxxxxxxxxxxxxxx`

   Optional mail variables:
   - `SMTP_HOST=smtp.example.com`
   - `SMTP_PORT=587`
   - `SMTP_USER=your_smtp_username`
   - `SMTP_PASS=your_smtp_password`
   - `SMTP_ENCRYPTION=starttls`
   - `EMAIL_FROM=noreply@example.com`
   - `EMAIL_SUBJECT=Ecommerce Notification`

   Optional environment variable:
   - `APP_ENV=development`

   Note: these sample credentials are placeholders only. Replace them with real values before running the app.
5. Place the project in XAMPP's `htdocs` folder and open:
   - `http://localhost/ecommerce1/Home.php`

## Local development notes

- The app loads `config/Database.php` and `init.php` on every request.
- `init.php` creates or migrates support tables like `remember_tokens` and `password_resets` automatically.
- If you see database errors, verify MySQL is running and the credentials are correct.

## How to use

- Customer:
  - Open `Home.php` or `index.php?page=1`
  - Sign up / login via `auth.php`
  - Add products to cart, view `view_cart.php`, and checkout using `checkout.php`
- Admin:
  - Login via `auth.php` with admin credentials
  - Use `admin/dashboard.php`, `admin_products.php`, `admin_orders.php`, and backup manager
- Retailer:
  - Login via `Rlogin.php`
  - Add products via `Radd.php`
  - Manage product catalog via `Rview.php`

## Database

- Use `ecommerce-global.sql` for a full schema and sample data.
- Key tables:
  - `apadd` - product catalog
  - `cregister` - customer accounts
  - `aregister` - admin accounts
  - `cart_items` - customer cart state
  - `orders`, `payments`, `payment_logs` - order/payment records
  - `wishlist` - saved items

## QA

- Run `php scripts/qa_smoke.php` before deployment.
- Check that `public/assets`, `uploads/products`, and `images/products` files are present for product thumbnails and layout assets.
