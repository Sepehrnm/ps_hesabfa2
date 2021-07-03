<?php

interface ICustomerService {
    public function addOrUpdateCustomer(ApiContact $contact);
    public function deleteCustomer(ApiContact $contact);
    public function addContactRelationBetweenHesabfaAndPs(ApiContact $contact);
    public function deleteContactRelationBetweenHesabfaAndPs(ApiContact $contact);
}

class CustomerService implements ICustomerService
{

    public function __construct()
    {
    }

    public function addOrUpdateCustomer(ApiContact $contact)
    {

    }

    public function deleteCustomer(ApiContact $contact) {

    }


    public function addContactRelationBetweenHesabfaAndPs(ApiContact $contact)
    {

    }

    public function deleteContactRelationBetweenHesabfaAndPs(ApiContact $contact)
    {

    }
}