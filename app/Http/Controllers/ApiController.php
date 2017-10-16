<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Friend;
use App\Debits;
use Illuminate\Support\Facades\Hash;
use Validator;
use GuzzleHttp\Client;

class ApiController extends Controller
{

    /**
     * Function to create a new user
     * @param  Request $request     HTTP request being sent to the API.
     * @return [JsonArray]          Return array including username, status, tokens.
     */
    public function createUser(Request $request) {

        //  If we have an empty email or password supplied, return an error message,
    	if(empty($request->input('email'))) 
    	{
    		//  Return empty email error
    		return response()->json(['status' => '0', 'content' => 'Please specify an email address.']);
    	}
    	else if(empty($request->input('password'))) {
    		//  Return empty password error
    		return response()->json(['status' => '0', 'content' => 'Please specify a password.']);
    	}

    	//  Build the validation array.  Email has to be unique in the users table
    	$input['email'] = $request->input('email');
    	$rules = array('email' => 'unique:users,email');
    	$validator = Validator::make($input, $rules);

        //  If the validator fails, we have that email registered already, so return an error.
    	if ($validator->fails()) {
    		return response()->json(['status' => '0', 'content' => 'That email already exists.']);
		}
		else {
            //  Create the user
		    $user = new User();
			$user->password = Hash::make($request->input('password'));
			$user->email = $request->input('email');
			$user->save();

            //Generate a token for the user
            $http = new Client;
            $auth_response = $http->post('http://tabme.tinybird.ca/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => env('PASSWORD_GRANT_CLIENT'),
                    'client_secret' => env('PASSWORD_GRANT_SECRET'),
                    'username' => $request->input('email'),
                    'password' => $request->input('password'),
                    'scope' => '',
                ],
            ]);

            $tokens = json_decode($auth_response->getBody(), true);

			return response()->json(['status' => '200', 'content' => $user, 'access_token' => $tokens['access_token'], 'refresh_token' => $tokens['refresh_token']]);
		}
    }


    /**
     * Function to log a user in.
     * @param  Request $request     HTTP request being sent to the API
     * @return JsonArray            Return object including status, user object and tokens.
     */
    public function validateUser(Request $request) {

        //  If we're supplied an empty email or password, send back error message.
        if(empty($request->input('email'))) 
        {
            //  Return empty email error
            return response()->json(['status' => '0', 'content' => 'Please specify an email address.']);
        }
        else if(empty($request->input('password'))) {
            //  Return empty password error
            return response()->json(['status' => '0', 'content' => 'Please specify a password.']);
        }

        //  Build the validation array.  Email must be unique across users table
        $input['email'] = $request->input('email');
        $rules = array('email' => 'unique:users,email');
        $validator = Validator::make($input, $rules);

        //  If the validator fails, we know the email exists, so check if the password works
        if ($validator->fails()) {
            //  If the validator fails, we know the email exists.  Now test if the PW works
            $thisuser = User::where('email', $request->input('email'))->first();

            if (Hash::check($request->input('password'), $thisuser->password)) {

                //Generate a token for the user
                $http = new Client;

                $auth_response = $http->post('http://tabme.tinybird.ca/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => env('PASSWORD_GRANT_CLIENT'),
                        'client_secret' => env('PASSWORD_GRANT_SECRET'),
                        'username' => $request->input('email'),
                        'password' => $request->input('password'),
                        'scope' => '',
                    ],
                ]);

                $tokens = json_decode($auth_response->getBody(), true);

                return response()->json(['status' => '200', 'content' => $thisuser, 'access_token' => $tokens['access_token'], 'refresh_token' => $tokens['refresh_token']]);
            }
            else {
                return response()->json(['status' => '0', 'content' => 'The password is incorrect.']);
            }
        }
        else {
            return response()->json(['status' => '0', 'content' => 'That email is not registered with us.']);
        }
    }

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
    }


    function addDebit(Request $request) {

        //  Set the variables
        $amount = $request->input('amount');
        $user_id = $request->input('id');
        $debtor_email = $request->input('email');

        //  See if the debtor email exists
        if(User::where('email', '=', $debtor_email)->count() > 0) {

            //  If so, add a new debit
            $debit = new Debits;
            $debit->amount = $amount;
            $debit->user_id = $user_id;
            $debit->debtor_id = $debtor_id;
            $debit->save();

            return response()->json(['status' => '200']);
        }
        else {
            return response()->json(['status' => '0', 'content' => 'Sorry, the requested email address cannot be found.']);
        }

    }
}
