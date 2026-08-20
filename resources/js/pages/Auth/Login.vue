<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GoogleSigninButton from '@/Components/GoogleSigninButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
        <Head title="Log in" />

        <h1 class="font-serif text-lg font-semibold text-seal-ink"> Sign in to HandSeal</h1>
        <p class="mt-1 text-sm text-seal-muted">Welcome back! pick up where you left off.</p>

        <div
            v-if="status"
            class="mt-5 rounded-lg bg-seal-sage/10 px-3.5 py-2.5 text-sm font-medium text-seal-sage"
        >
            {{ status }}
        </div>

        <div class="mt-6">
            <GoogleSigninButton />
        </div>

        <div class="my-6 flex items-center gap-3">
            <div class="h-px flex-1 bg-seal-line"></div>
            <span class="text-xs font-medium uppercase tracking-wide text-seal-muted">or continue with email</span>
            <div class="h-px flex-1 bg-seal-line"></div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-sm text-seal-muted">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-seal-brass hover:text-seal-navy"
                >
                    Forgot password?
                </Link>
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">
                Log in
            </PrimaryButton>
        </form>

        <p class="mt-6 text-center text-sm text-seal-muted">
            New to HandSeal?
            <Link :href="route('register')" class="font-medium text-seal-brass hover:text-seal-navy">
                Create an account
            </Link>
        </p>
</template>
