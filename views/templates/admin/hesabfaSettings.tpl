<style>
    @import url('https://v1.fontapi.ir/css/Yekan');
    .panel, select {
        font-family: "Yekan", Tahoma, sans-serif !important;
    }
</style>
<div class="panel">
    <div class="panel-heading">
        {l s='Hesabfa Plugin Settings' mod='ps_hesabfa'}
        &nbsp;&nbsp;&nbsp;
        <a href="?controller=AdminModules&configure=ps_hesabfa&token={$tokenHesabfaModuleConfigure}">
            [ {l s='Return to main page' mod='ps_hesabfa'} ]</a>
    </div>
    <div class="panel-body">
        {* ================= Product settings ================= *}
        <strong class="text-primary">{l s='Product Settings' mod='ps_hesabfa'}</strong>
        <hr>

        <label>{l s='Barcode' mod='ps_hesabfa'}</label>&nbsp;
        <small>({l s='Which code use as Barcode in Hesabfa' mod='ps_hesabfa'})</small>
        <select class="form-control" style="max-width: 250px;font-size: 1rem;" id="hesabfa-settings-barcode">
            <option {if $selectedBarcode eq 0} selected {/if}>Reference</option>
            <option {if $selectedBarcode eq 1} selected {/if}>UPC barcode</option>
            <option {if $selectedBarcode eq 2} selected {/if}>EAN-13 or JAN barcode</option>
            <option {if $selectedBarcode eq 3} selected {/if}>ISBN</option>
        </select>

        <div class="checkbox">
            <label>
                <input type="checkbox" {if $updatePriceFromHesabfaToStore eq true} checked {/if}
                       id="updatePriceFromHesabfaToStore">
                {l s='Update price from Hesabfa to Store' mod='ps_hesabfa'}
            </label>
        </div>

        <div class="checkbox">
            <label>
                <input type="checkbox" {if $updatePriceFromStoreToHesabfa eq true} checked {/if}
                       id="updatePriceFromStoreToHesabfa">
                {l s='Update price from Store to Hesabfa' mod='ps_hesabfa'}
            </label>
        </div>

        <div class="checkbox">
            <label>
                <input type="checkbox" {if $updateQuantityFromHesabfaToStore eq true} checked {/if}
                       id="updateQuantityFromHesabfaToStore">
                {l s='Update Quantity from Hesabfa to Store' mod='ps_hesabfa'}
            </label>
        </div>

        {* ================= Customer settings ================= *}

        <br><br>
        <strong class="text-primary">{l s='Customer Settings' mod='ps_hesabfa'}</strong>
        <hr>
        <label>{l s='Customer Address' mod='ps_hesabfa'}</label>&nbsp;
        <small>({l s='When update customer address in Hesabfa' mod='ps_hesabfa'})</small>
        <select class="form-control" style="max-width: 250px" id="hesabfa-setting-customer-address">
            <option {if $selectedCustomerAddress eq 0} selected {/if}>{l s='Use first customer address' mod='ps_hesabfa'}</option>
            <option {if $selectedCustomerAddress eq 1} selected {/if}>{l s='Invoice address' mod='ps_hesabfa'}</option>
            <option {if $selectedCustomerAddress eq 2} selected {/if}>{l s='Delivery address' mod='ps_hesabfa'}</option>
        </select>

        <div class="form-group" style="margin-top: 10px;">
            <label>{l s='Customers category name' mod='ps_hesabfa'}</label>
            <input type="text" class="form-control" id="hesabfa-setting-customer-category"
                   placeholder="Online store customers" value="{$customerCategoryName}" style="max-width: 250px">
        </div>

        {* ================= Invoice settings ================= *}
        <br><br>
        <strong class="text-primary">{l s='Invoice Settings' mod='ps_hesabfa'}</strong>
        <hr>
        <label>{l s='Invoice reference number' mod='ps_hesabfa'}</label>&nbsp;
        <small>({l s='Which number use as reference number in Hesabfa' mod='ps_hesabfa'})</small>
        <select class="form-control" style="max-width: 250px" id="hesabfa-setting-invoice-reference">
            <option {if $selectedInvoiceReference eq 0} selected {/if}>{l s='Order ID' mod='ps_hesabfa'}</option>
            <option {if $selectedInvoiceReference eq 1} selected {/if}>{l s='Order Reference' mod='ps_hesabfa'}</option>
        </select>

        <label style="margin-top: 10px">{l s='In which status save invoice in Hesabfa' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" id="hesabfa-setting-invoice-status" style="max-width: 250px">
            {foreach from=$orderStatusOptions item=i}
                <option {if $selectedInvoiceStatus eq $i.id} selected {/if} value="{$i.id}">{$i.name}</option>
            {/foreach}
        </select>

        <label style="margin-top: 10px">{l s='In which statuses save return invoice in Hesabfa' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" id="hesabfa-setting-return-invoice-status" style="max-width: 250px">
            {foreach from=$orderStatusOptions item=i}
                <option {if $selectedReturnInvoiceStatus eq $i.id} selected {/if} value="{$i.id}">{$i.name}</option>
            {/foreach}
        </select>

        <label style="margin-top: 10px">{l s='save invoice freight' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" id="hesabfa-setting-freight-invoice-status" style="max-width: 250px">
            <option value="-1">انتخاب کنید</option>
            <option {if $selectedFreightOption eq 'newService'} selected {/if} value="newService">{l s='save freight as a new service' mod='ps_hesabfa'}</option>
            <option {if $selectedFreightOption eq 'newCost'} selected {/if} value="newCost">{l s='save freight as a cost' mod='ps_hesabfa'}</option>
        </select>

        <br>
        <label class="form-label" for="hesabfa-setting-freight-input-value">{l s='freight field code' mod='ps_hesabfa'}</label>
        <input style="max-width: 250px" class="form-control" type="text" id="hesabfa-setting-freight-input-value" value="{$selectedFreightValue}" />

        <label style="margin-top: 10px">{l s='save invoice project' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" id="hesabfa-setting-project-invoice" style="max-width: 250px">
            {foreach from=$projects item=i}
                <option {if $selectedProjectTitle eq $i.title} selected {/if} value="{$i.title}">{$i.title}</option>
            {/foreach}
        </select>

        <label style="margin-top: 10px">{l s='save invoice salesman' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" id="hesabfa-setting-salesman-invoice" style="max-width: 250px">
            {foreach from=$salesmen item=i}
                <option {if $selectedSalesmanName eq $i.code} selected {/if} value="{$i.code}">{$i.name}</option>
            {/foreach}
        </select>

        {* ================= Payment methods settings ================= *}

        <br><br>
        <strong class="text-primary">{l s='Payment methods Settings' mod='ps_hesabfa'}</strong>
        <hr>
        <small>{l s='Choose payment methods for each payment gateway' mod='ps_hesabfa'}</small>
        <br><br>
        <label>{l s='Card Transfer' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" style="max-width: 250px" id="hesabfa-setting-card-transfer-option">
            {foreach from=$banks item=b}
                <option {if $selectedCardTransferOption eq $b.id} selected {/if}
                        value="{$b.id}">{$b.name}</option>
            {/foreach}
        </select>

        <br><br>
        <label>{l s='Deposit to bank receipt' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" style="max-width: 250px" id="hesabfa-setting-deposit-transfer-option">
            {foreach from=$banks item=b}
                <option {if $selectedDepositTransferOption eq $b.id} selected {/if}
                        value="{$b.id}">{$b.name}</option>
            {/foreach}
        </select>

        <br><br>
        <label>{l s='Cheque Transfer' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" style="max-width: 250px" id="hesabfa-setting-cheque-transfer-option">
            {foreach from=$banks item=b}
                <option {if $selectedChequeTransferOption eq $b.id} selected {/if}
                        value="{$b.id}">{$b.name}</option>
            {/foreach}
        </select>

        <br><br>
        <label>{l s='Other Payment Gateways' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" style="max-width: 250px" id="hesabfa-setting-others-transfer-option">
            {foreach from=$banks item=b}
                <option {if $selectedOtherTransferOption eq $b.id} selected {/if}
                        value="{$b.id}">{$b.name}</option>
            {/foreach}
        </select>

        {* ================= Receipt settings ================= *}
        <br><br>
        <strong class="text-primary">{l s='Receipt Settings' mod='ps_hesabfa'}</strong>
        <hr>

        <label style="margin-top: 10px">{l s='In which statuses save invoice receive receipt in Hesabfa' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" id="hesabfa-setting-invoice-receipt-status" style="max-width: 250px">
            {foreach from=$orderStatusOptions item=i}
                <option {if $selectedInvoiceReceiptStatus eq $i.id} selected {/if} value="{$i.id}">{$i.name}</option>
            {/foreach}
        </select>

{*        <strong>*}
{*            <p style="margin-top: 15px;">*}
{*                {l s='In which bank register payment receipt' mod='ps_hesabfa'}*}
{*            </p>*}
{*        </strong>*}
{*        <select class="form-control payment-method" id="receipt-bank" style="max-width: 250px">*}
{*            {foreach from=$banks item=b}*}
{*                <option {if $selectedBankId eq $b.id} selected {/if}*}
{*                        value="{$b.id}">{$b.name}</option>*}
{*            {/foreach}*}
{*        </select>*}

    </div>

    <div class="panel-footer">
        <a href="javascript:void(0)" class="btn btn-default" id="save">
            <i class="process-icon-save"></i>
            {l s='Save' mod='ps_hesabfa'}</a>
    </div>
</div>

<script>
    jQuery(function ($) {
        $('#save').click(function () {

            const formData = {
                selectedBarcode: $('#hesabfa-settings-barcode').prop('selectedIndex'),
                updatePriceFromHesabfaToStore: $('#updatePriceFromHesabfaToStore').is(':checked') ? 1 : 0,
                updatePriceFromStoreToHesabfa: $('#updatePriceFromStoreToHesabfa').is(':checked') ? 1 : 0,
                updateQuantityFromHesabfaToStore: $('#updateQuantityFromHesabfaToStore').is(':checked') ? 1 : 0,

                selectedCustomerAddress: $('#hesabfa-setting-customer-address').prop('selectedIndex'),
                customerCategory: $('#hesabfa-setting-customer-category').val(),

                invoiceReference: $('#hesabfa-setting-invoice-reference').prop('selectedIndex'),
                invoiceStatus: $('#hesabfa-setting-invoice-status').val(),
                returnInvoiceStatus: $('#hesabfa-setting-return-invoice-status').val(),

                invoiceFreightStatus: $('#hesabfa-setting-freight-invoice-status').val(),
                freightInputValue: $('#hesabfa-setting-freight-input-value').val(),

                invoiceReceiptStatus: $('#hesabfa-setting-invoice-receipt-status').val(),

                paymentReceiptBankCode: $('#receipt-bank').val(),

                //new feature
                cardTransferOption: $('#hesabfa-setting-card-transfer-option').val(),
                depositTransferOption: $('#hesabfa-setting-deposit-transfer-option').val(),
                chequeTransferOption: $('#hesabfa-setting-cheque-transfer-option').val(),
                otherTransferOption: $('#hesabfa-setting-others-transfer-option').val(),

                projectTitle: $('#hesabfa-setting-project-invoice').val(),
                salesmanName: $('#hesabfa-setting-salesman-invoice').val(),
            }

            const data = {
                'ajax': true,
                'controller': 'HesabfaSettings',
                'action': 'SaveSettings',
                'token': token,
                'formData': formData
            };
            $.post('index.php', data, function (response) {
                if(response) {
                    alert("تنظیمات با موفقیت ذحیره شد.");
                }
            });
            return false;
        });

    });

</script>