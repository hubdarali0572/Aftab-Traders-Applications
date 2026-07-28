import { computed, ref } from 'vue';
import ur from '@/i18n/ur';

const LOCALE_KEY = 'locale';
const DEFAULT_LOCALE = 'en';

const readStoredLocale = () => {
    try {
        const v = localStorage.getItem(LOCALE_KEY);
        if (v === 'ur' || v === 'en') return v;
    } catch (e) {
        // ignore
    }
    return DEFAULT_LOCALE;
};

const locale = ref(typeof document !== 'undefined' ? readStoredLocale() : DEFAULT_LOCALE);

/** Apply lang + document direction (RTL for Urdu, LTR for English). */
const applyDocumentLocale = (code) => {
    if (typeof document === 'undefined') return;
    const isUr = code === 'ur';
    document.documentElement.lang = isUr ? 'ur' : 'en';
    document.documentElement.dir = isUr ? 'rtl' : 'ltr';
    document.documentElement.classList.toggle('locale-ur', isUr);
    document.documentElement.classList.toggle('locale-en', !isUr);
};

applyDocumentLocale(locale.value);

const dictionaries = {
    en: {},
    ur,
};

/**
 * Translate UI text. Keys are English source strings.
 * English locale always returns the source string.
 */
export function t(key, replacements = {}) {
    if (key === null || key === undefined || key === '') return '';
    // Touch locale so Vue re-renders when language changes
    const current = locale.value;
    let source = String(key).trim().replace(/\s+/g, ' ');
    source = source
        .replace(/&amp;/g, '&')
        .replace(/&gt;/g, '>')
        .replace(/&lt;/g, '<')
        .replace(/&quot;/g, '"')
        .replace(/&#39;/g, "'");
    let out = source;
    if (current === 'ur') {
        out = dictionaries.ur[source] || dictionaries.ur[String(key)] || source;
    }
    Object.keys(replacements || {}).forEach((k) => {
        out = out.replace(new RegExp(`\\{${k}\\}`, 'g'), String(replacements[k]));
    });
    return out;
}

export function setLocale(code) {
    const next = code === 'ur' ? 'ur' : 'en';
    locale.value = next;
    applyDocumentLocale(next);
    try {
        localStorage.setItem(LOCALE_KEY, next);
    } catch (e) {
        // ignore
    }
}

export function useLocale() {
    const isUrdu = computed(() => locale.value === 'ur');
    const isEnglish = computed(() => locale.value === 'en');
    const dir = computed(() => (locale.value === 'ur' ? 'rtl' : 'ltr'));

    return {
        locale,
        dir,
        isUrdu,
        isEnglish,
        t,
        setLocale,
        toggleLocale: () => setLocale(locale.value === 'ur' ? 'en' : 'ur'),
    };
}

export const i18nPlugin = {
    install(app) {
        app.config.globalProperties.$t = t;
        app.config.globalProperties.$locale = locale;
        app.provide('t', t);
        app.provide('locale', locale);
        app.provide('setLocale', setLocale);
    },
};
