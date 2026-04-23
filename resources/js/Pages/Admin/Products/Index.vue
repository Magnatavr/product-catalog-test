<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Управление товарами</h1>
                <Link href="/admin/products/create"
                      class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                    + Добавить товар
                </Link>
            </div>

            <div v-if="loading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
            </div>

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
                                <Link :href="`/admin/products/${product.id}/edit`"
                                      class="text-indigo-600 hover:text-indigo-900">Редактировать</Link>
                                <button @click="confirmDelete(product)"
                                        class="text-red-600 hover:text-red-900">Удалить</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
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

        <!-- Модалка подтверждения удаления -->
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
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from '@/axios';

const products = ref({ data: [], current_page: 1, last_page: 1, next_page_url: null, prev_page_url: null });
const loading = ref(false);
const showModal = ref(false);
const productToDelete = ref(null);

const fetchProducts = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/products', { params: { page } });
        // Адаптируем структуру пагинации
        products.value = {
            data: response.data.data,
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            next_page_url: response.data.links.next,
            prev_page_url: response.data.links.prev,
        };
    } catch (err) {
        console.error(err);
        if (err.response?.status === 401) {
            localStorage.removeItem('token');
            router.visit('/login');
        }
    } finally {
        loading.value = false;
    }
};

const changePage = (page) => {
    if (page >= 1 && page <= products.value.last_page) {
        fetchProducts(page);
    }
};

const confirmDelete = (product) => {
    productToDelete.value = product;
    showModal.value = true;
};

const deleteProduct = async () => {
    if (!productToDelete.value) return;
    try {
        await axios.delete(`/products/${productToDelete.value.id}`);
        showModal.value = false;
        productToDelete.value = null;
        await fetchProducts(products.value.current_page);
    } catch (err) {
        console.error(err);
        alert('Ошибка при удалении');
    }
};

onMounted(() => {
    fetchProducts();
});
</script>
