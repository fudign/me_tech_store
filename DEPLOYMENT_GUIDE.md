# Инструкция по деплою Mi Tech Store на Vercel

## Шаг 1: Подготовка базы данных Supabase

1. **Войдите в ваш проект Supabase**: https://supabase.com/dashboard
2. **Получите данные подключения**:
   - Перейдите в Settings → Database
   - Скопируйте:
     - Host: `db.wtevayfmmvrbtevxsbwh.supabase.co`
     - Database password
   - Перейдите в Settings → API
   - Скопируйте:
     - Project URL: `https://wtevayfmmvrbtevxsbwh.supabase.co`
     - anon/public key
     - service_role key

3. **Выполните миграции** (локально, если composer установлен):
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

## Шаг 2: Генерация APP_KEY

Если у вас локально установлен PHP и composer:
```bash
php artisan key:generate --show
```

Скопируйте полученный ключ (начинается с `base64:`).

Или используйте онлайн генератор:
- Перейдите на https://generate-random.org/laravel-key-generator
- Скопируйте сгенерированный ключ

## Шаг 3: Подготовка проекта к деплою

1. **Соберите frontend assets** (уже сделано):
   ```bash
   npm run build
   ```

2. **Закоммитьте изменения в Git**:
   ```bash
   git add .
   git commit -m "feat: prepare for production deployment"
   git push
   ```

## Шаг 4: Деплой на Vercel

### Вариант А: Через веб-интерфейс Vercel (рекомендуется)

1. **Перейдите на https://vercel.com** и войдите в аккаунт
2. **Нажмите "Add New Project"**
3. **Импортируйте ваш Git репозиторий**:
   - Выберите провайдер (GitHub/GitLab/Bitbucket)
   - Выберите репозиторий mi_tech
4. **Настройте проект**:
   - Framework Preset: Other
   - Build Command: `npm run build`
   - Output Directory: `public`
   - Install Command: `npm install`

5. **Добавьте Environment Variables** (Settings → Environment Variables):

   **Обязательные переменные:**
   ```
   APP_NAME=Mi Tech Store
   APP_ENV=production
   APP_KEY=base64:ВАМІШ_СГЕНЕРИРОВАННЫЙ_КЛЮЧ
   APP_DEBUG=false
   APP_URL=https://ваш-домен.vercel.app
   APP_TIMEZONE=Asia/Bishkek

   APP_LOCALE=ru
   APP_FALLBACK_LOCALE=ru

   LOG_CHANNEL=stack
   LOG_LEVEL=error

   DB_CONNECTION=pgsql
   DB_HOST=db.wtevayfmmvrbtevxsbwh.supabase.co
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=postgres
   DB_PASSWORD=ВАШ_ПАРОЛЬ_ОТ_SUPABASE

   SUPABASE_URL=https://wtevayfmmvrbtevxsbwh.supabase.co
   SUPABASE_KEY=ВАШ_ANON_KEY
   SUPABASE_SERVICE_KEY=ВАШ_SERVICE_KEY

   SESSION_DRIVER=cookie
   SESSION_LIFETIME=120
   SESSION_ENCRYPT=true
   SESSION_SECURE_COOKIE=true
   SESSION_HTTP_ONLY=true
   SESSION_SAME_SITE=lax

   CACHE_STORE=file
   QUEUE_CONNECTION=database
   FILESYSTEM_DISK=public

   BROADCAST_CONNECTION=log
   MAIL_MAILER=log
   MAIL_FROM_ADDRESS=noreply@mitech.kg
   ```

6. **Нажмите "Deploy"**

### Вариант Б: Через Vercel CLI

1. **Установите Vercel CLI**:
   ```bash
   npm i -g vercel
   ```

2. **Войдите в аккаунт**:
   ```bash
   vercel login
   ```

3. **Деплой проекта**:
   ```bash
   vercel
   ```

4. **Добавьте переменные окружения**:
   ```bash
   vercel env add APP_KEY
   vercel env add DB_PASSWORD
   vercel env add SUPABASE_KEY
   vercel env add SUPABASE_SERVICE_KEY
   # и т.д. для всех переменных
   ```

5. **Продакшн деплой**:
   ```bash
   vercel --prod
   ```

## Шаг 5: После деплоя

1. **Обновите APP_URL**:
   - После первого деплоя вы получите URL типа `https://mi-tech-xxx.vercel.app`
   - Обновите переменную `APP_URL` в настройках Vercel на этот URL
   - Redeploy проект

2. **Настройте кастомный домен** (опционально):
   - Перейдите в Settings → Domains
   - Добавьте свой домен
   - Настройте DNS записи как указано в Vercel

3. **Проверьте работу сайта**:
   - Откройте ваш URL
   - Проверьте регистрацию/вход
   - Проверьте добавление товаров в корзину
   - Проверьте оформление заказа

4. **Выполните миграции** (если еще не сделали):
   - Это можно сделать через Supabase SQL Editor
   - Или подключиться к базе локально и выполнить `php artisan migrate --force`

## Шаг 6: Настройка административной панели

1. **Создайте администратора**:
   - Зарегистрируйтесь на сайте
   - Подключитесь к базе данных Supabase
   - Выполните SQL:
   ```sql
   UPDATE users SET is_admin = true WHERE email = 'ваш@email.com';
   ```

2. **Войдите в админ-панель**:
   - Перейдите на `/admin`
   - Начните добавлять товары, категории и т.д.

## Возможные проблемы и решения

### Проблема: 500 Internal Server Error
**Решение**:
- Проверьте что все environment переменные установлены корректно
- Проверьте логи в Vercel Dashboard → Functions → Logs

### Проблема: Не работает соединение с базой данных
**Решение**:
- Проверьте что пароль от Supabase указан правильно
- Убедитесь что IP Vercel не заблокирован в Supabase (обычно не блокируется)

### Проблема: Ошибка "No application encryption key has been specified"
**Решение**:
- Сгенерируйте новый APP_KEY
- Добавьте его в environment variables на Vercel
- Redeploy проект

### Проблема: CSS/JS не загружаются
**Решение**:
- Убедитесь что `npm run build` был выполнен
- Проверьте что файлы в `public/build/` закоммичены в Git
- Redeploy проект

## Альтернативные платформы для деплоя

Если Vercel не подходит, можно использовать:

1. **Railway.app** - поддерживает PHP + PostgreSQL
2. **Heroku** - классический вариант для PHP
3. **DigitalOcean App Platform** - более дорогой, но стабильный
4. **AWS Elastic Beanstalk** - для больших проектов
5. **Собственный VPS** (DigitalOcean, Vultr, Hetzner) - полный контроль

## Мониторинг и обслуживание

1. **Мониторинг ошибок**:
   - Проверяйте логи в Vercel Dashboard
   - Настройте уведомления в Supabase

2. **Резервное копирование**:
   - Supabase автоматически создает бэкапы
   - Дополнительно можно настроить регулярный экспорт данных

3. **Обновления**:
   - При обновлении кода просто пушьте в Git
   - Vercel автоматически задеплоит изменения

## Контакты поддержки

- Vercel Docs: https://vercel.com/docs
- Supabase Docs: https://supabase.com/docs
- Laravel Docs: https://laravel.com/docs

---

Готово! Ваш сайт должен быть доступен по адресу из Vercel.
