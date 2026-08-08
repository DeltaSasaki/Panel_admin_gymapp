<?php

namespace App\Mail;

use App\Models\ProductSale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PosReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;

    /**
     * Create a new message instance.
     *
     * @param ProductSale $sale
     */
    public function __construct(ProductSale $sale)
    {
        $this->sale = $sale;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->sale->load(['items.product', 'user.profile', 'seller', 'gym']);

        return $this->subject("Comprobante de Venta POS #" . $this->sale->id . " - BigWorldFitness")
                    ->view('emails.pos_receipt');
    }
}
