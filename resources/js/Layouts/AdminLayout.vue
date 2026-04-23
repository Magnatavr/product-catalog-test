<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Если не авторизован – показываем сообщение и ссылку на вход -->
        <div v-if="!isAuthenticated" class="flex justify-center items-center min-h-screen">
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <p class="text-gray-800 mb-4">Необходимо войти в админ-панель</p>
                <Link href="/login" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                    Войти
                </Link>
            </div>
        </div>

        <!-- Если авторизован – показываем админ-интерфейс -->
        <div v-else>
            <!-- Навигация -->
            <nav class="bg-gray-800 shadow-md sticky top-0 z-10">
                <div class="container mx-auto px-4 py-3 flex justify-between items-center">
                    <div class="flex items-center space-x-6">
                        <Link href="/admin/products" class="text-white hover:text-indigo-300 transition">
                            Управление товарами
                        </Link>
                    </div>
                    <button @click="logout"
                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition text-sm">
                        Выйти
                    </button>
                </div>
            </nav>

            <!-- Основной контент -->
            <main class="container mx-auto px-4 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const isAuthenticated = computed(() => !!localStorage.getItem('token'));

const logout = () => {
    localStorage.removeItem('token');
    router.visit('/');
};
</script>
