<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\UserEnrolledAutoInschrijven;
use App\Models\EnrollmentAutoInschrijven;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ManageEnrollment extends Command
{
    protected $signature = 'enrollment:manage 
                            {action : enroll|unenroll} 
                            {center : autoveiligheid|sbat} 
                            {user_id : The ID of the user}';

    protected $description = 'Enroll or unenroll a user in Autoveiligheid or SBAT';

    /** @var array<string, string> */
    private array $validCenters = [
        'autoveiligheid' => 'Autoveiligheid',
        'sbat' => 'SBAT',
    ];

    public function handle(): int
    {
        $action = strtolower($this->argument('action'));
        $centerInput = strtolower($this->argument('center'));
        $userId = $this->argument('user_id');

        if (! $this->isValidCenter($centerInput)) {
            $this->error('Invalid exam center. Allowed values: autoveiligheid, sbat.');

            return self::FAILURE;
        }

        $user = $this->findUser($userId);
        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return self::FAILURE;
        }

        $center = $this->validCenters[$centerInput];

        return match ($action) {
            'enroll' => $this->enrollUser($user, $center),
            'unenroll' => $this->unenrollUser($user, $center),
            default => $this->handleInvalidAction(),
        };
    }

    private function isValidCenter(string $center): bool
    {
        return array_key_exists($center, $this->validCenters);
    }

    private function findUser(mixed $userId): ?User
    {
        return User::find($userId);
    }

    private function enrollUser(User $user, string $center): int
    {
        EnrollmentAutoInschrijven::firstOrCreate([
            'user_id' => $user->id,
            'examencentrum' => $center,
        ]);

        Mail::to($user->email)->queue(new UserEnrolledAutoInschrijven($user));

        $this->info("{$user->voornaam} {$user->achternaam} has been enrolled in {$center}.");

        return self::SUCCESS;
    }

    private function unenrollUser(User $user, string $center): int
    {
        EnrollmentAutoInschrijven::where('user_id', $user->id)
            ->where('examencentrum', $center)
            ->delete();

        $this->info("{$user->voornaam} {$user->achternaam} has been unenrolled from {$center}.");

        return self::SUCCESS;
    }

    private function handleInvalidAction(): int
    {
        $this->error('Invalid action. Allowed values: enroll, unenroll.');

        return self::FAILURE;
    }
}
