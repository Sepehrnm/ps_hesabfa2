<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingsService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/CustomerService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ProductService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ReceiptService.php');

class InvoiceService
{
    public $idLang;
    private $module;

    public function __construct(Module $module)
    {
        $this->idLang = Configuration::get('PS_LANG_DEFAULT');
        $this->module = $module;
    }

    public function saveInvoice($orderId, $orderType = 0, $reference = null)
    {
        if (!isset($orderId))
            return false;
        $order = new Order($orderId);

        // set invoice customer
        if (!$this->saveInvoiceCustomer($order))
            return false;

        // set invoice products
        if (!$this->saveInvoiceProducts($order))
            return false;

        // get discount and shipping
        $discountAndShipping = $this->getDiscount($order, $orderId);

        // map Invoice Items
        $hesabfaInvoiceItems = $this->mapInvoiceItems($order, $orderId, $discountAndShipping["order_total_discount"],
            $discountAndShipping["split"]);

        // map Invoice
        $hesabfaInvoice = $this->mapInvoice($order, $orderId, $orderType, $reference, $discountAndShipping["shipping"], $hesabfaInvoiceItems);

        return $this->saveInvoiceToHesabfa($hesabfaInvoice, $orderType);
    }

    public function saveReturnInvoice($orderId, $orderStatus)
    {
        $settingService = new SettingService();
        if ($orderStatus == $settingService->getInWhichStatusAddReturnInvoiceToHesabfa()) {
            $psFaService = new PsFaService();
            $psFa = $psFaService->getPsFa('order', $orderId);
            if ($psFa->id > 0) {
                $this->saveInvoice($orderId, 2, $psFa->idHesabfa);
            }
        }
    }

    private function saveInvoiceToHesabfa($hesabfaInvoice, $orderType)
    {
        $hesabfa = new HesabfaApiService(new SettingService());
        $psFaService = new PsFaService();
        $response = $hesabfa->invoiceSave($hesabfaInvoice);
        if ($response->Success) {
            $psFaService->saveInvoice($response->Result, $orderType);
            return true;
        } else {
            LogService::writeLogStr("Cannot add/update Hesabfa Invoice. Error Code: " . (string)$response->ErrorCode . ". Error Message: $response->ErrorMessage.");
            return false;
        }
    }

    private function mapInvoiceItems(Order $order, $orderId, $order_total_discount, $split)
    {
        $products = $order->getProducts();
        $items = array();
        $psFaService = new PsFaService();
        $i = 0;
        $total_discounts = 0;

        $result = $this->checkForPackProducts($products);
        $products = $result[0];
        $order_total_discount += $result[1];

        foreach ($products as $key => $product) {
            $code = $psFaService->getProductCodeByPrestaId($product['product_id'], $product['product_attribute_id']);

            //fix remaining discount amount on last item
            $array_key = array_keys($products);
            $product_price = $this->getOrderPriceInHesabfaDefaultCurrency($product['product_price'], $order);
            if (end($array_key) == $key) {
                $discount = $order_total_discount - $total_discounts;
            } else {
                $discount = ($product_price * $split * $product['product_quantity']);
                $total_discounts += round($discount);
            }

            //$reduction_amount = $this->getOrderPriceInHesabfaDefaultCurrency($product['original_product_price'] - $product['product_price'], $order);
            //$discount += $reduction_amount * $product['product_quantity'];

            //fix if total discount greater than product price
            if ($discount > $product_price * $product['product_quantity']) {
                $discount = $product_price * $product['product_quantity'];
            }

            $item = array(
                'RowNumber' => $i,
                'ItemCode' => (int)$code,
                'Description' => $product['product_name'],
                'Quantity' => (int)$product['product_quantity'],
                'UnitPrice' => (float)$product_price,
                'Discount' => round((float)$discount),
                'Tax' => (float)$this->getOrderPriceInHesabfaDefaultCurrency(($product['unit_price_tax_incl'] - $product['unit_price_tax_excl']), $order),
            );
            array_push($items, $item);
            $i++;
        }

        if ($order->total_wrapping_tax_excl > 0) {
            $psFaGift = $psFaService->getPsFa('gift_wrapping', 0);
            array_push($items, array(
                'RowNumber' => $i + 1,
                'ItemCode' => $psFaGift->idHesabfa,
                'Description' => $this->module->l('Gift wrapping Service'),
                'Quantity' => 1,
                'UnitPrice' => $this->getOrderPriceInHesabfaDefaultCurrency(($order->total_wrapping), $order),
                'Discount' => 0,
                'Tax' => $this->getOrderPriceInHesabfaDefaultCurrency(($order->total_wrapping_tax_incl - $order->total_wrapping_tax_excl), $order),
            ));
        }

        return $items;
    }

