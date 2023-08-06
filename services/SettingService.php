<?php

interface ISettingService
{
    public function setSetting($key, $value);

    public function getSetting($key);

    public function setApiKeyAndToken($apiKey, $apiToken);

    public function getApiKey();

    public function getApiToken();

    public function testApiConnection($apiKey, $apiToken);

    public function setInWhichStatusAddInvoiceToHesabfa($status);

    public function getInWhichStatusAddInvoiceToHesabfa();

    public function setInWhichStatusAddReturnInvoiceToHesabfa($status);

    public function getInWhichStatusAddReturnInvoiceToHesabfa();

    public function setInWhichStatusAddPaymentReceipt($status);

    public function getInWhichStatusAddPaymentReceipt();

    public function setCustomersCategory($value);

    public function getCustomersCategory();

    public function setCustomerAddressStatus($status);

    public function getCustomerAddressStatus();

    public function setWhichNumberSetAsInvoiceReference($value);

    public function getWhichNumberSetAsInvoiceReference();

    public function setPaymentReceiptDestination($bankCode);

    public function getPaymentReceiptDestination();

    public function setLastChangesLogId($value);

    public function getLastChangesLogId();

    public function setDebugMode($value);

    public function getDebugMode();

    public function setLiveMode($value);

    public function getLiveMode();

    public function setWebhookPassword($value);

    public function getWebhookPassword();

    public function setCodeToUseAsBarcode($value);

    public function getCodeToUseAsBarcode();

    public function setUpdatePriceFromHesabfaToStore($value);

    public function getUpdatePriceFromHesabfaToStore();

    public function setUpdatePriceFromStoreToHesabfa($value);

    public function getUpdatePriceFromStoreToHesabfa();

    public function setUpdateQuantityFromHesabfaToStore($value);

    public function getUpdateQuantityFromHesabfaToStore();

    public function setHesabfaDefaultCurrency($value);

    public function getHesabfaDefaultCurrency();

    public function setConnectionStatus($value);

    public function getConnectionStatus();

    public function setLastChangesCheckDate($value);

    public function getLastChangesCheckDate();

    public function setDefaultSettings();

    public function deleteAllSettings();
}

class SettingService implements ISettingService
{
    public static $pluginPrefix = "HESABFA_";

    public function __construct()
    {
    }

    public function setSetting($key, $value)
    {
        Configuration::updateValue(self::$pluginPrefix . $key, $value);
    }

    public function getSetting($key)
    {
        return Configuration::get(self::$pluginPrefix . $key);
    }

    public function deleteSetting($key) {
        Configuration::deleteByName(self::$pluginPrefix . $key);
    }

    public function setApiKeyAndToken($apiKey, $apiToken)
    {
        $this->setSetting("API_KEY", $apiKey);
        $this->setSetting("API_TOKEN", $apiToken);
    }

    public function getApiKey()
    {
        return $this->getSetting("API_KEY");
    }

    public function getApiToken()
    {
        return $this->getSetting("API_TOKEN");
    }

    public function testApiConnection($apiKey, $apiToken)
    {
        // TODO: Implement testApiConnection() method.
    }

    public function setLiveMode($value)
    {
        $this->setSetting("LIVE_MODE", $value);
    }

    public function getLiveMode()
    {
        return $this->getSetting("LIVE_MODE");
    }

    public function setInWhichStatusAddInvoiceToHesabfa($status)
    {
        $this->setSetting("IN_WHICH_STATUS_ADD_INVOICE_TO_HESABFA", $status);
    }

    public function getInWhichStatusAddInvoiceToHesabfa()
    {
        return $this->getSetting("IN_WHICH_STATUS_ADD_INVOICE_TO_HESABFA");
    }

    public function setInWhichStatusAddReturnInvoiceToHesabfa($status)
    {
        $this->setSetting("IN_WHICH_STATUS_ADD_RETURN_INVOICE_TO_HESABFA", $status);
    }

