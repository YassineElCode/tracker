import { Link } from '@inertiajs/react';

export default function AppLogo() {
    return (
        <Link href="/" className="block">
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">Rijbewijsboeker</span>
            </div>
        </Link>
    );
}
