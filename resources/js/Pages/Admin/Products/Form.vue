<!--
  Компонент: форма создания/редактирования товара (админка)
  Путь: /admin/products/create (создание) и /admin/products/{id}/edit (редактирование)
  Что делает:
    - Загружает список категорий для выпадающего списка
    - В режиме редактирования загружает данные товара по id
    - Отправляет POST (создание) или PUT (обновление) на API
    - Отображает ошибки валидации (422) под соответствующими полями
  Использует: AdminLayout (админский лэйаут с проверкой токена)
-->
<template>
    <AdminLayout>
        <div class="container mx-auto px-4 py-8 max-w-2xl">
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Заголовок меняется в зависимости от режима -->
                <h1 class="text-2xl font-bold text-gray-800 mb-6">
                    {{ isEdit ? 'Редактировать товар' : 'Добавить товар' }}
                </h1>

                <!-- Форма: при сабмите вызывается submit (prevent по умолчанию) -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Поле "Название" -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Название <span class="text-red-500">*</span>
                        </label>
                        <input type="text" v-model="form.name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               :class="{ 'border-red-500': errors.name }" />
                        <!-- Вывод первой ошибки валидации для поля name (бэкенд возвращает массив) -->
                        <div v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name[0] }}</div>
                    </div>

                    <!-- Поле "Категория" (выпадающий список) -->
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

                    <!-- Поле "Описание" (textarea) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                        <textarea v-model="form.description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <!-- Поле "Цена" (number с шагом 0.01) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Цена <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" v-model="form.price"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               :class="{ 'border-red-500': errors.price }" />
                        <div v-if="errors.price" class="text-red-600 text-sm mt-1">{{ errors.price[0] }}</div>
                    </div>

                    <!-- Кнопки управления -->
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
// Импорты
import AdminLayout from '@/Layouts/AdminLayout.vue';   // админский лэйаут (проверяет авторизацию)
import { Link, router } from '@inertiajs/vue3';        // Inertia-ссылки и роутер для редиректа
import { ref, onMounted } from 'vue';                  // реактивность и хук монтирования
import axios from '@/axios';                           // настроенный axios с токеном (через интерцептор)

// --- Пропсы ---
// productId передаётся из Edit.vue (для редактирования) или отсутствует для создания
const props = defineProps({
    productId: {
        type: Number,
        default: null
    }
});

// --- Реактивные данные ---
const isEdit = !!props.productId;                // флаг: редактирование (true) или создание (false)
const saving = ref(false);                       // блокировка кнопки во время отправки
const errors = ref({});                          // объект с ошибками валидации (ключ -> массив сообщений)
const categories = ref([]);                      // список категорий для селекта

// Данные формы (привязаны к полям v-model)
const form = ref({
    name: '',
    category_id: '',
    description: '',
    price: ''
});

// --- Методы ---

// Загрузка категорий из API (без пагинации)
const fetchCategories = async () => {
    const response = await axios.get('/categories');
    categories.value = response.data.data;   // response.data.data – массив категорий
};

// Загрузка товара для редактирования (заполнение формы)
const fetchProduct = async () => {
    if (!isEdit) return;                     // если создание – не загружаем
    try {
        const response = await axios.get(`/products/${props.productId}`);
        const product = response.data.data;   // данные товара (через ProductResource)
        // Заполняем форму полученными значениями
        form.value = {
            name: product.name,
            category_id: product.category_id,
            description: product.description,
            price: product.price
        };
    } catch (err) {
        console.error(err);
        alert('Товар не найден');
        router.visit('/admin/products');       // редирект, если товар не существует
    }
};

// Отправка формы (создание или обновление)
const submit = async () => {
    saving.value = true;            // блокируем кнопку
    errors.value = {};              // сбрасываем старые ошибки

    try {
        if (isEdit) {
            // PUT-запрос на обновление
            await axios.put(`/products/${props.productId}`, form.value);
        } else {
            // POST-запрос на создание
            await axios.post('/products', form.value);
        }
        // При успехе – редирект на список товаров
        router.visit('/admin/products');
    } catch (err) {
        // Если ошибка валидации (422), сохраняем ответ от бэкенда в errors
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors;
        } else {
            // Иначе – общее сообщение об ошибке (например, нет сети, 500 и т.д.)
            alert('Ошибка сохранения');
        }
    } finally {
        saving.value = false;        // разблокируем кнопку
    }
};

// --- Хук монтирования: загружаем категории и (если нужно) данные товара ---
onMounted(() => {
    fetchCategories();
    fetchProduct();
});
</script>
