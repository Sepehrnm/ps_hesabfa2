<?php

include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_.'ps_hesabfa/model/PsFa.php');

class PsFaService
{
    public function __construct()
    {
    }

    public function getPsFa($objType, $idPs, $idPsAttribute = 0) {
        $sql = 'SELECT * 
                    FROM `' . _DB_PREFIX_ . 'ps_hesabfa`
                    WHERE `id_ps` = '. $idPs .' AND `id_ps_attribute` = \''. $idPsAttribute .'\' AND `obj_type` = \''. $objType .'\'
                    ';

        $result = Db::getInstance()->executeS($sql);

        if(isset($result) && is_array($result) && count($result) > 0)
            return $this->mapPsFa($result[0]);
        return null;
    }

    public function getPsFaId($objType, $idPs, $idPsAttribute = 0) {
        $sql = 'SELECT `id` 
                    FROM `' . _DB_PREFIX_ . 'ps_hesabfa`
                    WHERE `id_ps` = '. $idPs .' AND `id_ps_attribute` = \''. $idPsAttribute .'\' AND `obj_type` = \''. $objType .'\'
                    ';

        return (int)Db::getInstance()->getValue($sql);
    }

    public function getProductCodeByPrestaId($id_product, $id_attribute = 0)
    {
        $obj = $this->getPsFa('product', $id_product, $id_attribute);
        if($obj != null)
            return $obj->idHesabfa;
        return null;
    }

    public function getCustomerCodeByPrestaId($id_customer)
    {
        $obj = $this->getPsFa('customer', $id_customer);
        if($obj != null)
            return $obj->idHesabfa;
        return null;
    }

    public function getInvoiceCodeByPrestaId($id_order)
    {
        $obj = $this->getPsFa('order', $id_order);
        if($obj != null)
            return $obj->idHesabfa;
        return null;
    }


    public function getProductAndCombinations($idPs) {
        $sql = "SELECT * FROM `" . _DB_PREFIX_  . "ps_hesabfa` WHERE `obj_type` = 'product' AND `id_ps` = '$idPs'";
        $result = Db::getInstance()->executeS($sql);

        $psFaObjects = array();
        if(isset($result) && is_array($result) && count($result) > 0)
        {
            foreach ($result as $item) {
                $psFaObjects[] = $this->mapPsFa($item);
            }
            return $psFaObjects;
        }
        return null;
    }

    public function mapPsFa($sqlObj) {
        $psFa = new PsFa();
        $psFa->id = $sqlObj["id"];
        $psFa->idHesabfa = $sqlObj["id_hesabfa"];
        $psFa->idPs = $sqlObj["id_ps"];
        $psFa->idPsAttribute = $sqlObj["id_ps_attribute"];
        $psFa->objType = $sqlObj["obj_type"];
        return $psFa;
    }

    public function saveProduct($item) {
        $json = json_decode($item->Tag);
        $id = $this->getPsFaId('product', (int)$json->id_product, (int)$json->id_attribute);

        if ($id == false) {
            Db::getInstance()->insert('ps_hesabfa', array(
                'id_hesabfa' => (int)$item->Code,
                'obj_type' => 'product',
                'id_ps' => (int)$json->id_product,
                'id_ps_attribute' => (int)$json->id_attribute,
            ));
            LogService::writeLogStr("Item successfully added. Item code: " . (string)$item->Code . ". Product ID: $json->id_product-$json->id_attribute");
        } else {
            Db::getInstance()->update('ps_hesabfa', array(
                'id_hesabfa' => (int)$item->Code,
                'obj_type' => 'product',
                'id_ps' => (int)$json->id_product,
                'id_ps_attribute' => (int)$json->id_attribute,
            ), array('id' => $id),0,true,true);
            LogService::writeLogStr("Item successfully updated. Item code: " . (string)$item->Code . ". Product ID: $json->id_product-$json->id_attribute");
        }

        return true;
    }

    public function saveCustomer($customer) {
        $json = json_decode($customer->Tag);
        $id = $this->getPsFaId('customer', (int)$json->id_customer);

        if ($id == false) {
            Db::getInstance()->insert('ps_hesabfa', array(
                'id_hesabfa' => (int)$customer->Code,
                'obj_type' => 'customer',
                'id_ps' => (int)$json->id_customer,
            ));
            LogService::writeLogStr("Customer successfully added. Customer code: " . (string)$customer->Code . ". Customer ID: $json->id_customer");
        } else {
            Db::getInstance()->update('ps_hesabfa', array(
                'id_hesabfa' => (int)$customer->Code,
                'obj_type' => 'customer',
                'id_ps' => (int)$json->id_customer,
            ), array('id' => $id),0,true,true);
            LogService::writeLogStr("Customer successfully updated. Customer code: " . (string)$customer->Code . ". Customer ID: $json->id_customer");
        }

        return true;
    }

    public function saveInvoice($invoice, $orderType) {
        $json = json_decode($invoice->Tag);
        $id = $this->getPsFaId('order', (int)$json->id_order);

        $invoiceNumber = (int)$invoice->Number;
        $objType = $orderType == 0 ? 'order' : 'returnOrder';

        if ($id == false) {
            Db::getInstance()->insert('ps_hesabfa', array(
                'id_hesabfa' => $invoiceNumber,
                'obj_type' => $objType,
                'id_ps' => (int)$json->id_order,
            ));
            if($objType == 'order')
                LogService::writeLogStr("Invoice successfully added. invoice number: " . (string)$invoice->Number . ", order id: " . $json->id_order);
            else
                LogService::writeLogStr("Return Invoice successfully added. Customer code: " . (string)$invoice->Number . ", order id: " . $json->id_order);
        } else {
            Db::getInstance()->update('ps_hesabfa', array(
                'id_hesabfa' => $invoiceNumber,
                'obj_type' => $objType,
                'id_ps' => (int)$json->id_order,
            ), array('id' => $id),0,true,true);
            if($objType == 'order')
                LogService::writeLogStr("Invoice successfully updated. invoice number: " . (string)$invoice->Number . ", order id: " . $json->id_order);
            else
                LogService::writeLogStr("Return Invoice successfully updated. Customer code: " . (string)$invoice->Number . ", order id: " . $json->id_order);
        }

        return true;
    }

    public function update(PsFa $psFa) {
        Db::getInstance()->update('ps_hesabfa', array(
            'id_hesabfa' => $psFa->idHesabfa,
            'obj_type' => $psFa->objType,
            'id_ps' => (int)$psFa->idPs,
            'id_ps_attribute' => (int)$psFa->idPsAttribute
        ), array('id' => $psFa->id),0,true,true);
    }

    public function delete($psFa) {
        Db::getInstance()->delete('ps_hesabfa', 'id=' . $psFa->id);
    }

}