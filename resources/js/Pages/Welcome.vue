<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div style="font-family: sans-serif; padding: 2rem;">
        <h1>Funguje to!</h1>
        <p>{{ message }}</p>
    </div>
</template>