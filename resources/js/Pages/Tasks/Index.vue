<script setup>
import { ref, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    tasks: Object,
    tags: Array,
    filters: Object,
});

// --- Filtre a vyhľadávanie ---
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const tag = ref(props.filters.tag ?? '');
let searchTimeout = null;

function applyFilters() {
    router.get('/', {
        search: search.value || undefined,
        status: status.value || undefined,
        tag: tag.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch([status, tag], applyFilters);

function clearFilters() {
    search.value = '';
    status.value = '';
    tag.value = '';
}

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

// --- Tags ---
const tagInputs = ref({}); // { [taskId]: 'text' }

function attachTag(task) {
    const name = (tagInputs.value[task.id] ?? '').trim();
    if (!name) return;

    router.post(`/tasks/${task.id}/tags`, { name }, {
        preserveScroll: true,
        onSuccess: () => {
            tagInputs.value[task.id] = '';
        },
    });
}

function detachTag(task, tag) {
    router.delete(`/tasks/${task.id}/tags/${tag.id}`, { preserveScroll: true });
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

        <!-- Filters Bar -->
        <div class="mb-6 flex flex-wrap items-center gap-2">
            <input v-model="search" type="text" placeholder="Hľadať v názve alebo popise..."
                class="flex-1 min-w-[200px] rounded-lg border border-[#222634] bg-[#161922] px-3.5 py-2 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none transition-all">
            <select v-model="status"
                class="rounded-lg border border-[#222634] bg-[#161922] px-3 py-2 text-sm text-white focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none transition-all">
                <option value="">Všetky stavy</option>
                <option value="pending">Rozpracované</option>
                <option value="completed">Dokončené</option>
            </select>
            <select v-model="tag"
                class="rounded-lg border border-[#222634] bg-[#161922] px-3 py-2 text-sm text-white focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none transition-all">
                <option value="">Všetky tagy</option>
                <option v-for="t in tags" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
            <button v-if="search || status || tag" @click="clearFilters" type="button"
                class="rounded-lg px-3 py-2 text-sm text-[#9BA1AE] hover:bg-[#1D212C] hover:text-white transition-colors cursor-pointer">
                Zrušiť filtre
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
            <h3 class="text-sm font-medium text-white">
                {{ search || status || tag ? 'Žiadne úlohy nezodpovedajú filtru' : 'Zatiaľ žiadne úlohy' }}
            </h3>
            <p class="text-xs text-[#9BA1AE] mt-1">
                {{ search || status || tag ? 'Skús zmeniť alebo zrušiť filter.' : 'Vytvorte si svoju prvú úlohu a začnite pracovať.' }}
            </p>
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
                            <h3 class="text-sm font-medium transition-colors"
                                :class="task.is_completed ? 'text-[#9BA1AE] line-through' : 'text-white'">
                                {{ task.title }}
                            </h3>
                            <p v-if="task.description" class="text-xs text-[#9BA1AE] mt-0.5">
                                {{ task.description }}
                            </p>

                            <!-- Tags -->
                            <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                <span v-for="tag in task.tags" :key="tag.id"
                                    class="group/tag inline-flex items-center gap-1 rounded-md bg-[#1D212C] px-2.5 py-0.5 text-[11px] font-medium text-[#9BA1AE]">
                                    {{ tag.name }}
                                    <button @click="detachTag(task, tag)" type="button"
                                        class="text-[#9BA1AE] hover:text-red-400 transition-colors cursor-pointer"
                                        aria-label="Odobrať tag">
                                        ×
                                    </button>
                                </span>

                                <form @submit.prevent="attachTag(task)" class="inline-flex items-center">
                                    <input v-model="tagInputs[task.id]" type="text" placeholder="+ tag"
                                        class="w-16 rounded-md border border-transparent bg-transparent px-1.5 py-0.5 text-[11px] text-[#9BA1AE] placeholder:text-[#9BA1AE]/50 hover:border-[#222634] focus:w-24 focus:border-[#635BFF] focus:bg-[#0F1117] focus:text-white focus:outline-none transition-all">
                                </form>
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
                v-html="link.label.includes('Previous') || link.label.includes('&laquo;') ? 'Predchádzajúca' : (link.label.includes('Next') || link.label.includes('&raquo;') ? 'Nasledujúca' : link.label)" />
        </div>
    </AppLayout>
</template>