    private function mapInvoice($order, $orderId, $orderType, $reference, $shipping, $invoiceItems)
    {
        $settingService = new SettingService();
        $psFaService = new PsFaService();

        switch ($orderType) {
            case 0:
                $date = $order->date_add;
                break;
            case 2:
                $date = $order->date_upd;
                break;
            default:
                $date = $order->date_add;
        }

        if ($reference === null)
            $reference = $settingService->getWhichNumberSetAsInvoiceReference() ? $order->reference : $orderId;

        return array(
            'Number' => $psFaService->getInvoiceCodeByPrestaId($orderId),
            'InvoiceType' => $orderType,
            'ContactCode' => $psFaService->getCustomerCodeByPrestaId($order->id_customer),
            'Date' => $date,
            'DueDate' => $date,
            'Reference' => $reference,
            'Status' => 2,
            'Tag' => json_encode(array('id_order' => $orderId)),
            'Freight' => $shipping != null ? $shipping : 0,
            'InvoiceItems' => $invoiceItems,
        );
    }

    private function saveInvoiceCustomer(Order $order)
    {
        $psFaService = new PsFaService();
        $customerService = new CustomerService();
        $settingService = new SettingService();

        $contactCode = $psFaService->getPsFaId('customer', $order->id_customer);
        if ($contactCode == 0 || $settingService->getCustomerAddressStatus() == 2) {
            if (!$customerService->saveCustomer($order->id_customer, $order->id_address_invoice))
                return false;
        } elseif ($settingService->getCustomerAddressStatus() == 3) {
            if (!$customerService->saveCustomer($order->id_customer, $order->id_address_delivery))
                return false;
        }

        return true;
    }

    private function saveInvoiceProducts(Order $order)
    {
        $productService = new ProductService();
        $psFaService = new PsFaService();
        $items = array();
        $products = $order->getProducts();
        foreach ($products as $product) {
            $code = $psFaService->getProductCodeByPrestaId($product['product_id'], $product['product_attribute_id']);
            if ($code == null)
                $items[] = $product['product_id'];
        }
        if (!empty($items)) {
            if ($productService->saveProducts($items))
                return true;
            else
                return false;
        } else
            return true;
    }

    private function getDiscount(Order $order, $orderId)
    {
        $order_total_discount = $this->getOrderPriceInHesabfaDefaultCurrency($order->total_discounts, $order);
        $shipping = $this->getOrderPriceInHesabfaDefaultCurrency($order->total_shipping_tax_incl, $order);

        $sql = 'SELECT `free_shipping` 
                    FROM `' . _DB_PREFIX_ . 'order_cart_rule`
                    WHERE `id_order` = ' . $orderId;
        $result = Db::getInstance()->executeS($sql);

//        foreach ($result as $item) {
//            if ($item['free_shipping']) {
//                $order_total_discount = $this->getOrderPriceInHesabfaDefaultCurrency($order->total_discounts - $order->total_shipping_tax_incl, $order);
//                $shipping = 0;
//            }
//        }

        //calculate discount split
        $order_total_products = $this->getOrderPriceInHesabfaDefaultCurrency($order->total_products, $order);
        $split = 0;
        if ($order_total_discount > 0)
            $split = $order_total_discount / $order_total_products;

        return array('order_total_discount' => $order_total_discount,
            'split' => $split, 'shipping' => $shipping);
    }

    public function getOrderPriceInHesabfaDefaultCurrency($price, $order)
    {
        if (!isset($price) || !isset($order))
            return false;
        $price = $price * (int)$order->conversion_rate;
        $productService = new ProductService();
        $price = $productService->getPriceInHesabfaDefaultCurrency($price);
        return $price;
    }

    public function exportOrders($batch, $totalBatch, $total, $updateCount, $from_date, $overwrite)
    {
        $result = array();
        $result["error"] = false;
        $rpp = 10;

        if (!isset($from_date) || empty($from_date)) {
            $result['error'] = true;
            $result['errorMessage'] = 'Error: Enter correct date.';
            return $result;
        }
        if (!$this->isDateInFiscalYear($from_date)) {
            $result['error'] = true;
            $result['errorMessage'] = 'Error: Selected date is not in Hesabfa financial year.';
            return $result;
        }

        if ($batch == 1) {
            $total = $this->getTotalOrders($from_date);
            $totalBatch = ceil($total / $rpp);
        }
        LogService::writeLogStr("===== Export Orders: part $batch of $totalBatch =====");

        $offset = ($batch - 1) * $rpp;
        $orders = $this->getOrdersId($from_date, $offset, $rpp);

        $psFaService = new PsFaService();
        $receiptService = new ReceiptService($this->module);
        $settings = $this->getSettingsForExportOrders();
        $settings["overwrite"] = $overwrite == 'true';

        foreach ($orders as $orderDbRow)
            $updateCount += $this->exportOrder($orderDbRow, $psFaService, $receiptService, $settings);

        $result["batch"] = $batch;
        $result["totalBatch"] = $totalBatch;
        $result["total"] = $total;
        $result["updateCount"] = $updateCount;
        return $result;
    }

