import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type FormEventHandler, useEffect } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Date & Time settings',
        href: '/settings/dates',
    },
];

const parseDate = (dateStr: string): Date | null => {
    if (!/^\d{2}\/\d{2}\/\d{4}$/.test(dateStr)) return null;
    const [day, month, year] = dateStr.split('/').map(Number);
    // JavaScript months are 0-indexed
    return new Date(year, month - 1, day);
};

const parseTime = (timeStr: string): number | null => {
    if (!/^\d{2}:\d{2}$/.test(timeStr)) return null;
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
};

type DatesForm = {
    startDatum: string;
    endDatum: string;
    startUur: string;
    endUur: string;
};

export default function Dates() {
    const { auth } = usePage<SharedData>().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm<DatesForm>({
        startDatum: auth.user.startDatum ?? '',
        endDatum: auth.user.endDatum ?? '',
        startUur: auth.user.startUur ?? '',
        endUur: auth.user.endUur || '',
    });

    useEffect(() => {
        const startDate = parseDate(data.startDatum);
        const endDate = parseDate(data.endDatum);

        if (startDate && endDate && endDate < startDate) {
            setData('endDatum', data.startDatum);
        }
    }, [data.startDatum, data.endDatum]);

    useEffect(() => {
        const startTime = parseTime(data.startUur);
        const endTime = parseTime(data.endUur);

        if (startTime !== null && endTime !== null && endTime < startTime) {
            setData('endUur', data.startUur);
        }
    }, [data.startUur, data.endUur]);

    const handleFormatting = (e: React.ChangeEvent<HTMLInputElement>, field: keyof DatesForm, format: 'date' | 'time') => {
        const input = e.target.value;
        const digitsOnly = input.replace(/\D/g, '');

        if (format === 'date') {
            const limitedDigits = digitsOnly.slice(0, 8);
            let day = limitedDigits.slice(0, 2);
            let month = limitedDigits.slice(2, 4);
            let year = limitedDigits.slice(4, 8);

            if (day.length === 2) {
                if (parseInt(day, 10) > 31) day = '31';
                if (parseInt(day, 10) === 0) day = '01';
            }

            if (month.length === 2) {
                if (parseInt(month, 10) > 12) month = '12';
                if (parseInt(month, 10) === 0) month = '01';
            }

            if (year.length === 4 && parseInt(year, 10) > 2026) {
                year = '2026';
            }

            const newDigits = day + month + year;
            let formattedInput = '';
            if (newDigits.length > 4) {
                formattedInput = `${newDigits.slice(0, 2)}/${newDigits.slice(2, 4)}/${newDigits.slice(4)}`;
            } else if (newDigits.length > 2) {
                formattedInput = `${newDigits.slice(0, 2)}/${newDigits.slice(2)}`;
            } else {
                formattedInput = newDigits;
            }
            setData(field, formattedInput);
        } else {
            const limitedDigits = digitsOnly.slice(0, 4);
            let hour = limitedDigits.slice(0, 2);
            let minute = limitedDigits.slice(2, 4);

            if (hour.length === 2) {
                const hourNum = parseInt(hour, 10);
                if (hourNum < 6) {
                    hour = '06';
                } else if (hourNum > 18) {
                    hour = '18';
                }
            }

            // If hour is 18 and user types any minutes, force them to 00.
            if (hour === '18' && limitedDigits.length === 4) {
                minute = '00';
            }

            if (minute.length === 2) {
                if (parseInt(minute, 10) > 59) minute = '59';
            }

            const newDigits = hour + minute;
            let formattedInput = '';
            if (newDigits.length > 2) {
                formattedInput = `${newDigits.slice(0, 2)}:${newDigits.slice(2)}`;
            } else {
                formattedInput = newDigits;
            }
            setData(field, formattedInput);
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('settings.dates.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Date & Time settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <div className="flex flex-col space-y-2">
                        <HeadingSmall
                            title="Datum & Uur"
                            description="Stel je voorkeur voor start- en einddatum en -uur voor notificaties en inschrijvingen."
                        />
                        <div className="text-sm text-red-500">
                            <p>Datum moet in format DD/MM/YYYY (voorbeeld: 27/07/2025)</p>
                            <p>Uur moet in format HH:MM (voorbeeld: 18:00)</p>
                            <p>De startdatum en -uur moeten voor de einddatum en -uur liggen.</p>
                        </div>
                    </div>

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="startDatum">Start Date</Label>
                            <Input
                                id="startDatum"
                                className="mt-1 block w-full"
                                value={data.startDatum}
                                onChange={(e) => handleFormatting(e, 'startDatum', 'date')}
                                autoComplete="off"
                                placeholder="DD/MM/YYYY"
                            />
                            <InputError className="mt-2" message={errors.startDatum} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="endDatum">End Date</Label>
                            <Input
                                id="endDatum"
                                className="mt-1 block w-full"
                                value={data.endDatum}
                                onChange={(e) => handleFormatting(e, 'endDatum', 'date')}
                                autoComplete="off"
                                placeholder="DD/MM/YYYY"
                            />
                            <InputError className="mt-2" message={errors.endDatum} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="startUur">Start Time</Label>
                            <Input
                                id="startUur"
                                className="mt-1 block w-full"
                                value={data.startUur}
                                onChange={(e) => handleFormatting(e, 'startUur', 'time')}
                                autoComplete="off"
                                placeholder="HH:MM"
                            />
                            <InputError className="mt-2" message={errors.startUur} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="endUur">End Time</Label>
                            <Input
                                id="endUur"
                                className="mt-1 block w-full"
                                value={data.endUur}
                                onChange={(e) => handleFormatting(e, 'endUur', 'time')}
                                autoComplete="off"
                                placeholder="HH:MM"
                            />
                            <InputError className="mt-2" message={errors.endUur} />
                        </div>

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