    public function getInWhichStatusAddReturnInvoiceToHesabfa()
    {
        return $this->getSetting("IN_WHICH_STATUS_ADD_RETURN_INVOICE_TO_HESABFA");
    }

    public function setInWhichStatusAddPaymentReceipt($status)
    {
        $this->setSetting("IN_WHICH_STATUS_ADD_PAYMENT_RECEIPT", $status);
    }

    public function getInWhichStatusAddPaymentReceipt()
    {
        return $this->getSetting("IN_WHICH_STATUS_ADD_PAYMENT_RECEIPT");
    }

    public function setCustomersCategory($value)
    {
        $this->setSetting("CUSTOMER_CATEGORY", $value);
    }

    public function getCustomersCategory()
    {
        return $this->getSetting("CUSTOMER_CATEGORY");
    }

    public function setWhichNumberSetAsInvoiceReference($value)
    {
        $this->setSetting("NUMBER_TO_SET_AS_INVOICE_REFERENCE", $value);
    }

    public function getWhichNumberSetAsInvoiceReference()
    {
        return $this->getSetting("NUMBER_TO_SET_AS_INVOICE_REFERENCE");
    }

    public function setPaymentReceiptDestination($bankCode)
    {
        $this->setSetting("PAYMENT_RECEIPT_DESTINATION", $bankCode);
    }

    public function getPaymentReceiptDestination()
    {
        return $this->getSetting("PAYMENT_RECEIPT_DESTINATION");
    }

    public function setFreightOption($freightOption)
    {
        $this->setSetting("FREIGHT_INVOICE_OPTION", $freightOption);
    }

    public function getFreightOption()
    {
        return $this->getSetting("FREIGHT_INVOICE_OPTION");
    }

    public function setFreightValue($freightOption)
    {
        $this->setSetting("FREIGHT_INVOICE_VALUE", $freightOption);
    }

    public function getFreightValue()
    {
        return $this->getSetting("FREIGHT_INVOICE_VALUE");
    }

    //new feature
    public function setCardTransferValue($cardTransferOption)
    {
        $this->setSetting("CARD_TRANSFER_VALUE", $cardTransferOption);
    }

    public function setDepositTransferValue($depositTransferOption)
    {
        $this->setSetting("DEPOSIT_TRANSFER_VALUE", $depositTransferOption);
    }

    public function setChequeTransferValue($chequeTransferOption)
    {
        $this->setSetting("CHEQUE_TRANSFER_VALUE", $chequeTransferOption);
    }

    public function setOtherTransferValue($otherTransferOption)
    {
        $this->setSetting("OTHER_TRANSFER_VALUE", $otherTransferOption);
    }

    public function getCardTransferValue()
    {
        return $this->getSetting("CARD_TRANSFER_VALUE");
    }

    public function getDepositTransferValue()
    {
        return $this->getSetting("DEPOSIT_TRANSFER_VALUE");
    }

    public function getChequeTransferValue()
    {
        return $this->getSetting("CHEQUE_TRANSFER_VALUE");
    }

    public function getOtherTransferValue()
    {
        return $this->getSetting("OTHER_TRANSFER_VALUE");
    }

    ////////////////////////////////////////////////////////////

    public function setLastChangesLogId($value)
    {
        $this->setSetting("LAST_CHANGE_LOG_ID", $value);
    }

    public function getLastChangesLogId()
    {
        return $this->getSetting("LAST_CHANGE_LOG_ID");
    }

    public function setDebugMode($value)
    {
        $this->setSetting("DEBUG_MODE", $value);
    }

    public function getDebugMode()
    {
        return $this->getSetting("DEBUG_MODE");
    }

    public function setWebhookPassword($value)
    {
        $this->setSetting("WEBHOOK_PASSWORD", $value);
    }

    public function getWebhookPassword()
    {
        return $this->getSetting("WEBHOOK_PASSWORD");
    }

