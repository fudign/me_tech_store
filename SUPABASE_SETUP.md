# Настройка Supabase для Mi Tech Store

Этот документ описывает, как настроить и использовать Supabase в проекте Mi Tech Store.

## Что такое Supabase?

Supabase - это open-source альтернатива Firebase, которая предоставляет:
- PostgreSQL базу данных
- Аутентификацию
- Хранилище файлов (Storage)
- Realtime подписки
- REST API

## Шаг 1: Создание проекта в Supabase

1. Перейдите на [https://supabase.com](https://supabase.com)
2. Зарегистрируйтесь или войдите в аккаунт
3. Создайте новый проект
4. Запишите следующие данные из раздела **Project Settings > API**:
   - Project URL (например: `https://xxxxx.supabase.co`)
   - Anon/Public Key (публичный ключ)
   - Service Role Key (секретный ключ - только для сервера!)

## Шаг 2: Настройка переменных окружения

Откройте файл `.env` и обновите следующие переменные:

```env
# Подключение к PostgreSQL базе данных Supabase
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-database-password

# Supabase API настройки
SUPABASE_URL=https://xxxxx.supabase.co
SUPABASE_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-key
SUPABASE_STORAGE_BUCKET=public
```

### Где найти пароль базы данных?

1. Перейдите в **Project Settings > Database**
2. Найдите **Connection string** или **Database password**
3. Если пароль не сохранен, сбросьте его

## Шаг 3: Установка зависимостей

Установите необходимые пакеты PHP:

```bash
composer require supabase/supabase-php
composer require guzzlehttp/guzzle
```

Если используете npm для фронтенда:

```bash
npm install @supabase/supabase-js
```

## Шаг 4: Миграция базы данных

После настройки подключения выполните миграции Laravel:

```bash
php artisan migrate
```

## Использование Supabase в Laravel

### Вариант 1: Использование Facade (рекомендуется)

```php
use App\Facades\Supabase;

// Получение данных
$products = Supabase::select('products', ['select' => '*', 'limit' => 10]);

// Вставка данных
$newProduct = Supabase::insert('products', [
    'name' => 'iPhone 15',
    'price' => 999.99,
    'is_active' => true,
]);

// Обновление данных
$updated = Supabase::update('products',
    ['price' => 899.99],
    ['id' => 'eq.1']
);

// Удаление данных
$deleted = Supabase::delete('products', ['id' => 'eq.1']);
```

### Вариант 2: Dependency Injection

```php
use App\Services\SupabaseService;

class ProductController extends Controller
{
    public function __construct(
        private SupabaseService $supabase
    ) {}

    public function index()
    {
        $products = $this->supabase->select('products', [
            'select' => '*',
            'is_active' => 'eq.true',
            'order' => 'created_at.desc',
        ]);

        return view('products.index', compact('products'));
    }
}
```

### Работа с файлами (Storage)

```php
use App\Facades\Supabase;

// Загрузка файла
$file = $request->file('image');
$path = 'products/' . uniqid() . '.' . $file->extension();
$result = Supabase::uploadFile('public', $path, $file->get());

// Получение публичного URL
$url = Supabase::getPublicUrl('public', $path);

// Удаление файла
Supabase::deleteFile('public', $path);
```

## Использование PostgreSQL (основная база данных)

Laravel автоматически использует Supabase PostgreSQL, когда вы работаете с Eloquent:

```php
use App\Models\Product;

// Все стандартные операции Eloquent работают через Supabase PostgreSQL
$products = Product::where('is_active', true)->get();
$product = Product::create([...]);
$product->update([...]);
$product->delete();
```

## Row Level Security (RLS)

Для защиты данных настройте Row Level Security в Supabase:

1. Перейдите в **Authentication > Policies**
2. Создайте политики для ваших таблиц

Пример политики для таблицы `products`:

```sql
-- Все могут читать активные продукты
CREATE POLICY "Anyone can read active products"
ON products FOR SELECT
USING (is_active = true);

-- Только аутентифицированные пользователи могут изменять
CREATE POLICY "Authenticated users can update products"
ON products FOR UPDATE
USING (auth.role() = 'authenticated');
```

## Realtime подписки (опционально)

Для использования Realtime функций на фронтенде:

```javascript
import { createClient } from '@supabase/supabase-js'

const supabase = createClient(
  process.env.VITE_SUPABASE_URL,
  process.env.VITE_SUPABASE_KEY
)

// Подписка на изменения
const subscription = supabase
  .channel('products')
  .on('postgres_changes',
    { event: '*', schema: 'public', table: 'products' },
    (payload) => {
      console.log('Change received!', payload)
    }
  )
  .subscribe()
```

## Резервное копирование

Supabase автоматически создает ежедневные бэкапы. Дополнительные бэкапы можно создать в **Database > Backups**.

## Мониторинг

Отслеживайте использование в **Project Settings > Usage**:
- Database size
- Storage size
- API requests
- Bandwidth

## Полезные ссылки

- [Документация Supabase](https://supabase.com/docs)
- [Laravel + Supabase](https://supabase.com/docs/guides/getting-started/quickstarts/laravel)
- [Supabase PHP Client](https://github.com/supabase-community/supabase-php)
- [Row Level Security](https://supabase.com/docs/guides/auth/row-level-security)

## Troubleshooting

### Ошибка подключения к базе данных

Проверьте:
1. Правильность DB_HOST (должен быть `db.xxxxx.supabase.co`)
2. Правильность DB_PASSWORD
3. Доступность порта 5432

### 401 Unauthorized при API запросах

Проверьте:
1. Правильность SUPABASE_KEY
2. Включен ли RLS для таблиц
3. Настроены ли политики доступа

### Файлы не загружаются в Storage

Проверьте:
1. Существует ли bucket в Supabase Storage
2. Настроены ли права доступа к bucket
3. Используется ли SERVICE_KEY для загрузки
