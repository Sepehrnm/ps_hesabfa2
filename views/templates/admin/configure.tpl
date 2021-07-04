<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Hesabfa' mod='ps_hesabfa'}</h3>
	<p>
		<strong>{l s='Hesabfa Online Accounting Software module!' mod='ps_hesabfa'}</strong><br />
		{l s='This module helps connect your (online) store to Hesabfa online accounting
software. By using this module, saving products, contacts, and orders in your
store will also save them automatically in your Hesabfa account. Besides that,
just after a client pays a bill, the receipt document will be stored in
Hesabfa as well. Of course, you have to register your account in Hesabfa
first. To do so, visit Hesabfa at the link here www.hesabfa.com and sign up
for free. After you signed up and entered your account, choose your business,
then in the settings menu/API, you can find the API keys for the business and
import them to the plugin’s settings. Now your module is ready to use.' mod='ps_hesabfa'}<br />
	</p>

	{if $showBusinessInfo eq true}

		<a href="?controller=HesabfaSettings&token={$tokenHesabfaSettings}" class="btn btn-info">
			<i class="icon-gear" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Plugin Settings' mod='ps_hesabfa'}</a>

		<a href="?controller=ImportExport&token={$tokenImportExport}" class="btn btn-info">
			<i class="icon-exchange" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Import And Export' mod='ps_hesabfa'}</a>

		<a href="https://www.hesabfa.com/help/topics/افزونه/پرستاشاپ" target="_blank" class="btn btn-warning">
			<i class="icon-question" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Plugin Help' mod='ps_hesabfa'}</a>

		<a href="https://app.hesabfa.com/u/login" target="_blank" class="btn btn-success">
			<i class="icon-arrow-right" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Login To Hesabfa' mod='ps_hesabfa'}</a>
	{/if}
</div>

<div class="panel">
	<h3><i class="icon icon-tags"></i> {l s='Documentation' mod='ps_hesabfa'}</h3>
	<p>
		&raquo; {l s='Click on the link below to view the manual of this module' mod='ps_hesabfa'} :
		<ul>
			<li><a href="https://www.hesabfa.com/help/topics/افزونه/پرستاشاپ" target="_blank">{l s='Hesabfa Module Help' mod='ps_hesabfa'}</a></li>
		</ul>
	</p>
</div>

{if $showBusinessInfo eq true}
	<div class="panel hesabfa-f">
		<h3><i class="icon icon-tags"></i> {l s='Business Information' mod='ps_hesabfa'}</h3>
		{l s='Business Name' mod='ps_hesabfa'}: <strong>{$businessName}</strong><br>
		{l s='Subscription Plan' mod='ps_hesabfa'}: <strong>{$subscription}</strong><br>
		{l s='Document Credit' mod='ps_hesabfa'}: <strong>{$documentCredit}</strong><br>
		{l s='Expire Date' mod='ps_hesabfa'}: <strong>{$expireDate}</strong>
	</div>
{/if}

