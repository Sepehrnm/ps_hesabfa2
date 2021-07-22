<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ProductService.php');

class ImportExportController extends ModuleAdminController
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
        $this->setTemplate('import_export.tpl');
    }

    public function  ajaxProcessExportProducts() {
        $batch = Tools::getValue('batch');
        $totalBatch = Tools::getValue('totalBatch');
        $total = Tools::getValue('total');
        $updateCount = Tools::getValue('updateCount');

        $productService = new ProductService();
        $result = $productService->exportProducts($batch, $totalBatch, $total, $updateCount);

        if ($result['error']) {
            if ($updateCount === -1) {
                $result["errorMessage"] = 'Nothing to export';
            } else {
                $result["errorMessage"] = 'Error while trying to export products';
            }
        }

        //echo json_encode($result);
        die(Tools::jsonEncode($result));
    }
}
