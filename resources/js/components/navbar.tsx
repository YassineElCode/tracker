import { SharedData } from '@/types';
import { useTrans } from '@/hooks/use-trans';
import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';

export const Navbar = () => {
    const { auth, locale } = usePage<SharedData>().props;
    const { t } = useTrans();
    const isAuthenticated = auth?.user !== null;
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [isLangOpen, setIsLangOpen] = useState(false);

    const languages = [
        { code: 'nl', label: 'NL', flag: '🇧🇪' },
        { code: 'fr', label: 'FR', flag: '🇫🇷' },
        { code: 'en', label: 'EN', flag: '🇬🇧' },
    ];

    const handleLangChange = (code: string) => {
        setIsLangOpen(false);
        router.post(`/locale/${code}`, {}, { preserveScroll: true });
    };

    const currentLanguage = languages.find(l => l.code === locale) ?? languages[0];

    const handleScroll = (e: React.MouseEvent, id: string) => {
        e.preventDefault();
        const target = document.getElementById(id);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <nav className="fixed top-0 z-10 w-full bg-transparent shadow-lg backdrop-blur-lg">
            <div className="container mx-auto flex items-center justify-between px-6 py-3">
                <a className="text-xl font-bold text-white" href="/">
                    Rijbewijsboeker
                </a>
                <div className="hidden items-center space-x-6 md:flex">
                    {isAuthenticated ? (
                        <>
                            <a className="text-white hover:text-gray-200" href="/dashboard">
                                {t('Dashboard')}
                            </a>
                            <a className="text-white hover:text-gray-200" href="/contact">
                                {t('Contact')}
                            </a>
                            <button
                                onClick={(e) => {
                                    e.preventDefault();
                                    router.post('/logout');
                                }}
                                className="cursor-pointer text-white hover:text-gray-200"
                            >
                                {t('Uitloggen')}
                            </button>
                        </>
                    ) : (
                        <>
                            <a className="text-white hover:text-gray-200" href="/contact">
                                {t('Contact')}
                            </a>
                        </>
                    )}
                    <div className="relative">
                        <button
                            onClick={() => setIsLangOpen(!isLangOpen)}
                            className="flex items-center gap-1.5 rounded-md border border-gray-600 px-2.5 py-1.5 text-sm text-white transition hover:border-gray-400"
                        >
                            <span>{currentLanguage.flag}</span>
                            <span>{currentLanguage.label}</span>
                            <svg className={`h-3.5 w-3.5 transition-transform ${isLangOpen ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        {isLangOpen && (
                            <div className="absolute right-0 mt-1 w-32 overflow-hidden rounded-md border border-gray-700 bg-gray-900 shadow-lg">
                                {languages.map((lang) => (
                                    <button
                                        key={lang.code}
                                        onClick={() => handleLangChange(lang.code)}
                                        className={`flex w-full items-center gap-2 px-3 py-2 text-sm transition hover:bg-gray-800 ${
                                            locale === lang.code ? 'bg-gray-800 text-white' : 'text-gray-300'
                                        }`}
                                    >
                                        <span>{lang.flag}</span>
                                        <span>{lang.label}</span>
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
                <div className="relative md:hidden">
                    <button onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)} className="text-white focus:outline-none">
                        <svg
                            className="h-6 w-6"
                            fill="none"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path d="M4 6h16M4 12h16m-7 6h7"></path>
                        </svg>
                    </button>

                    {isMobileMenuOpen && (
                        <div className="ring-opacity-5 absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black focus:outline-none">
                            {isAuthenticated ? (
                                <>
                                    <a href="/dashboard" className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {t('Dashboard')}
                                    </a>
                                    <a href="/contact" className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {t('Contact')}
                                    </a>
                                    <button
                                        onClick={(e) => {
                                            e.preventDefault();
                                            router.post('/logout');
                                        }}
                                        className="block w-full cursor-pointer px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        {t('Uitloggen')}
                                    </button>
                                </>
                            ) : (
                                <>
                                    <a href="/contact" className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {t('Contact')}
                                    </a>
                                </>
                            )}
                            <div className="border-t border-gray-200 px-4 py-2">
                                <div className="flex gap-2">
                                    {languages.map((lang) => (
                                        <button
                                            key={lang.code}
                                            onClick={() => handleLangChange(lang.code)}
                                            className={`flex items-center gap-1 rounded px-2 py-1 text-sm ${
                                                locale === lang.code
                                                    ? 'bg-indigo-100 text-indigo-700'
                                                    : 'text-gray-600 hover:bg-gray-100'
                                            }`}
                                        >
                                            <span>{lang.flag}</span>
                                            <span>{lang.label}</span>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </nav>
    );
};
