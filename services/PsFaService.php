<?php

include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/LogService.php');

class PsFaService
{
    public function getPsFa($objType, $idPs, $idPsAttribute = 0) {
        if (!isset($objType) || !isset($id_ps)) {
            return false;
        }

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
        if (!isset($objType) || !isset($id_ps)) {
            return false;
        }

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

    public function mapPsFa($sqlObj) {
        $psFa = new PsFa();
        $psFa->id = $sqlObj->id;
        $psFa->idHesabfa = $sqlObj->id_hesabfa;
        $psFa->idPs = $sqlObj->id_ps;
        $psFa->idPsAttribute = $sqlObj->id_ps_attribute;
        $psFa->objType = $sqlObj->obj_type;
        return $psFa;
    }

    public function save($item) {
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
}