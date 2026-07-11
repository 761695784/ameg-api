<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageAdminNotification;
use App\Mail\ContactMessageClientAcknowledgment;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    /**
     * POST /api/contact-messages
     */
    public function store(StoreContactMessageRequest $request)
    {
        $message = ContactMessage::create($request->validated());

        // Email vers AMEG International (notification interne)
        Mail::to(config('ameg.admin_email'), config('ameg.admin_name'))
            ->send(new ContactMessageAdminNotification($message));

        // Accusé de réception automatique envoyé au client
        Mail::to($message->email, $message->name)
            ->send(new ContactMessageClientAcknowledgment($message));

        return response()->json($message, 201);
    }

    /**
     * GET /api/admin/contact-messages
     */
    public function index()
    {
        return response()->json(ContactMessage::latest()->paginate(20));
    }

    public function show(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'nouveau') {
            $contactMessage->update(['status' => 'lu']);
        }

        return response()->json($contactMessage);
    }

    public function update(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate([
            'status' => ['required', 'in:nouveau,lu,traite'],
        ]);

        $contactMessage->update($data);

        return response()->json($contactMessage);
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return response()->json(null, 204);
    }
}
