/**
 * Client-side image compression, shared across any form that accepts
 * image uploads (certificate template requests, business logo, etc.).
 *
 * Resizes to maxDimension on the longest edge, then re-encodes as JPEG,
 * stepping quality down until the result is under maxSizeMb (or quality
 * bottoms out). Skips compression entirely if the file's already small
 * enough that it's not worth the trip through canvas.
 *
 * Usage:
 *   import { compressImage } from '@/Composables/useImageCompression';
 *   const smaller = await compressImage(file, { maxSizeMb: 1, maxDimension: 800 });
 */
export function compressImage(file, options = {}) {
    const {
        maxSizeMb = 2,
        maxDimension = 1920,
        minQuality = 0.4,
        startQuality = 0.85,
        qualityStep = 0.15,
    } = options;

    return new Promise((resolve, reject) => {
        if (file.size <= maxSizeMb * 1024 * 1024) {
            resolve(file);
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        const img = new Image();

        img.onload = () => {
            let { width, height } = img;
            if (width > maxDimension || height > maxDimension) {
                const scale = maxDimension / Math.max(width, height);
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
                        const underLimit = blob.size <= maxSizeMb * 1024 * 1024;
                        if (underLimit || quality <= minQuality) {
                            URL.revokeObjectURL(objectUrl);
                            const compressed = new File(
                                [blob],
                                file.name.replace(/\.\w+$/, '.jpg'),
                                { type: 'image/jpeg', lastModified: Date.now() }
                            );
                            resolve(compressed);
                        } else {
                            tryQuality(quality - qualityStep);
                        }
                    },
                    'image/jpeg',
                    quality
                );
            };

            tryQuality(startQuality);
        };

        img.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            reject(new Error('Could not read image'));
        };

        img.src = objectUrl;
    });
}
