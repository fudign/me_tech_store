-- Supabase PostgreSQL Schema for MeTech Store
-- Run this script in Supabase SQL Editor

-- Drop tables if exist (be careful in production!)
DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS orders CASCADE;
DROP TABLE IF EXISTS reviews CASCADE;
DROP TABLE IF EXISTS coupons CASCADE;
DROP TABLE IF EXISTS product_attributes CASCADE;
DROP TABLE IF EXISTS category_product CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS settings CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Users table
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    parent_id BIGINT REFERENCES categories(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    images TEXT,
    stock INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    availability_status VARCHAR(50) DEFAULT 'in_stock',
    sku VARCHAR(100),
    brand VARCHAR(100),
    view_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Category-Product pivot table
CREATE TABLE category_product (
    category_id BIGINT NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    PRIMARY KEY (category_id, product_id)
);

-- Product attributes table
CREATE TABLE product_attributes (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    key VARCHAR(100) NOT NULL,
    value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id BIGSERIAL PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_email VARCHAR(255),
    delivery_address TEXT NOT NULL,
    notes TEXT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order items table
CREATE TABLE order_items (
    id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id),
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10, 2) NOT NULL,
    quantity INTEGER NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reviews table
CREATE TABLE reviews (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Coupons table
CREATE TABLE coupons (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type VARCHAR(20) NOT NULL CHECK (type IN ('fixed', 'percentage')),
    value DECIMAL(10, 2) NOT NULL,
    min_order_amount DECIMAL(10, 2),
    max_discount_amount DECIMAL(10, 2),
    usage_limit INTEGER,
    used_count INTEGER DEFAULT 0,
    starts_at TIMESTAMP,
    expires_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX idx_products_slug ON products(slug);
CREATE INDEX idx_products_is_active ON products(is_active);
CREATE INDEX idx_products_view_count ON products(view_count DESC);
CREATE INDEX idx_categories_slug ON categories(slug);
CREATE INDEX idx_orders_order_number ON orders(order_number);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_reviews_product_id ON reviews(product_id);
CREATE INDEX idx_reviews_is_approved ON reviews(is_approved);

-- Insert demo data
-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Смартфоны', 'smartfony', 'Современные смартфоны всех брендов'),
('Ноутбуки', 'noutbuki', 'Ноутбуки для работы и развлечений'),
('Планшеты', 'planshety', 'Планшеты и электронные книги'),
('Аксессуары', 'aksessuary', 'Аксессуары для техники');

-- Products
INSERT INTO products (name, slug, description, price, stock, is_active, availability_status, brand, view_count) VALUES
('iPhone 15 Pro', 'iphone-15-pro', 'Новейший iPhone 15 Pro с чипом A17 Pro и титановым корпусом. Камера 48MP, ProMotion дисплей 120Hz.', 85000, 10, TRUE, 'in_stock', 'Apple', 0),
('Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24', 'Флагманский смартфон Samsung с S Pen. Snapdragon 8 Gen 3, камера 200MP.', 75000, 8, TRUE, 'in_stock', 'Samsung', 0),
('MacBook Pro 14', 'macbook-pro-14', 'Ноутбук Apple MacBook Pro 14 дюймов с чипом M3 Pro. 18GB RAM, 512GB SSD.', 150000, 5, TRUE, 'in_stock', 'Apple', 0),
('iPad Air', 'ipad-air', 'Планшет Apple iPad Air с чипом M1. Дисплей 10.9 дюймов, поддержка Apple Pencil.', 45000, 12, TRUE, 'in_stock', 'Apple', 0),
('AirPods Pro 2', 'airpods-pro-2', 'Беспроводные наушники с активным шумоподавлением. USB-C зарядка.', 18000, 20, TRUE, 'in_stock', 'Apple', 0);

-- Link products to categories
INSERT INTO category_product (category_id, product_id) VALUES
(1, 1), -- iPhone -> Смартфоны
(1, 2), -- Samsung -> Смартфоны
(2, 3), -- MacBook -> Ноутбуки
(3, 4), -- iPad -> Планшеты
(4, 5); -- AirPods -> Аксессуары

-- Settings
INSERT INTO settings (key, value) VALUES
('site_name', 'MeTech Store'),
('site_description', 'Интернет-магазин техники в Бишкеке'),
('site_keywords', 'техника, электроника, смартфоны, ноутбуки, Бишкек'),
('contact_phone', '+996 XXX XXX XXX'),
('contact_whatsapp', '+996 XXX XXX XXX'),
('contact_email', 'info@metech.kg'),
('contact_address', 'г. Бишкек, ул. Пример, 123'),
('map_lat', '42.8746'),
('map_lng', '74.5698'),
('instagram_url', 'https://instagram.com/metech'),
('facebook_url', 'https://facebook.com/metech'),
('working_hours', 'Пн-Пт: 9:00-18:00, Сб-Вс: 10:00-16:00');

-- Create admin user (password: admin123)
INSERT INTO users (name, email, password, is_admin) VALUES
('Admin', 'admin@metech.kg', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5Tl0rPe2bv6/K', TRUE);

-- Enable Row Level Security (optional, for public access)
ALTER TABLE products ENABLE ROW LEVEL SECURITY;
ALTER TABLE categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE settings ENABLE ROW LEVEL SECURITY;

-- Create policies for public read access
CREATE POLICY "Public can read active products"
ON products FOR SELECT
USING (is_active = TRUE);

CREATE POLICY "Public can read categories"
ON categories FOR SELECT
USING (TRUE);

CREATE POLICY "Public can read settings"
ON settings FOR SELECT
USING (TRUE);

-- Grant full access to authenticated users (admins)
CREATE POLICY "Authenticated users can manage products"
ON products FOR ALL
USING (auth.role() = 'authenticated');

CREATE POLICY "Authenticated users can manage categories"
ON categories FOR ALL
USING (auth.role() = 'authenticated');

-- Note: For admin panel, you'll need to disable RLS or use service role key
-- To disable RLS for admin operations, use service_role key in Laravel
