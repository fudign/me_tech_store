# Быстрый старт - Деплой за 10 минут

## Что вам понадобится:
- [ ] Аккаунт на Vercel (https://vercel.com)
- [ ] Аккаунт на GitHub/GitLab (для Git репозитория)
- [ ] Аккаунт на Supabase (уже есть: wtevayfmmvrbtevxsbwh.supabase.co)

## Шаги для запуска:

### 1. Получите ключи Supabase (2 минуты)
Зайдите на https://supabase.com/dashboard/project/wtevayfmmvrbtevxsbwh

**Settings → Database:**
- [ ] Скопируйте пароль БД

**Settings → API:**
- [ ] Скопируйте `anon public` ключ
- [ ] Скопируйте `service_role` ключ (secret)

### 2. Сгенерируйте APP_KEY (1 минута)
Откройте: https://generate-random.org/laravel-key-generator
- [ ] Скопируйте сгенерированный ключ (начинается с `base64:`)

### 3. Запушьте код в Git (2 минуты)
```bash
git add .
git commit -m "feat: ready for production deployment"
git push
```

### 4. Деплой на Vercel (5 минут)

1. Откройте https://vercel.com/new
2. Выберите ваш репозиторий mi_tech
3. В настройках укажите:
   - Build Command: `npm run build`
   - Output Directory: `public`
   - Install Command: `npm install`

4. Добавьте переменные окружения (Environment Variables):

```
APP_NAME=Mi Tech Store
APP_ENV=production
APP_KEY=ВАШ_КЛЮЧ_ИЗ_ШАГА_2
APP_DEBUG=false
APP_URL=https://mi-tech.vercel.app
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
DB_PASSWORD=ВАШ_ПАРОЛЬ_ИЗ_ШАГА_1

SUPABASE_URL=https://wtevayfmmvrbtevxsbwh.supabase.co
SUPABASE_KEY=ВАШ_ANON_KEY_ИЗ_ШАГА_1
SUPABASE_SERVICE_KEY=ВАШ_SERVICE_KEY_ИЗ_ШАГА_1

SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

5. Нажмите **Deploy**

### 5. После деплоя

1. **Скопируйте URL вашего сайта** (например: `https://mi-tech-xyz.vercel.app`)

2. **Обновите APP_URL**:
   - Вернитесь в Vercel → Settings → Environment Variables
   - Измените `APP_URL` на ваш реальный URL
   - Нажмите **Redeploy**

3. **Создайте админа**:
   - Зайдите на ваш сайт
   - Зарегистрируйтесь
   - Откройте Supabase → SQL Editor
   - Выполните:
   ```sql
   UPDATE users SET is_admin = true WHERE email = 'ваш@email.com';
   ```

4. **Войдите в админ-панель**:
   - Перейдите на `/admin`
   - Добавьте категории и товары

## Готово!

Ваш магазин работает по адресу из Vercel!

## Что дальше?

- Добавьте свой домен в Vercel (Settings → Domains)
- Настройте email уведомления (MAIL_MAILER)
- Добавьте товары и категории через админ-панель
- Настройте WhatsApp интеграцию для заказов

## Нужна помощь?

Смотрите полную инструкцию в `DEPLOYMENT_GUIDE.md`

## Важные ссылки:

- Ваш сайт: https://mi-tech-xyz.vercel.app (будет после деплоя)
- Админ-панель: https://mi-tech-xyz.vercel.app/admin
- Supabase Dashboard: https://supabase.com/dashboard/project/wtevayfmmvrbtevxsbwh
- Vercel Dashboard: https://vercel.com/dashboard
