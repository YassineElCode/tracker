<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Python Automation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for Python script automation used in job processing.
    | These settings control the execution environment and behavior of
    | external Python scripts called from Laravel jobs.
    |
    */

    'python' => [
        'path' => env('PYTHON_PATH', base_path('.venv/bin/python3')),
        'script_path' => env('PYTHON_SCRIPT_PATH', base_path('script/auto_inschrijven_autoveiligheid.py')),
        'timeout' => env('PYTHON_SCRIPT_TIMEOUT', 60),
    ],

];
