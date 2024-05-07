<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/InvoiceService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ReceiptService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingService.php');

class HesabfaWidgetsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        parent::init();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign(array());
        $this->setTemplate('order-widget.tpl');
    }

    public function ajaxProcessSaveInvoice()
    {
        $orderId = Tools::getValue('orderId');
        $invoiceService = new InvoiceService($this->module);
        $result = $invoiceService->saveInvoice($orderId);
        if(_PS_VERSION_ > '1.8') {
            //8.0
            die(json_encode($result));
        } else {
            //1.7
            die(Tools::jsonEncode($result));
        }
    }

    public function ajaxProcessSaveInvoiceReceipt()
    {
        $orderId = Tools::getValue('orderId');
        $receiptService = new ReceiptService($this->module);
        $result = $receiptService->saveReceipt($orderId);
        if(_PS_VERSION_ > '1.8') {
            //8.0
            die(json_encode($result));
        } else {
            //1.7
            die(Tools::jsonEncode($result));
        }
    }

    public function ajaxProcessClearInvoiceLinkWithHesabfa()
    {
        $orderId = Tools::getValue('orderId');
        $invoiceService = new InvoiceService($this->module);
        $result = $invoiceService->clearLink($orderId);
        if(_PS_VERSION_ > '1.8') {
            //8.0
            die(json_encode($result));
        } else {
            //1.7
            die(Tools::jsonEncode($result));
        }
    }

    public function ajaxProcessDeletePluginData()
    {
        $settingService = new SettingService();

        $hesabfaApiService = new HesabfaApiService($settingService);
        $response = $hesabfaApiService->fixClearTags();
        if ($response->Success)
            LogService::writeLogStr("All tags deleted in hesabfa.");
        else
            LogService::writeLogStr("Error deleting tags.");

        $settingService->deleteAllSettings();
        LogService::writeLogStr("All plugin settings deleted.");

        include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/sql/uninstall.php');
        LogService::writeLogStr("Database tables deleted.");

        if(_PS_VERSION_ > '1.8') {
            //8.0
            die(json_encode(true));
        } else {
            //1.7
            die(Tools::jsonEncode(true));
        }
    }

}