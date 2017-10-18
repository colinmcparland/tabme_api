<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Friend;
use App\Debits;
use Illuminate\Support\Facades\Hash;
use Validator;
use GuzzleHttp\Client;
use Stripe\Stripe;
use Stripe\Payout;

class FriendController extends Controller
{
/**
     * Add a friend for a specified user.
     * @param Request $request     HTTP request sent to the API
     */
    function addFriend(Request $request, $email) {

        //  Get the variables
        $user_id = $request->input('user_id');

        //  Check if the requested email exists
        if(User::where('email', '=', $email)->count() > 0) {

            //  Check if the friendship exists
            $user_to_add = User::where('email', '=', $email)->first();
            if(Friend::where([
                ['friend_id', '=', $user_to_add->id],
                ['user_id', '=', $user_id]
            ])->count() == 0) {

                //  Check if the user tried to friend themselves
                if($user_to_add->id == $user_id) {
                    return response()->json(['status' => '0', 'content' => 'You cannot add yourself as a friend!']);
                }

                //  Create the friendship
                $newfriendship = new Friend;
                $newfriendship->user_id = $user_id;
                $newfriendship->friend_id = $user_to_add->id;
                $newfriendship->save();

                return response()->json(['status' => '200']);
            }
            else {
                return response()->json(['status' => '0', 'content' => "You are already friends with this person."]);
            }
        }
        else {
            return response()->json(['status' => '0', 'content' => 'That email is not registered.']);
        }
    }


    function searchFriends(Request $request, $id) {
        $friends = Friend::where('user_id', '=', $id)->get();

        if($friends->count() > 0) {

            $ret = [];

            foreach($friends as $friend) {
                $this_user = User::where('id', '=', $friend->friend_id)->first();
                array_push($ret, ['email' => $this_user->email, 'id' => $this_user->id]);
            }
            return response()->json(['status' => '200', 'content' => $ret]);
        }
        else {
            return response()->json(['status' => '0', 'content' => 'No friends found.']);
        }
    }}
