<style>
    @import url('https://v1.fontapi.ir/css/Yekan');
    .panel, select {
        font-family: "Yekan", Tahoma, sans-serif !important;
    }
</style>
<div class="panel">
    <div class="panel-heading">
        {l s='Export products to Hesabfa' mod='ps_hesabfa'}
        &nbsp;&nbsp;&nbsp;
        <a href="?controller=AdminModules&configure=ps_hesabfa&token={$tokenHesabfaModuleConfigure}">
            [ {l s='Return to main page' mod='ps_hesabfa'} ]</a>
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

<div class="panel">
    <div class="panel-heading">
        {l s='Export orders to Hesabfa' mod='ps_hesabfa'}
    </div>
    <div class="panel-body">
        <p class="hesabfa-p mt-2">{l s='Export online store orders to Hesabfa as Invoice' mod='ps_hesabfa'}</p>
        <div class="progress mt-1 mb-2" style="height: 5px; max-width: 400px; border: 1px solid silver"
             id="exportOrdersProgress">
            <div class="progress-bar progress-bar-striped bg-success" id="exportOrdersProgressBar"
                 role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0"
                 aria-valuemax="100"></div>
        </div>
        <div class="p-2 hesabfa-f">
            <label class="fw-bold mb-2">نکات مهم:</label>
            <ul>
                <li>با انجام این عملیات سفارشات فروشگاه که در حسابفا ثبت نشده اند از تاریخ انتخاب شده بررسی و در
                    حسابفا ثبت می شوند.
                </li>
                <li>
                    علاوه بر فاکتور، رسید دریافت فاکتور و فاکتور برگشت از فروش نیز با توجه به وضعیت سفارش و
                    تنظیمات مربوطه ثبت می شوند.
                </li>
                <li>توجه کنید که بصورت نرمال با فعالسازی افزونه و تکمیل تنظیمات API
                    این همسان سازی بصورت خودکار انجام می شود و این گزینه صرفاْ برای مواقعی است که به دلایل فنی
                    مثل قطع اتصال فروشگاه با حسابفا و یا خطا و باگ این همسان سازی صورت نگرفته است.
                </li>
                <li>
                    تاریخ انتخاب شده باید در بازه آخرین سال مالی در حسابفا باشد.
                </li>
                <li>
                    با انتخاب گزینه ثبت مجدد تمام فاکتورها، فاکتورهایی که قبلاً ثبت شده اند نیز مجدداً بر روی فاکتور قبل
                    ثبت و ویرایش می شوند.
                </li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-3 col-md-2">
            <div class="input-group" style="max-width: 150px;">
                <input class="datetimepicker" type="text" id="hesabfa_sync_order_date" name="hesabfa_sync_order_date">
                <script type="text/javascript">
                    $(document).ready(function(){
                        $(".datetimepicker").datepicker({
                            prevText: '',
                            nextText: '',
                            dateFormat: 'yy-mm-dd'
                        });
                    });
                </script>
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3">
            <input type="checkbox" id="hesabfa_sync_order_overwrite"> {l s='Overwrite all invoices' mod='ps_hesabfa'}
        </div>
    </div>
    <button class="btn btn-primary" id="hesabfa_export_orders" style="margin-top: 5px;">{l s='Export orders' mod='ps_hesabfa'}</button>
</div>

<div class="panel">
    <div class="panel-heading">
        {l s='Export invoice receipts to Hesabfa' mod='ps_hesabfa'}
    </div>
    <div class="panel-body">
        <div class="progress mt-1 mb-2" style="height: 5px; max-width: 400px; border: 1px solid silver"
             id="exportReceiptsProgress">
            <div class="progress-bar progress-bar-striped bg-success" id="exportReceiptsProgressBar"
                 role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0"
                 aria-valuemax="100"></div>
        </div>
        <div class="p-2 hesabfa-f">
            <label class="fw-bold mb-2">نکات مهم:</label>
            <ul>
                <li>با انجام این عملیات رسید دریافت فاکتورهایی که در حسابفا ثبت شده اند از تاریخ انتخاب شده بررسی و در
                    حسابفا ثبت می شوند.
                </li>
                <li>
                    با انجام این عملیات رسید یا رسیدهای قبلی فاکتور حذف و رسید یا رسیدهای جدید برای فاکتور ثبت می شود.
                </li>
                <li>
                    تاریخ انتخاب شده باید در بازه آخرین سال مالی در حسابفا باشد.
                </li>
            </ul>

        </div>
    </div>
    <div class="input-group" style="max-width: 150px;">
        <input class="datetimepicker" type="text" id="hesabfa_sync_receipts_date" name="hesabfa_sync_receipts_date">
        <script type="text/javascript">
            $(document).ready(function(){
                $(".datetimepicker").datepicker({
                    prevText: '',
                    nextText: '',
                    dateFormat: 'yy-mm-dd'
                });
            });
        </script>
        <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
    </div>
    <button class="btn btn-primary" id="hesabfa_export_receipts" style="margin-top: 5px;">{l s='Export invoice receipts' mod='ps_hesabfa'}</button>
