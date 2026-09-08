import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard({ latestDate }: { latestDate: string | null }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="relative flex h-full flex-1 items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <div className="text-center">
                        <h2 className="text-2xl font-bold text-foreground">Most Recent Date Found</h2>
                        <p className="mt-2 text-lg text-muted-foreground">
                            {latestDate ? new Date(latestDate).toLocaleString() : 'No date found yet.'}
                        </p>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
