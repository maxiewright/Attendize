<?php

namespace App\Services;

use App\Models\Event;

class Order
{
    /**
     * @var float
     */
    private $orderTotal;

    /**
     * @var float
     */
    private $totalBookingFee;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var float
     */
    public $orderTotalWithBookingFee;

    /**
     * @var float
     */
    public $taxAmount;

    /**
     * @var float
     */
    public $grandTotal;

    /**
     * Order constructor.
     */
    public function __construct($orderTotal, $totalBookingFee, $event)
    {

        $this->orderTotal = $orderTotal;
        $this->totalBookingFee = $totalBookingFee;
        $this->event = $event;
    }

    /**
     * Calculates the final costs for an event and sets the various totals
     */
    public function calculateFinalCosts()
    {
        $this->orderTotalWithBookingFee = $this->orderTotal + $this->totalBookingFee;

        if ($this->event->organiser->charge_tax == 1) {
            $this->taxAmount = ($this->orderTotalWithBookingFee * $this->event->organiser->tax_value) / 100;
        } else {
            $this->taxAmount = 0;
        }

        $this->grandTotal = $this->orderTotalWithBookingFee + $this->taxAmount;
    }

    /**
     * @return float|string
     */
    public function getOrderTotalWithBookingFee(bool $currencyFormatted = false)
    {

        if ($currencyFormatted == false) {
            return number_format($this->orderTotalWithBookingFee, 2, '.', '');
        }

        return money($this->orderTotalWithBookingFee, $this->event->currency);
    }

    /**
     * @return float|string
     */
    public function getTaxAmount(bool $currencyFormatted = false)
    {

        if ($currencyFormatted == false) {
            return number_format($this->taxAmount, 2, '.', '');
        }

        return money($this->taxAmount, $this->event->currency);
    }

    /**
     * @return float|string
     */
    public function getGrandTotal(bool $currencyFormatted = false)
    {

        if ($currencyFormatted == false) {
            return number_format($this->grandTotal, 2, '.', '');
        }

        return money($this->grandTotal, $this->event->currency);

    }

    public function getVatFormattedInBrackets(): string
    {
        return '(+'.$this->getTaxAmount(true).' '.$this->event->organiser->tax_name.')';
    }
}
