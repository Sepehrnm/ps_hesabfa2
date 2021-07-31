<style>
	.hesabfa-menu {
		width: 100px;
	}
</style>
{if $needUpdate eq true}
	<div class="panel">
		<h3><i class="icon icon-credit-card"></i> {l s='Upgrade to new version' mod='ps_hesabfa'} [{$latestVersion}]</h3>
		<p>
			{l s='A new version of plugin is available and we highly recommend you to upgrade plugin to new version before continue. to upgrade first download new version and then install it on previous version.' mod='ps_hesabfa'}<br />
		</p>
		<p>
			<a href='https://www.hesabfa.com/file/prestashop/latest/plugin.zip'>
				[ {l s='download new version of module' mod='ps_hesabfa'} ]
			</a>
		</p>
	</div>
{/if}

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Hesabfa Online Accounting Software module!' mod='ps_hesabfa'}</h3>
	<p>
		{l s='This module helps connect your (online) store to Hesabfa online accounting software. By using this module, saving products, contacts, and orders in your store will also save them automatically in your Hesabfa account. Besides that, just after a client pays a bill, the receipt document will be stored in Hesabfa as well. Of course, you have to register your account in Hesabfa first. To do so, visit Hesabfa at the link here www.hesabfa.com and sign up for free. After you signed up and entered your account, choose your business, then in the settings menu/API, you can find the API keys for the business and import them to the plugin’s settings. Now your module is ready to use.' mod='ps_hesabfa'}<br />
	</p>

	{if $showBusinessInfo eq true}

		<a href="?controller=HesabfaSettings&token={$tokenHesabfaSettings}" class="btn btn-info hesabfa-menu">
			<i class="icon-gear" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Plugin Settings' mod='ps_hesabfa'}</a>

		<a href="?controller=ImportExport&token={$tokenImportExport}" class="btn btn-info hesabfa-menu">
			<i class="icon-exchange" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Import And Export' mod='ps_hesabfa'}</a>

		<a href="?controller=Synchronization&token={$tokenSynchronization}" class="btn btn-info hesabfa-menu">
			<i class="icon-refresh" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Synchronization' mod='ps_hesabfa'}</a>

		<a href="?controller=Log&token={$tokenLog}" class="btn btn-info hesabfa-menu">
			<i class="icon-file" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Events Log' mod='ps_hesabfa'}</a>

		<a href="https://www.hesabfa.com/help/topics/افزونه/پرستاشاپ" target="_blank" class="btn btn-warning hesabfa-menu">
			<i class="icon-question" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Plugin Help' mod='ps_hesabfa'}</a>

		<a href="https://app.hesabfa.com/u/login" target="_blank" class="btn btn-success hesabfa-menu">
			<i class="icon-arrow-right" style="font-size: 28px; height: 30px; width: 30px;margin: 0 auto;display: block;"></i>
			{l s='Login To Hesabfa' mod='ps_hesabfa'}</a>

		<a href="javascript:void(0)" class="btn btn-danger hesabfa-menu" id="hesabfa-delete-plugin" style="width: 120px">
			<i class="icon-remove" style="font-size: 28px; height: 30px; width: 30px; margin: 0 auto; display: block;"></i>
			{l s='Delete Plugin Data' mod='ps_hesabfa'}</a>
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
		{if $connected eq true}
		<hr>
			<span class="text-danger">
				{l s='To connect another business to plugin first delete plugin data and uninstall plugin, then reinstall plugin.' mod='ps_hesabfa'}
			</span>
		{/if}
	</div>
{/if}

<script>
	jQuery(function ($) {
		$('#hesabfa-delete-plugin').click(function () {

			const r = confirm("هشدار: آیا از حذف دیتای افزونه مطئن هستید؟");
			if (r) {
				const rr = confirm("هشدار: آیا از حذف دیتای افزونه مطئن هستید؟"
				+ "\n توجه کنید که با این عملیات تمام ارتباطات افزونه با کسب و کار کنونی و تمام تنظیمات از بین می رود" +
						" و این عملیات غیر قابل برگشت است.");
				if(rr) {
					$('#hesabfa-delete-plugin').prop('disabled', true);
					const data = {
						'ajax': true,
						'controller': 'HesabfaWidgets',
						'action': 'deletePluginData',
						'token': '{$tokenHesabfaWidgets}'
					};
					$.post('index.php', data, function (response) {
						$('#hesabfa-delete-plugin').prop('disabled', false);
						if (response !== 'failed') {
							const res = JSON.parse(response);
							if(res) {
								alert('Plugin data and tables deleted successfully. now uninstall plugin from modules management.\n' +
										'دیتای افزونه و جداول مربوطه حذف شدند، اکنون می توانید از منوی مدیریت ماژول ها افزونه حسابفا را حذف کنید و در صورت نیاز مجدد نصب کنید.');
								location.reload();
							} else {
								alert('Error deleting plugin data. see log for details.');
							}
						} else {
							alert('Error deleting plugin data. see log for details.');
							return false;
						}
					});
				}
			}
			return false;
		});
	});
</script>
