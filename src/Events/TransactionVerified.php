<?php

namespace TufikHasan\PaisaPay\Events;

use TufikHasan\PaisaPay\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionVerified
{
    use Dispatchable, SerializesModels;

    public Transaction $transaction;
    public array $verificationResponse;

    public function __construct(Transaction $transaction, array $verificationResponse)
    {
        $this->transaction = $transaction;
        $this->verificationResponse = $verificationResponse;
    }
}