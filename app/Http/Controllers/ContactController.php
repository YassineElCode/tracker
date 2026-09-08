<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('contact');
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        Mail::to('satumbusiness@gmail.com')->queue(new ContactFormMail($validated));

        session()->put('contact_form_sent', true);

        return response()->json([
            'message' => 'Bedankt voor je bericht! We nemen zo snel mogelijk contact met je op.',
        ]);
    }
}
