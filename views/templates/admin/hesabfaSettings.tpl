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
                <input type="checkbox" {if $updatePriceFromHesabfaToStore eq 1} checked {/if}
                id="updatePriceFromHesabfaToStore">
                {l s='Update price from Hesabfa to Store' mod='ps_hesabfa'}
            </label>
        </div>

        <div class="checkbox">
            <label>
                <input type="checkbox" {if $updatePriceFromStoreToHesabfa eq 1} checked {/if}
                       id="updatePriceFromStoreToHesabfa">
                {l s='Update price from Store to Hesabfa' mod='ps_hesabfa'}
            </label>
        </div>

        <div class="checkbox">
            <label>
                <input type="checkbox" {if $updateQuantityFromHesabfaToStore eq 1} checked {/if}
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
        <select class="form-control" style="max-width: 250px" id="hesabfa-setting-">
            <option {if $selectedCustomerAddress eq 0} selected {/if}>{l s='Use first customer address' mod='ps_hesabfa'}</option>
            <option {if $selectedCustomerAddress eq 1} selected {/if}>{l s='Invoice address' mod='ps_hesabfa'}</option>
            <option {if $selectedCustomerAddress eq 2} selected {/if}>{l s='Delivery address' mod='ps_hesabfa'}</option>
        </select>

        <div class="form-group" style="margin-top: 10px;">
            <label>Customers category name</label>
            <input type="text" class="form-control" id="hesabfa-setting-customer-category"
                   placeholder="Online store customers" style="max-width: 250px">
        </div>

        {* ================= Invoice settings ================= *}
        <br><br>
        <strong class="text-primary">{l s='Invoice Settings' mod='ps_hesabfa'}</strong>
        <hr>
        <label>{l s='Invoice reference number' mod='ps_hesabfa'}</label>&nbsp;
        <small>({l s='Which number use as reference number in Hesabfa' mod='ps_hesabfa'})</small>
        <select class="form-control" style="max-width: 250px" id="hesabfa-setting-invoice-reference">
            <option>{l s='Order ID' mod='ps_hesabfa'}</option>
            <option>{l s='Order Reference' mod='ps_hesabfa'}</option>
        </select>

        <label style="margin-top: 10px">{l s='In which statuses save invoice in Hesabfa' mod='ps_hesabfa'}</label>&nbsp;
        <select multiple class="form-control" id="hesabfa-setting-invoice-status" style="max-width: 250px">
            <option>1</option>
            <option>2</option>
        </select>

        <label style="margin-top: 10px">{l s='In which statuses save return invoice in Hesabfa' mod='ps_hesabfa'}</label>&nbsp;
        <select multiple class="form-control" id="hesabfa-setting-return-invoice-status" style="max-width: 250px">
            <option>1</option>
            <option>2</option>
        </select>

        {* ================= Receipt settings ================= *}
        <br><br>
        <strong class="text-primary">{l s='Receipt Settings' mod='ps_hesabfa'}</strong>
        <hr>

        <label style="margin-top: 10px">{l s='In which statuses save invoice receive receipt in Hesabfa' mod='ps_hesabfa'}</label>&nbsp;
        <select multiple class="form-control" id="hesabfa-setting-return-invoice-status" style="max-width: 250px">
            <option>1</option>
            <option>2</option>
        </select>

        {* loop for payment methods *}
        <label style="margin-top: 10px">{l s='payment method1' mod='ps_hesabfa'}</label>&nbsp;
        <select class="form-control" id="hesabfa-setting-return-invoice-status" style="max-width: 250px">
            <option>1</option>
            <option>2</option>
        </select>


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

            const selectedBarcode = $('#hesabfa-settings-barcode').prop('selectedIndex');;

            const data = {
                'ajax': true,
                'controller': 'HesabfaSettings',
                'action': 'SaveSettings',
                'token': token,
                'selected-barcode': selectedBarcode
            };
            $.post('index.php', data, function (response) {
                console.log(response);
                alert(response);
            });
            return false;
        });

    });

</script>