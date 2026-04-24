<!--
  Компонент: публичный список товаров
  Путь: / (главная страница)
  Что делает: показывает товары с пагинацией, фильтр по категориям
  Использует: MainLayout (шапка/подвал)
-->
<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Товары</h1>

            <!-- Блок фильтрации по категории -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Фильтр по категории</label>
                <select v-model="selectedCategory" @change="fetchProducts"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Все категории</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
                <!-- При выборе категории вызывается fetchProducts (сброс на первую страницу) -->
            </div>

            <!-- Индикатор загрузки (спиннер) -->
            <div v-if="loading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
            </div>

            <!-- Основной контент: список товаров -->
            <div v-else>
                <!-- Сетка карточек товаров (адаптивная: 1, 2, 3 колонки) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="product in products.data" :key="product.id"
                         class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ product.name }}</h2>
                            <p class="text-gray-600 mb-4 line-clamp-2">{{ product.description }}</p>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-2xl font-bold text-indigo-600">{{ product.price }} ₽</span>
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ product.category.name }}</span>
                            </div>
                            <!-- Inertia-ссылка на карточку товара (без перезагрузки) -->
                            <Link :href="`/product/${product.id}`"
                                  class="mt-4 inline-block w-full text-center bg-indigo-50 text-indigo-700 py-2 rounded-md hover:bg-indigo-100 transition">
                                Подробнее
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Пагинация (кнопки + номер текущей страницы) -->
                <div class="flex justify-between items-center mt-8">
                    <button @click="changePage(products.current_page - 1)" :disabled="!products.prev_page_url"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                        ← Назад
                    </button>
                    <span class="text-sm text-gray-600">Страница {{ products.current_page }} из {{ products.last_page }}</span>
                    <button @click="changePage(products.current_page + 1)" :disabled="!products.next_page_url"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                        Вперед →
                    </button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
// Импорты
import MainLayout from '@/Layouts/MainLayout.vue';      // общий лэйаут (шапка, подвал)
import { ref, onMounted } from 'vue';                   // реактивные переменные и хук монтирования
import axios from '@/axios';                            // настроенный экземпляр axios (базовый URL + авторизация)
import { Link } from '@inertiajs/vue3';                 // компонент для навигации Inertia

// --- Реактивные данные ---
// Объект пагинации: data - массив товаров, остальное - поля пагинации
const products = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null
});

const categories = ref([]);           // список категорий для фильтра
const selectedCategory = ref('');     // id выбранной категории (пустая строка = все)
const loading = ref(false);           // флаг загрузки (показываем спиннер)

// --- Методы работы с API ---

// Загрузка товаров с учётом пагинации и фильтрации
// page - номер запрашиваемой страницы (по умолчанию 1)
const fetchProducts = async (page = 1) => {
    loading.value = true;   // включаем спиннер
    try {
        // Собираем параметры запроса: page и, если выбрана категория, category_id
        const params = { page };
        if (selectedCategory.value) {
            params.category_id = selectedCategory.value;
        }

        // GET-запрос к /api/products (axios настроен на baseURL '/api')
        const response = await axios.get('/products', { params });

        // Адаптируем ответ от Laravel (пр: {data: [], links: {...}, meta: {...}})
        // в плоскую структуру, удобную для шаблона
        products.value = {
            data: response.data.data,                     // массив товаров
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            next_page_url: response.data.links.next,
            prev_page_url: response.data.links.prev,
        };
    } catch (err) {
        console.error('Ошибка загрузки товаров:', err);
    } finally {
        loading.value = false;    // выключаем спиннер (даже при ошибке)
    }
};

// Смена страницы пагинации
const changePage = (page) => {
    // Проверяем, что запрашиваемая страница в допустимых пределах
    if (page >= 1 && page <= products.value.last_page) {
        fetchProducts(page);   // загружаем товары для выбранной страницы
    }
};

// Загрузка списка категорий (без пагинации, все сразу)
const fetchCategories = async () => {
    const response = await axios.get('/categories');
    categories.value = response.data.data;   // response.data.data – массив категорий
};

// --- Хук жизненного цикла ---
// После монтирования компонента загружаем категории и товары (первая страница)
onMounted(() => {
    fetchCategories();
    fetchProducts();
});
</script>
