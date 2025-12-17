<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class NewExpenseAlert extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @throws MailerSendAssertException
     */
    public function build(): NewExpenseAlert
    {
        $personalization = [
            new Personalization($this->data['cardOwnerEmail'], [
                'date' => $this->data['date'],
                'name' => $this->data['name'],
                'amount' => number_format($this->data['amount'], 2, ',', '.'),
                'description' => $this->data['description'],
                'last4digits' => $this->data['last4digits'],
            ])
        ];

        return $this->mailersend(
            template_id: '0r83ql3ykqmgzw1j',
            personalization: $personalization
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Alerta de Despesa'),
            subject: 'Nova Despesa Registrada',
        );
    }
}
