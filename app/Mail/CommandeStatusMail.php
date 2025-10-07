<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // implémente si vous voulez queue
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Commande;
use App\Models\Shop;

class CommandeStatusMail extends Mailable // implements ShouldQueue // décommente si vous queuez
{
    use Queueable, SerializesModels;

    public Commande $commande;
    public ?Shop $shop;
    public string $subjectLine;
    public $isAdmin;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($commande, $shop, $isAdmin = false)
    {
        $this->commande = $commande;
        $this->shop = $shop;
        $this->subjectLine = "Mise à jour de la commande #{$commande->id} - " . ucfirst($commande->status);
        $this->isAdmin = $isAdmin;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    // public function build()
    // {
    //     return $this
    //         ->subject('Mise à jour de votre commande #' . $this->commande->id)
    //         ->markdown('emails.commande_status')
    //         ->with([
    //             'commande' => $this->commande,
    //             'shop' => $this->shop,
    //         ]);
    // }
    public function build()
    {
        $subject = $this->isAdmin 
            ? "Nouvelle mise à jour de commande #{$this->commande->id}" 
            : "Votre commande #{$this->commande->id} a été mise à jour";
    
        return $this->subject($subject)
                    ->markdown('emails.commande_status')
                    ->with([
                        'commande' => $this->commande,
                        'shop' => $this->shop,
                        'isAdmin' => $this->isAdmin,
                    ]);
    }
    

}
