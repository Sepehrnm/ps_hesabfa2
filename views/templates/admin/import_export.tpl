<div class="panel">
    <div class="panel-heading">
        {l s='Export products to Hesabfa' mod='ps_hesabfa'}
    </div>
    <div class="panel-body">
        <p class="hesabfa-p mt-2">{l s='Export and add all online store products to Hesabfa' mod='ps_hesabfa'}</p>
        <div class="progress mt-1 mb-2" style="height: 5px; max-width: 400px; border: 1px solid silver"
             id="exportProductsProgress">
            <div class="progress-bar progress-bar-striped bg-success" id="exportProductsProgressBar"
                 role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0"
                 aria-valuemax="100"></div>
        </div>
        <div class="p-2 hesabfa-f">
            <label class="fw-bold mb-2">نکات مهم:</label>
            <ul>
                <li>با انجام این عملیات محصولات لینک نشده از فروشگاه وارد حسابفا می شوند.</li>
                <li>اگر محصولات از قبل هم در فروشگاه تعریف شده اند و هم در حسابفا و به هم لینک نشده اند باید از
                    گزینه
                    همسان سازی دستی محصولات استفاده کنید.
                </li>
                <li>با انجام این عملیات موجودی محصولات وارد حسابفا نمی شود و برای وارد کردن موجودی محصولات فروشگاه
                در حسابفا، باید از گزینه استخراج موجودی اول دوره استفاده کنید.</li>
            </ul>
        </div>
    </div>
    <button class="btn btn-primary" id="hesabfa_export_products">{l s='Export products' mod='ps_hesabfa'}</button>
</div>

<div class="panel">
    <div class="panel-heading">
        {l s='Export products opening quantity to Hesabfa' mod='ps_hesabfa'}
    </div>
    <div class="panel-body">
        <p class="hesabfa-p mt-2">{l s='Export products opening quantity to Hesabfa' mod='ps_hesabfa'}</p>
        <div class="progress mt-1 mb-2" style="height: 5px; max-width: 400px; border: 1px solid silver"
             id="exportProductsOpeningQuantityProgress">
            <div class="progress-bar progress-bar-striped bg-success" id="exportProductsOpeningQuantityProgressBar"
                 role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0"
                 aria-valuemax="100"></div>
        </div>
        <div class="p-2 hesabfa-f">
            <label class="fw-bold mb-2">نکات مهم:</label>
            <ul>
                <li>با انجام این عملیات موجودی کنونی محصولات در فروشگاه بعنوان موجودی اول دوره محصولات در حسابفا
                    ثبت می شوند.
                </li>
                <li>بطور کلی فقط یک بار باید از این گزینه استفاده کنید،
                    که این کار باید پس از خروج محصولات به حسابفا و یا پس از همسان سازی دستی تمام محصولات
                    انجام شود.
                </li>
            </ul>
        </div>
    </div>
    <button class="btn btn-primary" id="hesabfa_export_products_opening_quantity">{l s='Export products opening quantity' mod='ps_hesabfa'}</button>
</div>

<div class="panel">
    <div class="panel-heading">
        {l s='Export customers to Hesabfa' mod='ps_hesabfa'}
    </div>
    <div class="panel-body">
        <p class="hesabfa-p mt-2">{l s='Export and add all online store customers to Hesabfa' mod='ps_hesabfa'}</p>
        <div class="progress mt-1 mb-2" style="height: 5px; max-width: 400px; border: 1px solid silver"
             id="exportCustomersProgress">
            <div class="progress-bar progress-bar-striped bg-success" id="exportCustomersProgressBar"
                 role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0"
                 aria-valuemax="100"></div>
        </div>
        <div class="p-2 hesabfa-f">
            <label class="fw-bold mb-2">نکات مهم:</label>
            <ul>
                <li>با انجام این عملیات مشتریان لینک نشده از فروشگاه وارد حسابفا می شوند.</li>
                <li>
                    اگر یک مشتری بیش از یک بار وارد حسابفا شده است می توانید از گزینه ادغام تراکنش ها در حسابفا
                    استفاده کنید.
                </li>
            </ul>
        </div>
    </div>
    <button class="btn btn-primary" id="hesabfa_export_customers">{l s='Export customers' mod='ps_hesabfa'}</button>
</div>

