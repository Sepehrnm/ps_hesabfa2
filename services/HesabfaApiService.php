<?php

interface IHesabfaApiService {
    public function apiRequest($method, $data = array());
    public function contactGet($code);
    public function contactGetById($idList);
    public function contactGetContacts($queryInfo);
    public function contactSave($contact);
    public function contactBatchSave($contacts);
    public function contactDelete($code);

    public function itemGet($code);
    public function itemGetByBarcode($barcode);
    public function itemGetById($idList);
    public function itemGetItems($queryInfo = null);
    public function itemSave($item);
    public function itemBatchSave($items);
    public function itemDelete($code);
    public function itemUpdateOpeningQuantity($items);

    public function invoiceGet($number, $type = 0);
    public function invoiceGetById($id);
    public function invoiceGetInvoices($queryInfo, $type = 0);
    public function invoiceSave($invoice);
    public function invoiceDelete($number, $type = 0);
    public function invoiceSavePayment($number, $bankCode, $date, $amount, $transactionNumber = null, $description = null);
    public function invoiceGetOnlineInvoiceURL($number, $type = 0);

    public function settingSetChangeHook($url, $hookPassword);
    public function settingGetChanges($start = 0);
    public function settingGetBanks();
    public function settingGetCurrency();
    public function settingGetFiscalYear();
    public function settingGetWarehouses();
    public function fixClearTags();
    public function settingGetSubscriptionInfo();
}

class HesabfaApiService implements IHesabfaApiService
{
    private $settingService;

