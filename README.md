# FPBG Stock Management System

A comprehensive stock management and point-of-sale system.

## Project Structure
```
fpbg_final/
├── config/
│   ├── config.php
│   └── database.php
├── public/
│   ├── css/
│   ├── js/
│   └── index.php
├── includes/
│   ├── auth.php
│   ├── functions.php
│   └── session.php
├── models/
│   ├── User.php
│   ├── Product.php
│   └── Transaction.php
├── controllers/
│   ├── AuthController.php
│   ├── InventoryController.php
│   └── TransactionController.php
├── views/
│   ├── auth/
│   ├── dashboard/
│   └── inventory/
└── assets/
    ├── images/
    └── vendor/
```

## Setup Instructions
1. Configure your database settings in `config/database.php`
2. Import the SQL files from the `database` directory
3. Ensure proper permissions on directories
4. Configure your web server to point to the `public` directory

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer for dependency management

SlowlyKent = Felicia
spiegel123 = Gagni
erinellarojas = Buhisan
Cjvp200414 = Plaida
