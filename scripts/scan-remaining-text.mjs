import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

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

// Find English-looking text nodes that are NOT already in $t()/t()
const hits = [];
for (const f of files) {
    const s = fs.readFileSync(f, 'utf8');
    const templates = [...s.matchAll(/<template[\s\S]*?<\/template>/g)].map((m) => m[0]);
    for (const t of templates) {
        // Strip already translated interpolations for scanning
        const cleaned = t
            .replace(/\{\{\s*\$t\([^)]+\)\s*\}\}/g, '')
            .replace(/\{\{\s*t\([^)]+\)\s*\}\}/g, '')
            .replace(/:[\w-]+="\$t\([^"]+\)"/g, '')
            .replace(/:[\w-]+="t\([^"]+\)"/g, '');

        const re = />([^<>{]*[A-Za-z]{3,}[^<>{}]*)</g;
        let m;
        while ((m = re.exec(cleaned))) {
            const text = m[1].trim().replace(/\s+/g, ' ');
            if (!text || text.length < 3) continue;
            if (/^[\d$\s.,:%\-#/]+$/.test(text)) continue;
            if (text.includes('v-') || text.includes('@')) continue;
            // skip pure variables leftovers
            if (!/[A-Za-z]{3,}/.test(text)) continue;
            hits.push({ file: path.relative(root, f), text });
        }
    }
}

const uniq = [...new Set(hits.map((h) => h.text))];
console.log('Remaining English-like snippets:', uniq.length);
uniq.slice(0, 80).forEach((t) => console.log('-', t));
