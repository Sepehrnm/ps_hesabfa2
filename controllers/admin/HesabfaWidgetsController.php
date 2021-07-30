<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/InvoiceService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ReceiptService.php');

class HesabfaWidgetsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init() {
        parent::init();
        $this->bootstrap = true;
    }

    public function initContent() {
        parent::initContent();
        $this->context->smarty->assign(array());
        $this->setTemplate('order-widget.tpl');
    }

    public function ajaxProcessSaveInvoice() {
        $orderId = Tools::getValue('orderId');
        $invoiceService = new InvoiceService($this->module);
        $result = $invoiceService->saveInvoice($orderId);
        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessSaveInvoiceReceipt() {
        $orderId = Tools::getValue('orderId');
        $receiptService = new ReceiptService($this->module);
        $result = $receiptService->saveReceipt($orderId);
        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessClearInvoiceLinkWithHesabfa() {
        $orderId = Tools::getValue('orderId');
        $invoiceService = new InvoiceService($this->module);
        $result = $invoiceService->clearLink($orderId);
        die(Tools::jsonEncode($result));
    }

}