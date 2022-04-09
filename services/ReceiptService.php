<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingsService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');

class ReceiptService
{
    public $idLang;
    private $module;

    public function __construct(Module $module)
    {
        $this->idLang = Configuration::get('PS_LANG_DEFAULT');
        $this->module = $module;
    }

    public function saveReceipt($id_order)
    {
        if (!isset($id_order))
            return false;

        $hesabfaApi = new HesabfaApiService(new SettingService());
        $invoiceService = new InvoiceService($this->module);
        $psFaService = new PsFaService();
        $invoiceNumber = $psFaService->getInvoiceCodeByPrestaId((int)$id_order);

        if ($invoiceNumber && !$this->checkOldReceipts($invoiceNumber)) return false;

        $payments = OrderPayment::getByOrderId($id_order);
        $order = new Order($id_order);

        $ok = false;

        foreach ($payments as $payment) {
            if ($payment->amount <= 0) return true;

            $bank_code = $this->getBankCode();

            if ($bank_code == -1)
                return true;
            elseif ($bank_code != false) {
                if ($payment->transaction_id == '') $payment->transaction_id = 'None';

                $response = $hesabfaApi->invoiceSavePayment($invoiceNumber, $bank_code, $payment->date_add,
                    $invoiceService->getOrderPriceInHesabfaDefaultCurrency($payment->amount, $order), $payment->transaction_id);

                if ($response->Success) {
                    $ok = true;
                    LogService::writeLogStr("Hesabfa invoice receipt added. order id: $id_order");
                } else {
                    $msg = 'Cannot add Hesabfa Invoice receipt. Error Message: ' . $response->ErrorMessage . ', Error code: ' . $response->ErrorCode . ', order id: ' . $id_order;
                    LogService::writeLogStr($msg);
                }
            } else {
                LogService::writeLogStr('Cannot add Hesabfa Invoice receipt - Bank Code not defined. order id: ' . $id_order);
            }
        }

        return $ok;
    }

    public function checkOldReceipts($invoiceNumber)
    {
        $hesabfaApi = new HesabfaApiService(new SettingService());
        $response = $hesabfaApi->invoiceGetReceipts($invoiceNumber);

        if ($response->Success) {
            if ($response->Result->FilteredCount > 0)
                return $this->deleteOldReceipts($response->Result->List);
            return true;
        } else {
            $msg = 'Error getting invoice receipts. Error Message: ' . $response->ErrorMessage . ', Error code: ' . $response->ErrorCode . ', invoice number: ' . $invoiceNumber;
            LogService::writeLogStr($msg);
            return false;
        }
    }

    public function deleteOldReceipts($receipts)
    {
        $hesabfaApi = new HesabfaApiService(new SettingService());
        $allDeleted = true;

        foreach ($receipts as $receipt) {
            $response = $hesabfaApi->invoiceDeleteReceipt($receipt->Number);
            if ($response->Success) {
                $msg = 'Invoice receipt deleted. receipt number: ' . $receipt->Number;
                LogService::writeLogStr($msg);
            } else {
                $msg = 'Error deleting invoice receipt. Error Message: ' . $response->ErrorMessage . ', Error code: ' . $response->ErrorCode . ', receipt number: ' . $receipt->Number;
                LogService::writeLogStr($msg);
                $allDeleted = false;
            }
        }
        return $allDeleted;
    }

    public function getBankCode()
    {
        $settingService = new SettingService();
        return $settingService->getPaymentReceiptDestination();
    }

    public function exportReceipts($batch, $totalBatch, $total, $updateCount, $from_date)
    {
        $settingService = new SettingService();
        $statusToSubmitPayment = $settingService->getInWhichStatusAddPaymentReceipt();

        $result = array();
        $result["error"] = false;
        $rpp = 10;

        if ($batch == 1) {
            if (!isset($from_date) || empty($from_date)) {
                $result['error'] = true;
                $result['errorMessage'] = 'Error: Enter correct date.';
                return $result;
            }
            if (!$this->isDateInFiscalYear($from_date)) {
                $result['error'] = true;
                $result['errorMessage'] = 'Error: Selected date is not in Hesabfa financial year.';
                return $result;
            }

            $sql = "SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "orders` WHERE date_add >= '" . $from_date . "'";
            $total = (int)Db::getInstance()->getValue($sql);
            $totalBatch = ceil($total / $rpp);
        }
        LogService::writeLogStr("===== Export Invoice Receipts: part $batch of $totalBatch =====");

        $offset = ($batch - 1) * $rpp;
        $sql = "SELECT id_order FROM `" . _DB_PREFIX_ . "orders`
                                WHERE date_add >= '" . $from_date . "'
                                ORDER BY id_order ASC LIMIT $offset,$rpp";
        $orders = Db::getInstance()->executeS($sql);

        $psFaService = new PsFaService();

        foreach ($orders as $orderDbRow) {
            $id_order = $orderDbRow["id_order"];

            $psFa = $psFaService->getPsFa('order', $id_order);
            if (!$psFa) continue;
            $order = new Order($id_order);
            if (!$order) continue;

            $targetStatus = $order->getHistory($order->id_lang, $statusToSubmitPayment, false);

            if ($statusToSubmitPayment == -1 || count($targetStatus) > 0) {
                if ($this->saveReceipt($id_order))
                    $updateCount++;
            }

        }

        $result["batch"] = $batch;
        $result["totalBatch"] = $totalBatch;
        $result["total"] = $total;
        $result["updateCount"] = $updateCount;
        return $result;
    }

    public function isDateInFiscalYear($date)
    {
        $hesabfaApi = new HesabfaApiService(new SettingService());
        $fiscalYear = $hesabfaApi->settingGetFiscalYear();

        if ($fiscalYear->Success) {
            $fiscalYearStartTimeStamp = strtotime($fiscalYear->Result->StartDate);
            $fiscalYearEndTimeStamp = strtotime($fiscalYear->Result->EndDate);
            $dateTimeStamp = strtotime($date);

            if ($dateTimeStamp >= $fiscalYearStartTimeStamp && $dateTimeStamp <= $fiscalYearEndTimeStamp) {
                return true;
            }
            return false;
        }
        return false;
    }
}