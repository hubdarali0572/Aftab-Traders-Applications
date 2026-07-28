/**
 * Auto-wrap static English UI strings in Vue templates with $t().
 * Only wraps exact dictionary keys. Skips script blocks and already-wrapped text.
 * Usage: node scripts/wrap-i18n.mjs
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath, pathToFileURL } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');

const { default: ur } = await import(pathToFileURL(path.join(root, 'resources/js/i18n/ur.js')).href);
const keys = Object.keys(ur).sort((a, b) => b.length - a.length);

function walk(dir, out = []) {
    for (const name of fs.readdirSync(dir)) {
        const p = path.join(dir, name);
        if (fs.statSync(p).isDirectory()) walk(p, out);
        else if (p.endsWith('.vue')) out.push(p);
    }
    return out;
}

function escapeRegExp(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function wrapTemplate(template) {
    let result = template;
    let changed = 0;

    const wrapIfPlain = (match, build) => {
        if (match.includes('$t(') || match.includes("t('") || match.includes('{{')) return match;
        changed++;
        return build();
    };

    for (const key of keys) {
        if (key.length < 2) continue;
        const safe = key.replace(/'/g, "\\'");
        const variants = [key];
        // HTML entity form as it may appear in templates
        const entity = key
            .replace(/&/g, '&amp;')
            .replace(/>/g, '&gt;')
            .replace(/</g, '&lt;');
        if (entity !== key) variants.push(entity);

        for (const variant of variants) {
            const esc = escapeRegExp(variant);

            const textRe = new RegExp(`>(\\s*)${esc}(\\s*)<`, 'g');
            result = result.replace(textRe, (match, a, b) =>
                wrapIfPlain(match, () => `>${a}{{ $t('${safe}') }}${b}<`)
            );

            const phRe = new RegExp(`(?<!:)placeholder="${esc}"`, 'g');
            result = result.replace(phRe, (match) =>
                wrapIfPlain(match, () => `:placeholder="$t('${safe}')"`)
            );

            const titleRe = new RegExp(`(?<!:)title="${esc}"`, 'g');
            result = result.replace(titleRe, (match) =>
                wrapIfPlain(match, () => `:title="$t('${safe}')"`)
            );

            const ariaRe = new RegExp(`(?<!:)aria-label="${esc}"`, 'g');
            result = result.replace(ariaRe, (match) =>
                wrapIfPlain(match, () => `:aria-label="$t('${safe}')"`)
            );
        }
    }

    result = result.replace(
        /(<InputLabel\b[^>]*\s):?value="([^"]+)"/g,
        (match, prefix, val) => {
            if (match.includes('$t(') || match.includes(':value="$t')) return match;
            const decoded = val.replace(/&amp;/g, '&').replace(/&gt;/g, '>').replace(/&lt;/g, '<');
            if (ur[val] || ur[decoded]) {
                changed++;
                const k = ur[val] ? val : decoded;
                return `${prefix}:value="$t('${k.replace(/'/g, "\\'")}')"`;
            }
            return match;
        }
    );

    // <Head title="...">
    result = result.replace(
        /(<Head\b[^>]*\s):?title="([^"]+)"/g,
        (match, prefix, val) => {
            if (match.includes('$t(') || match.includes(':title="$t')) return match;
            if (ur[val]) {
                changed++;
                return `${prefix}:title="$t('${val.replace(/'/g, "\\'")}')"`;
            }
            return match;
        }
    );

    return { result, changed };
}

const dirs = [
    path.join(root, 'resources/js/Pages'),
    path.join(root, 'resources/js/Components'),
    path.join(root, 'resources/js/Layouts'),
];

let totalFiles = 0;
let totalChanges = 0;

for (const dir of dirs) {
    const files = walk(dir);
    for (const file of files) {
        // Skip LanguageSwitcher / i18n internals if any
        if (file.includes('LanguageSwitcher')) continue;

        const raw = fs.readFileSync(file, 'utf8');
        const parts = raw.split(/(<template\b[^>]*>[\s\S]*?<\/template>)/);
        let fileChanged = 0;
        const next = parts
            .map((part) => {
                if (!part.startsWith('<template')) return part;
                const { result, changed } = wrapTemplate(part);
                fileChanged += changed;
                return result;
            })
            .join('');

        if (fileChanged > 0 && next !== raw) {
            fs.writeFileSync(file, next, 'utf8');
            totalFiles++;
            totalChanges += fileChanged;
            console.log(`updated ${path.relative(root, file)} (+${fileChanged})`);
        }
    }
}

console.log(`\nDone. ${totalFiles} files, ${totalChanges} replacements.`);
