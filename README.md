# Travel-Hub — Каталог туров

Веб-приложение для управления каталогом туров с публичной частью и админ-панелью.

## Возможности

### Публичная часть
- **Главная страница** — популярные направления и список туров
- **Каталог туров** — фильтрация по категории, количеству дней и цене
- **Страница тура** — галерея изображений, описание, карта маршрута (Яндекс Карты)

### Админ-панель
- **Авторизация** — вход для администраторов
- **Управление турами** — создание, редактирование, удаление
  - Название, описание, категория
  - Количество дней
  - Даты и цены
  - Включено/не включено
  - Особенности (highlight)
  - Изображения
  - Точки маршрута на карте (клик для добавления, клик на точку для удаления)
- **Управление категориями** — CRUD для категорий туров
- **AI генератор** — создание туров с помощью AI (Ollama)

## Технологии

### Backend
- Laravel 10
- PHP 8.x
- MySQL
- REST API

### Frontend
- Vue 3 + Composition API
- Vite
- Tailwind CSS
- Axios

## Установка

### Требования
- PHP 8.x
- Composer
- Node.js 18+
- MySQL

### Шаги

1. **Клонирование и установка зависимостей**
```bash
cd /home/elena/Laravel/laravel_project

# Backend
composer install
npm install

# Frontend
cd frontend
npm install
```

2. **Настройка базы данных**
```bash
# Создайте базу данных MySQL
mysql -u root -p
CREATE DATABASE tour_catalog;

# Выполните миграции
php artisan migrate
php artisan db:seed  # если есть сидеры
```

3. **Конфигурация**
```bash
cp .env.example .env
php artisan key:generate
```

Отредактируйте `.env`:
```env
DB_DATABASE=tour_catalog
DB_USERNAME=root
DB_PASSWORD=ваш_пароль
```

4. **Запуск**
```bash
# Терминал 1 — Laravel API
php artisan serve --host=127.0.0.1 --port=8000

# Терминал 2 — Frontend
cd frontend
npm run dev -- --host 127.0.0.1 --port 5173
```

5. **Откройте в браузере**
- Фронтенд: http://127.0.0.1:5173
- API: http://127.0.0.1:8000

## Данные для входа

- **Email**: `admin@tours.local`
- **Пароль**: `admin123`

## Структура проекта

```
laravel_project/
├── app/
│   ├── Http/
│   │   └── Controllers/Api/     # API контроллеры
│   │       ├── TourController.php
│   │       ├── CategoryController.php
│   │       ├── AuthController.php
│   │       └── ...
│   ├── Models/                  # Модели Eloquent
│   │   ├── Tour.php
│   │   ├── Category.php
│   │   ├── TourImage.php
│   │   ├── TourDate.php
│   │   └── AdminUser.php
│   └── Services/               # Бизнес-логика
│       ├── OllamaService.php
│       └── EmbeddingService.php
├── database/migrations/         # Миграции БД
├── routes/api.php              # API маршруты
└── frontend/app/
    ├── pages/                  # Страницы Vue
    │   ├── Home.vue
    │   ├── Catalog.vue
    │   ├── TourDetail.vue
    │   └── admin/
    │       ├── Login.vue
    │       ├── Dashboard.vue
    │       ├── Index.vue
    │       ├── Tours.vue
    │       ├── Categories.vue
    │       └── AIGenerator.vue
    ├── components/             # Компоненты
    │   └── RouteMapEditor.vue  # Редактор маршрута (Яндекс Карты)
    ├── api.ts                  # API клиент
    └── App.vue                 # Корневой компонент
```

## API Endpoints

| Метод | Endpoint | Описание |
|-------|----------|----------|
| POST | `/api/v1/auth/login` | Авторизация |
| GET | `/api/v1/auth/me` | Данные текущего пользователя |
| GET | `/api/v1/tours` | Список туров (с фильтрами) |
| GET | `/api/v1/tours/{id}` | Один тур |
| POST | `/api/v1/tours` | Создание тура |
| PUT | `/api/v1/tours/{id}` | Обновление тура |
| DELETE | `/api/v1/tours/{id}` | Удаление тура |
| GET | `/api/v1/categories` | Категории |
| POST | `/api/v1/categories` | Создание категории |
| PUT | `/api/v1/categories/{id}` | Обновление категории |
| DELETE | `/api/v1/categories/{id}` | Удаление категории |
| POST | `/api/v1/images/upload` | Загрузка изображения |
| POST | `/api/v1/ai/generate-tour` | Генерация тура AI |

## Особенности реализации

### Яндекс Карты
- Публичная страница тура: отображение точек маршрута
- Админка: редактор маршрута (клик добавляет точку, клик на точку удаляет)
- Автогеокодинг адресов

### AI генератор
- Интеграция с Ollama для генерации описаний туров
- Использование эмбеддингов для поиска

## Лицензия

MIT