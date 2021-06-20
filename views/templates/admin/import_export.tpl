<div class="panel">
    <div class="panel-heading">
        hello new module
    </div>
    <div class="panel-body">
        dsklfjsldk kdsjflk sdjlfkjsdlf <br>
        dsfsdfdsfdsfs
    </div>
    <button class="btn btn-success" id="submit">submit</button>
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