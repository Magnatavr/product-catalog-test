<script setup>
import Form from './Form.vue';
const props = defineProps({
    id: Number
});
</script>
<template>
    <Form :productId="Number(id)" />
</template>
