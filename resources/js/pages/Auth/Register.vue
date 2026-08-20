<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GoogleSigninButton from '@/Components/GoogleSigninButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
        <Head title="Register" />

        <h1 class="font-serif text-lg font-semibold text-seal-ink">Create your HandSeal account</h1>
        <p class="mt-1 text-sm text-seal-muted">Start issuing certificates your students can trust.</p>

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
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
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
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm password" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton class="w-full" :disabled="form.processing">
                Create account
            </PrimaryButton>
        </form>

        <p class="mt-6 text-center text-sm text-seal-muted">
            Already have an account?
            <Link :href="route('login')" class="font-medium text-seal-brass hover:text-seal-navy">
                Log in
            </Link>
        </p>
</template>
