# Деплой на Vercel - Инструкция

## ⚠️ ВАЖНЫЕ ОГРАНИЧЕНИЯ

Vercel не поддерживает полноценный Laravel из-за serverless архитектуры. Для продакшена рекомендуется использовать:
- **Railway.app** (поддержка полного Laravel + MySQL + Storage)
- **Heroku** (полная поддержка PHP)
- **DigitalOcean App Platform**
- **Обычный VPS** (самый надежный вариант)

## Если все равно хотите деплоить на Vercel:

### Шаг 1: Подготовка базы данных

Vercel НЕ поддерживает SQLite в продакшене. Нужна удаленная база данных:

**Вариант 1: PlanetScale (бесплатный MySQL)**
1. Зарегистрируйтесь на https://planetscale.com
2. Создайте новую базу данных
3. Получите credentials (host, username, password, database)

**Вариант 2: Supabase (бесплатный PostgreSQL)**
1. Зарегистрируйтесь на https://supabase.com
2. Создайте новый проект
3. Получите Database URL

### Шаг 2: Подготовка хранилища файлов

Vercel НЕ сохраняет загруженные файлы. Нужно использовать:

**AWS S3 или совместимые:**
1. Cloudflare R2 (бесплатно 10GB)
2. DigitalOcean Spaces
3. Backblaze B2

Установите пакет:
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

### Шаг 3: Настройка репозитория

1. Создайте репозиторий на GitHub:
```bash
git init
git add .
git commit -m "Initial commit for Vercel deployment"
git branch -M main
git remote add origin https://github.com/ваш-username/mi-tech.git
git push -u origin main
```

### Шаг 4: Деплой на Vercel

1. Зайдите на https://vercel.com и войдите через GitHub
2. Нажмите "Add New Project"
3. Импортируйте ваш репозиторий `mi-tech`
4. Настройте переменные окружения (Environment Variables):

```env
# App
APP_NAME="Xiaomi Store"
APP_ENV=production
APP_KEY=base64:ваш_app_key_из_.env
APP_DEBUG=false
APP_URL=https://your-project.vercel.app

# Database - PlanetScale
DB_CONNECTION=mysql
DB_HOST=ваш-planetscale-host.psdb.cloud
DB_PORT=3306
DB_DATABASE=ваше-имя-базы
DB_USERNAME=ваш-username
DB_PASSWORD=ваш-password

# Session & Cache (используйте database driver)
SESSION_DRIVER=database
CACHE_DRIVER=database

# File Storage - S3/R2
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=ваш-ключ
AWS_SECRET_ACCESS_KEY=ваш-секретный-ключ
AWS_DEFAULT_REGION=auto
AWS_BUCKET=ваш-бакет
AWS_URL=https://ваш-бакет.r2.cloudflarestorage.com
AWS_ENDPOINT=https://ваш-account-id.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

5. Нажмите "Deploy"

### Шаг 5: После первого деплоя

После первого деплоя нужно запустить миграции:

1. Установите Vercel CLI:
```bash
npm i -g vercel
```

2. Войдите:
```bash
vercel login
```

3. Запустите миграции (это нужно сделать ВРУЧНУЮ через вашу базу данных):
- Экспортируйте SQL из локальной SQLite
- Импортируйте в PlanetScale/Supabase через их веб-интерфейс

### Что НЕ БУДЕТ работать на Vercel:

❌ Загрузка изображений товаров (нужен S3)
❌ SQLite база данных
❌ Сессии в файлах
❌ Кеш в файлах
❌ Очереди (queues)
❌ Крон задачи (scheduled tasks)
❌ Длительные запросы (timeout 10 секунд)

## Рекомендация: Используйте Railway.app

Railway.app намного лучше подходит для Laravel:

1. Зайдите на https://railway.app
2. Подключите GitHub репозиторий
3. Railway автоматически:
   - Создаст MySQL базу данных
   - Настроит PHP окружение
   - Запустит миграции
   - Настроит хранилище

**Стоимость:** ~$5/месяц (включает БД + хостинг + storage)

## Нужна помощь?

Если возникли проблемы:
1. Проверьте логи в Vercel Dashboard → Deployments → Logs
2. Убедитесь, что все Environment Variables настроены
3. Проверьте подключение к базе данных

**Важно:** Для полноценного интернет-магазина рекомендуется использовать VPS или Railway, а не Vercel.
