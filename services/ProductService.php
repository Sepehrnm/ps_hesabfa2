<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingsService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');

class ProductService
{
    public $moduleObj = null;

    public function __construct($moduleObj)
    {
        $this->$moduleObj = $moduleObj;
    }

    public function saveProducts($productIdArray)
    {
        if (!isset($productIdArray) || !is_array($productIdArray) || empty($productIdArray) || $productIdArray[0] == null)
            return false;

        $items = array();
        foreach ($productIdArray as $productId) {
            $product = new Product($productId);

            // set base product
            $items[] = $this->mapProduct($product, $productId, false);

            // set attributes
            if ($product->hasAttributes() > 0) {
                $combinations = $product->getAttributesResume($this->moduleObj->id_default_lang);
                foreach ($combinations as $combination) {
                    $items[] = $this->mapProductCombination($product, $combination, $productId, false);
                }
            }
        }

        if (count($items) === 0)
            return false;

        if (!$this->saveProductsToHesabfa($items))
            return false;
        return true;
    }

    private function saveProductsToHesabfa($items)
    {
        $apiService = new HesabfaApiService();
        $psFaService = new PsFaService();
        $response = $apiService->itemBatchSave($items);

        if ($response->Success) {
            foreach ($response->Result as $item)
                $psFaService->save($item);
            return true;
        } else {
            LogService::writeLogStr("Cannot add/update Hesabfa items. Error Code: " . (string)$response->ErrorCode . ". Error Message: $response->ErrorMessage.");
            return false;
        }
    }

    public function mapProduct($product, $id, $new = true)
    {
        $psFaService = new PsFaService();
        $code = $new ? null : $psFaService->getProductCodeByPrestaId($id);

        $hesabfaItem = array(
            'Code' => $code,
            'Name' => mb_substr($product->name[$this->moduleObj->id_default_lang], 0, 99),
            'PurchasesTitle' => mb_substr($product->name[$this->moduleObj->id_default_lang], 0, 99),
            'SalesTitle' => mb_substr($product->name[$this->moduleObj->id_default_lang], 0, 99),
            'ItemType' => ($product->is_virtual == 1 ? 1 : 0),
            'Barcode' => $this->getBarcode($id),
            'Tag' => json_encode(array('id_product' => $id, 'id_attribute' => 0)),
            'NodeFamily' => $this->getCategoryPath($product->id_category_default),
            'ProductCode' => $id
        );

        $settingsService = new SettingService();
        if ($settingsService->getUpdatePriceFromStoreToHesabfa())
            $hesabfaItem['SellPrice'] = $this->getPriceInHesabfaDefaultCurrency($product->price);

        return $hesabfaItem;
    }

    public function mapProductCombination($product, $combination, $id, $new = true)
    {
        $psFaService = new PsFaService();
        $code = $new ? null : $psFaService->getProductCodeByPrestaId($id, $combination['id_product_attribute']);

        $fullName = mb_substr($product->name[$this->id_default_lang] . ' - ' . $combination['attribute_designation'], 0, 99);

        $hesabfaItem = array(
            'Code' => $code,
            'Name' => $fullName,
            'PurchasesTitle' => $fullName,
            'SalesTitle' => $fullName,
            'ItemType' => ($product->is_virtual == 1 ? 1 : 0),
            'Barcode' => $this->getBarcode($id, $combination['id_product_attribute']),
            'Tag' => json_encode(array('id_product' => $id, 'id_attribute' => (int)$combination['id_product_attribute'])),
            'NodeFamily' => $this->getCategoryPath($product->id_category_default),
            'ProductCode' => $combination['id_product_attribute']
        );

        $settingsService = new SettingService();
        if ($settingsService->getUpdatePriceFromStoreToHesabfa())
            $hesabfaItem['SellPrice'] = $this->getPriceInHesabfaDefaultCurrency($product->price + $combination['price']);

        return $hesabfaItem;
    }

}