    public function __construct(ISettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function apiRequest($method, $data = array())
    {
        if ($method == null) {
            return false;
        }

        $data = array_merge(array(
            'apiKey' => $this->settingService->getApiKey(),
            'loginToken' => $this->settingService->getApiToken()
        ), $data);

        $data_string = json_encode($data);

        if ($this->settingService->getDebugMode()) {
            PrestaShopLogger::addLog('ssbhesabfa - Method:' . $method . ' - DataString: ' . serialize($data_string), 1, null, null, null, true);

        }

        $url = 'https://api.hesabfa.com/v1/' . $method;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        $result = curl_exec($ch);
        curl_close($ch);

        if ($this->settingService->getDebugMode()) {
            PrestaShopLogger::addLog('ssbhesabfa - Result: ' . serialize($result), 1, null, null, null, true);
//            var_dump('ssbhesabfa - Result: ' . print_r($result));
        }

        //Maximum request per minutes is 60 times,
//        sleep(1);

        if ($result == null) {
            return 'No response from Hesabfa';
        } else {
            $result = json_decode($result);

            if (!isset($result->Success)) {
                switch ($result->ErrorCode) {
                    case '100':
                        return 'InternalServerError';
                    case '101':
                        return 'TooManyRequests';
                    case '103':
                        return 'MissingData';
                    case '104':
                        return 'MissingParameter' . '. ErrorMessage: ' . $result->ErrorMessage;
                    case '105':
                        return 'ApiDisabled';
                    case '106':
                        return 'UserIsNotOwner';
                    case '107':
                        return 'BusinessNotFound';
                    case '108':
                        return 'BusinessExpired';
                    case '110':
                        return 'IdMustBeZero';
                    case '111':
                        return 'IdMustNotBeZero';
                    case '112':
                        return 'ObjectNotFound' . '. ErrorMessage: ' . $result->ErrorMessage;
                    case '113':
                        return 'MissingApiKey';
                    case '114':
                        return 'ParameterIsOutOfRange' . '. ErrorMessage: ' . $result->ErrorMessage;
                    case '190':
                        return 'ApplicationError' . '. ErrorMessage: ' . $result->ErrorMessage;
                }
            } else {
                return $result;
            }
        }
        return false;
    }

    //Contact functions
    public function contactGet($code)
    {
        $method = 'contact/get';
        $data = array(
            'code' => $code,
        );

        return $this->apiRequest($method, $data);
    }

    public function contactGetById($idList)
    {
        $method = 'contact/getById';
        $data = array(
            'idList' => $idList,
        );

        return $this->apiRequest($method, $data);
    }

    public function contactGetContacts($queryInfo)
    {
        $method = 'contact/getcontacts';
        $data = array(
            'queryInfo' => $queryInfo,
        );

        return $this->apiRequest($method, $data);
    }

    public function contactSave($contact)
    {
        $method = 'contact/save';
        $data = array(
            'contact' => $contact,
        );

        return $this->apiRequest($method, $data);
    }

    public function contactBatchSave($contacts)
    {
        $method = 'contact/batchsave';
        $data = array(
            'contacts' => $contacts,
        );

        return $this->apiRequest($method, $data);
    }

    public function contactDelete($code)
    {
        $method = 'contact/delete';
        $data = array(
            'code' => $code,
        );

        return $this->apiRequest($method, $data);
    }

    public function contactClearTag($codeList)
    {
        $method = 'contact/clearTag';
        $data = array(
            'codes' => $codeList,
        );

        return $this->apiRequest($method, $data);
    }

    //Items functions
    public function itemGet($code)
    {
        $method = 'item/get';
        $data = array(
            'code' => $code,
        );

        return $this->apiRequest($method, $data);
    }

    public function itemGetByBarcode($barcode)
    {
        $method = 'item/getByBarcode';
        $data = array(
            'barcode' => $barcode,
        );

        return $this->apiRequest($method, $data);
    }

    public function itemGetById($idList)
    {
        $method = 'item/getById';
        $data = array(
            'idList' => $idList,
        );

        return $this->apiRequest($method, $data);
    }

    public function itemGetItems($queryInfo = null)
    {
        $method = 'item/getitems';
        $data = array(
            'queryInfo' => $queryInfo,
        );

        return $this->apiRequest($method, $data);
    }

    public function itemSave($item)
    {
        $method = 'item/save';
        $data = array(
            'item' => $item,
        );

        return $this->apiRequest($method, $data);
    }

    public function itemBatchSave($items)
    {
        $method = 'item/batchsave';
        $data = array(
            'items' => $items,
        );

        return $this->apiRequest($method, $data);
    }

    public function itemDelete($code)
    {
        $method = 'item/delete';
        $data = array(
            'code' => $code,
        );

        return $this->apiRequest($method, $data);
    }

    public function itemUpdateOpeningQuantity($items)
    {
        $method = 'item/UpdateOpeningQuantity';
        $data = array(
            'items' => $items,
        );

        return $this->apiRequest($method, $data);
    }

    public function itemClearTag($codeList)
    {
        $method = 'item/clearTag';
        $data = array(
            'codes' => $codeList,
        );

        return $this->apiRequest($method, $data);
    }

    //Invoice functions
    public function invoiceGet($number, $type = 0)
    {
        $method = 'invoice/get';
        $data = array(
            'number' => $number,
            'type' => $type,
        );

        return $this->apiRequest($method, $data);
    }

    public function invoiceGetById($idList)
    {
        $method = 'invoice/getById';
        $data = array(
            'idList' => $idList,
        );

        return $this->apiRequest($method, $data);
    }

    public function invoiceGetInvoices($queryInfo, $type = 0)
    {
        $method = 'invoice/getinvoices';
        $data = array(
            'type' => $type,
            'queryInfo' => $queryInfo,
        );

        return $this->apiRequest($method, $data);
    }

    public function invoiceSave($invoice)
    {
        $method = 'invoice/save';
        $data = array(
            'invoice' => $invoice,
        );

        return $this->apiRequest($method, $data);
    }

    public function invoiceDelete($number, $type = 0)
    {
        $method = 'invoice/delete';
        $data = array(
            'code' => $number,
            'type' => $type,
        );

        return $this->apiRequest($method, $data);
    }

    public function invoiceSavePayment($number, $bankCode, $date, $amount, $transactionNumber = null, $description = null)
    {
        global $paymentCode;
        $method = 'invoice/savepayment';
        $data = array(
            'number' => (int)$number,
            'date' => $date,
            'amount' => $amount,
            'transactionNumber' => $transactionNumber,
            'description' => $description,
        );

        if(substr($bankCode, 0, 4) === 'cash') {
            $paymentCode = array('cashCode' => (int)substr($bankCode, 4));
        } elseif(substr($bankCode, 0, 4) === 'bank') {
            $paymentCode = array('bankCode' => (int)substr($bankCode, 4));
        }

        $data = array_merge($data, $paymentCode);

        return $this->apiRequest($method, $data);
    }

    public function invoiceGetReceipts($number) {
        $method = 'receipt/getReceipts';
        $data = array(
            'type' => 1,
            'queryInfo' => array('filters' => array(array('Property' => 'Invoice.Number', 'Operator' => '=', 'Value' => (int)$number))),
        );

        return $this->apiRequest($method, $data);
    }

    public function invoiceDeleteReceipt($number) {
        $method = 'receipt/delete';
        $data = array(
            'type' => 1,
            'number' => $number,
        );

        return $this->apiRequest($method, $data);
    }

    public function invoiceGetOnlineInvoiceURL($number, $type = 0)
    {
        $method = 'invoice/getonlineinvoiceurl';
        $data = array(
            'number' => $number,
            'type' => $type,
        );

        return $this->apiRequest($method, $data);
    }

    public function invoiceClearTag($numberList, $type = 0)
    {
        $method = 'invoice/clearTag';
        $data = array(
            'numbers' => $numberList,
            'type' => $type
        );

        return $this->apiRequest($method, $data);
    }

    //Settings functions
    public function settingSetChangeHook($url, $hookPassword)
    {
        $method = 'setting/SetChangeHook';
        $data = array(
            'url' => $url,
            'hookPassword' => $hookPassword,
        );

        return $this->apiRequest($method, $data);
    }

    public function settingGetChanges($start = 0)
    {
        $method = 'setting/GetChanges';
        $data = array(
            'start' => $start,
        );

        return $this->apiRequest($method, $data);
    }

    public function settingGetBanks()
    {
        $method = 'setting/getBanks';

        return $this->apiRequest($method);
    }

    public function settingGetCashes()
    {
        $method = 'setting/getCashes';

        return $this->apiRequest($method);
    }

    public function settingGetCurrency()
    {
        $method = 'setting/getCurrency';

        return $this->apiRequest($method);
    }

    public function settingGetFiscalYear()
    {
        $method = 'setting/GetFiscalYear';
        return $this->apiRequest($method);
    }

    public function settingGetWarehouses()
    {
        $method = 'setting/GetWarehouses';
        return $this->apiRequest($method);
    }

    public function fixClearTags()
    {
        $method = 'fix/clearTag';
        return $this->apiRequest($method);
    }

    public function settingGetSubscriptionInfo()
    {
        $method = 'setting/getBusinessInfo';
        return $this->apiRequest($method);
    }

    public function checkForModuleUpdateInfo() {
        try {
            $curl_connection = curl_init('https://hesabfa.com/file/prestashop_module_info.json');
            //curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);

            $data = json_decode(curl_exec($curl_connection), true);
            curl_close($curl_connection);

            if ($data) {
                return Array(
                    "latest_version"=> key_exists("latest_version", $data) ? $data["latest_version"] : null,
                    "notice"=> key_exists("notice", $data) ? $data["notice"] : null,
                    "latest_file_url"=> key_exists("latest_file_url", $data) ? $data["latest_file_url"] : null
                );
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }


}