    private function exportOrder($orderDbRow, $psFaService, $receiptService, $settings) {
        $id_order = $orderDbRow["id_order"];
        $order = new Order($id_order);
        $updateCount = 0;

        if(!$settings["overwrite"]) {
            $psFa = $psFaService->getPsFa('order', $id_order);
            if($psFa)
                return $updateCount;
        }

        $targetStatusInvoice = $order->getHistory($order->id_lang, $settings["statusToSubmitInvoice"], false);
        if ($settings["statusToSubmitInvoice"] == -1 || count($targetStatusInvoice) > 0) {
            if ($this->saveInvoice($id_order)) {
                $updateCount++;

                $targetStatusPayment = $order->getHistory($order->id_lang, $settings["statusToSubmitPayment"], false);
                if ($settings["statusToSubmitPayment"] == -1 || count($targetStatusPayment) > 0)
                    $receiptService->saveReceipt($id_order);

                $targetStatusReturnInvoice = $order->getHistory($order->id_lang, $settings["statusToSubmitReturnInvoice"], false);
                if (count($targetStatusReturnInvoice) > 0) {
                    $psFa = $psFaService->getPsFa('order', $id_order);
                    $this->saveInvoice($id_order, 2, $psFa->idHesabfa);
                }
            }
        }

        return $updateCount;
    }

    private function getTotalOrders($from_date)
    {
        $sql = "SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "orders` WHERE date_add >= '" . $from_date . "'";
        return (int)Db::getInstance()->getValue($sql);
    }

    private function getOrdersId($from_date, $offset, $rpp)
    {
        $sql = "SELECT id_order FROM `" . _DB_PREFIX_ . "orders`
                                WHERE date_add >= '" . $from_date . "'
                                ORDER BY id_order ASC LIMIT $offset,$rpp";
        return Db::getInstance()->executeS($sql);
    }

    private function getSettingsForExportOrders() {
        $settingService = new SettingService();
        $statusToSubmitInvoice = $settingService->getInWhichStatusAddInvoiceToHesabfa();
        $statusToSubmitReturnInvoice = $settingService->getInWhichStatusAddReturnInvoiceToHesabfa();
        $statusToSubmitPayment = $settingService->getInWhichStatusAddPaymentReceipt();
        return array("statusToSubmitInvoice" => $statusToSubmitInvoice,
            "statusToSubmitReturnInvoice" => $statusToSubmitReturnInvoice,
            "statusToSubmitPayment" => $statusToSubmitPayment);
    }

    public function isDateInFiscalYear($date)
    {
        $hesabfaApi = new HesabfaApiService(new SettingService());
        $fiscalYear = $hesabfaApi->settingGetFiscalYear();

        if ($fiscalYear->Success) {
            $fiscalYearStartTimeStamp = strtotime($fiscalYear->Result->StartDate);
            $fiscalYearEndTimeStamp = strtotime($fiscalYear->Result->EndDate);
            $dateTimeStamp = strtotime($date);

            if ($dateTimeStamp >= $fiscalYearStartTimeStamp && $dateTimeStamp <= $fiscalYearEndTimeStamp) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function clearLink($orderId)
    {
        $psFaService = new PsFaService();
        $psFa = $psFaService->getPsFa('order', $orderId);
        if ($psFa)
            $psFaService->delete($psFa);

        $hesabfaApiService = new HesabfaApiService(new SettingService());
        $response = $hesabfaApiService->invoiceClearTag(array($orderId));

        if ($response->Success) {
            LogService::writeLogStr("Order link with hesabfa invoice removed. order id: $orderId, invoice number: " . ($psFa ? $psFa->idHesabfa : ''));
            return true;
        } else {
            LogService::writeLogStr("Cannot remove order link with hesabfa invoice. Error Code: " . (string)$response->ErrorCode . ". Error Message: $response->ErrorMessage.");
            return false;
        }
    }

    private function checkForPackProducts($products)
    {
        $finalProducts = [];
        $addToDiscount = 0;
        foreach ($products as $key => $product) {
            if (Pack::isPack($product['product_id'])) {
                $itemsInPack = Pack::getItems($product['product_id'], $this->idLang);
                $result = $this->convertPackItemsToOrderItems($itemsInPack, $product);
                array_push($finalProducts, ...$result[0]);
                $addToDiscount += $result[1];
            } else
                $finalProducts[] = $product;
        }
        return array($finalProducts, $addToDiscount);
    }

    private function convertPackItemsToOrderItems($itemsInPack, $orderItem) {
        $orderItems = [];
        $numItems = count($itemsInPack);
        $i=0;
        $sumItems = 0;
        foreach ($itemsInPack as $item) {
            $product = array();
            $product['product_id'] = $item->id;
            $product['product_attribute_id'] = $item->id_pack_product_attribute;
            $product['product_price'] = $item->price;
            $product['product_quantity'] = $orderItem['product_quantity'] * $item->pack_quantity;
            $product['product_name'] = $item->name;
            if(++$i === $numItems) {
                $product['unit_price_tax_incl'] = $orderItem['unit_price_tax_incl'];
                $product['unit_price_tax_excl'] = $orderItem['unit_price_tax_excl'];
            }
            $orderItems[] = $product;
            $sumItems += ($item->price * $item->pack_quantity) * $orderItem['product_quantity'];
        }

        $addToDiscount = $sumItems - ($orderItem['product_price'] * $orderItem['product_quantity']);

        return array($orderItems, $addToDiscount);
    }



}