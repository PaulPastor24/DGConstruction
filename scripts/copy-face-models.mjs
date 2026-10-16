import { mkdir, copyFile } from 'node:fs/promises';
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
console.log(`Face recognition models copied to ${fileURLToPath(target)}`);
