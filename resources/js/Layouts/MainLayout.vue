<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Навигация -->
        <nav class="bg-white shadow-md sticky top-0 z-10">
            <div class="container mx-auto px-4 py-3 flex flex-wrap justify-between items-center">
                <!-- Логотип или бренд -->
                <Link href="/" class="text-xl font-bold text-indigo-600 hover:text-indigo-800 transition">
                    Товары
                </Link>

                <!-- Меню (адаптивное) -->
                <div class="flex items-center space-x-4">
                    <Link href="/" class="text-gray-700 hover:text-indigo-600 transition">
                        Главная
                    </Link>
                    <Link v-if="isAuthenticated" href="/admin/products" class="text-gray-700 hover:text-indigo-600 transition">
                        Админка
                    </Link>
                    <button v-if="isAuthenticated" @click="logout" class="text-red-600 hover:text-red-800 transition">
                        Выйти
                    </button>
                    <Link v-else href="/login" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                        Войти
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Основной контент -->
        <main class="container mx-auto px-4 py-8">
            <slot />
        </main>

        <!-- Подвал (необязательно) -->
        <footer class="bg-white border-t mt-12 py-6">
            <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
                © {{ new Date().getFullYear() }} Каталог товаров. Все права защищены.
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const isAuthenticated = computed(() => !!localStorage.getItem('token'));

const logout = () => {
    localStorage.removeItem('token');
    window.location.href = '/';
};
</script>
