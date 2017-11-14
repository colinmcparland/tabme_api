<?php

namespace App\Mail;

use DateTime;
use App\Debits;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DebitReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $debit;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Debits $debit)
    {
        $this->debit = $debit;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = User::where('id', '=', $this->debit->user_id)->first();

        $this->data = [
            'user' => $user->email,
            'amount' => $this->debit->amount,
            'date' => date_format(new DateTime($this->debit->date_created), 'd M, Y')
        ];

        return $this->view('mail.debitReminder');
    }
}
