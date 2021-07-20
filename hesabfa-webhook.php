<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');

include(dirname(__FILE__) . '/services/LogService.php');
include(dirname(__FILE__) . '/services/SettingService.php');
include(dirname(__FILE__) . '/services/WebhookService.php');

/* Check security token */
if (!Tools::isPHPCLI()) {
    if (Tools::substr(Tools::encrypt('hesabfa/webhook'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('ps_hesabfa')) {
        LogService::writeLogStr("Bad webhook token");
        die('Bad token');
    }
}

$hesabfaModule = Module::getInstanceByName('ps_hesabfa');

/* Check if the module is enabled */
if ($hesabfaModule->active) {
    $post = Tools::file_get_contents('php://input');
    $result = json_decode($post);

    if (!is_object($result)) {
        LogService::writeLogStr('Invalid Webhook request.');
        die('Invalid request.');
    }

    $settingService = new SettingService();

    if ($result->Password != $settingService->getWebhookPassword()) {
        LogService::writeLogStr('Invalid Webhook password.');
        die('Invalid password.');
    }

    LogService::writeLogStr('Hesabfa webhook called.');
    new WebhookService();
}