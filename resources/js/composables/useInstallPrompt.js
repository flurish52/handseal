import { ref } from 'vue';

const deferredPrompt = ref(null);
const canInstall = ref(false);

window.addEventListener('beforeinstallprompt', (e) => {
    console.log('beforeinstallprompt fired', e);
    e.preventDefault();
    deferredPrompt.value = e;
    canInstall.value = true;
});

export function useInstallPrompt() {
    const install = async () => {
        if (!deferredPrompt.value) return;
        deferredPrompt.value.prompt();
        const { outcome } = await deferredPrompt.value.userChoice;
        if (outcome === 'accepted') {
            canInstall.value = false;
            deferredPrompt.value = null;
        }
    };

    return { canInstall, install };
}
