import { Head } from '@inertiajs/react';

import { Faq } from '@/components/faq';
import { Navbar } from '@/components/navbar';
import { useTrans } from '@/hooks/use-trans';

const HeroSection = () => {
    const { t } = useTrans();

    return (
        <section id="home" className="relative flex min-h-screen items-center justify-center overflow-hidden pt-16">
            <div className="absolute top-0 left-0 z-0 h-full w-full">
                <div className="animate-float absolute top-1/4 left-1/4 h-24 w-24 rounded-full bg-purple-500 opacity-20"></div>
                <div
                    className="animate-float absolute top-1/2 right-1/4 h-32 w-32 rounded-lg bg-purple-500 opacity-20"
                    style={{ animationDelay: '1s' }}
                ></div>
                <div
                    className="animate-float absolute bottom-1/4 left-1/3 h-16 w-16 rounded-full bg-purple-500 opacity-20"
                    style={{ animationDelay: '2s' }}
                ></div>
            </div>

            <div className="relative z-10 text-center">
                <h1 className="text-5xl font-extrabold text-white">{t('Booking assistant Rijbewijs B - Belgie')}</h1>
                <p className="mt-4 text-lg text-gray-300">
                    {t('Voor slechts €35 schrijft ons programma jou automatisch in voor een rijexamen. Wij vinden meestal binnen 2 weken een plek in plaats van 6 maanden wachten!')}
                </p>

                <a
                    href="/contact"
                    className="mt-8 inline-block cursor-pointer rounded-lg bg-indigo-600 px-8 py-3 font-bold text-white transition duration-300 hover:bg-indigo-700"
                >
                    {t('Neem met ons contact')}
                </a>

                <div className="mx-auto mt-10 flex max-w-2xl flex-wrap justify-center gap-2">
                    {['Sint-Denijs-Westrem', 'Brakel', 'Eeklo', 'Erembodegem', 'Sint-Niklaas'].map((city) => (
                        <span
                            key={city}
                            className="rounded-full border border-gray-600 bg-gray-800/50 px-4 py-1.5 text-sm text-gray-300"
                        >
                            {city}
                        </span>
                    ))}
                </div>
            </div>
        </section>
    );
};

export default function Homepage() {
    return (
        <>
            <Head title="Homepage" />
            <meta name="description" content="Vind een vrije plek voor een examen en ontvang een melding wanneer er een vrije plek vrijkomt." />
            <div className="bg-gradient-to-br from-black via-gray-900 to-purple-900 font-sans text-gray-200 antialiased">
                <Navbar />
                <main>
                    <HeroSection />
                    {/* <ReviewSection /> */}
                    <Faq />
                </main>
            </div>
        </>
    );
}
