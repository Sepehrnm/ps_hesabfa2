<?php

namespace hesabfa\services;

class PsFaService
{
    public function getPsFaId($objType, $idPs, $idPsAttribute = 0) {
        if (!isset($type) || !isset($id_ps)) {
            return false;
        }

        $sql = 'SELECT `id_ps_hesabfa` 
                    FROM `' . _DB_PREFIX_ . 'ssb_hesabfa`
                    WHERE `id_ps` = '. $idPs .' AND `id_ps_attribute` = \''. $idPsAttribute .'\' AND `obj_type` = \''. $type .'\'
                    ';

        return (int)Db::getInstance()->getValue($sql);
    }
}