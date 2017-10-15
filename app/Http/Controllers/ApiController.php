<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use GuzzleHttp\Client;

class ApiController extends Controller
{

    public function createUser(Request $request) {

    	if(empty($request->input('email'))) 
    	{
    		//  Return empty email error
    		return response()->json(['status' => '0', 'content' => 'ERROR: Please specify an email address.']);
    	}
    	else if(empty($request->input('password'))) {
    		//  Return empty password error
    		return response()->json(['status' => '0', 'content' => 'ERROR: Please specify a password.']);
    	}

    	//  Build the validation array
    	$input['email'] = $request->input('email');
    	$rules = array('email' => 'unique:users,email');
    	$validator = Validator::make($input, $rules);

    	if ($validator->fails()) {
    		return response()->json(['status' => '0', 'content' => 'ERROR: That email already exists.']);
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

    public function validateUser(Request $request) {
        if(empty($request->input('email'))) 
        {
            //  Return empty email error
            return response()->json(['status' => '0', 'content' => 'ERROR: Please specify an email address.']);
        }
        else if(empty($request->input('password'))) {
            //  Return empty password error
            return response()->json(['status' => '0', 'content' => 'ERROR: Please specify a password.']);
        }

        //  Build the validation array
        $input['email'] = $request->input('email');
        $rules = array('email' => 'unique:users,email');
        $validator = Validator::make($input, $rules);

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
                return response()->json(['status' => '0', 'content' => 'ERROR:  The password is incorrect.']);
            }
        }
        else {
            return response()->json(['status' => '0', 'content' => 'ERROR: That email does not exist.']);
        }
    }
}
