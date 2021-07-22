<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingsService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');

class ProductService
{
    public $idLang;

    public function __construct()
    {
        $this->idLang = Configuration::get('PS_LANG_DEFAULT');
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
                $combinations = $product->getAttributesResume($this->idLang);
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
        $apiService = new HesabfaApiService(new SettingService());
        $psFaService = new PsFaService();
        $response = $apiService->itemBatchSave($items);

        if ($response->Success) {
            foreach ($response->Result as $item)
                $psFaService->saveProduct($item);
            return true;
        } else {
            LogService::writeLogStr("Cannot add/update Hesabfa items. Error Code: " . (string)$response->ErrorCode . ". Error Message: $response->ErrorMessage.");
            return false;
        }
    }

    private function mapProduct($product, $id, $new = true)
    {
        $psFaService = new PsFaService();
        $code = $new ? null : $psFaService->getProductCodeByPrestaId($id);

        $hesabfaItem = array(
            'Code' => $code,
            'Name' => mb_substr($product->name[$this->idLang], 0, 99),
            'PurchasesTitle' => mb_substr($product->name[$this->idLang], 0, 99),
            'SalesTitle' => mb_substr($product->name[$this->idLang], 0, 99),
            'ItemType' => ($product->is_virtual == 1 ? 1 : 0),
            'Barcode' => $this->getBarcode($product),
            'Tag' => json_encode(array('id_product' => $id, 'id_attribute' => 0)),
            'NodeFamily' => $this->getCategoryPath($product->id_category_default),
            'ProductCode' => $id
        );

        $settingsService = new SettingService();
        if ($settingsService->getUpdatePriceFromStoreToHesabfa())
            $hesabfaItem['SellPrice'] = $this->getPriceInHesabfaDefaultCurrency($product->price);

        return $hesabfaItem;
    }

    private function mapProductCombination($product, $combination, $id, $new = true)
    {
        $psFaService = new PsFaService();
        $code = $new ? null : $psFaService->getProductCodeByPrestaId($id, $combination['id_product_attribute']);

        $fullName = mb_substr($product->name[$this->idLang] . ' - ' . $combination['attribute_designation'], 0, 99);

        $hesabfaItem = array(
            'Code' => $code,
            'Name' => $fullName,
            'PurchasesTitle' => $fullName,
            'SalesTitle' => $fullName,
            'ItemType' => ($product->is_virtual == 1 ? 1 : 0),
            'Barcode' => $this->getBarcode($product, $combination['id_product_attribute']),
            'Tag' => json_encode(array('id_product' => $id, 'id_attribute' => (int)$combination['id_product_attribute'])),
            'NodeFamily' => $this->getCategoryPath($product->id_category_default),
            'ProductCode' => $combination['id_product_attribute']
        );

        $settingsService = new SettingService();
        if ($settingsService->getUpdatePriceFromStoreToHesabfa())
            $hesabfaItem['SellPrice'] = $this->getPriceInHesabfaDefaultCurrency($product->price + $combination['price']);

        return $hesabfaItem;
    }

    private function getBarcode($product, $id_attribute = 0)
    {
        if (!isset($product))
            return false;

        $settingService = new SettingService();
        $codeToUseAsBarcode = $settingService->getCodeToUseAsBarcode();

        if ($id_attribute == 0) {
            switch ($codeToUseAsBarcode) {
                case 1:
                    return $product->reference;
                case 2:
                    return $product->upc;
                case 3:
                    return $product->ean13;
                case 4:
                    return $product->isbn;
            }
        } else {
            $product_attribute = $product->getAttributeCombinationsById($id_attribute, $this->idLang);
            switch ($codeToUseAsBarcode) {
                case 1:
                    return $product_attribute[0]['reference'];
                case 2:
                    return $product_attribute[0]['upc'];
                case 3:
                    return $product_attribute[0]['ean13'];
                case 4:
                    return $product_attribute[0]['isbn'];
            }
        }

        return false;
    }

    private function getCategoryPath($id_category)
    {
        if ($id_category < 2) {
            $sign = ' : '; // You can customize your sign which splits categories
            //array_pop($this->categoryArray);
            $categoryArray = array_reverse($this->categoryArray);
            $categoryPath = '';
            foreach ($categoryArray as $categoryName) {
                $categoryPath .= $categoryName . $sign;
            }
            $this->categoryArray = array();
            return Tools::substr($categoryPath, 0, -Tools::strlen($sign));
        } else {
            $category = new Category($id_category, Context::getContext()->language->id);
            $this->categoryArray[] = $category->name;
            return $this->getCategoryPath($category->id_parent);
        }
    }

    public function getPriceInHesabfaDefaultCurrency($price)
    {
        if (!isset($price))
            return false;

        $settingService = new SettingService();

        $currency = new Currency($settingService->getHesabfaDefaultCurrency());
        $price *= $currency->conversion_rate;
        return $price;
    }

    public static function getPriceInPrestashopDefaultCurrency($price)
    {
        if (!isset($price))
            return false;

        $settingService = new SettingService();

        $currency = new Currency($settingService->getHesabfaDefaultCurrency());
        $price /= $currency->conversion_rate;
        return $price;
    }

