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


    public function getMessages(Request $request, $userId)
    {
        $user = $request->user(); // Usuario autenticado

        // Recupera mensajes específicamente entre el usuario autenticado y el otro usuario especificado
        $messages = Message::where(function ($query) use ($user, $userId) {
            $query->where(function ($q) use ($user, $userId) {
                $q->where('sender_id', $user->id)->where('receiver_id', $userId);
            })->orWhere(function ($q) use ($user, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $user->id);
            });
        })->get();

        // Descifra cada mensaje para que ambos usuarios puedan leerlos
        $decryptedMessages = $messages->map(function ($message) {
            // Descifra el mensaje, no importa si eres el emisor o el receptor
            $message->message = Crypt::decryptString($message->message);
            return $message;
        });

        return response()->json(['messages' => $decryptedMessages]);
    }
}
