import { copyFileSync, existsSync } from 'node:fs';

const source = 'node_modules/pdfjs-dist/build/pdf.worker.min.mjs';
const target = 'public/pdf.worker.min.mjs';

if (!existsSync(source)) {
    console.error('[pdf-worker] Sumber tidak ditemukan:', source);
    process.exit(1);
}

copyFileSync(source, target);

console.log('[pdf-worker] Disalin ke', target);
