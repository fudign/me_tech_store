-- Инструкции по настройке базы данных для Mi Tech Store
-- Выполните эти команды в Supabase SQL Editor (https://supabase.com/dashboard → SQL Editor)

-- 1. Создание администратора (после регистрации на сайте)
-- Замените 'ваш@email.com' на ваш реальный email
UPDATE users
SET is_admin = true
WHERE email = 'ваш@email.com';

-- 2. Проверка таблиц
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public'
ORDER BY table_name;

-- 3. Проверка наличия пользователей
SELECT id, name, email, is_admin, created_at
FROM users
ORDER BY created_at DESC
LIMIT 10;

-- 4. Проверка категорий
SELECT id, name, slug, created_at
FROM categories
ORDER BY created_at DESC;

-- 5. Проверка товаров
SELECT id, name, slug, price, created_at
FROM products
ORDER BY created_at DESC
LIMIT 10;

-- 6. Очистка тестовых данных (если нужно)
-- ВНИМАНИЕ: Эти команды удалят ВСЕ данные из таблиц!
-- Раскомментируйте только если точно уверены
-- TRUNCATE TABLE order_items CASCADE;
-- TRUNCATE TABLE orders CASCADE;
-- TRUNCATE TABLE products CASCADE;
-- TRUNCATE TABLE categories CASCADE;
-- TRUNCATE TABLE users CASCADE;

-- 7. Создание тестового администратора напрямую (альтернатива)
-- Используйте только если не можете зарегистрироваться через сайт
-- Пароль: password (обязательно смените после первого входа!)
-- INSERT INTO users (name, email, password, is_admin, email_verified_at, created_at, updated_at)
-- VALUES (
--     'Admin',
--     'admin@mitech.kg',
--     '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lE3Yb5vCqfKe', -- пароль: password
--     true,
--     NOW(),
--     NOW(),
--     NOW()
-- );

-- 8. Проверка настроек базы данных
SELECT
    current_database() as database_name,
    current_user as user_name,
    inet_server_addr() as server_ip,
    version() as postgres_version;

-- 9. Резервное копирование данных (экспорт)
-- Эту команду выполняйте локально через pg_dump
-- pg_dump -h db.wtevayfmmvrbtevxsbwh.supabase.co -U postgres -d postgres -f backup.sql

-- 10. Восстановление данных (импорт)
-- psql -h db.wtevayfmmvrbtevxsbwh.supabase.co -U postgres -d postgres -f backup.sql
