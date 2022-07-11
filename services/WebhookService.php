<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ProductService.php');

class WebhookService
{
    public $invoicesObjectId = array();
    public $invoiceItemsCode = array();
    public $itemsObjectId = array();
    public $contactsObjectId = array();

    public function __construct()
    {
        $settingService = new SettingService();
        $hesabfaApi = new HesabfaApiService($settingService);
        $psFaService = new PsFaService();

        $lastChange = $settingService->getLastChangesLogId();
        $changes = $hesabfaApi->settingGetChanges($lastChange + 1);
        if ($changes->Success) {
            foreach ($changes->Result as $item) {
                if (!$item->API) {
                    switch ($item->ObjectType) {
                        case 'Invoice':
                            if ($item->Action == 123) { // delete invoice link
                                $psFa = $psFaService->getPsFaByHesabfaId('order', $item->Extra2);
                                if($psFa)
                                    $psFaService->delete($psFa);
                                break;
                            }

                            $this->invoicesObjectId[] = $item->ObjectId;
                            foreach (explode(',', $item->Extra) as $invoiceItem) {
                                if ($invoiceItem != ''){
                                    $this->invoiceItemsCode[] = $invoiceItem;
                                }
                            }

                            break;
                        case 'Product':
                            if ($item->Action == 53) { // delete product link
                                $psFa = $psFaService->getPsFaByHesabfaId('product', $item->Extra);
                                if($psFa)
                                    $psFaService->delete($psFa);
                                break;
                            }

                            $this->itemsObjectId[] = $item->ObjectId;
                            break;
                        case 'Contact':
                            if ($item->Action == 33) { // delete contact link
                                $psFa = $psFaService->getPsFaByHesabfaId('customer', $item->Extra);
                                if($psFa)
                                    $psFaService->delete($psFa);
                                break;
                            }

                            $this->contactsObjectId[] = $item->ObjectId;
                            break;
                    }
                }
            }

            // remove duplicate values
            $this->invoiceItemsCode = array_unique($this->invoiceItemsCode);
            $this->contactsObjectId = array_unique($this->contactsObjectId);
            $this->itemsObjectId = array_unique($this->itemsObjectId);
            $this->invoicesObjectId = array_unique($this->invoicesObjectId);

            $this->setChanges();
            //set LastChange ID
            $lastChange = end($changes->Result);
            if (is_object($lastChange)) {
                $settingService->setLastChangesLogId($lastChange->Id);
            }

        } else {
            LogService::writeLogStr('Cannot check last changes. Error Message: ' . $changes->ErrorMessage. 'Error Code: ' . $changes->ErrorCode);
        }
    }

    public function setChanges() {
        //Invoices
        if (!empty($this->invoicesObjectId)) {
            $invoices = $this->getObjectsByIdList($this->invoicesObjectId, 'invoice');
            if ($invoices != false) {
                foreach ($invoices as $invoice) {
                    $this->setInvoiceChanges($invoice);
                }
            }
        }

        //Contacts
        if (!empty($this->contactsObjectId)) {
            $contacts = $this->getObjectsByIdList($this->contactsObjectId, 'contact');
            if ($contacts != false) {
                foreach ($contacts as $contact) {
                    $this->setContactChanges($contact);
                }
            }
        }

        //Items
        $items = array();
        if (!empty($this->itemsObjectId)) {
            $objects = $this->getObjectsByIdList($this->itemsObjectId, 'item');
            if ($objects != false) {
                foreach ($objects as $object) {
                    array_push($items, $object);
                }
            }
        }

        if (!empty($this->invoiceItemsCode)) {
            $objects = $this->getObjectsByCodeList($this->invoiceItemsCode);
            if ($objects != false) {
                foreach ($objects as $object) {
                    array_push($items, $object);
                }
            }
        }

        if (!empty($items)) {
            foreach ($items as $item) {
                $this->setItemChanges($item);
            }
        }

        return true;
    }

    public function setInvoiceChanges($invoice)
    {
        if (!is_object($invoice)) {
            return false;
        }

        $psFaService = new PsFaService();

        //1.set new Hesabfa Invoice Code if changes
        $number = $invoice->Number;
        $json = json_decode($invoice->Tag);
        if (is_object($json)) {
            $id_order = $json->id_order;
        } else {
            $id_order = 0;
        }

        if ($invoice->InvoiceType == 0) {
            //check if Tag not set in hesabfa
            if ($id_order == 0) {
                return false;
            } else {
                //check if order exist in prestashop
                $psFa = $psFaService->getPsFa('order', $id_order);
                if ($psFa->id > 0) {
                    if ($psFa->idHesabfa != $number) {
                        $id_hesabfa_old = $psFa->idHesabfa;
                        $psFa->idHesabfa = $number;
                        $psFaService->update($psFa);

                        $msg = 'Invoice Number changed. Old Number: ' . $id_hesabfa_old . '. New ID: ' . $number . ', order number: ' . $id_order;
                        LogService::writeLogStr($msg);
                    }
                }
            }
        }

        return true;
    }

