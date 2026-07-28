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

const dirs = [
    path.join(root, 'resources/js/Pages'),
    path.join(root, 'resources/js/Components'),
    path.join(root, 'resources/js/Layouts'),
];

let files = 0;
let hits = 0;

for (const dir of dirs) {
    for (const file of walk(dir)) {
        let s = fs.readFileSync(file, 'utf8');
        const before = s;
        // Fix $t('Something}') → $t('Something')
        s = s.replace(/\$t\('([^']+)\}'\)/g, (_, key) => {
            hits++;
            return `$t('${key}')`;
        });
        // Fix t('Something}') → t('Something')
        s = s.replace(/(?<![$\w])t\('([^']+)\}'\)/g, (_, key) => {
            hits++;
            return `t('${key}')`;
        });
        if (s !== before) {
            fs.writeFileSync(file, s);
            files++;
        }
    }
}

console.log(`Fixed ${hits} occurrences in ${files} files`);
