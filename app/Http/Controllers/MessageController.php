<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        // Accede al usuario autenticado
        $user = $request->user();

        // Cifra el mensaje
        $encryptedMessage = Crypt::encryptString($request->message);

        // Crea un nuevo mensaje
        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'message' => $encryptedMessage
        ]);

        return response()->json(['message' => 'Message sent successfully', 'data' => $message]);
    }


    public function getMessages(Request $request)
    {
        $user = $request->user(); // Authenticated user

        // Fetch messages where the authenticated user is either the sender or the receiver
        $messages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })->get();

        // Decrypt each message
        $decryptedMessages = $messages->map(function ($message) use ($user) {
            // Only decrypt the message if the current user is the intended receiver
            if ($message->receiver_id === $user->id) {
                $message->message = Crypt::decryptString($message->message);
            }
            return $message;
        });

        return response()->json(['messages' => $decryptedMessages]);
    }
}
