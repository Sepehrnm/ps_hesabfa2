<?php

include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/LogService.php');
use hesabfa\services\LogService;

class AdminOriginController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init() {
        parent::init();
        $this->bootstrap = true;

        LogService::writeLogStr("test");
    }

    public function initContent() {
        parent::initContent();
        $this->context->smarty->assign(array());
        $this->setTemplate('origin.tpl');
    }
}