<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ProductService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/CustomerService.php');

class ImportExportController extends ModuleAdminController
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
        $this->context->smarty->assign('tokenHesabfaModuleConfigure', Tools::getAdminTokenLite('AdminModules'));
        $this->setTemplate('import_export.tpl');
    }

    public function ajaxProcessExportProducts() {
        $batch = Tools::getValue('batch');
        $totalBatch = Tools::getValue('totalBatch');
        $total = Tools::getValue('total');
        $updateCount = Tools::getValue('updateCount');

        $productService = new ProductService();
        $result = $productService->exportProducts($batch, $totalBatch, $total, $updateCount);

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessExportProductsOpeningQuantity() {
        $batch = Tools::getValue('batch');
        $totalBatch = Tools::getValue('totalBatch');
        $total = Tools::getValue('total');

        $productService = new ProductService();
        $result = $productService->exportProductsOpeningQuantity($batch, $totalBatch, $total);

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessExportCustomers() {
        $batch = Tools::getValue('batch');
        $totalBatch = Tools::getValue('totalBatch');
        $total = Tools::getValue('total');
        $updateCount = Tools::getValue('updateCount');

        $customerService = new CustomerService();
        $result = $customerService->exportCustomers($batch, $totalBatch, $total, $updateCount);

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessExportOrders() {
        $batch = Tools::getValue('batch');
        $totalBatch = Tools::getValue('totalBatch');
        $total = Tools::getValue('total');
        $updateCount = Tools::getValue('updateCount');
        $date = Tools::getValue('date');

        $invoiceService = new InvoiceService($this->module);
        $result = $invoiceService->exportOrders($batch, $totalBatch, $total, $updateCount, $date);

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessExportReceipts() {
        $batch = Tools::getValue('batch');
        $totalBatch = Tools::getValue('totalBatch');
        $total = Tools::getValue('total');
        $updateCount = Tools::getValue('updateCount');
        $date = Tools::getValue('date');

        $receiptService = new ReceiptService($this->module);
        $result = $receiptService->exportReceipts($batch, $totalBatch, $total, $updateCount, $date);

        die(Tools::jsonEncode($result));
    }
}
