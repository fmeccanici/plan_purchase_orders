<?php

namespace Tests\Unit\Warehouse\Mocks\Picqer\BatchPicklists;

use Illuminate\Contracts\Support\Arrayable;

class PicqerBatchPicklistPicklistInsideProducts implements Arrayable
{
    protected int $idPicklist;
    protected int $amount;
    protected int $amountPicked;
    protected int $amountCollected;

    /**
     * @param int $idPicklist
     * @param int $amount
     * @param int $amountPicked
     * @param int $amountCollected
     */
    public function __construct(int $idPicklist, int $amount, int $amountPicked, int $amountCollected)
    {
        $this->idPicklist = $idPicklist;
        $this->amount = $amount;
        $this->amountPicked = $amountPicked;
        $this->amountCollected = $amountCollected;
    }

    public function toArray()
    {
        return [
            'idpicklist' => $this->idPicklist,
            'amount' => $this->amount,
            'amount_picked' => $this->amountPicked,
            'amount_collected' => $this->amountCollected
        ];
    }
}
