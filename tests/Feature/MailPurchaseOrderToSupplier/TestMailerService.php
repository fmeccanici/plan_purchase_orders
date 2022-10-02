<?php

namespace Tests\Feature\Inventory\MailPurchaseOrderToSupplier;

use App\Inventory\Domain\Mails\PurchaseOrderMail;
use Illuminate\Support\Collection;

class TestMailerService implements \App\Inventory\Domain\Services\MailerServiceInterface
{
    protected Collection $sentMails;

    public function __construct()
    {
        $this->sentMails = collect();
    }

    public function sentMails(): Collection
    {
        return $this->sentMails;
    }

    public function send(PurchaseOrderMail $mail): bool
    {
        $this->sentMails->push($mail);
        return true;
    }
}
