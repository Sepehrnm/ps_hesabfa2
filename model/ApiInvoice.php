<?php


class ApiInvoice
{
    public $number;
    public $invoiceType;
    public $date;
    public $dueDate;
    public $contactCode;
    public $contact;
    public $contactTitle;
    public $sum;
    public $payable;
    public $paid;
    public $rest;
    public $note;
    public $reference;
    public $sent;
    public $returned;
    public $status;
    public $tag;
    public $freight;
    public $warehouseReceiptStatus;
    public $project;
    public $salesmanCode;
    public $invoiceItems;

    public function __construct()
    {
    }


}