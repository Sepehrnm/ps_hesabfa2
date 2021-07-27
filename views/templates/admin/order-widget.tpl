<div class="card-header">
    <h3 class="card-header-title">{l s='Hesabfa' mod='ps_hesabfa'}</h3>
</div>
<div class="card-body">
    <div>
        {if $invoiceNumber neq false}
            <div>{l s='Invoice number in Hesabfa' mod='ps_hesabfa'}: {$invoiceNumber}</div>
        {else}
            <div>{l s='Invoice is not submited in Hesabfa' mod='ps_hesabfa'}.</div>
        {/if}
    </div>
    <div class="mt-2">
        <button type="button"
                class="btn btn-primary btn-sm" id="hesabfaSaveInvoice">{l s='Save Invoice' mod='ps_hesabfa'}</button>
        <button type="button"
                class="btn btn-primary btn-sm" id="hesabfaSaveReceipt">{l s='Save Receipt' mod='ps_hesabfa'}</button>
        <button type="button"
                class="btn btn-danger btn-sm" id="hesabfaClearInvoiceLink">{l s='Delete Link with Hesabfa' mod='ps_hesabfa'}</button>
    </div>
</div>

<script>
    jQuery(function ($) {

        $('#hesabfaSaveInvoice').click(function () {
            $('#hesabfaSaveInvoice').prop('disabled', true);

            const data = {
                'ajax': true,
                'controller': 'HesabfaWidgets',
                'action': 'saveInvoice',
                'orderId': {$orderId},
                'token': '{$tokenHesabfaWidgets}'
            };
            $.post('index.php', data, function (response) {
                $('#hesabfaSaveInvoice').prop('disabled', false);
                if (response !== 'failed') {
                    const res = JSON.parse(response);
                    if(res) {
                        alert('Invoice saved successfully.');
                        location.reload();
                    } else {
                        alert('Invoice save failed. see log for details.');
                    }
                } else {
                    alert('Error saving invoice.');
                    return false;
                }
            });

            return false;
        });

        $('#hesabfaSaveReceipt').click(function () {
            $('#hesabfaSaveReceipt').prop('disabled', true);

            const data = {
                'ajax': true,
                'controller': 'HesabfaWidgets',
                'action': 'saveInvoiceReceipt',
                'orderId': {$orderId},
                'token': '{$tokenHesabfaWidgets}'
            };
            $.post('index.php', data, function (response) {
                $('#hesabfaSaveReceipt').prop('disabled', false);
                if (response !== 'failed') {
                    const res = JSON.parse(response);
                    if(res) {
                        alert('Invoice Receipt saved successfully.');
                    } else {
                        alert('Invoice receipt save failed. see log for details.');
                    }
                } else {
                    alert('Error saving invoice receipt.');
                    return false;
                }
            });

            return false;
        });

        $('#hesabfaClearInvoiceLink').click(function () {
            $('#hesabfaClearInvoiceLink').prop('disabled', true);

            const data = {
                'ajax': true,
                'controller': 'HesabfaWidgets',
                'action': 'clearInvoiceLinkWithHesabfa',
                'orderId': {$orderId},
                'token': '{$tokenHesabfaWidgets}'
            };
            $.post('index.php', data, function (response) {
                $('#hesabfaClearInvoiceLink').prop('disabled', false);
                if (response !== 'failed') {
                    const res = JSON.parse(response);
                    if(res) {
                        alert('Invoice Link with Hesabfa removed.');
                        location.reload();
                    } else {
                        alert('Error removing invoice link. see log for details.');
                    }
                } else {
                    alert('Error removing invoice link. see log for details.');
                    return false;
                }
            });

            return false;
        });
    });
</script>