    public function setContactChanges($contact)
    {
        if (!is_object($contact))
            return false;

        $psFaService = new PsFaService();

        //1.set new Hesabfa Contact Code if changes
        $code = $contact->Code;

        $json = json_decode($contact->Tag);
        if (is_object($json)) {
            $id_customer = $json->id_customer;
        } else {
            $id_customer = 0;
        }

        //check if Tag not set in hesabfa
        if ($id_customer == 0)
            return false;

        //check if customer exist in prestashop
        $psFa = $psFaService->getPsFa('customer', $id_customer);
        if ($psFa->id > 0) {
            if ($psFa->idHesabfa != $code) {
                $id_hesabfa_old = $psFa->idHesabfa;
                $psFa->idHesabfa = (int)$code;
                $psFaService->update($psFa);

                $msg = 'Contact Code changed. Old ID: ' . $id_hesabfa_old . '. New ID: ' . $code . ', Customer code: ' . $id_customer;
                LogService::writeLogStr($msg);
            }
        }

        return true;
    }

    public static function setItemChanges($item)
    {
        if (!is_object($item))
            return false;

        $settingService = new SettingService();
        $psFaService = new PsFaService();
        $productService = new ProductService();

        //do nothing if product is GiftWrapping item
        $psFa = $psFaService->getPsFa('gift_wrapping', 0);
        if ($psFa != null && $psFa->idHesabfa == $item->Code)
            return true;

        $psFa = $psFaService->getPsFaByHesabfaId('product', $item->Code);
        if(!$psFa)
            return false;

        $id_product = $psFa->idPs;
        $id_attribute = $psFa->idPsAttribute;

        //check if Tag not set in hesabfa
        if ($id_product == 0)
            return false;

        $psFa = $psFaService->getPsFa('product', $id_product, $id_attribute);

        if ($psFa) {
            $product = new Product($id_product);
            if(!$product)
                return false;
            if(Pack::isPack($id_product))
                return false;

            //1.set new Price
            if ($settingService->getUpdatePriceFromHesabfaToStore()) {
                if ($id_attribute != 0) {
                    $combination = new Combination($id_attribute);
                    $price = $productService->getPriceInHesabfaDefaultCurrency($product->price + $combination->price);
                    if ($item->SellPrice != $price) {
                        $old_price = $price;
                        $combination->price = $productService->getPriceInPrestashopDefaultCurrency($item->SellPrice) - $product->price;
                        $combination->update();

                        $msg = "Item $id_product-$id_attribute price changed. Old Price: $old_price. New Price: $item->SellPrice, Product id: $id_product-$id_attribute";
                        LogService::writeLogStr($msg);
                    }
                } else {
                    $price = $productService->getPriceInHesabfaDefaultCurrency($product->price);
                    if ($item->SellPrice != $price) {
                        $old_price = $price;
                        $product->price = $productService->getPriceInPrestashopDefaultCurrency($item->SellPrice);
                        $product->update();

                        $msg = "Item $id_product price changed. Old Price: $old_price. New Price: $item->SellPrice, Product id: $id_product";
                        LogService::writeLogStr($msg);
                    }
                }
            }

            //2.set new Quantity
            if ($settingService->getUpdateQuantityFromHesabfaToStore()) {
                if ($id_attribute != 0) {
                    $current_quantity = StockAvailable::getQuantityAvailableByProduct($id_product, $id_attribute);
                    if ($item->Stock != $current_quantity) {
                        StockAvailable::setQuantity($id_product, $id_attribute, $item->Stock);
//                        StockAvailable::updateQuantity($id_product, $id_attribute, $item->Stock);

                        //TODO: Check why this object not update the quantity
//                        $combination = new Combination($id_attribute);
//                        $combination->quantity = $item->Stock;
//                        $combination->update();

                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_attribute`
                                SET `quantity` = '. $item->Stock . '
                                WHERE `id_product` = ' . $id_product . ' AND `id_product_attribute` = ' . $id_attribute;
                        Db::getInstance()->execute($sql);

                        $msg = "Item $id_product-$id_attribute quantity changed. Old qty: $current_quantity. New qty: $item->Stock, Product id: $id_product";
                        LogService::writeLogStr($msg);
                    }
                } else {
                    $current_quantity = StockAvailable::getQuantityAvailableByProduct($id_product);
                    if ($item->Stock != $current_quantity) {
                        StockAvailable::setQuantity($id_product, null, $item->Stock);
//                        StockAvailable::updateQuantity($id_product, null, $item->Stock);

                        //TODO: Check why this object not update the quantity
//                    $product->quantity = $item->Stock;
//                    $product->update();

                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product`
                                SET `quantity` = '. $item->Stock . '
                                WHERE `id_product` = ' . $id_product;
                        Db::getInstance()->execute($sql);

                        $msg = "Item $id_product quantity changed. Old qty: $current_quantity. New qty: $item->Stock, Product id: $id_product";
                        LogService::writeLogStr($msg);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function getObjectsByIdList($idList, $type) {
        $hesabfaApi = new HesabfaApiService(new SettingService());
        switch ($type) {
            case 'item':
                $result = $hesabfaApi->itemGetById($idList);
                break;
            case 'contact':
                $result = $hesabfaApi->contactGetById($idList);
                break;
            case 'invoice':
                $result = $hesabfaApi->invoiceGetById($idList);
                break;
            default:
                return false;
        }

        if (is_object($result) && $result->Success) {
            return $result->Result;
        }

        return false;
    }

    public function getObjectsByCodeList($codeList) {
        $queryInfo = array(
            'Take' => 10000,
            'Filters' => array(array(
                'Property' => 'Code',
                'Operator' => 'in',
                'Value' => $codeList,
            ))
        );
        $hesabfaApi = new HesabfaApiService(new SettingService());
        $result = $hesabfaApi->itemGetItems($queryInfo);

        if (is_object($result) && $result->Success) {
            return $result->Result->List;
        }

        return false;
    }
}