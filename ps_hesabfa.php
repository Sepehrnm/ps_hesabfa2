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

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/ProductService.php');
include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/SettingService.php');
include_once(_PS_MODULE_DIR_.'ps_hesabfa/services/HesabfaApiService.php');

use hesabfa\services\LogService;
use hesabfa\services\ProductService;

class Ps_hesabfa extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'ps_hesabfa';
        $this->tab = 'administration';
        $this->version = '1.0.0';
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
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('PS_HESABFA_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        $settingService = new SettingService();
        $settingService->setDefaultSettings();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionProductDelete') &&
            $this->registerHook('actionProductUpdate') &&
            $this->createTabLink();
    }

    public function uninstall()
    {
        $settingService = new SettingService();
        $settingService->deleteAllSettings();

        include(dirname(__FILE__).'/sql/uninstall.php');

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

        if (((bool)Tools::isSubmit('submitPs_hesabfaModule')) == true) {
            $result = $this->postProcess();
            if($result) {
                if($result["success"])
                    $output .= $this->displayConfirmation($result["message"]);
                else
                    $output .= $this->displayError($result["message"]);
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $settingService = new SettingService();
        $apiKey = $settingService->getApiKey();
        $apiToken = $settingService->getApiToken();
        if($apiKey && $apiToken) {
            $apiService = new HesabfaApiService($settingService);
            $result = $apiService->settingGetSubscriptionInfo();
            if($result->Success) {
                $this->context->smarty->assign('showBusinessInfo', true);
                $this->context->smarty->assign('businessName', $result->Result->Name);
                $this->context->smarty->assign('subscription', $result->Result->Subscription);
                $this->context->smarty->assign('expireDate', date("Y/m/d", $result->Result->ExpireDate));
                $this->context->smarty->assign('documentCredit', $result->Result->Credit);
                $this->context->smarty->assign('tokenHesabfaSettings', Tools::getAdminTokenLite('HesabfaSettings'));
                $this->context->smarty->assign('tokenImportExport', Tools::getAdminTokenLite('ImportExport'));
            }
        }

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    public function createTabLink() {
        $tab = new Tab;
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Hesabfa Plugin Settings');
        }
        $tab->class_name = 'HesabfaSettings';
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('ShopParameters');
        $tab->add();

        $tab2 = new Tab;
        foreach (Language::getLanguages() as $lang) {
            $tab2->name[$lang['id_lang']] = $this->l('Import Export Hesabfa');
        }
        $tab2->class_name = 'ImportExport';
        $tab2->module = $this->name;
        $tab2->id_parent = (int) Tab::getIdFromClassName('ShopParameters');
        $tab2->add();

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
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
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
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter Hesabfa API Key'),
                        'name' => 'PS_HESABFA_API_KEY',
                        'label' => $this->l('API Key'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter Hesabfa API Token'),
                        'name' => 'PS_HESABFA_API_TOKEN',
                        'label' => $this->l('API Token'),
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

        $settingService = new SettingService();
        $settingService->setApiKeyAndToken($apiKey, $apiToken);

        // connect to Hesabfa
        $apiService = new HesabfaApiService($settingService);
        $result = $apiService->settingGetSubscriptionInfo();

        if($result->Success) {
            $this->getAndSetHesabfaDefaultCurrency($settingService);
            return (array("success"=>true,"message"=>$this->l("Connected to Hesabfa successfully.")));
        } else {
            return (array("success"=>true,"message"=>"Unable to connect to Hesabfa, error code: $result->ErrorCode, error message: $result->ErrorMessage"));
        }
    }

    private function getAndSetHesabfaDefaultCurrency($settingService) {
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
                    $msg = 'ssbhesabfa - Hesabfa default currency('. $result->Result->Currency .') added to Online Store';
                    LogService::writeLogStr($msg);
                }
            }
        } else {
            $msg = 'Cannot check the Hesabfa default currency. Error Message: ' . $result->ErrorMessage;
            LogService::writeLogStr($msg . ', Error Code: ' . $result->ErrorCode);
        }
    }



    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookActionProductAdd($params)
    {
        LogService::writeLogStr("product add hook called!");
        LogService::writeLogObj($params);
    }

    public function hookActionProductDelete()
    {
        /* Place your code here. */
    }

    private $hookProductUpdateCalled = false;
    public function hookActionProductUpdate($params)
    {
        if($this->hookProductUpdateCalled)
            return;
        $this->hookProductUpdateCalled = true;

        $product = new Product($params["id_product"]);
        $productService = new ProductService();

        LogService::writeLogStr("product update hook called!");
        LogService::writeLogStr('id:' . $params["id_product"]);
        LogService::writeLogStr('tax_rate:' . $params["product"]->tax_rate);
        LogService::writeLogStr('name:' . $params["product"]->name[1]);
        LogService::writeLogStr('quantity:' . $params["product"]->quantity);

//        $logService->writeLogObj($params["product"]);
        $productQuantity = StockAvailable::getQuantityAvailableByProduct($params["id_product"]);
        LogService::writeLogStr("product quantity: $productQuantity");
        //$logService->writeLogObj();
    }
}