    public function setCustomerAddressStatus($status)
    {
        $this->setSetting("CUSTOMER_ADDRESS_STATUS", $status);
    }

    public function getCustomerAddressStatus()
    {
        return $this->getSetting("CUSTOMER_ADDRESS_STATUS");
    }

    public function setCodeToUseAsBarcode($value)
    {
        $this->setSetting("CODE_TO_USE_AS_BARCODE", $value);
    }

    public function getCodeToUseAsBarcode()
    {
        return $this->getSetting("CODE_TO_USE_AS_BARCODE");
    }

    public function setUpdatePriceFromHesabfaToStore($value)
    {
        $this->setSetting("UPDATE_PRICE_FROM_HESABFA_TO_STORE", $value);
    }

    public function getUpdatePriceFromHesabfaToStore()
    {
        return $this->getSetting("UPDATE_PRICE_FROM_HESABFA_TO_STORE");
    }

    public function setUpdatePriceFromStoreToHesabfa($value)
    {
        $this->setSetting("UPDATE_PRICE_FROM_STORE_TO_HESABFA", $value);
    }

    public function getUpdatePriceFromStoreToHesabfa()
    {
        return $this->getSetting("UPDATE_PRICE_FROM_STORE_TO_HESABFA");
    }

    public function setUpdateQuantityFromHesabfaToStore($value)
    {
        $this->setSetting("UPDATE_QUANTITY_FROM_HESABFA_TO_STORE", $value);
    }

    public function getUpdateQuantityFromHesabfaToStore()
    {
        return $this->getSetting("UPDATE_QUANTITY_FROM_HESABFA_TO_STORE");
    }

    public function setHesabfaDefaultCurrency($value)
    {
        $this->setSetting("DEFAULT_CURRENCY", $value);
    }

    public function getHesabfaDefaultCurrency()
    {
        return $this->getSetting("DEFAULT_CURRENCY");
    }

    public function setConnectionStatus($value)
    {
        $this->setSetting("CONNECTED", $value);
    }

    public function getConnectionStatus()
    {
        return $this->getSetting("CONNECTED");
    }

    public function setLastChangesCheckDate($value)
    {
        $this->setSetting("LAST_CHANGES_CHECK_DATE", $value);
    }

    public function getLastChangesCheckDate()
    {
        return $this->getSetting("LAST_CHANGES_CHECK_DATE");
    }

    public function setDefaultSettings()
    {
        $this->setLiveMode(false);
        $this->setDebugMode(false);
        $this->setApiKeyAndToken(null, null);
        $this->setWebhookPassword(bin2hex(openssl_random_pseudo_bytes(16)));
        $this->setCustomerAddressStatus(1);
        $this->setCustomersCategory('Online Store Customer\'s');
        $this->setCodeToUseAsBarcode(2);
        $this->setUpdatePriceFromHesabfaToStore(1);
        $this->setUpdatePriceFromStoreToHesabfa(1);
        $this->setUpdateQuantityFromHesabfaToStore(1);
        $this->setLastChangesLogId(0);
        $this->setInWhichStatusAddInvoiceToHesabfa(-1);
        $this->setInWhichStatusAddReturnInvoiceToHesabfa(7);
        $this->setInWhichStatusAddPaymentReceipt(2);
        $this->setWhichNumberSetAsInvoiceReference(0);
    }

    public function deleteAllSettings()
    {
        $sql = "SELECT `name` FROM `" . _DB_PREFIX_ . "configuration`
                WHERE `name` LIKE '%HESABFA_%'";
        $configurations = Db::getInstance()->ExecuteS($sql);

        foreach ($configurations as $configuration) {
            Configuration::deleteByName($configuration['name']);
        }
    }

    public function deleteSomeSettings()
    {
        $this->deleteSetting("CONNECTED");
        $this->deleteSetting("API_KEY");
        $this->deleteSetting("API_TOKEN");
    }

}