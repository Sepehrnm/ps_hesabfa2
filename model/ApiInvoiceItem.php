<?php


class ApiInvoiceItem
{
    public $rowNumber;
    public $itemCode;
    public $item;
    public $description;
    public $unit;
    public $quantity;
    public $unitPrice;
    public $sum;
    public $discount;
    public $tax;
    public $totalAmount;

    public function __construct()
    {
    }

}