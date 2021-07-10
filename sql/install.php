<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
$sql = array();

// create new table

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ps_hesabfa` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `obj_type` varchar(32) NOT NULL,
    `id_hesabfa` int(11) UNSIGNED NOT NULL,
    `id_ps` int(11) UNSIGNED NOT NULL,
    `id_ps_attribute` INT(10) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ps_hesabfa_action_queue` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `action` varchar(50) NOT NULL,
    `json_data` varchar(20000) UNSIGNED NOT NULL,
    `submit_date` datetime NOT NULL,
    `process_date` datetime NOT NULL,
    `retry_count` int NOT NULL DEFAULT 0,
    `status` varchar(50) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// copy data from previous plugin table

$sql[] = 'INSERT INTO `' . _DB_PREFIX_ . 'ps_hesabfa`
    (obj_type,id_hesabfa,id_ps,id_ps_attribute) 
    SELECT obj_type,id_hesabfa,id_ps,id_ps_attribute
    FROM `' . _DB_PREFIX_ . 'ssb_hesabfa`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
