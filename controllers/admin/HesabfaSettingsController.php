<?php

include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/LogService.php');

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
        $this->setTemplate('hesabfaSettings.tpl');
    }

}