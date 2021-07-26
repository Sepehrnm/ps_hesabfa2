<div class="panel">
    <div class="panel-heading">
        {l s='Sync Products Quantity and Price' mod='ps_hesabfa'}
    </div>
    <div class="panel-body">
        <p class="hesabfa-p mt-2">{l s='Sync Products Quantity and Price with Hesabfa' mod='ps_hesabfa'}</p>
        <div class="progress mt-1 mb-2" style="height: 5px; max-width: 400px; border: 1px solid silver"
             id="syncProductsProgress">
            <div class="progress-bar progress-bar-striped bg-success" id="syncProductsProgressBar"
                 role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0"
                 aria-valuemax="100"></div>
        </div>
        <div class="p-2 hesabfa-f">
            <label class="fw-bold mb-2">نکات مهم:</label>
            <ul>
                <li>با انجام این عملیات موجودی و قیمت محصولات در فروشگاه، بر اساس قیمت و موجودی آنها در حسابفا
                    تنظیم می شود.
                </li>
                <li>این عملیات بر اساس تنظیمات صورت گرفته در صفحه تنظیمات افزونه انجام می شود.</li>
            </ul>
        </div>
    </div>
    <button class="btn btn-primary" id="hesabfa_sync_products">{l s='Sync Products' mod='ps_hesabfa'}</button>
</div>

<script>
    jQuery(function ($) {
        $('#syncProductsProgress').hide();

        $('#hesabfa_sync_products').click(function () {
            $('#hesabfa_sync_products').prop('disabled', true);
            $('#syncProductsProgress').show();
            $('#syncProductsProgressBar').css('width', 0 + '%').attr('aria-valuenow', 0);
            syncProducts(1, 1, 1);
            return false;
        });
        function syncProducts(batch, totalBatch, total) {
            const data = {
                'ajax': true,
                'controller': 'Synchronization',
                'action': 'syncProducts',
                'batch': batch,
                'totalBatch': totalBatch,
                'total': total,
                'token': token
            };
            $.post('index.php', data, function (response) {
                if (response !== 'failed') {
                    const res = JSON.parse(response);

                    if(res.error) {
                        alert(res.errorMessage);
                        $('#syncProductsProgress').hide();
                        $('#hesabfa_sync_products').prop('disabled', false);
                        return false;
                    }

                    res.batch = parseInt(res.batch);
                    if (res.batch < res.totalBatch) {
                        let progress = (res.batch * 100) / res.totalBatch;
                        progress = Math.round(progress);
                        $('#syncProductsProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                        syncProducts(res.batch + 1, res.totalBatch, res.total);
                        return false;
                    } else {
                        $('#syncProductsProgressBar').css('width', 100 + '%').attr('aria-valuenow', 100);
                        setTimeout(() => {
                            $('#syncProductsProgress').hide();
                            $('#hesabfa_sync_products').prop('disabled', false);
                            alert('Sync products finished successfully.');
                            // top.location.replace(res.redirectUrl);
                        }, 1000);
                        return false;
                    }
                } else {
                    alert('Error syncing products.');
                    return false;
                }
            });
        }
    });
</script>