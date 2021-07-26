<div class="panel">
    <div class="panel-heading">
        {l s='Hesabfa Plugin Settings' mod='ps_hesabfa'}
    </div>
    <div class="panel-body">
        {* ================= Product settings ================= *}
        <strong class="text-primary">{l s='Product Settings' mod='ps_hesabfa'}</strong>
        <hr>

        <label>{l s='Barcode' mod='ps_hesabfa'}</label>&nbsp;
        <small>({l s='Which code use as Barcode in Hesabfa' mod='ps_hesabfa'})</small>
        <select class="form-control" style="max-width: 250px" id="hesabfa-settings-barcode">
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

        <p style="margin-top: 15px;">
            {l s='Select in every Payment Method which bank should be affected.' mod='ps_hesabfa'}
        </p>

        {* loop for payment methods *}
        {foreach from=$paymentMethods item=p}
            <label style="margin-top: 10px">{l s=$p.name mod='ps_hesabfa'}</label>&nbsp;
            <select class="form-control payment-method" data-id="{$p.id}" style="max-width: 250px">
                {foreach from=$banks item=b}
                    <option {if $selectedBanks[$p.id] eq $b.id} selected {/if}
                            value="{$b.id}">{$b.name}</option>
                {/foreach}
            </select>
        {/foreach}

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

                invoiceReceiptStatus: $('#hesabfa-setting-invoice-receipt-status').val()
            }

            formData["paymentMethods"] = [];
            const paymentMethodEls = $('.payment-method');

            for (let i = 0; i < paymentMethodEls.length; i++) {
                const p = paymentMethodEls[i];
                formData["paymentMethods"].push({ paymentMethodId: $(p).attr("data-id"), bankId: $(p).val() });
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