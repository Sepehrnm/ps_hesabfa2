<?php

include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/SettingService.php');
include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/HesabfaApiService.php');

class HesabfaSettingsController extends ModuleAdminController
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

        $settingService = new SettingService();
        $this->context->smarty->assign('selectedBarcode', $settingService->getCodeToUseAsBarcode());
        $this->context->smarty->assign('updatePriceFromHesabfaToStore', $settingService->getUpdatePriceFromHesabfaToStore());
        $this->context->smarty->assign('updatePriceFromStoreToHesabfa', $settingService->getUpdatePriceFromStoreToHesabfa());
        $this->context->smarty->assign('updateQuantityFromHesabfaToStore', $settingService->getUpdateQuantityFromHesabfaToStore());
        $this->context->smarty->assign('selectedCustomerAddress', $settingService->getCustomerAddressStatus());
        $this->context->smarty->assign('customerCategoryName', $settingService->getCustomersCategory());
        $this->context->smarty->assign('selectedInvoiceReference', $settingService->getWhichNumberSetAsInvoiceReference());

        $orderStatusOptions = array();
        $order_states = OrderState::getOrderStates(Context::getContext()->language->id);
        foreach ($order_states as $order_state) {
            array_push($orderStatusOptions, array(
                'id' => $order_state['id_order_state'],
                'name' => $order_state['name'],
            ));
        }
        $this->context->smarty->assign('orderStatusOptions', $orderStatusOptions);
        $this->context->smarty->assign('selectedInvoiceStatus', $settingService->getInWhichStatusAddInvoiceToHesabfa());
        $this->context->smarty->assign('selectedReturnInvoiceStatus', $settingService->getInWhichStatusAddReturnInvoiceToHesabfa());
        $this->context->smarty->assign('selectedInvoiceReceiptStatus', $settingService->getInWhichStatusAddPaymentReceipt());

        $paymentMethods = $this->getPaymentMethodsName();
        $banks = $this->getBanksInHesabfa();

        $this->context->smarty->assign('paymentMethods', $paymentMethods);
        $this->context->smarty->assign('banks', $banks);

        //LogService::writeLogObj($banks);

        $selectedBanks = array();
        foreach ($paymentMethods as $p) {
            $this->context->smarty->assign($selectedBanks[$p["id"]], 0);
        }

        $this->setTemplate('hesabfaSettings.tpl');
    }

    public function getPaymentMethodsName()
    {
        $payment_array = array();
        $modules_list = Module::getPaymentModules();

        foreach ($modules_list as $module) {
            $module_obj = Module::getInstanceById($module['id_module']);
            array_push($payment_array, array(
                'name' => $module_obj->displayName,
                'id' => $module['id_module'],
            ));
        }

        return $payment_array;
    }

    public function getBanksInHesabfa() {
        $bank_options = array(array(
            'id_option' => -1,
            'name' => $this->l('Not Selected'),
        ));

        $settingService = new SettingService();
        $api = new HesabfaApiService($settingService);
        $response = $api->settingGetBanks();
        if ($response->Success) {
            foreach ($response->Result as $bank) {
                // show only bank with default currency in hesabfa
                $default_currency = new Currency($settingService->getHesabfaDefaultCurrency());
                if ($bank->Currency == $default_currency->iso_code) {
                    $bankName = $bank->Name;
                    if($bank->Branch) $bankName .= ' - ' . $bank->Branch;
                    if($bank->AccountNumber) $bankName .= ' - ' . $bank->AccountNumber;
                    array_push($bank_options, array(
                        'id' => $bank->Code,
                        'name' => $bankName,
                    ));
                }
            }
        }

        return $bank_options;
    }

    public function  ajaxProcessSaveSettings() {
        echo Tools::getValue('selected-barcode');
        die;
    }

}