<script setup>
/**
 * GoogleSignInButton.vue
 * Standalone "Continue with Google" button.
 *
 * Usage:
 *   <GoogleSignInButton />
 *   <GoogleSignInButton label="Sign up with Google" />
 *   <GoogleSignInButton href="/auth/google/redirect" />
 */
import { ref } from 'vue'
import {computed} from "vue";

const props = defineProps({
    href: {
        type: String,
        default: '/auth/google/redirect',
    },
    label: {
        type: String,
        default: 'Continue with Google',
    },
})

const isLoading = ref(false)


// Append the current page as a query param so the backend can stash it in
// session before redirecting to Google, and return here after auth completes.
const finalHref = computed(() => {
    const url = new URL(props.href, window.location.origin)
    url.searchParams.set('current_url', window.location.href)
    return url.toString()
})
function handleClick() {
    // Just drives the visual loading state before the browser
    // navigates away — no need to prevent default.
    isLoading.value = true
}

</script>

<template>
    <a
        :href="href"
        @click="handleClick"
        class="google-btn"
        :class="{ 'is-loading': isLoading }"
        :aria-disabled="isLoading"
    >
    <span class="google-btn__icon">
      <svg
          v-if="!isLoading"
          width="18"
          height="18"
          viewBox="0 0 18 18"
          aria-hidden="true"
      >
        <path
            fill="#4285F4"
            d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.874 2.684-6.616z"
        />
        <path
            fill="#34A853"
            d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"
        />
        <path
            fill="#FBBC05"
            d="M3.964 10.706A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.706V4.962H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.038l3.007-2.332z"
        />
        <path
            fill="#EA4335"
            d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.962L3.964 7.294C4.672 5.167 6.656 3.58 9 3.58z"
        />
      </svg>

      <svg
          v-else
          class="google-btn__spinner"
          width="18"
          height="18"
          viewBox="0 0 18 18"
          aria-hidden="true"
      >
        <circle
            cx="9"
            cy="9"
            r="7.5"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-dasharray="28 100"
        />
      </svg>
    </span>

        <span class="google-btn__label">
      {{ isLoading ? 'Redirecting…' : label }}
    </span>
    </a>
</template>

<style scoped>
.google-btn {
    --gbtn-border: #dadce0;
    --gbtn-border-hover: #d2d3d4;
    --gbtn-text: #3c4043;
    --gbtn-shadow-hover: 0 1px 3px rgba(60, 64, 67, 0.15), 0 1px 2px rgba(60, 64, 67, 0.1);
    --gbtn-shadow-active: 0 1px 2px rgba(60, 64, 67, 0.2);
    --gbtn-focus-ring: rgba(66, 133, 244, 0.4);

    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    padding: 10px 16px;
    border: 1px solid var(--gbtn-border);
    border-radius: 12px;
    background: #fff;
    color: var(--gbtn-text);
    font-family: 'Google Sans', Roboto, system-ui, -apple-system, sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.15px;
    text-decoration: none;
    cursor: pointer;
    user-select: none;
    transition:
        background-color 0.15s ease,
        border-color 0.15s ease,
        box-shadow 0.15s ease,
        transform 0.1s ease;
}

.google-btn:hover {
    background-color: #f8f9fa;
    border-color: var(--gbtn-border-hover);
    box-shadow: var(--gbtn-shadow-hover);
}

.google-btn:active {
    background-color: #f1f3f4;
    box-shadow: var(--gbtn-shadow-active);
    transform: translateY(0.5px);
}

.google-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--gbtn-focus-ring);
}

.google-btn.is-loading {
    cursor: progress;
    color: #80868b;
    pointer-events: none;
}

.google-btn__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.google-btn__label {
    line-height: 20px;
    white-space: nowrap;
}

.google-btn__spinner {
    color: #4285f4;
    animation: gbtn-spin 0.8s linear infinite;
}

@keyframes gbtn-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .google-btn,
    .google-btn__spinner {
        transition: none;
        animation: none;
    }
}
</style>
