# 🚀 Деплой Mi Tech Store на Vercel

Пошаговая инструкция по развертыванию Laravel проекта на Vercel.

## 📋 Предварительные требования

- Аккаунт на [Vercel](https://vercel.com)
- Аккаунт на GitHub/GitLab/Bitbucket
- База данных (рекомендуется: PlanetScale, Supabase или Railway)
- Cloudinary аккаунт для хранения изображений

## 🔧 Шаг 1: Подготовка проекта

Все необходимые файлы уже созданы:
- ✅ `vercel.json` - конфигурация Vercel
- ✅ `api/index.php` - точка входа для serverless функций
- ✅ `.vercelignore` - файлы для игнорирования

## 🗄️ Шаг 2: Настройка базы данных

### Вариант A: PlanetScale (рекомендуется для MySQL)

1. Зарегистрируйтесь на [PlanetScale](https://planetscale.com/)
2. Создайте новую базу данных
3. Получите connection string (формат: `mysql://user:pass@host/database?sslaccept=strict`)

### Вариант B: Supabase (PostgreSQL)

1. Зарегистрируйтесь на [Supabase](https://supabase.com/)
2. Создайте новый проект
3. Получите connection string из Settings → Database

### Вариант C: Railway (MySQL/PostgreSQL)

1. Зарегистрируйтесь на [Railway](https://railway.app/)
2. Создайте MySQL или PostgreSQL сервис
3. Получите connection string

## 📤 Шаг 3: Загрузка на GitHub

```bash
# Инициализируйте git репозиторий (если еще не сделано)
git init

# Добавьте все файлы
git add .

# Создайте коммит
git commit -m "Initial commit for Vercel deployment"

# Добавьте remote репозиторий
git remote add origin https://github.com/ваш-username/mi-tech-store.git

# Отправьте на GitHub
git push -u origin main
```

## 🌐 Шаг 4: Деплой на Vercel

### Через Vercel Dashboard:

1. Войдите на [Vercel](https://vercel.com)
2. Нажмите **"Add New"** → **"Project"**
3. Импортируйте ваш GitHub репозиторий
4. Настройте следующие параметры:

**Framework Preset:** Other
**Root Directory:** ./
**Build Command:** `composer install --no-dev --optimize-autoloader && npm install && npm run build`
**Output Directory:** public

### Через Vercel CLI:

```bash
# Установите Vercel CLI
npm i -g vercel

# Войдите в Vercel
vercel login

# Деплой проекта
vercel

# Для production деплоя
vercel --prod
```

## ⚙️ Шаг 5: Environment Variables

В Vercel Dashboard → Settings → Environment Variables, добавьте:

### Основные переменные:
```env
APP_NAME="Mi Tech Store"
APP_ENV=production
APP_KEY=base64:ВАШ_КЛЮЧ_ИЗ_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://ваш-домен.vercel.app

LOG_CHANNEL=stderr
LOG_LEVEL=error
```

### База данных:
```env
DB_CONNECTION=mysql
DB_HOST=ваш-хост
DB_PORT=3306
DB_DATABASE=ваша-база
DB_USERNAME=пользователь
DB_PASSWORD=пароль
```

### Cloudinary (для изображений):
```env
CLOUDINARY_CLOUD_NAME=ваше-имя
CLOUDINARY_API_KEY=ваш-ключ
CLOUDINARY_API_SECRET=ваш-секрет
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
```

### Session и Cache:
```env
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
```

### Почта (опционально):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=ваш-username
MAIL_PASSWORD=ваш-пароль
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 🔑 Шаг 6: Генерация APP_KEY

```bash
# Локально выполните
php artisan key:generate --show

# Скопируйте полученный ключ (начинается с base64:)
# И добавьте его в Environment Variables на Vercel
```

## 🗃️ Шаг 7: Миграция базы данных

После успешного деплоя, выполните миграции:

### Вариант A: Через локальное подключение
```bash
# Установите переменные окружения локально из Vercel
# Затем выполните:
php artisan migrate --force
php artisan db:seed --force
```

### Вариант B: Через Vercel CLI
```bash
vercel env pull .env.production
php artisan migrate --force --env=production
```

## 📁 Шаг 8: Настройка хранилища файлов

Так как Vercel использует read-only файловую систему, используйте Cloudinary для всех загрузок:

1. Убедитесь, что все изображения загружаются через Cloudinary
2. В админ-панели настройте Cloudinary credentials
3. Протестируйте загрузку изображений

## 🔍 Шаг 9: Проверка работы

После деплоя проверьте:

- ✅ Главная страница загружается
- ✅ Страницы товаров отображаются
- ✅ Корзина работает
- ✅ Поиск функционирует
- ✅ Админ-панель доступна
- ✅ Загрузка изображений работает

## 🐛 Troubleshooting

### Ошибка 500
- Проверьте логи: `vercel logs`
- Убедитесь, что APP_KEY установлен
- Проверьте подключение к БД

### Изображения не загружаются
- Убедитесь, что Cloudinary настроен правильно
- Проверьте CLOUDINARY_URL в environment variables

### Session не работает
- Установите `SESSION_DRIVER=cookie`
- Убедитесь, что `SESSION_SECURE_COOKIE=true` для HTTPS

### База данных не подключается
- Проверьте все DB_* переменные
- Убедитесь, что firewall БД разрешает подключения от Vercel

## 🔄 Автоматические деплои

После настройки, каждый push в main ветку будет автоматически деплоиться на Vercel!

```bash
# Внесите изменения
git add .
git commit -m "Update feature"
git push origin main

# Vercel автоматически задеплоит изменения
```

## 📊 Мониторинг

- **Логи:** https://vercel.com/your-project/logs
- **Analytics:** https://vercel.com/your-project/analytics
- **Speed Insights:** Включите в настройках проекта

## 🎉 Готово!

Ваш магазин теперь доступен на Vercel!

### Полезные команды:

```bash
# Просмотр логов
vercel logs

# Откат к предыдущей версии
vercel rollback

# Просмотр всех деплоев
vercel ls

# Удалить проект
vercel remove
```

## 📚 Дополнительные ресурсы

- [Vercel Documentation](https://vercel.com/docs)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [PlanetScale MySQL](https://planetscale.com/docs)
- [Cloudinary PHP SDK](https://cloudinary.com/documentation/php_integration)

---

🚨 **Важно:** Никогда не коммитьте `.env` файл с реальными credentials!
