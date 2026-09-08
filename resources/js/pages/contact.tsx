import React, { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { Navbar } from '@/components/navbar';
import { useTrans } from '@/hooks/use-trans';

const ContactPage = () => {
    const { t } = useTrans();
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        message: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [message, setMessage] = useState({ text: '', type: '' });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const { csrf } = usePage().props;

    const submit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        setMessage({ text: '', type: '' });

        try {
            const formDataToSend = new FormData();
            formDataToSend.append('_token', csrf as string);
            formDataToSend.append('name', formData.name);
            formDataToSend.append('email', formData.email);
            formDataToSend.append('message', formData.message);

            const response = await fetch('/contact', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf as string,
                },
                body: formDataToSend
            });

            const data = await response.json();

            if (response.ok) {
                  router.visit('/danku');
                  return;
              } else {
                throw new Error(data.message || t('Er is iets misgegaan. Probeer het later opnieuw.'));
            }
        } catch (error) {
            setMessage({
                text: error instanceof Error ? error.message : t('Er is iets misgegaan. Probeer het later opnieuw.'),
                type: 'error'
            });
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <>
            <Head title="Contact Us" />
            <div className="min-h-screen font-sans antialiased text-gray-200 bg-gradient-to-br from-black via-gray-900 to-purple-900">
                <Navbar />
                <main className="flex items-center justify-center min-h-screen px-4 pt-20">
                    <div className="w-full max-w-lg p-8 space-y-8 border shadow-2xl rounded-xl backdrop-blur-lg bg-gray-800/50 border-gray-700/50">
                        <div className="text-center">
                            <h2 className="text-4xl font-extrabold text-white">{t('Neem contact met ons op')}</h2>
                            <p className="mt-2 text-lg text-gray-300">{t('Heb je een vraag? We horen graag van je.')}</p>
                        </div>

                        {message.text && (
                            <div className={`p-4 rounded-md border-l-4 ${
                                message.type === 'success'
                                    ? 'text-green-700 bg-green-100 border-green-500'
                                    : 'text-red-700 bg-red-100 border-red-500'
                            }`} role="alert">
                                <p className="font-bold">{message.type === 'success' ? t('Gelukt!') : t('Fout')}</p>
                                <p>{message.text}</p>
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-6" id="contact-form">
                            <div>
                                <label htmlFor="name" className="block text-sm font-medium text-gray-300">
                                    {t('Naam')}
                                </label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value={formData.name}
                                    onChange={handleChange}
                                    className="block w-full p-2 mt-1 text-white border-gray-600 rounded-md shadow-sm bg-gray-700/50 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                                    required
                                />
                                {message.type === 'error' && message.text.includes('name') && (
                                    <p className="mt-1 text-sm text-red-400">{message.text}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-gray-300">
                                    {t('Email')}
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value={formData.email}
                                    onChange={handleChange}
                                    className="block w-full p-2 mt-1 text-white border-gray-600 rounded-md shadow-sm bg-gray-700/50 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                                    required
                                />
                                {message.type === 'error' && message.text.includes('email') && (
                                    <p className="mt-1 text-sm text-red-400">{message.text}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="message" className="block text-sm font-medium text-gray-300">
                                    {t('Bericht')}
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows={4}
                                    value={formData.message}
                                    onChange={handleChange}
                                    className="block w-full p-2 mt-1 text-white border-gray-600 rounded-md shadow-sm bg-gray-700/50 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                                    required
                                ></textarea>
                                {message.type === 'error' && message.text.includes('message') && (
                                    <p className="mt-1 text-sm text-red-400">{message.text}</p>
                                )}
                            </div>

                            <div>
                                <button
                                    type="submit"
                                    disabled={isSubmitting}
                                    className="flex justify-center w-full px-4 py-3 text-sm font-medium text-white transition-colors duration-200 bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {isSubmitting ? t('Versturen...') : t('Verstuur bericht')}
                                </button>
                            </div>
                        </form>

                        <div className="flex items-center gap-4">
                            <div className="flex-1 h-px bg-gray-600"></div>
                            <span className="text-sm text-gray-400">{t('OF')}</span>
                            <div className="flex-1 h-px bg-gray-600"></div>
                        </div>

                        <div className="text-center">
                            <p className="mb-3 text-gray-300">{t('Neem contact met ons op via Instagram')}</p>
                            <a
                                href="https://www.instagram.com/rijschool_boekingassistant/"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 px-5 py-2.5 text-white rounded-lg transition-opacity duration-200 bg-gradient-to-r from-purple-500 via-pink-500 to-orange-400 hover:opacity-90"
                            >
                                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                                </svg>
                                @rijschool_boekingassistant
                            </a>
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
};

export default ContactPage;
