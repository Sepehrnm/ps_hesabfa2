<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingsService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/WebhookService.php');

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
            if (Pack::isPack($productId))
                continue;
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
            return true;

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
                case 0:
                    return $product->reference;
                case 1:
                    return $product->upc;
                case 2:
                    return $product->ean13;
                case 3:
                    return $product->isbn;
            }
        } else {
            $product_attribute = $product->getAttributeCombinationsById($id_attribute, $this->idLang);
            switch ($codeToUseAsBarcode) {
                case 0:
                    return $product_attribute[0]['reference'];
                case 1:
                    return $product_attribute[0]['upc'];
                case 2:
                    return $product_attribute[0]['ean13'];
                case 3:
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
            if ($psFaObject->idPsAttribute == 0)
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
        LogService::writeLogStr("===== Export Products: part $batch =====");
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

            if(Pack::isPack($id_product))
                continue;

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

        if (!empty($items)) {
            $hesabfa = new HesabfaApiService(new SettingService());
            $response = $hesabfa->itemBatchSave($items);
            if ($response->Success) {
                LogService::writeLogStr("*** bulk insert ***");
                $psFaService->saveProductBatch($response->Result);
            } else {
                $result["error"] = true;
                $result["errorMessage"] = "Cannot add bulk item. Error Message: " . (string)$response->ErrorMessage . ". Error Code: " . (string)$response->ErrorCode . ".";
                LogService::writeLogStr($result["errorMessage"]);
            }
            sleep(2);
        }

        $result["batch"] = $batch;
        $result["totalBatch"] = $totalBatch;
        $result["total"] = $total;
        $result["updateCount"] = $updateCount;
        return $result;
    }

    public function exportProductsOpeningQuantity($batch, $totalBatch, $total)
    {
        LogService::writeLogStr("===== Export Products Opening Quantity: part $batch =====");
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

            if(Pack::isPack($id_product))
                continue;

            if ($product->hasAttributes() == 0) {
                $obj = $psFaService->getPsFa('product', $id_product, 0);
                if ($obj != null) {
                    $quantity = StockAvailable::getQuantityAvailableByProduct($item['id_product']);
                    if (is_object($product) && is_object($obj) && $quantity > 0 && $product->price > 0) {
                        array_push($items, array(
                            'Code' => $obj->idHesabfa,
                            'Quantity' => $quantity,
                            'UnitPrice' => $this->getPriceInHesabfaDefaultCurrency($product->price),
                        ));
                    }
                }
            } else {
                $variations = $product->getAttributesResume($this->idLang);
                foreach ($variations as $variation) {
                    $obj = $psFaService->getPsFa('product', $id_product, $variation['id_product_attribute']);
                    if ($obj != null) {
                        $quantity = StockAvailable::getQuantityAvailableByProduct($item['id_product'], $variation['id_product_attribute']);
                        if (is_object($obj) && $quantity > 0 && $product->price + $variation['price'] > 0) {
                            array_push($items, array(
                                'Code' => $obj->idHesabfa,
                                'Quantity' => $quantity,
                                'UnitPrice' => $this->getPriceInHesabfaDefaultCurrency($product->price + $variation['price']),
                            ));
                        }
                    }
                }
            }
        }

        if (!empty($items)) {
            $hesabfa = new HesabfaApiService(new SettingService());
            $response = $hesabfa->itemUpdateOpeningQuantity($items);
            if ($response->Success) {
                // continue
            } else {
                $result["error"] = true;
                $result["errorMessage"] = "Cannot set Opening quantity. Error Message: " . (string)$response->ErrorMessage . ". Error Code: " . (string)$response->ErrorCode . ".";
                LogService::writeLogStr($result["errorMessage"]);
            }
            sleep(2);
        }

        $result["batch"] = $batch;
        $result["totalBatch"] = $totalBatch;
        $result["total"] = $total;
        return $result;
    }

    public function syncProductsPriceAndQuantity($batch, $totalBatch, $total) {
        LogService::writeLogStr("===== Sync products price and quantity from hesabfa to store: part $batch =====");
        $result = array();
        $result["error"] = false;

        $hesabfa = new HesabfaApiService(new SettingService());
        $filters = array(array("Property" => "ItemType", "Operator" => "=", "Value" => 0));
        $rpp = 500;

        if ($batch == 1) {
            $total = 0;
            $response = $hesabfa->itemGetItems(array('Take' => 1, 'Filters' => $filters));
            if ($response->Success) {
                $total = $response->Result->FilteredCount;
                $totalBatch = ceil($total / $rpp);
            } else {
                $result["error"] = true;
                $result["errorMessage"] = "Error while trying to get products for sync. Error Message: $response->ErrorMessage. Error Code: $response->ErrorCode.";
                LogService::writeLogStr($result["errorMessage"]);
                return $result;
            };
        }

        $offset = ($batch - 1) * $rpp;
        $response = $hesabfa->itemGetItems(array('Skip' => $offset, 'Take' => $rpp, 'SortBy' => 'Id', 'Filters' => $filters));
        if ($response->Success) {
            $products = $response->Result->List;
            foreach ($products as $product) {
                WebhookService::setItemChanges($product);
            }
            sleep(1);
        } else {
            $result["error"] = true;
            $result["errorMessage"] = "Error while trying to get products for sync. Error Message: $response->ErrorMessage. Error Code: $response->ErrorCode.";
            LogService::writeLogStr($result["errorMessage"]);
            return $result;
        }

        $result["batch"] = $batch;
        $result["totalBatch"] = $totalBatch;
        $result["total"] = $total;
        return $result;
    }

}