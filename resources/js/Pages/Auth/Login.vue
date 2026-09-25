<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

function submit() {
    form.post('/login');
}
</script>

<template>
    <div style="max-width: 320px; margin: 4rem auto; font-family: sans-serif;">
        <h1>Prihlásenie</h1>

        <form @submit.prevent="submit">
            <div>
                <label>E-mail</label>
                <input v-model="form.email" type="email" style="width: 100%; margin-bottom: 1rem;">
                <div v-if="form.errors.email" style="color: red; margin-bottom: 1rem;">{{ form.errors.email }}</div>
            </div>

            <div>
                <label>Heslo</label>
                <input v-model="form.password" type="password" style="width: 100%; margin-bottom: 1rem;">
            </div>

            <button type="submit" :disabled="form.processing" style="width: 100%; padding: 0.5rem;">Prihlásiť
                sa</button>
        </form>

        <p style="margin-top: 1rem;">
            <Link href="/register">Nemáš účet? Zaregistruj sa</Link>
        </p>
    </div>
</template>