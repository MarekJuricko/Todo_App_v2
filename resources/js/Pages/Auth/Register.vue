<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register');
}
</script>

<template>
    <div style="max-width: 320px; margin: 4rem auto; font-family: sans-serif;">
        <h1>Registrácia</h1>

        <form @submit.prevent="submit">
            <div>
                <label>Meno</label>
                <input v-model="form.name" type="text" style="width: 100%; margin-bottom: 1rem;">
                <div v-if="form.errors.name" style="color: red; margin-bottom: 1rem;">{{ form.errors.name }}</div>
            </div>

            <div>
                <label>E-mail</label>
                <input v-model="form.email" type="email" style="width: 100%; margin-bottom: 1rem;">
                <div v-if="form.errors.email" style="color: red; margin-bottom: 1rem;">{{ form.errors.email }}</div>
            </div>

            <div>
                <label>Heslo</label>
                <input v-model="form.password" type="password" style="width: 100%; margin-bottom: 1rem;">
                <div v-if="form.errors.password" style="color: red; margin-bottom: 1rem;">{{ form.errors.password }}
                </div>
            </div>

            <div>
                <label>Zopakovať heslo</label>
                <input v-model="form.password_confirmation" type="password" style="width: 100%; margin-bottom: 1rem;">
            </div>

            <button type="submit" :disabled="form.processing" style="width: 100%; padding: 0.5rem;">Zaregistrovať
                sa</button>
        </form>

        <p style="margin-top: 1rem;">
            <Link href="/login">Už máš účet? Prihlás sa</Link>
        </p>
    </div>
</template>