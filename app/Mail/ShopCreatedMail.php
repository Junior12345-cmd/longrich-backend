<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShopCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $shop;

    /**
     * Create a new message instance.
     */
    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Votre boutique a été créée !')
                    ->markdown('emails.shop_created')
                    ->with([
                        'shop' => $this->shop
                    ]);
    }

}
