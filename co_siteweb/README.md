# FragranceBoutique - Complete E-Commerce Fragrance Store

FragranceBoutique is a fully functional PHP-based e-commerce website for a premium fragrance boutique. The platform features a complete product catalog, shopping cart system, user authentication, admin dashboard, and an intelligent FAQ chatbot assistant.

---

## 🎯 Project Overview

This project is a complete online fragrance store with:

### **Customer Features:**
- 🛍️ Product catalog with gender filtering (Men/Women)
- 🛒 Full shopping cart system (add, update, delete items)
- 💳 Checkout process with shipping options
- 📧 Order confirmation and email notifications
- 👤 User registration and authentication
- ❓ Intelligent FAQ chatbot assistant
- 📱 Fully responsive design (mobile, tablet, desktop)

### **Admin Features:**
- 📊 Dashboard with sales statistics
- 📦 Product management (CRUD operations)
- 📋 Order management

### **FragranceBoutique/
├── index.php                 # Home page
├── catalog.php               # Product catalog
├── cart.php                  # Shopping cart
├── login.php                 # User login
├── register.php              # User registration
├── logout.php                # User logout
├── order_confirmation.php    # Order confirmation page
├── process_order.php         # Order processing
├── faq_api.php              # FAQ API endpoints
├── faq_chatbot.php          # FAQ chatbot widget
├── config.php               # Database configuration
├── auth.php                 # Authentication functions
├── database_schema.sql      # Database structure & sample data
│
├── admin/
│   ├── dashboard.php        # Admin dashboard
│   ├── products.php         # Product management
│   ├── orders.php           # Order management
│   ├── users.php            # User management
│   └── settings.php         # Store settings
│
├── assets/
│   ├── style.css            # Main stylesheet
│   ├── script.js            # Frontend JavaScript
│
├── images/
│   ├── logo.png             # Store logo
│   └── products/            # Product images
│
├── composer.json            # PHP dependencies
├── composer.lock            # Dependency lock file
├── .gitignore               # Git ignore rules
└── README.md                # This file

### **🗄️ Database Schema
The project uses MySQL with the following main tables:

Table	        Purpose
users	        Customer and admin accounts
products	    Product catalog
orders	        Customer orders
order_items	    Individual items in orders
faqs	        FAQ database (71 entries)
settings	    Store configuration

### **🤖 FAQ Chatbot System
The intelligent FAQ assistant includes:
71 Comprehensive FAQs covering:

About Us
Products & Inventory
Shopping & Cart
Payment Methods
Shipping & Delivery
Returns & Refunds
Account Management
Search & Browsing
Reviews & Ratings
Technical Support
Special Requests
Loyalty Programs
Newsletter

-- Features:
Real-time search functionality
Category filtering (5 popular + all categories)
Mobile-responsive interface
Beautiful gradient UI matching brand colors
Direct contact link for additional support

-- FAQ Categories:
À propos (About)
Produits (Products)
Achat (Shopping)
Paiement (Payment)
Livraison (Shipping)
Retours (Returns)
Compte (Account)
Recherche (Search)
Avis (Reviews)
Technique (Technical)
Demandes spéciales (Special Requests)
Fidélité (Loyalty)
Newsletter

### **🔐 User Roles & Permissions
-- Customer:
Browse products
Add to cart
Checkout
View order history
Access FAQ chatbot

-- Admin:
Full product management
Order tracking and updates
User account management
Store settings configuration
Dashboard statistics

### **🎨 Technologies Used
Technology	Purpose
PHP 8.x	Backend server-side scripting
MySQL	Database management
HTML5	Page structure
CSS3	Styling and responsiveness
JavaScript (Vanilla)	Frontend interactivity
Composer	PHP dependency management
XAMPP	Local development environment

### **📦 Features in Detail
Product Management
Browse by gender (Men/Women)
Detailed product information
Product images and pricing
Add to cart functionality

-- Shopping Cart
Add/update/remove items
Real-time quantity updates
Total price calculation
Shipping cost display

-- Checkout Process
Shipping method selection (Standard/Express)
Customer information form
Order summary
Payment at delivery option

-- Admin Dashboard
Sales statistics
Recent orders overview
Quick product/user access
Store configuration

-- FAQ Chatbot
Search across 71 FAQs
Browse by categories
Mobile-friendly interface
Contact support link

### **👥 Team
Member	                    Role	                    Contribution
EL MOTAOUKKEL SAAD	    Full-Stack Developer	    Backend, Database, Admin
JOUT OUAIL	            Full-Stack Developer	    Frontend, UI/UX, FAQ System

