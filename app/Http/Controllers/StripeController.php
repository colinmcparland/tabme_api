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
use Stripe\Account;

class StripeController extends Controller
{

	function addStripeAccount($token, $country) {

		//  Init stripe
		Stripe::setApiKey(env("STRIPE_KEY"));

		$new_account = Account::create(array(
			"type" => "custom",
			"country" => $country
		));
		
		$resp = $new_account->external_accounts->create(array("external_account" => $token['id']));

		return response()->json($resp);
	}

}