<script>
    jQuery(function ($) {
        $('#exportProductsProgress').hide();
        $('#exportCustomersProgress').hide();
        $('#exportProductsOpeningQuantityProgress').hide();

        $('#hesabfa_export_products').click(function () {
            $('#hesabfa_export_products').prop('disabled', true);
            $('#exportProductsProgress').show();
            $('#exportProductsProgressBar').css('width', 0 + '%').attr('aria-valuenow', 0);
            exportProducts(1, 1, 1, 0);
            return false;
        });
        function exportProducts(batch, totalBatch, total, updateCount) {
            const data = {
                'ajax': true,
                'controller': 'ImportExport',
                'action': 'exportProducts',
                'batch': batch,
                'totalBatch': totalBatch,
                'total': total,
                'updateCount': updateCount,
                'token': token
            };
            $.post('index.php', data, function (response) {
                if (response !== 'failed') {
                    const res = JSON.parse(response);

                    if(res.error) {
                        alert(res.errorMessage);
                        $('#exportCustomersProgress').hide();
                        $('#hesabfa_export_customers').prop('disabled', false);
                        return false;
                    }

                    res.batch = parseInt(res.batch);
                    if (res.batch < res.totalBatch) {
                        let progress = (res.batch * 100) / res.totalBatch;
                        progress = Math.round(progress);
                        $('#exportProductsProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                        exportProducts(res.batch + 1, res.totalBatch, res.total, res.updateCount);
                        return false;
                    } else {
                        $('#exportProductsProgressBar').css('width', 100 + '%').attr('aria-valuenow', 100);
                        setTimeout(() => {
                            $('#exportProductsProgress').hide();
                            $('#hesabfa_export_products').prop('disabled', false);
                            alert('Export products finished successfully. total products exported: ' + res.updateCount);
                            // top.location.replace(res.redirectUrl);
                        }, 1000);
                        return false;
                    }
                } else {
                    alert('Error exporting products.');
                    return false;
                }
            });
        }

        $('#hesabfa_export_customers').click(function () {
            $('#hesabfa_export_customers').prop('disabled', true);
            $('#exportCustomersProgress').show();
            $('#exportCustomersProgressBar').css('width', 0 + '%').attr('aria-valuenow', 0);
            exportCustomers(1, 1, 1, 0);
            return false;
        });
        function exportCustomers(batch, totalBatch, total, updateCount) {
            const data = {
                'ajax': true,
                'controller': 'ImportExport',
                'action': 'exportCustomers',
                'batch': batch,
                'totalBatch': totalBatch,
                'total': total,
                'updateCount': updateCount,
                'token': token
            };
            $.post('index.php', data, function (response) {
                if (response !== 'failed') {
                    const res = JSON.parse(response);

                    if(res.error) {
                        alert(res.errorMessage);
                        $('#exportCustomersProgress').hide();
                        $('#hesabfa_export_customers').prop('disabled', false);
                        return false;
                    }

                    res.batch = parseInt(res.batch);
                    if (res.batch < res.totalBatch) {
                        let progress = (res.batch * 100) / res.totalBatch;
                        progress = Math.round(progress);
                        $('#exportCustomersProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                        exportProducts(res.batch + 1, res.totalBatch, res.total, res.updateCount);
                        return false;
                    } else {
                        $('#exportCustomersProgressBar').css('width', 100 + '%').attr('aria-valuenow', 100);
                        setTimeout(() => {
                            $('#exportCustomersProgress').hide();
                            $('#hesabfa_export_customers').prop('disabled', false);
                            alert('Export customers finished successfully. total customers exported: ' + res.updateCount);
                        }, 1000);
                        return false;
                    }
                } else {
                    alert('Error exporting customers.');
                    return false;
                }
            });
        }

        $('#hesabfa_export_products_opening_quantity').click(function () {
            $('#hesabfa_export_products_opening_quantity').prop('disabled', true);
            $('#exportProductsOpeningQuantityProgress').show();
            $('#exportProductsOpeningQuantityProgressBar').css('width', 0 + '%').attr('aria-valuenow', 0);
            exportProductsOpeningQuantity(1, 1, 1);
            return false;
        });
        function exportProductsOpeningQuantity(batch, totalBatch, total) {
            const data = {
                'ajax': true,
                'controller': 'ImportExport',
                'action': 'exportProductsOpeningQuantity',
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
                        $('#exportProductsOpeningQuantityProgress').hide();
                        $('#hesabfa_export_products_opening_quantity').prop('disabled', false);
                        return false;
                    }

                    res.batch = parseInt(res.batch);
                    if (res.batch < res.totalBatch) {
                        let progress = (res.batch * 100) / res.totalBatch;
                        progress = Math.round(progress);
                        $('#exportProductsOpeningQuantityProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                        exportProductsOpeningQuantity(res.batch + 1, res.totalBatch, res.total);
                        return false;
                    } else {
                        $('#exportProductsOpeningQuantityProgressBar').css('width', 100 + '%').attr('aria-valuenow', 100);
                        setTimeout(() => {
                            $('#exportProductsOpeningQuantityProgress').hide();
                            $('#hesabfa_export_products_opening_quantity').prop('disabled', false);
                            alert('Export products opening quantity finished successfully');
                        }, 1000);
                        return false;
                    }
                } else {
                    alert('Error exporting products.');
                    return false;
                }
            });
        }
    });
</script>