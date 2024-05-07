<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ProductService.php');

class SynchronizationController extends ModuleAdminController
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
        $this->context->smarty->assign('tokenHesabfaModuleConfigure', Tools::getAdminTokenLite('AdminModules'));
        $this->setTemplate('synchronization.tpl');
    }

    public function ajaxProcessSyncProducts() {
        $batch = Tools::getValue('batch');
        $totalBatch = Tools::getValue('totalBatch');
        $total = Tools::getValue('total');

        $productService = new ProductService();
        $result = $productService->syncProductsPriceAndQuantity($batch, $totalBatch, $total);
        if(_PS_VERSION_ > '1.8') {
            //8.0
            die(json_encode($result));
        } else {
            //1.7
            die(Tools::jsonEncode($result));
        }
    }

}