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
    <div class="flex min-h-screen items-center justify-center bg-[#0F1117] px-4 py-12">
        <div
            class="w-full max-w-[400px] rounded-2xl border border-[#222634] bg-[#161922] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.4)]">
            <div class="mb-8">
                <h1 class="text-xl font-semibold tracking-tight text-white">Vytvoriť účet</h1>
                <p class="text-sm text-[#9BA1AE] mt-1">Zaregistrujte sa a začnite spravovať svoje úlohy.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-[#9BA1AE]">Celé meno</label>
                    <input v-model="form.name" type="text" placeholder="Ján Mrkvička"
                        class="w-full rounded-lg border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none transition-all">
                    <div v-if="form.errors.name" class="mt-1.5 text-xs text-red-400 font-medium">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-[#9BA1AE]">E-mailová adresa</label>
                    <input v-model="form.email" type="email" placeholder="jan.mrkvicka@example.com"
                        class="w-full rounded-lg border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none transition-all">
                    <div v-if="form.errors.email" class="mt-1.5 text-xs text-red-400 font-medium">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-[#9BA1AE]">Heslo</label>
                    <input v-model="form.password" type="password" placeholder="••••••••"
                        class="w-full rounded-lg border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-[#9BA1AE]">Zopakovať heslo</label>
                    <input v-model="form.password_confirmation" type="password" placeholder="••••••••"
                        class="w-full rounded-lg border border-[#222634] bg-[#0F1117] px-3.5 py-2.5 text-sm text-white placeholder:text-[#9BA1AE]/40 focus:border-[#635BFF] focus:ring-1 focus:ring-[#635BFF] focus:outline-none transition-all">
                    <div v-if="form.errors.password" class="mt-1.5 text-xs text-red-400 font-medium">
                        {{ form.errors.password }}
                    </div>
                    <div v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-400 font-medium">
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full mt-2 inline-flex items-center justify-center rounded-lg bg-[#635BFF] px-4 py-2.5 text-sm font-medium text-white shadow-lg shadow-[#635BFF]/20 hover:bg-[#5146E5] focus:outline-none focus:ring-2 focus:ring-[#635BFF]/30 disabled:opacity-50 transition-all cursor-pointer">
                    Zaregistrovať sa
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-[#9BA1AE]">
                Už máte účet?
                <Link href="/login" class="font-medium text-white underline hover:text-[#635BFF] transition-colors">
                    Prihláste sa</Link>
            </p>
        </div>
    </div>
</template>