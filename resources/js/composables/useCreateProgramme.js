import { ref } from 'vue';
import axios from 'axios';

export function useCreateProgramme() {
    const creating = ref(false);
    const errors = ref({});

    async function createProgramme({ name, price, typical_duration }) {
        creating.value = true;
        errors.value = {};

        try {
            const { data } = await axios.post(route('programmes.store'), {
                name,
                price: price || null,
                typical_duration: typical_duration || null,
            });
            return data; // the created programme, straight from the response
        } catch (e) {
            if (e.response?.status === 422) {
                errors.value = e.response.data.errors ?? {};
            } else {
                errors.value = { name: ['Something went wrong. Please try again.'] };
            }
            return null;
        } finally {
            creating.value = false;
        }
    }

    return { creating, errors, createProgramme };
}
