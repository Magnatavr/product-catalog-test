<!--
  Компонент: список товаров для администратора (CRUD)
  Путь: /admin/products
  Что делает:
    - Загружает список товаров с пагинацией (через API)
    - Показывает таблицу с кнопками "Редактировать" и "Удалить"
    - Удаление с подтверждением через модальное окно
    - Пагинация (кнопки Назад/Вперёд)
    - При неавторизованном запросе (401) чистит токен и редиректит на логин
  Использует: AdminLayout (проверяет наличие токена)
-->
<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Заголовок и кнопка добавления -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Управление товарами</h1>
                <Link href="/admin/products/create"
                      class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                    + Добавить товар
                </Link>
            </div>

            <!-- Спиннер загрузки -->
            <div v-if="loading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
            </div>

            <!-- Таблица товаров (появляется после загрузки) -->
            <div v-else>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.category.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.price }} ₽</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <!-- Ссылка на редактирование (Inertia) -->
                                <Link :href="`/admin/products/${product.id}/edit`"
                                      class="text-indigo-600 hover:text-indigo-900">Редактировать</Link>
                                <!-- Кнопка удаления (открывает модалку) -->
                                <button @click="confirmDelete(product)"
                                        class="text-red-600 hover:text-red-900">Удалить</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация (блок кнопок) -->
                <div class="flex justify-between items-center mt-6">
                    <button @click="changePage(products.current_page - 1)"
                            :disabled="!products.prev_page_url"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        ← Назад
                    </button>
                    <span class="text-sm text-gray-600">
                        Страница {{ products.current_page }} из {{ products.last_page }}
                    </span>
                    <button @click="changePage(products.current_page + 1)"
                            :disabled="!products.next_page_url"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Вперед →
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно подтверждения удаления -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-sm mx-auto">
                <p class="text-gray-800 mb-4">Удалить товар "{{ productToDelete?.name }}"?</p>
                <div class="flex justify-end space-x-3">
                    <button @click="deleteProduct"
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">Да</button>
                    <button @click="showModal = false"
                            class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition">Отмена</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
// Импорты
import AdminLayout from '@/Layouts/AdminLayout.vue';   // админ-лэйаут (проверяет токен)
import { Link, router } from '@inertiajs/vue3';        // Inertia-ссылки и роутер
import { ref, onMounted } from 'vue';                  // реактивность, хук монтирования
import axios from '@/axios';                           // настроенный axios с токеном

// --- Реактивные данные ---
// Объект пагинации (структура адаптирована под ответ API)
const products = ref({
    data: [],           // массив товаров
    current_page: 1,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null
});
const loading = ref(false);           // флаг загрузки
const showModal = ref(false);         // показывать модалку подтверждения удаления
const productToDelete = ref(null);    // товар, который собираемся удалить

// --- Методы ---

// Загрузка товаров с учётом пагинации
const fetchProducts = async (page = 1) => {
    loading.value = true;
    try {
        // GET /api/products?page=...
        const response = await axios.get('/products', { params: { page } });
        // Адаптируем структуру пагинации Laravel под удобный формат
        products.value = {
            data: response.data.data,                         // массив товаров
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            next_page_url: response.data.links.next,
            prev_page_url: response.data.links.prev,
        };
    } catch (err) {
        console.error(err);
        // Если токен просрочен или невалиден, очищаем хранилище и редиректим на логин
        if (err.response?.status === 401) {
            localStorage.removeItem('token');
            router.visit('/login');
        }
    } finally {
        loading.value = false;
    }
};

// Смена страницы пагинации
const changePage = (page) => {
    if (page >= 1 && page <= products.value.last_page) {
        fetchProducts(page);
    }
};

// Открытие модалки перед удалением
const confirmDelete = (product) => {
    productToDelete.value = product;
    showModal.value = true;
};

// Удаление товара (после подтверждения)
const deleteProduct = async () => {
    if (!productToDelete.value) return;
    try {
        // DELETE /api/products/{id}
        await axios.delete(`/products/${productToDelete.value.id}`);
        // Закрываем модалку и очищаем данные удаляемого товара
        showModal.value = false;
        productToDelete.value = null;
        // Перезагружаем текущую страницу списка (чтобы удалённый товар исчез)
        await fetchProducts(products.value.current_page);
    } catch (err) {
        console.error(err);
        alert('Ошибка при удалении');
    }
};

// --- Хук монтирования ---
// При загрузке компонента сразу запрашиваем первую страницу товаров
onMounted(() => {
    fetchProducts();
});
</script>
