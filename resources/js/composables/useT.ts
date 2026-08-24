import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Translations = { [key: string]: string | Translations };

/**
 * Access shared translations exposed by HandleInertiaRequests.
 *
 * Usage: const t = useT(); t('common.save') → "Save"
 * Falls back to the last segment of the key when missing.
 */
export function useT() {
    const page = usePage<{ translations: Translations }>();

    const translations = computed<Translations>(
        () => page.props.translations ?? {},
    );

    return (key: string): string => {
        const value = key
            .split('.')
            .reduce<
                Translations | string | undefined
            >((carry, segment) => (carry && typeof carry === 'object' ? carry[segment] : undefined), translations.value);

        return typeof value === 'string'
            ? value
            : (key.split('.').pop() ?? key);
    };
}
