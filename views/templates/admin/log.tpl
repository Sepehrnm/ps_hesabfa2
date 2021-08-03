<div class="panel">
    <div class="panel-heading">
        {l s='Hesabfa plugin events and errors log' mod='ps_hesabfa'}
        &nbsp;&nbsp;&nbsp;
        <a href="?controller=AdminModules&configure=ps_hesabfa&token={$tokenHesabfaModuleConfigure}">
            [ {l s='Return to main page' mod='ps_hesabfa'} ]</a>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <button class="btn btn-primary" id="hesabfa_clear_log">{l s='Clear log' mod='ps_hesabfa'}</button>
            </div>
            <div class="col-md-9">
                    <div class="form-group">
                        <label for="logFilePath">{l s='Log file address' mod='ps_hesabfa'}</label>
                        <input type="text" class="form-control" style="text-align: left" value="{$logFilePath}" id="logFilePath" readonly>
                    </div>
            </div>
        </div>

        <textarea rows="35"  style="width: 100%; box-sizing: border-box; margin-top: 10px;
         direction: ltr; background-color: #f5f5f5">{$log}</textarea>
    </div>
</div>

<script>
    jQuery(function ($) {
        $('#hesabfa_clear_log').click(function () {
            $('#hesabfa_clear_log').prop('disabled', true);

            const data = {
                'ajax': true,
                'controller': 'Log',
                'action': 'clearLog',
                'token': token
            };

            $.post('index.php', data, function (response) {
                $('#hesabfa_clear_log').prop('disabled', false);

                if (response !== 'failed') {
                    location.reload();
                } else {
                    alert('Error clearing log.');
                    return false;
                }
            });

            return false;
        });
    });
</script>