<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessengerController extends Controller
{
    public function index(Request $request) {
        return view('messenger', [
            'user' => $request->user(),
        ]);
    }

    public function getMe() {
        $user = Auth::user();

        return $me = User::where('id', '=', $user->id)
            ->orderBy('name')
            ->paginate();
    }

    public function getUsers() {
        $user = Auth::user();

        return $friends = User::where('id', '<>', $user->id)
            ->orderBy('name')
            ->paginate();
    }

    public function store(Request $request) {
        $user =  Auth::user();
        $user_id = $request->post('user_id');

        $conversation = Conversation::where('type', '=', 'peer')
            ->whereHas('participants', function($builder) use ($user_id, $user) {
                $builder->join('participants as participants2', 'participants2.conversation_id', '=', 'participants.conversation_id')
                    ->where('participants.user_id', '=', $user_id)
                    ->where('participants2.user_id', '=', $user->id);
            })->first();

        DB::beginTransaction();
        try {
            if(!$conversation)
            {
                $conversation = Conversation::create([
                    'user_id' => $user->id,
                    'type' => 'peer',
                ]);

                $conversation->participants()->attach([
                    $user->id => ['joined_at' => now()],
                    $user_id => ['joined_at' => now()],
                ]);
            }
            DB::commit();
        } catch (Throwable $e)
        {
            DB::rollBack();

            throw $e;
        }

        $conversationsArray = $user->conversations()
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

        $conversationId = Conversation::where('type', '=', 'peer')
            ->whereHas('participants', function($builder) use ($user_id, $user) {
                $builder->join('participants as participants2', 'participants2.conversation_id', '=', 'participants.conversation_id')
                    ->where('participants.user_id', '=', $user_id)
                    ->where('participants2.user_id', '=', $user->id);
            })->first();

        return [
            'conversationsArray' => $conversationsArray,
            'conversationId' => $conversationId,
        ];
    }

    public function destroy(Request $request) {

    }

}
