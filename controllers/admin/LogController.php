<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');

class LogController  extends ModuleAdminController
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
        $log = LogService::readLog();
        $this->context->smarty->assign(array());
        $this->context->smarty->assign('log', $log);
        $this->context->smarty->assign('logFilePath', LogService::getLogFilePath());
        $this->context->smarty->assign('tokenHesabfaModuleConfigure', Tools::getAdminTokenLite('AdminModules'));
        $this->setTemplate('log.tpl');
    }

    public function ajaxProcessClearLog() {
        LogService::clearLog();
        die();
    }


}