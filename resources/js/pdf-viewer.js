import * as pdfjsLib from 'pdfjs-dist';

// Worker disimpan di public/ agar selalu same-origin (dev maupun produksi),
// sehingga tidak terblokir aturan same-origin untuk Web Worker.
pdfjsLib.GlobalWorkerOptions.workerSrc = '/pdf.worker.min.mjs';

/**
 * Render PDF ke <canvas> di dalam container (view-only, lintas browser).
 * Tidak ada tombol/UI download karena hanya canvas yang ditampilkan.
 */
export async function renderPdf(container, url) {
    if (!container) return;

    container.innerHTML = '<div class="text-center text-muted py-5">'
        + '<span class="spinner-border spinner-border-sm me-2"></span> Memuat dokumen&hellip;</div>';

    try {
        const pdf = await pdfjsLib.getDocument({
            url,
            withCredentials: true,
            isEvalSupported: false,
        }).promise;

        container.innerHTML = '';
        const maxWidth = (container.clientWidth || 800) - 16;

        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            const page = await pdf.getPage(pageNumber);
            const baseViewport = page.getViewport({ scale: 1 });
            const scale = Math.min(2.5, maxWidth / baseViewport.width);
            const viewport = page.getViewport({ scale });

            const canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            canvas.style.width = '100%';
            canvas.style.height = 'auto';
            canvas.className = 'd-block mb-3 border rounded shadow-sm bg-white mx-auto';
            container.appendChild(canvas);

            await page.render({
                canvasContext: canvas.getContext('2d'),
                viewport,
            }).promise;
        }
    } catch (error) {
        container.innerHTML = '<div class="alert alert-danger m-3">Gagal memuat dokumen PKS. '
            + 'Silakan hubungi admin.</div>';
        console.error('PDF render error', error);
    }
}

export function clearPdf(container) {
    if (container) {
        container.innerHTML = '';
    }
}