</div>

<script>
    jQuery(function ($) {
        $('.progress').hide();

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
                        $('#exportProductsProgressBar').hide();
                        $('#hesabfa_export_products').prop('disabled', false);
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
                        exportCustomers(res.batch + 1, res.totalBatch, res.total, res.updateCount);
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

        $('#hesabfa_export_orders').click(function () {
            $('#hesabfa_export_orders').prop('disabled', true);
            $('#exportOrdersProgress').show();
            $('#exportOrdersProgressBar').css('width', 0 + '%').attr('aria-valuenow', 0);
            exportOrders(1, 1, 1, 0);
            return false;
        });
        function exportOrders(batch, totalBatch, total, updateCount) {
            const data = {
                'ajax': true,
                'controller': 'ImportExport',
                'action': 'exportOrders',
                'batch': batch,
                'totalBatch': totalBatch,
                'total': total,
                'updateCount': updateCount,
                'token': token,
                'date': $('#hesabfa_sync_order_date').val(),
                'overwrite': $('#hesabfa_sync_order_overwrite').prop('checked'),
            };
            $.post('index.php', data, function (response) {
                if (response !== 'failed') {
                    const res = JSON.parse(response);

                    if(res.error) {
                        alert(res.errorMessage);
                        $('#exportOrdersProgress').hide();
                        $('#hesabfa_export_orders').prop('disabled', false);
                        return false;
                    }

                    res.batch = parseInt(res.batch);
                    if (res.batch < res.totalBatch) {
                        let progress = (res.batch * 100) / res.totalBatch;
                        progress = Math.round(progress);
                        $('#exportOrdersProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                        setTimeout(()=> {
                            exportOrders(res.batch + 1, res.totalBatch, res.total, res.updateCount);
                        }, 5000);
                        return false;
                    } else {
                        $('#exportOrdersProgressBar').css('width', 100 + '%').attr('aria-valuenow', 100);
                        setTimeout(() => {
                            $('#exportOrdersProgress').hide();
                            $('#hesabfa_export_orders').prop('disabled', false);
                            alert('Export orders finished successfully. total orders exported: ' + res.updateCount);
                        }, 1000);
                        return false;
                    }
                } else {
                    alert('Error exporting orders.');
                    return false;
                }
            });
        }

        $('#hesabfa_export_receipts').click(function () {
            $('#hesabfa_export_receipts').prop('disabled', true);
            $('#exportReceiptsProgress').show();
            $('#exportReceiptsProgressBar').css('width', 0 + '%').attr('aria-valuenow', 0);
            exportReceipts(1, 1, 1, 0);
            return false;
        });
        function exportReceipts(batch, totalBatch, total, updateCount) {
            const data = {
                'ajax': true,
                'controller': 'ImportExport',
                'action': 'exportReceipts',
                'batch': batch,
                'totalBatch': totalBatch,
                'total': total,
                'updateCount': updateCount,
                'token': token,
                'date': $('#hesabfa_sync_receipts_date').val()
            };
            $.post('index.php', data, function (response) {
                if (response !== 'failed') {
                    const res = JSON.parse(response);

                    if(res.error) {
                        alert(res.errorMessage);
                        $('#exportReceiptsProgress').hide();
                        $('#hesabfa_export_receipts').prop('disabled', false);
                        return false;
                    }

                    res.batch = parseInt(res.batch);
                    if (res.batch < res.totalBatch) {
                        let progress = (res.batch * 100) / res.totalBatch;
                        progress = Math.round(progress);
                        $('#exportReceiptsProgressBar').css('width', progress + '%').attr('aria-valuenow', progress);
                        setTimeout(()=> {
                            exportReceipts(res.batch + 1, res.totalBatch, res.total, res.updateCount);
                        }, 5000);
                        return false;
                    } else {
                        $('#exportReceiptsProgressBar').css('width', 100 + '%').attr('aria-valuenow', 100);
                        setTimeout(() => {
                            $('#exportReceiptsProgress').hide();
                            $('#hesabfa_export_receipts').prop('disabled', false);
                            alert('Export invoice receipts finished successfully. total receipts exported: ' + res.updateCount);
                        }, 1000);
                        return false;
                    }
                } else {
                    alert('Error exporting receipts.');
                    return false;
                }
            });
        }
    });
</script>