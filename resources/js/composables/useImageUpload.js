import { ref } from 'vue';

const MAX_UPLOAD_MB = 2;       // target size after compression
const MAX_RAW_INPUT_MB = 25;   // sanity ceiling before we even try to compress
const MAX_DIMENSION = 1920;    // longest edge, px
const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

/**
 * Client-side image compression + preview management, shared between the
 * AI-generate form and the team-request form (both accept the same
 * reference-image inputs).
 *
 * @param {import('@inertiajs/vue3').InertiaForm} form  must have an `images` array field
 * @param {number} maxImages
 */
export function useImageUpload(form, maxImages = 3) {
    const previews = ref([]);
    const fileError = ref('');
    const isCompressing = ref(false);

    function compressImage(file) {
        return new Promise((resolve, reject) => {
            if (file.size <= MAX_UPLOAD_MB * 1024 * 1024) {
                resolve(file);
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            const img = new Image();

            img.onload = () => {
                let { width, height } = img;
                if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
                    const scale = MAX_DIMENSION / Math.max(width, height);
                    width = Math.round(width * scale);
                    height = Math.round(height * scale);
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const tryQuality = (quality) => {
                    canvas.toBlob(
                        (blob) => {
                            if (!blob) {
                                URL.revokeObjectURL(objectUrl);
                                reject(new Error('Compression failed'));
                                return;
                            }
                            const underLimit = blob.size <= MAX_UPLOAD_MB * 1024 * 1024;
                            if (underLimit || quality <= 0.4) {
                                URL.revokeObjectURL(objectUrl);
                                const compressed = new File(
                                    [blob],
                                    file.name.replace(/\.\w+$/, '.jpg'),
                                    { type: 'image/jpeg', lastModified: Date.now() }
                                );
                                resolve(compressed);
                            } else {
                                tryQuality(quality - 0.15);
                            }
                        },
                        'image/jpeg',
                        quality
                    );
                };

                tryQuality(0.85);
            };

            img.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Could not read image'));
            };

            img.src = objectUrl;
        });
    }

    async function onFilesSelected(e) {
        fileError.value = '';
        const incoming = Array.from(e.target.files ?? []);
        e.target.value = '';

        for (const file of incoming) {
            if (form.images.length >= maxImages) {
                fileError.value = `You can attach up to ${maxImages} images.`;
                break;
            }
            if (!ACCEPTED_TYPES.includes(file.type)) {
                fileError.value = 'Only JPG, PNG, or WEBP images are allowed.';
                continue;
            }
            if (file.size > MAX_RAW_INPUT_MB * 1024 * 1024) {
                fileError.value = 'That image is too large to process. Try a smaller photo.';
                continue;
            }

            isCompressing.value = true;
            try {
                const processed = await compressImage(file);
                form.images.push(processed);
                previews.value.push({ url: URL.createObjectURL(processed), name: processed.name });
            } catch (err) {
                fileError.value = 'One of the images could not be processed. Try a different photo.';
            } finally {
                isCompressing.value = false;
            }
        }
    }

    function removeImage(index) {
        URL.revokeObjectURL(previews.value[index].url);
        previews.value.splice(index, 1);
        form.images.splice(index, 1);
    }

    function resetImages() {
        previews.value.forEach((p) => URL.revokeObjectURL(p.url));
        previews.value = [];
        form.images = [];
    }

    return { previews, fileError, isCompressing, onFilesSelected, removeImage, resetImages, MAX_IMAGES: maxImages };
}
