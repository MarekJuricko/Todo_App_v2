<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    tasks: Object,
    tags: Array,
    filters: Object,
});
</script>

<template>
    <AppLayout>
        <h1>Moje úlohy</h1>

        <p v-if="tasks.data.length === 0">Zatiaľ nemáš žiadne úlohy.</p>

        <ul style="list-style: none; padding: 0;">
            <li v-for="task in tasks.data" :key="task.id"
                style="border: 1px solid #ddd; border-radius: 6px; padding: 0.75rem; margin-bottom: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <strong :style="{ textDecoration: task.is_completed ? 'line-through' : 'none' }">
                            {{ task.title }}
                        </strong>
                        <p v-if="task.description" style="margin: 0.25rem 0; color: #555;">{{ task.description }}</p>
                        <span v-for="tag in task.tags" :key="tag.id"
                            style="display: inline-block; background: #eee; border-radius: 4px; padding: 0.1rem 0.5rem; margin-right: 0.25rem; font-size: 0.85rem;">
                            {{ tag.name }}
                        </span>
                    </div>
                </div>
            </li>
        </ul>

        <div v-if="tasks.links && tasks.links.length > 3" style="display: flex; gap: 0.5rem; margin-top: 1rem;">
            <Link v-for="link in tasks.links" :key="link.label" :href="link.url ?? '#'" :style="{
                padding: '0.25rem 0.6rem',
                border: '1px solid #ccc',
                borderRadius: '4px',
                background: link.active ? '#333' : 'white',
                color: link.active ? 'white' : 'black',
                opacity: link.url ? 1 : 0.4,
                textDecoration: 'none',
            }" v-html="link.label" />
        </div>
    </AppLayout>
</template>