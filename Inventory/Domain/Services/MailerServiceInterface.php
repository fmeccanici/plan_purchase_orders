<?php

namespace App\Inventory\Domain\Services;

use App\Inventory\Domain\Mails\PurchaseOrderMail;

interface MailerServiceInterface
{
    public function send(PurchaseOrderMail $mail): bool;
}
