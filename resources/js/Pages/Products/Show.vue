<!--
  Компонент: карточка товара (публичная страница)
  Путь: /product/{id}
  Что делает: загружает и показывает детальную информацию о товаре
  Использует: MainLayout
-->
<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Спиннер загрузки -->
            <div v-if="loading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
            </div>

            <!-- Товар загружен успешно -->
            <div v-else-if="product" class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ product.name }}</h1>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-bold text-indigo-600">{{ product.price }} ₽</span>
                            <span class="text-sm bg-gray-100 text-gray-600 px-3 py-1 rounded-full">{{ product.category.name }}</span>
                        </div>

                        <div class="border-t border-gray-100 pt-4 mb-4">
                            <h2 class="text-lg font-semibold text-gray-700 mb-2">Описание</h2>
                            <p class="text-gray-600 leading-relaxed">{{ product.description }}</p>
                        </div>

                        <!-- Кнопка возврата на главную (через Inertia) -->
                        <Link href="/" class="inline-block bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                            ← Назад к списку
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Товар не найден (ошибка или неверный id) -->
            <div v-else class="text-center py-12">
                <p class="text-red-500 text-lg">Товар не найден</p>
                <Link href="/" class="mt-4 inline-block text-indigo-600 hover:underline">Вернуться на главную</Link>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
// Импорты
import MainLayout from '@/Layouts/MainLayout.vue';      // основной лэйаут
import { ref, onMounted } from 'vue';                   // реактивность и хук монтирования
import axios from '@/axios';                            // настроенный axios (базовый URL /api)
import { Link } from '@inertiajs/vue3';                 // компонент навигации Inertia

// --- Пропсы (получаем id товара из маршрута) ---
// ВНИМАНИЕ: В URL параметры всегда строки, поэтому принимаем String.
// Если нужно число, преобразуем внутри.
const props = defineProps({
    id: {
        type: Number,
        required: true
    }
});

// --- Реактивные данные ---
const product = ref(null);   // объект товара (данные из API)
const loading = ref(false);  // флаг загрузки

// --- Хук монтирования: загружаем товар при создании компонента ---
onMounted(async () => {
    loading.value = true;
    try {

        const productId = props.id
        // GET-запрос к /api/products/{id}
        const response = await axios.get(`/products/${productId}`);
        // Сохраняем данные товара (response.data.data — из-за использования ProductResource)
        product.value = response.data.data;
    } catch (err) {
        console.error('Ошибка загрузки товара:', err);
        product.value = null;   // в случае ошибки (404, 500 и т.д.) показываем "не найден"
    } finally {
        loading.value = false;
    }
});
</script>
