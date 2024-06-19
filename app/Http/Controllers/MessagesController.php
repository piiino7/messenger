<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Events\MessageDeleted;
use App\Events\MessageEdited;
use App\Models\Conversation;
use App\Models\Recipient;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models;
use Illuminate\Validation\Rule;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id, Request $request)
    {
        $user = Auth::user();
        $conversation = $user->conversations()
            ->with(['participants' => function($builder) use ($user) {
                $builder->where('id', '<>', $user->id);
            }])->findOrFail($id);

        $messages = $conversation->messages()
            ->with('user')
            ->where(function ($query) use ($user)  {
                $query->where('user_id', $user->id)
                    ->orWhereRaw('id IN (
                    SELECT message_id FROM recipients
                    WHERE recipients.message_id = messages.id
                    AND recipients.user_id = ? AND recipients.deleted_at IS NULL
                )', [$user->id]);
            })
            ->with('children.user')
            ->latest()
            ->paginate();

        return [
            'conversation' => $conversation,
            'messages' => $messages,
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            //    'message' => [Rule::requiredIf(function() use ($request) {
            //        return !$request->input('user_id');
            //    }),
            //    'string'],
            //'attachment' => ['file'],
            'conversation_id' =>[
                Rule::requiredIf(function() use ($request) {
                    return !$request->input('user_id');
                }),
                'int',
                'exists:conversations,id'
            ],
            'user_id' =>[
                Rule::requiredIf(function() use ($request) {
                    return !$request->input('conversation_id');
                }),
                'int',
                'exists:users,id'],
        ]);

        $user =  Auth::user();

        $conversation_id = $request->post('conversation_id');
        $user_id = $request->post('user_id');
        $forward_id = $request->input('forwardMessage');

        DB::beginTransaction();
        try {
            if ($conversation_id) {
                $conversation = $user->conversations()->findOrFail($conversation_id);
            }
            else {
                $conversation = Conversation::where('type', '=', 'peer')
                    ->whereHas('participants', function($builder) use ($user_id, $user) {
                        $builder->join('participants as participants2', 'participants2.conversation_id', '=', 'participants.conversation_id')
                            ->where('participants.user_id', '=', $user_id)
                            ->where('participants2.user_id', '=', $user->id);
                    })->first();

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
            }


            $type = 'text';
            $message = $request->post('message');
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $message = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mimetype' => $file->getMimeType(),
                    'file_path' => $file->store('attachments', [
                        'disk' => 'public',
                    ]),
                ];
                $type = 'attachment';
            }

            if ($forward_id) {
                $message = $conversation->messages()->create([
                    'forward_id' => $forward_id,
                    'user_id' => $user->id,
                    'type' => $type,
                    'body' => $message,
                ]);
                //dd($message);
            } else {
                $message = $conversation->messages()->create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'body' => $message,
                ]);
            }

            DB::statement('
                INSERT INTO recipients (user_id, message_id)
                SELECT user_id, ? FROM participants
                WHERE conversation_id = ?
                AND user_id <> ?
            ', [$message->id, $conversation->id, $user->id]);

            //DB::statement('
            //UPDATE recipients
            //SET read_at = ?
            //WHERE user_id = ? AND message_id = ?
            //', [Carbon::now(), Auth::id(), $message->id]);

            $conversation->update([
                'last_message_id' => $message->id,
            ]);

            DB::commit();
            $message->load('user');
            if($forward_id) {
                $message->load('children.user');
            }
            broadcast(new MessageCreated($message));
        } catch (Throwable $e)
        {
            DB::rollBack();

            throw $e;
        }

        return $message;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $message = $request->post('message');
        $user->sentMessages()
            ->where('id', '=', $id)
            ->update([
                'body' => $message,
            ]);
        $message = Message::findOrFail($id);


        broadcast(new MessageEdited($message));
        return [
            'message' => 'updated',
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $user_id =  $request->post('user_id');
        $message = Message::findOrFail($id);
        $message->load('user');

        $user->sentMessages()
            ->where('id', '=', $id)
            ->update([
                'deleted_at' => Carbon::now(),
            ]);

        if ($request->target == 'me') {
            Recipient::where([
                'user_id' => $user_id,
                'message_id' => $id,
            ])->delete();

        } else {
            Recipient::where([
                'message_id' => $id,
            ])->delete();
        }

        broadcast(new MessageDeleted($message));
        return [
            'message' => 'deleted',
            'id' => $id,
        ];
    }
}
