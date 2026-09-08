<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\EnrollmentSuccess;
use App\Models\City;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Process;

class ProcessAutoEnrollmentSBAT implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $tries = 3;

    public $timeout = 300;

    public $failOnTimeout = true;

    public $user;

    public $city;

    public $incomingDatums;

    public function __construct(User $user, City $city, Collection $incomingDatums)
    {
        $this->user = $user;
        $this->city = $city;
        $this->incomingDatums = $incomingDatums;
    }

    /**
     * TODO: test first with py script
     */
    public function handle()
    {
        $pythonPath = config('automation.python.path');
        $scriptPath = base_path('script/auto_inschrijven_sbat.py');
        $idAfspraak = $this->incomingDatums->first()['id_afspraak'];
        $command = [$pythonPath, $scriptPath, $this->user->id, $this->city->code, $idAfspraak];

        $process = Process::timeout(600)->run($command);
        if ($process->successful()) {
            $output = $process->output();
            if (str_contains($output, 'ENROLLMENT_SUCCESS')) {
                $enrollments = \App\Models\EnrollmentAutoInschrijven::where('user_id', $this->user->id)->get();
                if ($enrollments) {
                    foreach ($enrollments as $enrollment) {
                        $enrollment->delete();
                    }
                    Mail::to($this->user->email)->queue(new EnrollmentSuccess($this->user));
                }

                return true;
            } else {
                throw new \Exception("Failed to enroll user {$this->user->id}: Python script did not return ENROLLMENT_SUCCESS. Output: ".$output);
            }
        } else {
            throw new \Exception("Failed to enroll user {$this->user->id}: Python script execution failed.");
        }

    }
}
