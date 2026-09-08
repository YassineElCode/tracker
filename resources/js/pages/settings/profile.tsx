import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

type City = {
    id: number;
    name: string;
    company: string;
};

type ProfileForm = {
    voornaam: string;
    achternaam: string;
    email: string;
    whatsapp: string;
    cities: number[];
};

export default function Profile({
    mustVerifyEmail,
    status,
    cities,
    userCities,
}: {
    mustVerifyEmail: boolean;
    status?: string;
    cities: City[];
    userCities: number[];
}) {
    const { auth } = usePage<SharedData>().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm<Required<ProfileForm>>({
        voornaam: auth.user.voornaam,
        achternaam: auth.user.achternaam,
        email: auth.user.email,
        whatsapp: String(auth.user.whatsapp ?? ''),
        cities: userCities,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Profile information" description="Update your name and email address" />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="voornaam">Voornaam</Label>

                            <Input
                                id="voornaam"
                                className="block mt-1 w-full"
                                value={data.voornaam}
                                onChange={(e) => setData('voornaam', e.target.value)}
                                required
                                autoComplete="voornaam"
                                placeholder="Voornaam"
                            />

                            <InputError className="mt-2" message={errors.voornaam} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="achternaam">Achternaam</Label>

                            <Input
                                id="achternaam"
                                className="block mt-1 w-full"
                                value={data.achternaam}
                                onChange={(e) => setData('achternaam', e.target.value)}
                                required
                                autoComplete="achternaam"
                                placeholder="Achternaam"
                            />

                            <InputError className="mt-2" message={errors.achternaam} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email address (Let op! Als u uw e-mail verandert, moet u dit nogmaals verifiëren)</Label>

                            <Input
                                id="email"
                                type="email"
                                className="block mt-1 w-full"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                                autoComplete="username"
                                placeholder="Email address"
                            />

                            <InputError className="mt-2" message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="whatsapp">
                                Whatsapp Nummer (Let op! Controleer uw whatsapp nummer DUBBEL! Deze nummer wordt gebruikt voor de notificaties!)
                            </Label>

                            <Input
                                id="whatsapp"
                                className="block mt-1 w-full"
                                value={data.whatsapp}
                                onChange={(e) => setData('whatsapp', e.target.value)}
                                autoComplete="off"
                                placeholder="0000000000"
                            />

                            <InputError className="mt-2" message={errors.whatsapp} />
                        </div>

                        <div className="grid gap-4">
                            <div>
                                <h3 className="text-lg font-medium">SBAT Cities</h3>
                                <div className="grid grid-cols-2 gap-4 mt-2">
                                    {cities.filter(city => city.company === 'SBAT').map((city) => (
                                        <div key={city.id} className="flex gap-2 items-center">
                                            <Checkbox
                                                id={`city-${city.id}`}
                                                checked={data.cities.includes(city.id)}
                                                onCheckedChange={(checked) => {
                                                    if (checked) {
                                                        setData('cities', [...data.cities, city.id]);
                                                    } else {
                                                        setData(
                                                            'cities',
                                                            data.cities.filter((id) => id !== city.id),
                                                        );
                                                    }
                                                }}
                                            />
                                            <Label htmlFor={`city-${city.id}`}>{city.name}</Label>
                                        </div>
                                    ))}
                                </div>
                            </div>
                            <InputError className="mt-2" message={errors.cities} />
                        </div>

                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div>
                                <p className="-mt-4 text-sm text-muted-foreground">
                                    Your email address is unverified.{' '}
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                    >
                                        Click here to resend the verification email.
                                    </Link>
                                </p>

                                {status === 'verification-link-sent' && (
                                    <div className="mt-2 text-sm font-medium text-green-600">
                                        A new verification link has been sent to your email address.
                                    </div>
                                )}
                            </div>
                        )}

                        <div className="flex gap-4 items-center">
                            <Button disabled={processing}>Save</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Saved</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
