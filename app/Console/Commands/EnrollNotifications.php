<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\UserEnrolledNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnrollNotifications extends Command
{
    protected $signature = 'app:enroll-notifications {user_id} {status}';

    protected $description = 'Enable or disable send_notifications for a user';

    /** @var list<string> */
    private array $validStatuses = ['true', 'false', '1', '0'];

    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $status = strtolower($this->argument('status'));

        if (! $this->isValidStatus($status)) {
            $this->error('Invalid status. Use true/false or 1/0.');

            return self::FAILURE;
        }

        $user = $this->findUser($userId);
        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return self::FAILURE;
        }

        $booleanStatus = filter_var($status, FILTER_VALIDATE_BOOLEAN);

        $this->updateUserNotificationStatus($user, $booleanStatus);

        if ($booleanStatus) {
            $this->sendNotificationEmail($user);
        }

        $this->displaySuccessMessage($userId, $booleanStatus);

        return self::SUCCESS;
    }

    private function isValidStatus(string $status): bool
    {
        return in_array($status, $this->validStatuses, true);
    }

    private function findUser(mixed $userId): ?User
    {
        return User::find($userId);
    }

    private function updateUserNotificationStatus(User $user, bool $status): void
    {
        $user->update(['send_notifications' => $status]);
    }

    private function sendNotificationEmail(User $user): void
    {
        Mail::to($user->email)->queue(new UserEnrolledNotification($user));
    }

    private function displaySuccessMessage(mixed $userId, bool $status): void
    {
        $statusText = $status ? 'enabled' : 'disabled';
        $this->info("User ID {$userId} notifications have been {$statusText}.");
    }
}
