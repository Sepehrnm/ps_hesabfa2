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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ProductService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/CustomerService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/InvoiceService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/ReceiptService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/WebhookService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/model/PsFa.php');

use Spatie\Async\Pool;

class Ps_hesabfa extends Module
{
    protected $config_form = false;
    public $id_default_lang;

    public function __construct()
    {
        $this->name = 'ps_hesabfa';
        $this->tab = 'administration';
        $this->version = '2.0.23';
        $this->author = 'Hesabfa';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Hesabfa');
        $this->description = $this->l('Hesabfa Online Accounting Software');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Hesabfa module? Notice that relation table between Hesabfa and Prestashop  will be deleted.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->id_default_lang = Configuration::get('PS_LANG_DEFAULT');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('PS_HESABFA_LIVE_MODE', false);

        include(dirname(__FILE__) . '/sql/install.php');

        $settingService = new SettingService();
        $settingService->setDefaultSettings();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionProductDelete') &&
            $this->registerHook('actionProductUpdate') &&

            $this->registerHook('actionProductAttributeAdd') &&
            $this->registerHook('actionProductAttributeUpdate') &&
            $this->registerHook('actionObjectDeleteAfter') &&

            $this->registerHook('actionObjectCustomerAddAfter') &&
            $this->registerHook('actionCustomerAccountUpdate') &&
            $this->registerHook('actionObjectCustomerDeleteBefore') &&
            $this->registerHook('actionObjectAddressAddAfter') &&

            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionPaymentConfirmation') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('actionOrderEdited') &&

            $this->registerHook('displayAdminOrderTabContent') &&
            $this->registerHook('displayAdminOrderRight');

        //$this->createTabLink();
    }

