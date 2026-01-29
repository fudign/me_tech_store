# Настройка Supabase Storage для загрузки изображений

## Проблема

На Vercel файловая система **read-only**, поэтому нельзя загружать файлы локально в `storage/public`. Все изображения товаров должны загружаться в облачное хранилище.

## Решение

Мы реализовали **автоматическое переключение** между локальным и облачным хранилищем:
- **Локальная разработка** → изображения сохраняются в `storage/public`
- **Vercel (продакшен)** → изображения загружаются в Supabase Storage

## Настройка Supabase Storage

### 1. Создайте Storage Bucket в Supabase

1. Откройте [https://supabase.com](https://supabase.com) и войдите в свой проект
2. Перейдите в раздел **Storage** в боковом меню
3. Нажмите **"New bucket"**
4. Создайте новый bucket:
   - **Name**: `products`
   - **Public bucket**: ✅ Включите (чтобы изображения были доступны публично)
   - Нажмите **"Create bucket"**

### 2. Настройте политики доступа (Policies)

После создания bucket'а нужно настроить политики доступа:

1. Кликните на bucket `products`
2. Перейдите на вкладку **"Policies"**
3. Нажмите **"New Policy"**

#### Политика для чтения (публичный доступ)

Создайте политику для публичного чтения изображений:

```sql
-- Policy name: Public Read Access
-- Operation: SELECT
-- Target roles: public

CREATE POLICY "Public Read Access"
ON storage.objects FOR SELECT
USING (bucket_id = 'products');
```

#### Политика для загрузки (для авторизованных пользователей)

Если вы хотите, чтобы только авторизованные пользователи могли загружать файлы:

```sql
-- Policy name: Authenticated Upload
-- Operation: INSERT
-- Target roles: authenticated

CREATE POLICY "Authenticated Upload"
ON storage.objects FOR INSERT
WITH CHECK (bucket_id = 'products' AND auth.role() = 'authenticated');
```

**ИЛИ** если вы хотите загружать через Service Key (наш случай):

```sql
-- Policy name: Service Role Upload
-- Operation: INSERT
-- Target roles: service_role

CREATE POLICY "Service Role Upload"
ON storage.objects FOR INSERT
WITH CHECK (bucket_id = 'products');
```

#### Политика для удаления

```sql
-- Policy name: Service Role Delete
-- Operation: DELETE
-- Target roles: service_role

CREATE POLICY "Service Role Delete"
ON storage.objects FOR DELETE
USING (bucket_id = 'products');
```

### 3. Получите ключи доступа

1. Перейдите в **Settings** → **API**
2. Скопируйте следующие значения:
   - **URL**: Ваш Supabase URL (например: `https://xxx.supabase.co`)
   - **anon/public key**: Публичный ключ
   - **service_role key**: Секретный ключ (⚠️ Не публикуйте его!)

### 4. Добавьте переменные в Vercel

Перейдите в Vercel Dashboard → Settings → Environment Variables и добавьте:

```env
SUPABASE_URL=https://wtevayfmmvrbtevxsbwh.supabase.co
SUPABASE_KEY=ваш_anon_key
SUPABASE_SERVICE_KEY=ваш_service_role_key
SUPABASE_STORAGE_BUCKET=products
```

**⚠️ Важно:** Используйте `service_role_key` для загрузки файлов, так как он обходит RLS (Row Level Security) политики.

### 5. Redeploy проекта

После добавления переменных окружения:
1. Перейдите в Deployments
2. Нажмите на последний деплой
3. Нажмите **"Redeploy"**

## Как это работает

### ImageUploadService

Создан сервис `App\Services\ImageUploadService`, который автоматически определяет окружение:

```php
// Автоматически выбирает способ загрузки
$imageUploadService->upload($file, 'products');

// В разработке: сохраняет в storage/public/products/
// На Vercel: загружает в Supabase Storage
```

### Обновлённый ProductController

Контроллер теперь использует `ImageUploadService` вместо прямого обращения к `Storage`:

```php
// Старый код (не работает на Vercel)
Storage::disk('public')->put('products', $image);

// Новый код (работает везде)
$imageUploadService->upload($image, 'products');
```

## Проверка работы

### Локально (разработка):
1. Загрузите изображение через админку
2. Проверьте, что файл появился в `storage/app/public/products/`
3. Изображение доступно по адресу: `http://localhost/storage/products/filename.jpg`

### На Vercel (продакшен):
1. Загрузите изображение через админку
2. Проверьте в Supabase Storage → Bucket `products`
3. Изображение доступно по адресу: `https://xxx.supabase.co/storage/v1/object/public/products/products/filename.jpg`

## Устранение проблем

### ❌ Ошибка "Failed to upload to Supabase Storage"

**Причины:**
- Неверные ключи доступа
- Bucket не создан или имя неправильное
- Политики доступа не настроены

**Решение:**
1. Проверьте переменные `SUPABASE_URL`, `SUPABASE_SERVICE_KEY`
2. Убедитесь, что bucket `products` существует и публичный
3. Проверьте политики доступа в Supabase

### ❌ Изображения не отображаются

**Причина:** Bucket не публичный

**Решение:**
1. Откройте Supabase → Storage → `products`
2. Включите **"Public bucket"**
3. Добавьте политику для публичного чтения (см. выше)

### ❌ 403 Forbidden при загрузке

**Причина:** Нет прав на загрузку

**Решение:**
- Используйте `SUPABASE_SERVICE_KEY` вместо `SUPABASE_KEY`
- Проверьте политику "Service Role Upload"

## Альтернативы

Если не хотите использовать Supabase Storage, можете использовать:
- **AWS S3** (с пакетом `league/flysystem-aws-s3-v3`)
- **Cloudinary** (специально для изображений)
- **DigitalOcean Spaces** (S3-совместимое хранилище)

Для этого обновите метод `uploadToSupabase()` в `ImageUploadService.php`.

## Полезные ссылки

- [Supabase Storage Documentation](https://supabase.com/docs/guides/storage)
- [Supabase Storage API](https://supabase.com/docs/reference/javascript/storage-from-upload)
- [Laravel Filesystem](https://laravel.com/docs/filesystem)
