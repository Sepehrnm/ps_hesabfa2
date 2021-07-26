<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingsService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');

class ReceiptService
{
    public $idLang;

    public function __construct()
    {
        $this->idLang = Configuration::get('PS_LANG_DEFAULT');
    }

    public function saveReceipt($id_order) {
        if (!isset($id_order))
            return false;

        $hesabfaApi = new HesabfaApiService(new SettingService());
        $invoiceService = new InvoiceService();
        $psFaService = new PsFaService();
        $invoiceNumber = $psFaService->getInvoiceCodeByPrestaId((int)$id_order);

        $payments = OrderPayment::getByOrderId($id_order);

        foreach ($payments as $payment) {
            // Skip free order payment
            if ($payment->amount <= 0) {
                return true;
            }

            $bank_code = $this->getBankCodeByPaymentName($payment->payment_method);

            if ($bank_code == -1) {
                return true;
            } elseif ($bank_code != false) {
                // fix Hesabfa API error
                if ($payment->transaction_id == '')
                    $payment->transaction_id = 'None';

                $transactionFee = 0;
                $response = $hesabfaApi->invoiceSavePayment($invoiceNumber, $bank_code, $payment->date_add,
                    $invoiceService->getOrderPriceInHesabfaDefaultCurrency($payment->amount, $id_order), $payment->transaction_id, $transactionFee);

                if ($response->Success) {
                    LogService::writeLogStr("Hesabfa invoice payment added. order id: $id_order");
                } else {
                    $msg = 'Cannot add Hesabfa Invoice payment. Error Message: ' . $response->ErrorMessage . ', Error code: ' . $response->ErrorCode . ', order id: ' . $id_order;
                    LogService::writeLogStr($msg);
                }
            } else {
                LogService::writeLogStr('Cannot add Hesabfa Invoice payment - Bank Code not defined. order id: ' . $id_order);
            }

        }

    }

    public function getBankCodeByPaymentName($paymentName)
    {
        $settingService = new SettingService();

        $sql = 'SELECT `module` FROM `' . _DB_PREFIX_ . 'orders` 
                WHERE `payment` = \''. $paymentName .'\'
        ';
        $result = Db::getInstance()->ExecuteS($sql);

        $modules_list = Module::getPaymentModules();
        if (isset($result[0])) {
            $paymentMethodId = 0;
            foreach ($modules_list as $module) {
                $module_obj = Module::getInstanceById($module['id_module']);
                if ($module_obj->name == $result[0]['module']) {
                    $paymentMethodId = $module['id_module'];
                }
            }
            return $settingService->getPaymentReceiptDestination($paymentMethodId);
        } else {
            return false;
        }
    }
}