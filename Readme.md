#  Hesabfa Accounting 

Contributors: saeedsb, hamidprime, Sepehr-Najafi

Tags: accounting cloud hesabfa

Requires at least: 5.2

Tested up to: 6.2.2

Requires PHP: 7.4

Stable tag: 2.0.28

License: http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)


Connect Hesabfa Online Accounting to Prestashop.

== Description ==
This plugin helps connect your (online) store to Hesabfa online accounting software. By using this plugin, saving products, contacts, and orders in your store will also save them automatically in your Hesabfa account. Besides that, just after a client pays a bill, the receipt document will be stored in Hesabfa as well. Of course, you have to register your account in Hesabfa first. To do so, visit Hesabfa at the link here www.hesabfa.com and sign up for free. After you signed up and entered your account, choose your business, then in the settings menu/API, you can find the API keys for the business and import them to the plugin settings. Now your module is ready to use.

For more information and a full guide to how to use Hesabfa and WooCommerce Plugin, visit Hesabfa’s website and go to the “Accounting School” menu.

== Installation ==
1. Upload the plugin files to the `/modules/ps_hesabfa` directory.
2. Add your hesabfa API_KEY and LOGIN_TOKEN in setting
3. Use the settings screen to configure the plugin

== Changelog ==

= 2.0.27 - 12.07.2022 =
* Opening the pack goods (package of products) when registering the invoice in the account
* The possibility of issuing receipts of invoices to Accountfa as a group on the entry and exit page
* Changing receipt settings: From this version on, invoice receipts can only be registered in one bank, which must be selected in the settings
* Fixed the bug of registering customers' addresses when issuing a group of customers to the account
* Bug fix for discounted invoices
* Fixing the problem of re-registration of invoice receipts
* Fixing the problem of timeout of group issuing of goods in stores with a large number of goods
* Fixed the bug of updating invoices with more than 10 items

= 2.0.28 - 00.00.2023 =
* Expire Date bug fixed
* API address options deleted
* Add cash as a payment method in settings
* Transaction number bug fixed
* Freight as a new service or cost option added
* Sync quantity bug fixed
* Comments Refactored
* Codes Refactored