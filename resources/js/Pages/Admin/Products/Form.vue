<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8 max-w-2xl">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">
                    {{ isEdit ? 'Редактировать товар' : 'Добавить товар' }}
                </h1>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Название -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Название <span class="text-red-500">*</span>
                        </label>
                        <input type="text" v-model="form.name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               :class="{ 'border-red-500': errors.name }" />
                        <div v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name[0] }}</div>
                    </div>

                    <!-- Категория -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Категория <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.category_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-red-500': errors.category_id }">
                            <option value="">Выберите категорию</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <div v-if="errors.category_id" class="text-red-600 text-sm mt-1">{{ errors.category_id[0] }}</div>
                    </div>

                    <!-- Описание -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                        <textarea v-model="form.description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <!-- Цена -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Цена <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" v-model="form.price"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               :class="{ 'border-red-500': errors.price }" />
                        <div v-if="errors.price" class="text-red-600 text-sm mt-1">{{ errors.price[0] }}</div>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <Link href="/admin/products"
                              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            Отмена
                        </Link>
                        <button type="submit" :disabled="saving"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ saving ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from '@/axios';

const props = defineProps({
    productId: {
        type: Number,
        default: null
    }
});

const isEdit = !!props.productId;
const saving = ref(false);
const errors = ref({});
const categories = ref([]);

const form = ref({
    name: '',
    category_id: '',
    description: '',
    price: ''
});

const fetchCategories = async () => {
    const response = await axios.get('/categories');
    categories.value = response.data.data;
};

const fetchProduct = async () => {
    if (!isEdit) return;
    try {
        const response = await axios.get(`/products/${props.productId}`);
        const product = response.data.data;
        form.value = {
            name: product.name,
            category_id: product.category_id,
            description: product.description,
            price: product.price
        };
    } catch (err) {
        console.error(err);
        alert('Товар не найден');
        router.visit('/admin/products');
    }
};

const submit = async () => {
    saving.value = true;
    errors.value = {};
    try {
        if (isEdit) {
            await axios.put(`/products/${props.productId}`, form.value);
        } else {
            await axios.post('/products', form.value);
        }
        router.visit('/admin/products');
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors;
        } else {
            alert('Ошибка сохранения');
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchCategories();
    fetchProduct();
});
</script>
