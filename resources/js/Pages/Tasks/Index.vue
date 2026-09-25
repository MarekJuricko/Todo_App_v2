<script setup>
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    tasks: Object,
    tags: Array,
    filters: Object,
});

// --- Create new task ---
const showCreateForm = ref(false);

const createForm = useForm({
    title: '',
    description: '',
});

function submitCreate() {
    createForm.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            showCreateForm.value = false;
        },
    });
}

// --- Edit existing task ---
const editingTaskId = ref(null);

const editForm = useForm({
    title: '',
    description: '',
});

function startEdit(task) {
    editingTaskId.value = task.id;
    editForm.title = task.title;
    editForm.description = task.description ?? '';
}

function cancelEdit() {
    editingTaskId.value = null;
}

function submitEdit(task) {
    editForm.put(`/tasks/${task.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTaskId.value = null;
        },
    });
}

// --- Completion and deletion ---
function toggleComplete(task) {
    router.patch(`/tasks/${task.id}/complete`, {}, { preserveScroll: true });
}

function destroyTask(task) {
    if (confirm(`Naozaj chceš zmazať úlohu „${task.title}“?`)) {
        router.delete(`/tasks/${task.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <AppLayout>
        <!-- Page Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-white">Moje úlohy</h1>
                <p class="text-sm text-[#9BA1AE] mt-0.5">Prehľad a správa vašich denných úloh.</p>
            </div>

            <button @click="showCreateForm = !showCreateForm"
                class="inline-flex items-center gap-1.5 rounded-lg bg-[#635BFF] px-4 py-2 text-sm font-medium text-white shadow-lg shadow-[#635BFF]/20 hover:bg-[#5146E5] transition-all cursor-pointer">
                <span>{{ showCreateForm ? 'Zrušiť' : '+ Nová úloha' }}</span>
            </button>
        </div>

        <!-- Create form -->
        <div v-if="showCreateForm" class="mb-6 rounded-xl border border-[#222634] bg-[#161922] p-4 shadow-lg">
            <form @submit.prevent="submitCreate" class="space-y-3">
                <div>
                    <input v-model="createForm.title" type="text" placeholder="Názov úlohy"
                        class="w-full rounded-lg border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none transition-all">
                    <div v-if="createForm.errors.title" class="mt-1.5 text-xs text-red-400 font-medium">
                        {{ createForm.errors.title }}
                    </div>
                </div>

                <div>
                    <textarea v-model="createForm.description" placeholder="Popis (voliteľný)" rows="2"
                        class="w-full rounded-lg border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none transition-all resize-none"></textarea>
                </div>

                <button type="submit" :disabled="createForm.processing"
                    class="rounded-lg bg-[#635BFF] px-4 py-2 text-sm font-medium text-white hover:bg-[#5146E5] disabled:opacity-50 transition-all cursor-pointer">
                    Uložiť úlohu
                </button>
            </form>
        </div>

        <!-- Empty State -->
        <div v-if="tasks.data.length === 0"
            class="rounded-2xl border border-dashed border-[#222634] bg-[#161922] p-12 text-center">
            <div
                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#1D212C] text-[#9BA1AE] mb-4">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <h3 class="text-sm font-medium text-white">Zatiaľ žiadne úlohy</h3>
            <p class="text-xs text-[#9BA1AE] mt-1">Vytvorte si svoju prvú úlohu a začnite pracovať.</p>
        </div>

        <!-- Task List Items -->
        <ul v-else class="space-y-2.5">
            <li v-for="task in tasks.data" :key="task.id"
                class="group relative rounded-xl border border-[#222634] bg-[#161922] p-4 shadow-sm hover:border-[#635BFF]/50 transition-all">

                <!-- Edit mode -->
                <form v-if="editingTaskId === task.id" @submit.prevent="submitEdit(task)" class="space-y-3">
                    <input v-model="editForm.title" type="text"
                        class="w-full rounded-lg border border-[#222634] bg-[#0F1117] px-3.5 py-2 text-sm text-white focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none">
                    <div v-if="editForm.errors.title" class="text-xs text-red-400 font-medium">
                        {{ editForm.errors.title }}
                    </div>
                    <textarea v-model="editForm.description" rows="2"
                        class="w-full rounded-lg border border-[#222634] bg-[#0F1117] px-3.5 py-2 text-sm text-white focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none resize-none"></textarea>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="editForm.processing"
                            class="rounded-lg bg-[#635BFF] px-3 py-1.5 text-xs font-medium text-white hover:bg-[#5146E5] disabled:opacity-50 transition-all cursor-pointer">
                            Uložiť
                        </button>
                        <button type="button" @click="cancelEdit"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white transition-colors cursor-pointer">
                            Zrušiť
                        </button>
                    </div>
                </form>

                <!-- Display mode -->
                <div v-else class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3.5">
                        <button @click="toggleComplete(task)" type="button"
                            class="mt-0.5 h-4 w-4 shrink-0 rounded border border-[#222634] bg-[#0F1117] flex items-center justify-center cursor-pointer hover:border-[#635BFF] transition-colors">
                            <div v-if="task.is_completed" class="h-2 w-2 rounded-xs bg-[#635BFF]"></div>
                        </button>

                        <div>
                            <strong class="text-sm font-medium text-white block"
                                :class="{ 'text-[#9BA1AE] line-through font-normal': task.is_completed }">
                                {{ task.title }}
                            </strong>
                            <p v-if="task.description" class="mt-1 text-xs text-[#9BA1AE] leading-relaxed">
                                {{ task.description }}
                            </p>

                            <div v-if="task.tags.length" class="mt-2.5 flex flex-wrap gap-1.5">
                                <span v-for="tag in task.tags" :key="tag.id"
                                    class="inline-flex items-center rounded-md bg-[#1D212C] px-2.5 py-0.5 text-[11px] font-medium text-[#9BA1AE]">
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="startEdit(task)" type="button"
                            class="rounded-md px-2 py-1 text-xs text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white transition-colors cursor-pointer">
                            Upraviť
                        </button>
                        <button @click="destroyTask(task)" type="button"
                            class="rounded-md px-2 py-1 text-xs text-[#9BA1AE] hover:bg-red-500/10 hover:text-red-400 transition-colors cursor-pointer">
                            Zmazať
                        </button>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Pagination -->
        <div v-if="tasks.links && tasks.links.length > 3" class="mt-6 flex items-center justify-center gap-1">
            <Link v-for="link in tasks.links" :key="link.label" :href="link.url ?? '#'"
                class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors" :class="link.active
                    ? 'border-[#635BFF] bg-[#635BFF] text-white shadow-sm shadow-[#635BFF]/20'
                    : 'border-[#222634] bg-[#161922] text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white'"
                :style="{ opacity: link.url ? 1 : 0.3, pointerEvents: link.url ? 'auto' : 'none' }"
                v-html="link.label" />
        </div>
    </AppLayout>
</template>