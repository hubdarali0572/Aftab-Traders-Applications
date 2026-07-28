import fs from 'fs';
import path from 'path';
import { fileURLToPath, pathToFileURL } from 'url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const { default: ur } = await import(pathToFileURL(path.join(root, 'resources/js/i18n/ur.js')).href);

function walk(dir, out = []) {
    for (const name of fs.readdirSync(dir)) {
        const p = path.join(dir, name);
        if (fs.statSync(p).isDirectory()) walk(p, out);
        else if (p.endsWith('.vue')) out.push(p);
    }
    return out;
}

const files = [
    ...walk(path.join(root, 'resources/js/Pages')),
    ...walk(path.join(root, 'resources/js/Layouts')),
    ...walk(path.join(root, 'resources/js/Components')),
];

const counts = {};
const re = />([A-Za-z][^<{]{1,120})</g;

for (const f of files) {
    const s = fs.readFileSync(f, 'utf8');
    const templates = [...s.matchAll(/<template[\s\S]*?<\/template>/g)].map((m) => m[0]);
    for (const t of templates) {
        let m;
        while ((m = re.exec(t))) {
            let text = m[1].trim().replace(/\s+/g, ' ');
            // decode common entities for key matching
            text = text.replace(/&amp;/g, '&').replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&quot;/g, '"');
            if (!text || text.length < 2) continue;
            if (text.includes('$t') || text.includes("t('")) continue;
            if (/^[\d$\s.,:%\-#/]+$/.test(text)) continue;
            if (!/[A-Za-z]{2,}/.test(text)) continue;
            counts[text] = (counts[text] || 0) + 1;
        }
    }
}

const missing = Object.keys(counts).filter((k) => !ur[k]).sort();
fs.writeFileSync(path.join(root, 'scripts/missing-en.txt'), missing.join('\n') + '\n', 'utf8');
console.log('Missing count:', missing.length);
console.log('Dict size:', Object.keys(ur).length);
