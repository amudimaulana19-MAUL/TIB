# Marketplace Sepatu Bola - Premium Football Shoe Marketplace

A complete premium football shoe marketplace website built with PHP Native + MySQL without any frameworks. Ready to run on XAMPP.

## Features

### User Features
- User registration and login with secure authentication
- Product browsing with search, filter, and sort functionality
- Product detail pages with reviews
- Shopping cart with quantity management
- Wishlist functionality
- Checkout process with multiple payment methods
- Order history tracking
- Profile management with avatar upload
- Password change functionality

### Admin Features
- Dashboard with statistics and charts
- Product management (CRUD)
- Category management (CRUD)
- User management
- Order management with status updates
- Payment verification
- Banner management
- Promo management
- Testimonial management

### Technical Features
- CSRF protection
- XSS prevention
- SQL injection prevention (prepared statements)
- Password hashing
- Session security
- File upload validation
- AJAX for dynamic content loading
- Responsive design with Bootstrap 5
- Glassmorphism UI style
- Dark mode support
- Smooth animations with AOS
- SweetAlert2 for notifications

## Requirements

- XAMPP (or any PHP + MySQL server)
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge, Safari)

## Installation

### 1. Setup XAMPP

1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services from XAMPP Control Panel

### 2. Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `marketplace_sepatu_bola`
3. Import the `database.sql` file into the database
   - Click on the database name
   - Click "Import" tab
   - Choose the `database.sql` file
   - Click "Go"

### 3. Project Setup

1. Copy the entire `UASWEB` folder to your XAMPP htdocs directory
   - Default location: `C:\xampp\htdocs\`
2. Rename the folder if desired (e.g., `marketplace-sepatu-bola`)

### 4. Configuration

1. Open `config/database.php`
2. Update the database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'marketplace_sepatu_bola');
   ```
3. Update the site URL:
   ```php
   define('SITE_URL', 'http://localhost/marketplace-sepatu-bola');
   ```

### 5. File Permissions

Ensure the following directories have write permissions:
- `assets/uploads/products/`
- `assets/uploads/banners/`
- `assets/uploads/promos/`
- `assets/uploads/avatars/`
- `assets/uploads/payment/`

## Accessing the Website

### Public Website
- URL: `http://localhost/marketplace-sepatu-bola` (or your folder name)

### Admin Panel
- URL: `http://localhost/marketplace-sepatu-bola/admin/dashboard.php`

### Default Admin Credentials
- Email: `admin@marketplace.com`
- Password: `admin123`

**Important:** Change the default admin password after first login!

## Default Data

The database includes:
- 1 admin user
- 5 product categories
- 8 payment methods
- 3 banners
- 2 promos
- 3 testimonials
- 12 sample products

## Folder Structure

```
UASWEB/
├── admin/
│   ├── ajax/
│   │   └── get_order_detail.php
│   ├── banner.php
│   ├── dashboard.php
│   ├── detail_produk.php
│   ├── edit_produk.php
│   ├── kategori.php
│   ├── navbar.php
│   ├── pesanan.php
│   ├── produk.php
│   ├── promo.php
│   ├── sidebar.php
│   ├── testimoni.php
│   └── tamb_produk.php
├── ajax/
│   ├── add_to_cart.php
│   ├── add_to_wishlist.php
│   ├── filter_products.php
│   ├── get_cart_count.php
│   ├── get_user_order_detail.php
│   ├── get_wishlist_count.php
│   ├── live_search.php
│   ├── load_more_products.php
│   ├── process_payment.php
│   ├── remove_from_cart.php
│   ├── remove_from_wishlist.php
│   └── update_cart_quantity.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── uploads/
│       ├── products/
│       ├── banners/
│       ├── promos/
│       ├── avatars/
│       └── payment/
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/
│   └── database.php
├── database.sql
├── index.php
├── produk.php
├── detail_produk.php
├── wishlist.php
├── keranjang.php
├── checkout.php
├── pembayaran.php
├── riwayat_pesanan.php
├── profil.php
├── tentang.php
├── kontak.php
├── faq.php
└── README.md
```

## Security Notes

- All database queries use prepared statements to prevent SQL injection
- CSRF tokens are implemented on all forms
- User inputs are sanitized to prevent XSS attacks
- Passwords are hashed using PHP's password_hash()
- File uploads are validated for type and size
- Session security is implemented

## Technologies Used

- PHP 8+ (Native, no frameworks)
- MySQL with InnoDB engine
- Bootstrap 5 for responsive UI
- Font Awesome for icons
- jQuery for AJAX interactions
- SweetAlert2 for notifications
- AOS (Animate On Scroll) for animations
- Chart.js for admin dashboard charts

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Edge (latest)
- Safari (latest)

## License

This project is created for educational purposes.

## Support

For issues or questions, please contact the development team.
