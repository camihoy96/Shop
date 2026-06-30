# ⌚ ChronoVerse – The Universe of Time

<p align="center">
  <img src="image/logo.png" alt="ChronoVerse Logo" width="180">
</p>

<p align="center">
  <b>A premium e-commerce platform for luxury skeleton watches that combines timeless craftsmanship with modern technology.</b>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black">
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge">
</p>

---

# 📖 Overview

**ChronoVerse** is a full-featured e-commerce web application designed for showcasing and selling premium skeleton watches. The platform provides customers with a seamless online shopping experience while offering administrators a powerful dashboard for managing products, orders, customers, and business analytics.

---

# ✨ Key Features

## 🛍️ Shopping Experience

- Elegant and responsive storefront
- Browse premium watch collections
- Product categories and search functionality
- Interactive product details modal
- Featured products showcase
- Real-time stock availability
- Responsive product gallery

---

## 👤 User Management

- Secure account registration and login
- Google OAuth Authentication
- Facebook Login Integration
- Guest Checkout
- User Profile Management
- Avatar Upload
- Shipping Address Management

---

## 🛒 E-Commerce Features

- Shopping Cart (Local Storage)
- Multi-Step Checkout Process
- Multiple Payment Methods

Supported payment methods:

- 💳 Credit / Debit Card
- 🅿️ PayPal
- 📱 GCash
- 🏦 Bank Transfer
- 💵 Cash on Delivery (COD)

Additional features:

- Order Confirmation
- Unique Tracking Number
- Order History
- Order Status Tracking

---

## 📱 User Interface

- Fully Responsive Design
- Modern Landing Page
- Smooth Animations
- Parallax Scrolling Effects
- Dynamic Product Filtering
- Interactive Product Cards
- Mobile-Friendly Layout

---

## 👨‍💼 Administration Panel

The administrator dashboard provides complete control over the system.

### Dashboard

- Sales Overview
- Revenue Analytics
- Product Statistics
- Customer Insights

### Product Management

- Add Products
- Edit Products
- Delete Products
- Product Categories
- Image Management
- Inventory Control

### Order Management

- View Orders
- Update Order Status
- Process Orders
- Track Deliveries

### Customer Management

- Manage User Accounts
- View Customer Information
- User Profile Administration

### Reports

- Sales Reports
- Inventory Reports
- Revenue Analytics

### System Tools

- Contact Messages
- System Settings
- Database Backup
- Website Configuration

---

# 🛠️ Technology Stack

| Category | Technologies |
|----------|--------------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6) |
| **Backend** | PHP 7.4+ |
| **Database** | MySQL |
| **Framework/UI** | Bootstrap 5 |
| **Charts** | Chart.js |
| **Icons** | Font Awesome |
| **Notifications** | SweetAlert2 |
| **Email Service** | PHPMailer |
| **Authentication** | Google OAuth 2.0, Facebook OAuth |
| **Communication** | Fetch API, RESTful PHP Endpoints |

---

# 📋 System Requirements

- PHP 7.4 or later
- MySQL 5.7 or later
- Apache or Nginx Web Server
- XAMPP / WAMP / LAMP
- Composer

---

# ⚙️ Installation Guide

## 1. Clone the Repository

```bash
git clone https://github.com/camihoy96/Shop.git
```

## 2. Navigate to the Project

```bash
cd Shop
```

## 3. Install PHP Dependencies

```bash
composer install
```

## 4. Create the Database

Open **phpMyAdmin** and create a database.

Example:

```text
shop
```

## 5. Import the Database

Import the SQL backup located in:

```text
backups/
```

## 6. Configure Database Connection

Update your database configuration file.

Example:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "shop";
```

## 7. Configure OAuth Credentials

Create your own Google and Facebook OAuth applications and update the credentials accordingly.

> **Important:** Never commit OAuth credentials, `.env` files, or client secret JSON files to GitHub.

## 8. Start the Server

Start:

- Apache
- MySQL

using XAMPP (or your preferred local server).

## 9. Launch the Application

```
http://localhost/Shop
```

---

# 📂 Project Structure

```text
Shop/
│
├── admin/
├── assets/
├── css/
├── image/
├── js/
├── settings/
├── vendor/
├── backups/
│
├── index.php
├── shop.php
├── cart.php
├── checkout.php
├── login.php
├── register.php
├── profile.php
├── dbconn.php
├── composer.json
├── .gitignore
└── README.md
```

---

# 🔒 Security Features

- Password Hashing
- Session-Based Authentication
- OAuth Authentication
- Input Validation
- SQL Injection Protection
- Secure File Upload Handling
- CSRF-Aware Session Management

---

# 🚀 Future Enhancements

- Wishlist
- Product Reviews & Ratings
- Discount Coupons
- Email Notifications
- SMS Notifications
- Live Chat Support
- Inventory Forecasting
- Sales Dashboard Improvements
- Multi-Vendor Marketplace
- Progressive Web App (PWA)

---

# 👨‍💻 Author

**Charlie Amihoy**

Bachelor of Science in Information Technology

---

# 📄 License

This project is intended for **educational, portfolio, and demonstration purposes**.

---

⭐ If you found this project helpful, consider giving it a star on GitHub!