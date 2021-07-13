<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingsService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/CustomerService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ProductService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');

class InvoiceService
{
    public $idLang;

    public function __construct($idDefaultLang)
    {
        $this->idLang = $idDefaultLang;
    }

    public function saveInvoice($orderId, $orderType = 0, $reference = null)
    {
        if (!isset($orderId))
            return false;
        $order = new Order($orderId);

        // set invoice customer
        if(!$this->saveInvoiceCustomer($order))
            return false;

        // set invoice products
        if(!$this->saveInvoiceProducts($order))
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

    public function saveReturnInvoice($orderId, $orderStatus) {
        $settingService = new SettingService();
        if ($orderStatus == $settingService->getInWhichStatusAddReturnInvoiceToHesabfa()) {
            $psFaService = new PsFaService();
            $psFa = $psFaService->getPsFa('order', $orderId);
            if($psFa->id > 0) {
                $this->saveInvoice($orderId, 2, $psFa->idHesabfa);
            }
        }
    }

    private function saveInvoiceToHesabfa($hesabfaInvoice, $orderType) {
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

    private function mapInvoiceItems(Order $order, $orderId, $order_total_discount, $split) {
        $products = $order->getProducts();
        $items = array();
        $psFaService = new PsFaService();
        $i = 0;
        $total_discounts = 0;

        foreach ($products as $key => $product) {
            $code = $psFaService->getProductCodeByPrestaId($product['product_id'], $product['product_attribute_id']);

            //fix remaining discount amount on last item
            $array_key = array_keys($products);
            $product_price = $this->getOrderPriceInHesabfaDefaultCurrency($product['original_product_price'], $order);

            if (end($array_key) == $key) {
                $discount = $order_total_discount - $total_discounts;
            } else {
                $discount = ($product_price * $split * $product['product_quantity']);
                $total_discounts += $discount;
            }

            $reduction_amount = $this->getOrderPriceInHesabfaDefaultCurrency($product['original_product_price'] - $product['product_price'], $order);
            $discount += $reduction_amount * $product['product_quantity'];

            //fix if total discount greater than product price
            if ($discount > $product_price * $product['product_quantity']) {
                $discount = $product_price * $product['product_quantity'];
            }

            $item = array (
                'RowNumber' => $i,
                'ItemCode' => (int)$code,
                'Description' => $product['product_name'],
                'Quantity' => (int)$product['product_quantity'],
                'UnitPrice' => (float)$product_price,
                'Discount' => (float)$discount,
                'Tax' => (float)$this->getOrderPriceInHesabfaDefaultCurrency(($product['unit_price_tax_incl'] - $product['unit_price_tax_excl']), $order),
            );
            array_push($items, $item);
            $i++;
        }

//        if ($order->total_wrapping_tax_excl > 0) {
//            array_push($items, array (
//                'RowNumber' => $i+1,
//                'ItemCode' => Configuration::get('SSBHESABFA_ITEM_GIFT_WRAPPING_ID'),
//                'Description' => $this->l('Gift wrapping Service'),
//                'Quantity' => 1,
//                'UnitPrice' => $this->getOrderPriceInHesabfaDefaultCurrency(($order->total_wrapping), $orderId),
//                'Discount' => 0,
//                'Tax' => $this->getOrderPriceInHesabfaDefaultCurrency(($order->total_wrapping_tax_incl - $order->total_wrapping_tax_excl), $orderId),
//            ));
//        }

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

        return array (
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
        $customerService = new CustomerService($this->idLang);
        $settingService = new SettingService();

        $contactCode = $psFaService->getPsFaId('customer', $order->id_customer);
        if ($contactCode == 0 || $settingService->getCustomerAddressStatus() == 2) {
            if(!$customerService->saveCustomer($order->id_customer, $order->id_address_invoice))
                return false;
        } elseif ($settingService->getCustomerAddressStatus() == 3) {
            if(!$customerService->saveCustomer($order->id_customer, $order->id_address_delivery))
                return false;
        }

        return true;
    }

    private function saveInvoiceProducts(Order $order)
    {
        $productService = new ProductService($this->idLang);
        $psFaService = new PsFaService();
        $items = array();
        $products = $order->getProducts();
        foreach ($products as $product) {
            $code = $psFaService->getProductCodeByPrestaId($product['product_id'], $product['product_attribute_id']);
            if ($code == null)
                $items[] = $product['product_id'];
        }
        if (!empty($items)) {
            if($productService->saveProducts($items))
                return true;
            else
                return false;
        } else
            return true;
    }

    private function getDiscount(Order $order, $orderId) {
        $order_total_discount = $this->getOrderPriceInHesabfaDefaultCurrency($order->total_discounts, $order);
        $shipping = $this->getOrderPriceInHesabfaDefaultCurrency($order->total_shipping_tax_incl, $order);

        $sql = 'SELECT `free_shipping` 
                    FROM `' . _DB_PREFIX_ . 'order_cart_rule`
                    WHERE `id_order` = '. $orderId;
        $result = Db::getInstance()->executeS($sql);

        foreach ($result as $item) {
            if ($item['free_shipping']) {
                $order_total_discount = $this->getOrderPriceInHesabfaDefaultCurrency($order->total_discounts - $order->total_shipping_tax_incl, $order);
                $shipping = 0;
            }
        }

        //calculate discount split
        $order_total_products = $this->getOrderPriceInHesabfaDefaultCurrency($order->total_products, $order);
        $split = 0;
        if ($order_total_discount > 0)
            $split = $order_total_discount / $order_total_products;

        return Array('order_total_discount' => $order_total_discount,
            'split' => $split, 'shipping' => $shipping);
    }

    public function getOrderPriceInHesabfaDefaultCurrency($price, $order)
    {
        if (!isset($price) || !isset($order))
            return false;
        $price = $price * (int)$order->conversion_rate;
        $productService = new ProductService($this->idLang);
        $price = $productService->getPriceInHesabfaDefaultCurrency($price);
        return $price;
    }

}