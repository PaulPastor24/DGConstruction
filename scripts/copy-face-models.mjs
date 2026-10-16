import { mkdir, copyFile, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';

const source = new URL('../node_modules/@vladmandic/human/', import.meta.url);
const target = new URL('../public/models/human/', import.meta.url);
await mkdir(target, { recursive: true });
for (const model of ['blazeface', 'facemesh', 'faceres']) {
  for (const extension of ['json', 'bin']) {
    await copyFile(new URL(`models/${model}.${extension}`, source), new URL(`${model}.${extension}`, target));
  }
}
await copyFile(new URL('LICENSE', source), new URL('HUMAN-LICENSE.txt', target));
const wasmSource = new URL('../node_modules/@tensorflow/tfjs-backend-wasm/dist/', import.meta.url);
const wasmTarget = new URL('wasm/', target);
await mkdir(wasmTarget, { recursive: true });
for (const file of ['tfjs-backend-wasm.wasm', 'tfjs-backend-wasm-simd.wasm', 'tfjs-backend-wasm-threaded-simd.wasm']) {
  await copyFile(new URL(file, wasmSource), new URL(file, wasmTarget));
}
await writeFile(new URL('.htaccess', wasmTarget), '<IfModule mod_mime.c>\n    AddType application/wasm .wasm\n</IfModule>\n');
console.log(`Face recognition models copied to ${fileURLToPath(target)}`);
