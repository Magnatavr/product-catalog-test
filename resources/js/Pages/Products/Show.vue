<template>
    <MainLayout>
        <div class="container mx-auto px-4 py-8">
            <div v-if="loading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
            </div>

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

                        <Link href="/" class="inline-block bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                            ← Назад к списку
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12">
                <p class="text-red-500 text-lg">Товар не найден</p>
                <Link href="/" class="mt-4 inline-block text-indigo-600 hover:underline">Вернуться на главную</Link>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, onMounted } from 'vue';
import axios from '@/axios';
import { Link } from '@inertiajs/vue3';

// Принимаем id как строку (из маршрута), потом преобразуем в число
const props = defineProps({
    id: {
        type: Number,
        required: true
    }
});

const product = ref(null);
const loading = ref(false);

onMounted(async () => {
    loading.value = true;
    try {
        // Преобразуем строку в число для запроса
        const productId = props.id
        const response = await axios.get(`/products/${productId}`);
        product.value = response.data.data;
    } catch (err) {
        console.error(err);
        product.value = null;
    } finally {
        loading.value = false;
    }
});
</script>
