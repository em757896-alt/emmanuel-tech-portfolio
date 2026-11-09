-- Elevate Retail POS — MySQL/MariaDB schema
-- Import into the database created in InfinityFree phpMyAdmin.
-- This schema is intentionally separate from the retired student-management schema.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS audit_logs, sale_items, sales, purchase_items, purchases, stock_movements, products, categories, suppliers, users, store_settings;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(120) NOT NULL,
 username VARCHAR(60) NOT NULL UNIQUE,
 email VARCHAR(160) NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','manager','cashier') NOT NULL DEFAULT 'cashier',
 active TINYINT(1) NOT NULL DEFAULT 1,
 last_login DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL UNIQUE,
 description VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE suppliers (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(140) NOT NULL,
 contact_name VARCHAR(120) NULL,
 phone VARCHAR(40) NULL,
 email VARCHAR(160) NULL,
 address VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 sku VARCHAR(64) NOT NULL UNIQUE,
 barcode VARCHAR(100) NULL UNIQUE,
 name VARCHAR(160) NOT NULL,
 category_id INT UNSIGNED NULL,
 supplier_id INT UNSIGNED NULL,
 description TEXT NULL,
 cost_price DECIMAL(12,2) NOT NULL DEFAULT 0,
 selling_price DECIMAL(12,2) NOT NULL DEFAULT 0,
 quantity INT NOT NULL DEFAULT 0,
 reorder_level INT NOT NULL DEFAULT 5,
 unit VARCHAR(24) NOT NULL DEFAULT 'pcs',
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_product_category FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
 CONSTRAINT fk_product_supplier FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
 INDEX idx_product_name(name), INDEX idx_product_stock(quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stock_movements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 product_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NULL,
 movement_type ENUM('opening','purchase','sale','adjustment','return') NOT NULL,
 quantity_change INT NOT NULL,
 quantity_after INT NOT NULL,
 reference VARCHAR(80) NULL,
 notes VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(product_id) REFERENCES products(id),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_stock_product_date(product_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE purchases (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 supplier_id INT UNSIGNED NULL,
 reference_no VARCHAR(80) NOT NULL UNIQUE,
 status ENUM('received','cancelled') NOT NULL DEFAULT 'received',
 subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
 notes TEXT NULL,
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE purchase_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 purchase_id BIGINT UNSIGNED NOT NULL,
 product_id INT UNSIGNED NOT NULL,
 quantity INT UNSIGNED NOT NULL,
 unit_cost DECIMAL(12,2) NOT NULL,
 line_total DECIMAL(12,2) NOT NULL,
 FOREIGN KEY(purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
 FOREIGN KEY(product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sales (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 receipt_no VARCHAR(40) NOT NULL UNIQUE,
 cashier_id INT UNSIGNED NULL,
 subtotal DECIMAL(12,2) NOT NULL,
 discount DECIMAL(12,2) NOT NULL DEFAULT 0,
 tax DECIMAL(12,2) NOT NULL DEFAULT 0,
 total DECIMAL(12,2) NOT NULL,
 amount_paid DECIMAL(12,2) NOT NULL,
 change_due DECIMAL(12,2) NOT NULL DEFAULT 0,
 payment_method ENUM('cash','card','mobile_money','other') NOT NULL DEFAULT 'cash',
 status ENUM('completed','voided','refunded') NOT NULL DEFAULT 'completed',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(cashier_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_sales_date(created_at), INDEX idx_sales_status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sale_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 sale_id BIGINT UNSIGNED NOT NULL,
 product_id INT UNSIGNED NOT NULL,
 product_name VARCHAR(160) NOT NULL,
 sku VARCHAR(64) NOT NULL,
 quantity INT UNSIGNED NOT NULL,
 unit_price DECIMAL(12,2) NOT NULL,
 unit_cost DECIMAL(12,2) NOT NULL,
 line_total DECIMAL(12,2) NOT NULL,
 FOREIGN KEY(sale_id) REFERENCES sales(id) ON DELETE CASCADE,
 FOREIGN KEY(product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE store_settings (
 setting_key VARCHAR(80) PRIMARY KEY,
 setting_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO store_settings(setting_key,setting_value) VALUES
('store_name','Elevate Retail Demo Store'),('currency','KES'),('tax_rate','0');

CREATE TABLE audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NULL,
 action VARCHAR(80) NOT NULL,
 entity VARCHAR(80) NOT NULL,
 entity_id BIGINT NULL,
 details TEXT NULL,
 ip_address VARCHAR(45) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_audit_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo users must be created through the secure setup page, not seeded with a public password.
