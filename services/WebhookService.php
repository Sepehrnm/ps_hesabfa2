<?php

include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/HesabfaApiService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/SettingsService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/LogService.php');
include_once(_PS_MODULE_DIR_ . 'ps_hesabfa/services/PsFaService.php');

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
                            $this->invoicesObjectId[] = $item->ObjectId;
                            foreach (explode(',', $item->Extra) as $invoiceItem) {
                                if ($invoiceItem != ''){
                                    $this->invoiceItemsCode[] = $invoiceItem;
                                }
                            }

                            break;
                        case 'Product':
                            //if Action was deleted
                            if ($item->Action == 53) {
                                $psFa = $psFaService->getPsFa('product', $item->Extra);
                                $psFaService->delete($psFa);
                                break;
                            }

                            $this->itemsObjectId[] = $item->ObjectId;
                            break;
                        case 'Contact':
                            //if Action was deleted
                            if ($item->Action == 33) {
                                $psFa = $psFaService->getPsFa('customer', $item->Extra);
                                $psFaService->delete($psFa);
                                break;
                            }

                            $this->contactsObjectId[] = $item->ObjectId;
                            break;
                    }
                }
            }

            //remove duplicate values
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