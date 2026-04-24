# Product Catalog - тестовое задание (Laravel + Vue.js + Inertia)

## Стек
- Backend: Laravel 11, PostgreSQL, Sanctum (токены)
- Frontend: Vue 3 (Composition API), InertiaJS, Tailwind CSS, Axios

## Функциональность
- Публичная часть: список товаров (пагинация, фильтр по категории), карточка товара.
- Админ-панель: CRUD товаров, аутентификация по токену (Sanctum), модальное подтверждение удаления.

## Установка и запуск

### Требования
- PHP 8.2+, Composer, PostgreSQL, Node.js

### Шаги
```bash
# Клонировать репозиторий
git clone <url>
cd product-catalog

# Backend
composer install
cp .env.example .env   # настройте DB_* под вашу PostgreSQL
php artisan key:generate
php artisan migrate --seed

# Frontend
npm install
npm run dev

# Запуск сервера
php artisan serve 
``` 
- Тестовый пользователь: admin@example.com / password

### API эндпоинты
- GET /api/products – список товаров (пагинация, фильтр ?category_id=)
- GET /api/products/{id} – один товар
- GET /api/categories – все категории
- POST /api/products, PUT/PATCH, DELETE – требуют токен (авторизация Bearer)

### Примечания
- Токен сохраняется в localStorage, все защищённые запросы проходят через перехватчик axios.
- Inertia используется только для рендеринга страниц (вместо Blade), сами данные загружаются через отдельные API-вызовы.
- Tailwind CSS – для стилизации.
