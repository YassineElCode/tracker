import { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export function useTrans() {
    const { translations } = usePage<SharedData>().props;

    function t(key: string): string {
        return translations[key] ?? key;
    }

    return { t };
}
