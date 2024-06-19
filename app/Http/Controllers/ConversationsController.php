<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationsController extends Controller
{
    public function index() {
        $user = Auth::user();
        return $user->conversations()
            ->with([
                'lastMessage',
                'participants' => function($builder) use ($user) {
                    $builder->where('id', '<>', $user->id);
            }])
            ->withCount([
                'recipients as new_messages' => function($builder) use ($user) {
                    $builder->where('recipients.user_id', '=', $user->id)
                        ->whereNull('read_at');
                }
            ])
            ->paginate();
    }

    public function show($id) {
        $user = Auth::user();
        return $user->conversations()->with([
            'lastMessage',
            'participants' => function($builder) use ($user) {
                $builder->where('id', '<>', $user->id);
            }])
            ->withCount([
                'recipients as new_messages' => function($builder) use ($user) {
                    $builder->where('recipients.user_id', '=', $user->id)
                        ->whereNull('read_at');
                }
            ])
            ->findOrFail($id);
    }

    public function addParticipant(Request $request, Conversation $conversation) {
        $request->validate([
            'user_id' => ['required', 'int', 'exist:users, id']
        ]);

        $conversation->participants()->attach($request->post('user_id'), ['joined_at' => Carbon::now()]);
    }

    public function removeParticipants(Request $request, Conversation $conversation) {
        $request->validate([
            'user_id' => ['required', 'int', 'exist:users, id']
        ]);

        $conversation->participants()->detach($request->post('user_id'));
    }

    public function markAsRead($id) {
        Recipient::where('user_id', '=', Auth::id())
            ->whereNull('read_at')
            ->whereRaw('message_id IN (
                SELECT id FROM messages WHERE conversation_id = ?
            )', [$id])
            ->update([
                'read_at' => Carbon::now(),
                ]);

        return [
            'message' => 'Read'
        ];
    }

    public function markLastOnline($user_id) {
        User::where('id', '=', $user_id)
            ->update([
                'online' => Carbon::now(),
            ]);
    }

    public function destroy($id) {
        Recipient::where('user_id', '=', Auth::user())
            ->whereRaw('message_id IN (
                SELECT id FROM messages WHERE conversation_id = ?
            )', [$id])
            ->delete();
        //Conversation::destroy($id);

        return [
            'message' => 'Conversation deleted'
        ];
    }
}
