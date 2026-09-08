<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\EnrollmentSuccess;
use App\Models\City;
use App\Models\EnrollmentAutoInschrijven;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Process;

class ProcessAutoEnrollment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;

    public City $city;

    public int $tries = 3;

    public int $timeout = 600;

    public bool $failOnTimeout = true;

    public function __construct(User $user, City $city)
    {
        $this->user = $user;
        $this->city = $city;
    }

    public function uniqueId(): string
    {
        return $this->user->id;
    }

    public function handle(): bool
    {
        $pythonPath = config('automation.python.path');
        $scriptPath = config('automation.python.script_path');
        $processTimeout = config('automation.python.timeout');

        $command = [$pythonPath, $scriptPath, $this->user->id, $this->city->code];
        $process = Process::timeout($processTimeout)->run($command);

        if (! $process->successful()) {
            throw new \Exception("Failed to enroll user {$this->user->id}: Python script execution failed.");
        }

        $output = $process->output();

        if (! str_contains($output, 'ENROLLMENT_SUCCESS')) {
            throw new \Exception("Failed to enroll user {$this->user->id}: Python script did not return ENROLLMENT_SUCCESS. Output: {$output}");
        }

        $enrollments = EnrollmentAutoInschrijven::where('user_id', $this->user->id)->get();

        if ($enrollments->isEmpty()) {
            return true;
        }

        foreach ($enrollments as $enrollment) {
            $enrollment->delete();
        }

        Mail::to($this->user->email)->queue(new EnrollmentSuccess($this->user));

        return true;
    }
}
