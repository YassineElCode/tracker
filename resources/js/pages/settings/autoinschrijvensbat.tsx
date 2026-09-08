import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Auto Inschrijven Sbat Settings',
        href: '/settings/autoinschrijvensbat',
    },
];

type AutoInschrijvenSbatForm = {
    email: string;
    password: string;
    datum_slagen_theorieB: string;
    type_voorlopig_rijbewijs: string;
    afgiftedatum_voorlopig_rijbewijsB: string;
    hoeveelste_poging: string;
};

export default function AutoInschrijvenSbat() {
    const { auth } = usePage<SharedData>().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm<Required<AutoInschrijvenSbatForm>>({
        email: String(auth.user.sbat_email ?? ''),
        password: String(''),
        datum_slagen_theorieB: String(auth.user.datum_slagen_theorieB ?? ''),
        type_voorlopig_rijbewijs: String(auth.user.type_voorlopig_rijbewijs ?? ''),
        afgiftedatum_voorlopig_rijbewijsB: String(auth.user.afgiftedatum_voorlopig_rijbewijsB ?? ''),
        hoeveelste_poging: String(auth.user.hoeveelste_poging ?? ''),
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('autoinschrijvensbat.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Auto Inschrijven rijbewijs B SBAT" />

            <SettingsLayout>
                <div className="space-y-6">
                    <div className="flex flex-col space-y-2">
                        <HeadingSmall
                            title="Automatisch Inschrijven rijbewijs B SBAT"
                            description="U moet eerst een account hebben op SBAT en vervolgens de gegevens hier in vullen deze worden gebruikt om u in te schrijven."
                        />
                        <p className="text-sm text-red-600">(Deze instellingen zijn verplicht in te vullen als u deze functie wilt gebruiken)</p>
                    </div>

                    <form onSubmit={submit} className="space-y-6">
                        {/* Email */}
                        <div className="grid gap-2">
                            <Label htmlFor="email">SBAT Email</Label>
                            <Input id="email" value={data.email} onChange={(e) => setData('email', e.target.value)} placeholder="Email" />
                            <InputError className="mt-2" message={errors.email} />
                        </div>

                        {/* Password */}
                        <div className="grid gap-2">
                            <Label htmlFor="password">SBAT Password (password wordt niet getoond)</Label>
                            <Input id="password" value={data.password} onChange={(e) => setData('password', e.target.value)} placeholder="Password" />
                            <InputError className="mt-2" message={errors.password} />
                        </div>

                        {/* TheoryB Date */}
                        <div className="grid gap-2">
                            <Label htmlFor="datum_slagen_theorieB">Datum slagen theorie B</Label>
                            <Input
                                id="datum_slagen_theorieB"
                                type="date"
                                value={data.datum_slagen_theorieB}
                                onChange={(e) => setData('datum_slagen_theorieB', e.target.value)}
                            />
                            <InputError className="mt-2" message={errors.datum_slagen_theorieB} />
                        </div>

                        {/* Type Voorlopig Rijbewijs */}
                        <div className="grid gap-2">
                            <Label htmlFor="type_voorlopig_rijbewijs">Type Voorlopig Rijbewijs</Label>
                            <select
                                id="type_voorlopig_rijbewijs"
                                className="rounded border px-3 py-2"
                                value={data.type_voorlopig_rijbewijs}
                                onChange={(e) => setData('type_voorlopig_rijbewijs', e.target.value)}
                            >
                                <option value="">-- Kies een type --</option>
                                <option value="12 maand">12 maand</option>
                                <option value="18 maand">18 maand</option>
                                <option value="36 maand">36 maand</option>
                                <option value="Model 3">Model 3</option>
                                <option value="Stageattest">Stageattest</option>
                            </select>
                            <InputError className="mt-2" message={errors.type_voorlopig_rijbewijs} />
                        </div>

                        {/* TemporaryB Date */}
                        <div className="grid gap-2">
                            <Label htmlFor="temporaryB">Afigtedatum 1ste voorlopig rijbewijs B</Label>
                            <Input
                                id="temporaryB"
                                type="date"
                                value={data.afgiftedatum_voorlopig_rijbewijsB}
                                onChange={(e) => setData('afgiftedatum_voorlopig_rijbewijsB', e.target.value)}
                            />
                            <InputError className="mt-2" message={errors.afgiftedatum_voorlopig_rijbewijsB} />
                        </div>

                        {/* Hoeveelste Poging */}
                        <div className="grid gap-2">
                            <Label htmlFor="hoeveelste_poging">Hoeveelste poging</Label>
                            <select
                                id="hoeveelste_poging"
                                className="rounded border px-3 py-2"
                                value={data.hoeveelste_poging}
                                onChange={(e) => setData('hoeveelste_poging', e.target.value)}
                            >
                                <option value="">-- Kies een type --</option>
                                <option value="oneOrTwo">eenOfTwee</option>
                                <option value="threeOrMore">drieOfMeer</option>
                            </select>
                            <InputError className="mt-2" message={errors.hoeveelste_poging} />
                        </div>

                        {/* Submit */}
                        <div className="flex items-center gap-4">
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
            </SettingsLayout>
        </AppLayout>
    );
}