    public function deleteProduct($productId)
    {
        $psFaService = new PsFaService();
        $psFaObjects = $psFaService->getProductAndCombinations($productId);

        foreach ($psFaObjects as $psFaObject) {
            $hesabfaApi = new HesabfaApiService(new SettingService());
            $response = $hesabfaApi->itemDelete($psFaObject->idHesabfa);
            if ($response->Success) {
                $msg = "Product successfully deleted, product Hesabfa code: " . $psFaObject->idHesabfa . ", product Prestashop id: " . $psFaObject->idPs . "-" . $psFaObject->idPsAttribute;
                LogService::writeLogStr($msg);
            } else {
                $msg = 'Cannot delete product in Hesabfa.Error Code: ' . $response->ErrorCode . ', Error Message: ' . $response->ErrorMessage . ', product Hesabfa code: ' . $psFaObject->idHesabfa . ", product Prestashop id: " . $psFaObject->idPs . "-" . $psFaObject->idPsAttribute;
                LogService::writeLogStr($msg);
            }

            $psFaService->delete($psFaObject);
        }
    }

    public function deleteRemovedCombinationsOfProduct($productId)
    {
        $product = new Product($productId);
        $combinations = $product->getAttributesResume($this->idLang);

        $psFaService = new PsFaService();
        $psFaObjects = $psFaService->getProductAndCombinations($productId);

        foreach ($psFaObjects as $psFaObject) {
            $found = false;
            if($psFaObject->idPsAttribute == 0)
                $found = true;
            foreach ($combinations as $combination) {
                if ($combination["id_product_attribute"] == $psFaObject->idPsAttribute)
                    $found = true;
            }
            if (!$found)
                $this->deleteProductCombination($psFaObject->idPs, $psFaObject->idPsAttribute);
        }
    }

    public function deleteProductCombination($productId, $combinationId)
    {
        $psFaService = new PsFaService();
        $psFaObject = $psFaService->getPsFa('product', $productId, $combinationId);

        $hesabfaApi = new HesabfaApiService(new SettingService());
        $response = $hesabfaApi->itemDelete($psFaObject->idHesabfa);
        if ($response->Success) {
            $msg = "Product successfully deleted, product Hesabfa code: " . $psFaObject->idHesabfa . ", product Prestashop id: " . $psFaObject->idPs . "-" . $psFaObject->idPsAttribute;
            LogService::writeLogStr($msg);
        } else {
            $msg = 'Cannot delete product in Hesabfa.Error Code: ' . $response->ErrorCode . ', Error Message: ' . $response->ErrorMessage . ', product Hesabfa code: ' . $psFaObject->idHesabfa . ", product Prestashop id: " . $psFaObject->idPs . "-" . $psFaObject->idPsAttribute;
            LogService::writeLogStr($msg);
        }

        $psFaService->delete($psFaObject);
    }

    public function exportProducts($batch, $totalBatch, $total, $updateCount)
    {
        LogService::writeLogStr("===== Export Products =====");
        $psFaService = new PsFaService();

        $result = array();
        $result["error"] = false;
        $rpp = 500;

        if ($batch == 1) {
            $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'product`';
            $total = (int)Db::getInstance()->getValue($sql);
            $totalBatch = ceil($total / $rpp);
        }

        $offset = ($batch - 1) * $rpp;
        $sql = "SELECT id_product FROM `" . _DB_PREFIX_ . "product` ORDER BY 'id_product' ASC LIMIT $offset,$rpp";
        $products = Db::getInstance()->executeS($sql);
        $items = array();

        foreach ($products as $item) {
            $id_product = (int)$item["id_product"];
            $product = new Product($id_product);

            // Set base product
            $id_obj = $psFaService->getPsFaId('product', $id_product, 0);
            if (!$id_obj) {
                $hesabfaItem = $this->mapProduct($product, $id_product);
                array_push($items, $hesabfaItem);
                $updateCount++;
            }

            // Set variations
            if ($product->hasAttributes() > 0) {
                $variations = $product->getAttributesResume($this->idLang);
                foreach ($variations as $variation) {
                    $id_obj = $psFaService->getPsFaId('product', $id_product, $variation['id_product_attribute']);
                    if (!$id_obj) {
                        $hesabfaItem = $this->mapProductCombination($product, $variation, $id_product);
                        array_push($items, $hesabfaItem);
                        $updateCount++;
                    }
                }
            }
        }

        LogService::writeLogObj($items[0]);
        LogService::writeLogStr("================================");
        LogService::writeLogObj($items[1]);

        if (!empty($items)) {
            $hesabfa = new HesabfaApiService(new SettingService());
            $response = $hesabfa->itemBatchSave($items);
            if ($response->Success) {
                foreach ($response->Result as $item) {
                    $psFaService->saveProduct($item);
                }
            } else {
                LogService::writeLogStr("Cannot add bulk item. Error Message: " . (string)$response->ErrorMessage . ". Error Code: " . (string)$response->ErrorCode . ".");
            }
            sleep(2);
        }

        $result["batch"] = $batch;
        $result["totalBatch"] = $totalBatch;
        $result["total"] = $total;
        $result["updateCount"] = $updateCount;
        return $result;
    }
}