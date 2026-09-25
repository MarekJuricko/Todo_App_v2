<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div style="font-family: sans-serif; max-width: 700px; margin: 0 auto; padding: 1rem;">
        <header
            style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 1rem; margin-bottom: 1rem;">
            <Link href="/" style="font-weight: bold; text-decoration: none; color: black;">ToDo App</Link>
            <div v-if="page.props.auth.user">
                <span style="margin-right: 1rem;">{{ page.props.auth.user.name }}</span>
                <button @click="logout" style="padding: 0.25rem 0.5rem; cursor: pointer;">Odhlásiť sa</button>
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>