    public function uninstall()
    {
        $settingService = new SettingService();
        //        $hesabfaApiService = new HesabfaApiService($settingService);
//        $hesabfaApiService->fixClearTags();

        $settingService->deleteSomeSettings();
//
//        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $output = '';
        $needUpdate = false;

        if (((bool)Tools::isSubmit('submitPs_hesabfaModule')) == true) {
            $result = $this->postProcess();
            if ($result) {
                if ($result["success"])
                    $output .= $this->displayConfirmation($result["message"]);
                else
                    $output .= $this->displayError($result["message"]);
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $settingService = new SettingService();
        $apiKey = $settingService->getApiKey();
        $apiToken = $settingService->getApiToken();
        $connected = $settingService->getConnectionStatus();

        $apiService = new HesabfaApiService($settingService);

        if ($apiKey && $apiToken) {
            $result = $apiService->settingGetSubscriptionInfo();
            if ($result->Success) {
                $this->context->smarty->assign('showBusinessInfo', true);
                $this->context->smarty->assign('businessName', $result->Result->Name);
                $this->context->smarty->assign('subscription', $result->Result->Subscription);
                $this->context->smarty->assign('expireDate', date("Y/m/d", $result->Result->ExpireDate));
                $this->context->smarty->assign('documentCredit', $result->Result->Credit);
                $this->context->smarty->assign('tokenHesabfaSettings', Tools::getAdminTokenLite('HesabfaSettings'));
                $this->context->smarty->assign('tokenImportExport', Tools::getAdminTokenLite('ImportExport'));
                $this->context->smarty->assign('tokenSynchronization', Tools::getAdminTokenLite('Synchronization'));
                $this->context->smarty->assign('tokenLog', Tools::getAdminTokenLite('Log'));
            }
        }

        $updateInfo = $apiService->checkForModuleUpdateInfo();
        if ($updateInfo) {
            $latest_version = $updateInfo["latest_version"];
            $notices = $updateInfo["notice"];

            if ($latest_version && strpos($latest_version, '.') != false) {
                if (Tools::version_compare($this->version, $latest_version)) {
                    $needUpdate = true;
                    $this->context->smarty->assign('latestVersion', $latest_version);
                    $updateText = sprintf($this->l('A new version (%s) is available.'), $latest_version);
                    $output .= $this->displayConfirmation($updateText);
                }
            }

            if ($notices) {
                foreach ($notices as $val) {
                    if (!isset($val['text']) || !$val['text']) {
                        continue;
                    }
                    if ($val['type'] == 'error') {
                        $output .= $this->displayError($val['text']);
                    } elseif ($val['type'] == 'info') {
                        $output .= $this->displayConfirmation($val['text']);
                    } else {
                        $output .= $val['text'];
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'needUpdate' => $needUpdate,
            'connected' => $connected,
            'tokenHesabfaWidgets' => Tools::getAdminTokenLite('HesabfaWidgets')
        ));

        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    public function createTabLink()
    {
        $tab = new Tab;
        foreach (Language::getLanguages() as $lang)
            $tab->name[$lang['id_lang']] = $this->l('Hesabfa Plugin Settings');
        $tab->class_name = 'HesabfaSettings';
        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName('ShopParameters');
        $tab->add();

        $tab2 = new Tab;
        foreach (Language::getLanguages() as $lang)
            $tab2->name[$lang['id_lang']] = $this->l('Import Export Hesabfa');
        $tab2->class_name = 'ImportExport';
        $tab2->module = $this->name;
        $tab2->id_parent = (int)Tab::getIdFromClassName('ShopParameters');
        $tab2->add();

        $tab3 = new Tab;
        foreach (Language::getLanguages() as $lang)
            $tab3->name[$lang['id_lang']] = $this->l('Synchronization with Hesabfa');
        $tab3->class_name = 'Synchronization';
        $tab3->module = $this->name;
        $tab3->id_parent = (int)Tab::getIdFromClassName('ShopParameters');
        $tab3->add();

        $tab4 = new Tab;
        foreach (Language::getLanguages() as $lang)
            $tab4->name[$lang['id_lang']] = $this->l('Hesabfa plugin events log');
        $tab4->class_name = 'Log';
        $tab4->module = $this->name;
        $tab4->id_parent = (int)Tab::getIdFromClassName('ShopParameters');
        $tab4->add();

        return true;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPs_hesabfaModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $settingService = new SettingService();
        $connected = $settingService->getConnectionStatus();

        $apiAddressOptions = array(
            array(
                'id_option' => 1,
                'name' => 'Address 1 (Cloudflare)'
            ),
            array(
                'id_option' => 2,
                'name' => 'Address 2 (Arvancloud)'
            ),
        );

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('API Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter Hesabfa API Key'),
                        'name' => 'PS_HESABFA_API_KEY',
                        'label' => $this->l('API Key'),
                        'disabled' => $connected
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-token"></i>',
                        'desc' => $this->l('Enter Hesabfa API Token'),
                        'name' => 'PS_HESABFA_API_TOKEN',
                        'label' => $this->l('API Token'),
                        'disabled' => $connected
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-link"></i>',
                        'desc' => $this->l('Select Hesabfa API address to use'),
                        'name' => 'PS_HESABFA_API_ADDRESS',
                        'label' => $this->l('API Address'),
                        'options' => array(
                            'query' => $apiAddressOptions,
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $settingService = new SettingService();

        return array(
            'PS_HESABFA_API_KEY' => $settingService->getApiKey(),
            'PS_HESABFA_API_TOKEN' => $settingService->getApiToken(),
            'PS_HESABFA_API_ADDRESS' => $settingService->getApiAddress(),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $apiKey = Tools::getValue('PS_HESABFA_API_KEY');
        $apiToken = Tools::getValue('PS_HESABFA_API_TOKEN');
        $apiAddress = Tools::getValue('PS_HESABFA_API_ADDRESS');

        $settingService = new SettingService();
        if ($apiKey && $apiToken)
            $settingService->setApiKeyAndToken($apiKey, $apiToken);

        $settingService->setApiAddress($apiAddress);

        // connect to Hesabfa
        $apiService = new HesabfaApiService($settingService);
        $result = $apiService->settingGetSubscriptionInfo();

        if ($result->Success) {
            $this->getAndSetHesabfaDefaultCurrency($settingService);
            $this->setHesabfaWebhook();
            return (array("success" => true, "message" => $this->l("Connected to Hesabfa successfully.")));
        } else {
            return (array("success" => true, "message" => "Unable to connect to Hesabfa, error code: $result->ErrorCode, error message: $result->ErrorMessage"));
        }
    }

    private function getAndSetHesabfaDefaultCurrency($settingService)
    {
        $apiService = new HesabfaApiService($settingService);
        $result = $apiService->settingGetCurrency();
        if ($result->Success) {
            $id_currency = Currency::getIdByIsoCode($result->Result->Currency);
            if ($id_currency > 0) {
                $settingService->setHesabfaDefaultCurrency($id_currency);
            } elseif (_PS_VERSION_ > 1.7) {
                $currency = new Currency();
                $currency->iso_code = $result->Result->Currency;

                if ($currency->add()) {
                    $settingService->setHesabfaDefaultCurrency($currency->id);
                    $msg = 'ssbhesabfa - Hesabfa default currency(' . $result->Result->Currency . ') added to Online Store';
                    LogService::writeLogStr($msg);
                }
            }
        } else {
            $msg = 'Cannot check the Hesabfa default currency. Error Message: ' . $result->ErrorMessage;
            LogService::writeLogStr($msg . ', Error Code: ' . $result->ErrorCode);
        }
    }

    public function setHesabfaWebhook()
    {
        $store_url = $this->context->link->getBaseLink();
        $url = $store_url . 'modules/ps_hesabfa/hesabfa-webhook.php?token=' . Tools::substr(Tools::encrypt('hesabfa/webhook'), 0, 10);
        $settingService = new SettingService();
        $hookPassword = $settingService->getWebhookPassword();

        $hesabfa = new HesabfaApiService($settingService);
        $response = $hesabfa->settingSetChangeHook($url, $hookPassword);

        if (is_object($response)) {
            if ($response->Success) {
                $settingService->setConnectionStatus(1);

                //set the last log ID
                $lastChange = $settingService->getLastChangesLogId();
                if ($lastChange == 0) {
                    $changes = $hesabfa->settingGetChanges($lastChange);
                    if ($changes->Success) {
                        $lastChange = end($changes->Result);
                        $settingService->setLastChangesLogId($lastChange->Id);
                    } else {
                        $msg = 'Cannot check the last change ID. Error Message: ' . $changes->ErrorMessage . ', Error Code: ' . $changes->ErrorCode;
                        LogService::writeLogStr($msg);
                    }
                }

                //set the Gift wrapping service id
                $psFaService = new PsFaService();
                $psFa = $psFaService->getPsFa('gift_wrapping', 0);
                if (!$psFa) {
                    $gift_wrapping = $hesabfa->itemSave(array(
                        'Name' => 'Gift wrapping service',
                        'ItemType' => 1,
                        'Tag' => json_encode(array('id_product' => 0, 'id_attribute' => 0)),
                    ));

                    if ($gift_wrapping->Success) {
                        $psFa = new PsFa();
                        $psFa->idPs = 0;
                        $psFa->idPsAttribute = 0;
                        $psFa->idHesabfa = $gift_wrapping->Result->Code;
                        $psFa->objType = 'gift_wrapping';
                        $psFaService->save($psFa);
                        $msg = 'Hesabfa Gift wrapping service added successfully. Service Code: ' . $gift_wrapping->Result->Code;
                        LogService::writeLogStr($msg);
                    } else {
                        $msg = 'Cannot set Gift wrapping service code. Error Message: ' . $gift_wrapping->ErrorMessage . ', Error Code: ' . $gift_wrapping->ErrorCode;
                        LogService::writeLogStr($msg);
                    }
                }

                $msg = 'Hesabfa webHook successfully Set. URL: ' . (string)$response->Result->url;
                LogService::writeLogStr($msg);
            } else {
                $settingService->setConnectionStatus(0);
                $msg = 'Cannot set Hesabfa webHook. Error Message: ' . $response->ErrorMessage . ', Error Code: ' . $response->ErrorCode;
                LogService::writeLogStr($msg);
            }
        } else {
            LogService::writeLogStr('Cannot set Hesabfa webHook. Please check your Internet connection');
        }

        return $response;
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }

        $this->cronJob();
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    // Product hooks
    private $hookProductUpdateCalled = false;

    public function hookActionProductUpdate($params)
    {
        if ($this->hookProductUpdateCalled)
            return;
        $this->hookProductUpdateCalled = true;

        $productService = new ProductService();
        $productService->saveProducts(array($params["id_product"]));
    }

    public function hookActionProductDelete($params)
    {
        $productService = new ProductService();
        $productService->deleteProduct($params["id_product"]);
    }

    public function hookActionObjectDeleteAfter($params)
    {
        if (is_a($params["object"], 'Combination')) {
            $productService = new ProductService();
            $productService->deleteRemovedCombinationsOfProduct($params["object"]->id_product);
        }
    }

    public function hookActionProductAttributeUpdate($params)
    {
        return;
        $productService = new ProductService();
        $productService->saveProducts(array($params['product']->id));

        $pool = Pool::create();

        $pool[] = async(function () use ($params) {
            sleep(5);
            return true;
        })->then(function ($output) {
        });
        await($pool);
    }

    // Customer hooks
    public function hookActionObjectCustomerAddAfter($params)
    {
        $customerService = new CustomerService();
        $customerService->saveCustomer($params['object']->id);
    }

    public function hookActionCustomerAccountUpdate($params)
    {
        $customerService = new CustomerService();
        $customerService->saveCustomer($params['customer']->id);
    }

    public function hookActionObjectAddressAddAfter($params)
    {
        if (Address::getFirstCustomerAddressId($params['object']->id_customer) == 0) {
            $customerService = new CustomerService();
            $customerService->saveCustomer($params['object']->id_customer, $params['object']->id);
        }
    }

    public function hookActionObjectCustomerDeleteBefore($params)
    {
        $customerService = new CustomerService();
        $customerService->deleteCustomer($params['object']->id);
    }

    // Order hooks
    public function hookActionValidateOrder($params)
    {
        LogService::writeLogStr("====== hookActionValidateOrder ======");
        $settingService = new SettingService();
        $settingStatus = $settingService->getInWhichStatusAddInvoiceToHesabfa();

//        LogService::writeLogStr('order status to set invoice: ' . $settingStatus);
//        LogService::writeLogObj($params);

        if ($settingStatus == -1 || $params["orderStatus"]->id == $settingStatus) {
            $invoiceService = new InvoiceService($this);
            $invoiceService->saveInvoice((int)$params['order']->id);
        }
    }

    public function hookActionPaymentConfirmation($params)
    {
        LogService::writeLogStr("====== hookActionPaymentConfirmation ======");
        $receiptService = new ReceiptService($this);
        $receiptService->saveReceipt($params['id_order']);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        LogService::writeLogStr("====== hookActionOrderStatusPostUpdate ======");

        $invoiceService = new InvoiceService($this);
        $settingService = new SettingService();
        $settingStatus = $settingService->getInWhichStatusAddInvoiceToHesabfa();
        $settingStatusReceipt = $settingService->getInWhichStatusAddPaymentReceipt();

        if ($settingStatus == -1 || $params["newOrderStatus"]->id == $settingStatus) {
            $psFaService = new PsFaService();
            $psFa = $psFaService->getPsFa('order', (int)$params['id_order']);
            if (!$psFa) {
                $success = $invoiceService->saveInvoice((int)$params['id_order']);
                if ($success) {
                    if ($settingStatusReceipt == -1 || $params["newOrderStatus"]->id == $settingStatusReceipt) {
                        $receiptService = new ReceiptService($this);
                        $receiptService->saveReceipt($params['id_order']);
                    }
                }
            }
        }

        $invoiceService->saveReturnInvoice($params['id_order'], $params['newOrderStatus']->id);
    }

    public function hookActionOrderEdited($params)
    {
        LogService::writeLogStr("====== hookActionOrderEdited ======");
        $this->hookActionValidateOrder($params);
    }

    public function cronJob()
    {
        $settingService = new SettingService();
        $connected = $settingService->getConnectionStatus();
        if (!$connected)
            return;
        $syncChangesLastDate = $settingService->getLastChangesCheckDate();
        if (!isset($syncChangesLastDate) || $syncChangesLastDate == false) {
            $settingService->setLastChangesCheckDate((new DateTime())->format('Y-m-d H:i:s'));
            $syncChangesLastDate = new DateTime();
        } else {
            try {
                $syncChangesLastDate = new DateTime($syncChangesLastDate);
            } catch (Exception $e) {
            }
        }

        $nowDateTime = new DateTime();
        $diff = $nowDateTime->diff($syncChangesLastDate);

        if ($diff->i > 3) {
            LogService::writeLogStr('===== Sync Changes Automatically =====');
            $settingService->setLastChangesCheckDate((new DateTime())->format('Y-m-d H:i:s'));
            new WebhookService();
        }
    }

    public function hookDisplayAdminOrderTabContent($params)
    {
        $psFaService = new PsFaService();
        $psFa = $psFaService->getPsFa('order', $params["id_order"]);
        $this->context->smarty->assign('invoiceNumber', $psFa ? $psFa->idHesabfa : 0);
        $this->context->smarty->assign('orderId', $params["id_order"]);
        $this->context->smarty->assign('tokenHesabfaWidgets', Tools::getAdminTokenLite('HesabfaWidgets'));

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/order-widget.tpl');
        return $output;
    }

    public function hookDisplayAdminOrderRight($params)
    {
        $psFaService = new PsFaService();
        $psFa = $psFaService->getPsFa('order', $params["id_order"]);
        $this->context->smarty->assign('invoiceNumber', $psFa ? $psFa->idHesabfa : 0);
        $this->context->smarty->assign('orderId', $params["id_order"]);
        $this->context->smarty->assign('tokenHesabfaWidgets', Tools::getAdminTokenLite('HesabfaWidgets'));

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/order-widget.tpl');
        return $output;
    }

}
