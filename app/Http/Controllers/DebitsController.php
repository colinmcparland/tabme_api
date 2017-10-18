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

class DebitsController extends Controller
{
    function addDebit(Request $request) {

        //  Set the variables
        $amount = $request->input('amount');
        $user_id = $request->input('id');
        $debtor_email = $request->input('email');

        //  See if the debtor email exists
        if(User::where('email', '=', $debtor_email)->count() > 0) {

            //  Create the debtor
            $debtor = User::where('email', '=', $debtor_email)->first();

            //  If so, add a new debit
            $debit = new Debits;
            $debit->amount = $amount;
            $debit->user_id = $user_id;
            $debit->debtor_id = $debtor->id;
            $debit->save();

            return response()->json(['status' => '200']);
        }
        else {
            return response()->json(['status' => '0', 'content' => 'Sorry, the requested email address cannot be found.']);
        }

    }


    function getDebit(Request $request, $user_id) {

        // Get a collection of the users debits
        $debits = Debits::where('user_id', '=', $user_id)->get();

        //  See if any debits exist
        if($debits->count() > 0) {
            
            $ret = [];

            foreach($debits as $debit) {
                //  Get the debtor
                $debtor = User::where('id', '=', $debit->debtor_id)->first();
                array_push($ret, ['amount' => $debit->amount, 'debtor_email' => $debtor->email, 'debit_id' => $debit->id, 'created' => date('F d, Y', strtotime($debit->created_at))]);
            }

            return response()->json(['status' => '200', 'content' => $ret]);
        }
        else {
            return response()->json(['status' => '0', 'content' => 'You have no debits.']);
        }

    }

    function getDebitOwing(Request $request, $user_id) {

        // Get a collection of the users debits
        $debits = Debits::where('debtor_id', '=', $user_id)->get();

        //  See if any debits exist
        if($debits->count() > 0) {
            
            $ret = [];

            foreach($debits as $debit) {
                //  Get the debtor
                $creditor = User::where('id', '=', $debit->user_id)->first();
                array_push($ret, ['amount' => $debit->amount, 'creditor_email' => $creditor->email, 'debit_id' => $debit->id, 'created' => date('F d, Y', strtotime($debit->created_at))]);
            }

            return response()->json(['status' => '200', 'content' => $ret]);
        }
        else {
            return response()->json(['status' => '0', 'content' => 'You owe no debits.']);
        }

    }


    function payBackDebit(Request $request) {
        $id = $request->input('id');
        $this_debit = Debits::where('id', '=', $id)->first();

        $this_creditor = User::where('id', '=', $this_debit->user_id)->first();

        $application_fee = $this_debit->amount * .01;
        $payable_amount = intval(($this_debit->amount - $application_fee)*100);

        Stripe::setApiKey(env('STRIPE_KEY'));

        $resp = Payout::create(array(
            "amount" => $payable_amount,
            "currency" => "cad",
            "destination" => $this_creditor->stripe_cc_token
        ));

        return response()->json($resp);
    }
}
