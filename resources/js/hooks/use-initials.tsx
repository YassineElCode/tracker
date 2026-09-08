import { useCallback } from 'react';

export function useInitials() {
    return useCallback((voornaam: string, achternaam: string): string => {
        const firstInitial = voornaam.charAt(0);
        const lastInitial = achternaam?.charAt(0) || '';
        return `${firstInitial}${lastInitial}`.toUpperCase();
    }, []);
}
