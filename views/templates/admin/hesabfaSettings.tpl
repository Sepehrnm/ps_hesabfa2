<div class="panel">
    <div class="panel-heading">
        {l s='Hesabfa Plugin Settings' mod='ps_hesabfa'}
    </div>
    <div class="panel-body">

    </div>
    <a href="javascript:void(0)" class="btn btn-default">
        <i class="process-icon-save"></i>
        {l s='Save' mod='ps_hesabfa'}</a>
</div>

<script>
    jQuery(function ($) {
        $('#submit').click(function () {
            const data = {
                'ajax': true,
                'controller': 'ImportExport',
                'action': 'test',
                'token': token
            };
            $.post('index.php', data, function (response) {
                console.log(response);
                alert(response);
            });
            return false;
        });

    });

</script>