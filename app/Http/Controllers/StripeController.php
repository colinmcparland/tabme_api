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

	function addStripeAccount($request) {

		//  Init stripe
		Stripe::setApiKey(env("STRIPE_KEY"));

		$new_account = Account::create(array(
			"type" => "custom",
			"country" => $request['country']
		));

		//  Add legal entity information
		$new_account->legal_entity->type = 'individual';
		$new_account->legal_entity->address->city = $request['city'];
		$new_account->legal_entity->address->country = $request['country'];
		$new_account->legal_entity->address->line1 = $request['address'];
		$new_account->legal_entity->address->postal_code = $request['zip'];
		$new_account->legal_entity->first_name = $request['fname'];
		$new_account->legal_entity->last_name = $request['lname'];
		$new_account->legal_entity->dob->day = $request['dob_day'];
		$new_account->legal_entity->dob->month = $request['dob_month'];
		$new_account->legal_entity->dob->year = $request['dob_year'];
		$new_account->tos_acceptance->date = time();
		$new_account->tos_acceptance->ip = $request['ip'];
		$new_account->save();
		
		$resp = $new_account->external_accounts->create(array("external_account" => $request['token']['id']));

		return response()->json($resp);
	}

}
