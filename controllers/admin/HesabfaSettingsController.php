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
        $this->context->smarty->assign('tokenHesabfaModuleConfigure', Tools::getAdminTokenLite('AdminModules'));

        $this->context->smarty->assign('selectedFreightOption', $settingService->getFreightOption());
        $this->context->smarty->assign('selectedFreightValue', $settingService->getFreightValue());

        $this->context->smarty->assign('selectedCardTransferOption', $settingService->getCardTransferValue());
        $this->context->smarty->assign('selectedChequeTransferOption', $settingService->getChequeTransferValue());
        $this->context->smarty->assign('selectedDepositTransferOption', $settingService->getDepositTransferValue());
        $this->context->smarty->assign('selectedOtherTransferOption', $settingService->getOtherTransferValue());

        $this->context->smarty->assign('selectedProjectTitle', $settingService->getInvoiceProject());
        $this->context->smarty->assign('selectedSalesmanName', $settingService->getInvoiceSalesman());

//        LogService::writeLogStr($settingService->getUpdatePriceFromHesabfaToStore());

        $orderStatusOptions = array();
        $order_states = OrderState::getOrderStates(Context::getContext()->language->id);
        array_push($orderStatusOptions, array(
            'id' => -1,
            'name' => $this->l('Save in all statuses'),
        ));
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

        $this->context->smarty->assign('deleteOldReceipts', $settingService->getDeleteOldReceiptsStatus());
        $this->context->smarty->assign('saveReceiptBySubmittingInvoiceManually', $settingService->getSaveReceiptBySubmittingInvoiceManuallyStatus());

        $banks = $this->getBanksInHesabfa();
        $projects = $this->getProjectsInHesabfa();
        $salesmen = $this->getSalesmenInHesabfa();

        $this->context->smarty->assign('banks', $banks);
        $this->context->smarty->assign('projects', $projects);
        $this->context->smarty->assign('salesmen', $salesmen);

        $selectedBankId = $settingService->getPaymentReceiptDestination();
        $this->context->smarty->assign('selectedBankId', $selectedBankId);

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
            'id' => -1,
            'name' => $this->l('Not Selected'),
        ));

        $settingService = new SettingService();
        $api = new HesabfaApiService($settingService);
        $response = $api->settingGetBanks();
        $response2 = $api->settingGetCashes();
        if ($response->Success || $response2->Success) {
            foreach ($response->Result as $bank) {
                // show only bank with default currency in hesabfa
                $default_currency = new Currency($settingService->getHesabfaDefaultCurrency());
                if ($bank->Currency == $default_currency->iso_code) {
                    $bankName = $bank->Name;
                    if($bank->Branch) $bankName .= ' - ' . $bank->Branch;
                    if($bank->AccountNumber) $bankName .= ' - ' . $bank->AccountNumber;
                    array_push($bank_options, array(
                        'id' => 'bank'.$bank->Code,
                        'name' => $bankName,
                    ));
                }
            }
            foreach ($response2->Result as $cash) {
                // show only cash with default currency in hesabfa
                $default_currency = new Currency($settingService->getHesabfaDefaultCurrency());
                if ($cash->Currency == $default_currency->iso_code) {
                    $cashName = $cash->Name;
                    array_push($bank_options, array(
                        'id' => 'cash'.$cash->Code,
                        'name' => $cashName,
                    ));
                }
            }
        }

        return $bank_options;
    }

    public function getProjectsInHesabfa() {
        $project_options = array(array(
            'id' => -1,
            'title' => $this->l('Not Selected'),
        ));

        $settingService = new SettingService();
        $api = new HesabfaApiService($settingService);
        $response = $api->settingGetProjects();
        if ($response->Success) {
            foreach ($response->Result as $project) {
                $projectTitle = $project->Title;
                array_push($project_options, array(
                    'id' => $project->Id,
                    'title' => $projectTitle,
                ));
            }
        }

        return $project_options;
    }

    public function getSalesmenInHesabfa() {
        $salesman_options = array(array(
            'code' => -1,
            'name' => $this->l('Not Selected'),
        ));

        $settingService = new SettingService();
        $api = new HesabfaApiService($settingService);
        $response = $api->settingGetSalesmen();
        if ($response->Success) {
            foreach ($response->Result as $salesman) {
                $salesmanName = $salesman->Name;
                array_push($salesman_options, array(
                    'code' => $salesman->Code,
                    'name' => $salesmanName,
                ));
            }
        }

        return $salesman_options;
    }

    public function  ajaxProcessSaveSettings() {
        $formData = Tools::getValue('formData');

        $settingService = new SettingService();
        $settingService->setCodeToUseAsBarcode($formData["selectedBarcode"]);
        $settingService->setUpdatePriceFromHesabfaToStore($formData["updatePriceFromHesabfaToStore"]);
        $settingService->setUpdatePriceFromStoreToHesabfa($formData["updatePriceFromStoreToHesabfa"]);
        $settingService->setUpdateQuantityFromHesabfaToStore($formData["updateQuantityFromHesabfaToStore"]);

        $settingService->setCustomerAddressStatus($formData["selectedCustomerAddress"]);
        $settingService->setCustomersCategory($formData["customerCategory"]);

        $settingService->setWhichNumberSetAsInvoiceReference($formData["invoiceReference"]);
        $settingService->setInWhichStatusAddInvoiceToHesabfa($formData["invoiceStatus"]);
        $settingService->setInWhichStatusAddReturnInvoiceToHesabfa($formData["returnInvoiceStatus"]);

        $settingService->setInWhichStatusAddPaymentReceipt($formData["invoiceReceiptStatus"]);

        $settingService->setDeleteOldReceiptsStatus($formData["deleteOldReceipts"]);
        $settingService->setSaveReceiptBySubmittingInvoiceManuallyStatus($formData["saveReceiptBySubmittingInvoiceManually"]);

        $settingService->setPaymentReceiptDestination($formData["paymentReceiptBankCode"]);

        $settingService->setFreightOption($formData["invoiceFreightStatus"]);
        $settingService->setFreightValue($formData["freightInputValue"]);

        $settingService->setInvoiceProject($formData["projectTitle"]);
        $settingService->setInvoiceSalesman($formData["salesmanName"]);

        $settingService->setCardTransferValue($formData["cardTransferOption"]);
        $settingService->setChequeTransferValue($formData["chequeTransferOption"]);
        $settingService->setDepositTransferValue($formData["depositTransferOption"]);
        $settingService->setOtherTransferValue($formData["otherTransferOption"]);

        echo true;
        die;
    }

}