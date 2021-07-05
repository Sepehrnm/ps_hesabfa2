<?php

include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/SettingService.php');

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

        $this->setTemplate('hesabfaSettings.tpl');
    }

    public function  ajaxProcessSaveSettings() {
        echo Tools::getValue('selected-barcode');
        die;
    }

}