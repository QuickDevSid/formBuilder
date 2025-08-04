<?php include('header.php'); ?>
<style type="text/css">
    .error {
        color: red;
        float: left;
    }
    .chosen-container {
        font-size: 14px;
    }
</style>
<div class="main-content" id="project-list">
    <div class="container-fluid p-0">
        <div class="card p-4 mb-4">
            <div class="client-header">
                <div class="client-title">
                    <h3 class="mb-3">Module List</h3>
                </div>
            </div>
            <div class="client-body p-3">
                <table class="table table-striped responsive-utilities jambo_table"style="width: 100%;" id="example">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Module Name</th>
                            <th>Created On</th>
                            <th>Files</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        var oldExportAction = function(self, e, dt, button, config) {
            if (button[0].className.indexOf('buttons-excel') >= 0) {
                if ($.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)) {
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config);
                } else {
                    $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                }
            } else if (button[0].className.indexOf('buttons-print') >= 0) {
                $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
            }
        };
        var newExportAction = function(e, dt, button, config) {
            var self = this;
            var oldStart = dt.settings()[0]._iDisplayStart;
            dt.one('preXhr', function(e, s, data) {
                data.start = 0;
                data.length = 2147483647;
                dt.one('preDraw', function(e, settings) {
                    oldExportAction(self, e, dt, button, config);
                    dt.one('preXhr', function(e, s, data) {
                        settings._iDisplayStart = oldStart;
                        data.start = oldStart;
                    });
                    setTimeout(dt.ajax.reload, 0);
                    return false;
                });
            });
            dt.ajax.reload();
        };


        let table = $('#example').DataTable({
            "lengthChange": true,
            "scrollX": true,
            "lengthMenu": [10, 25, 50, 100, 200, 500],
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "order": [],
            "columnDefs": [{
                "orderable": false,
                "targets": []
            }],
            "bom": 'Bfrtip',
            "pagingType": "simple_numbers",
            "language": {
                search: `<i class="icon icon-primary"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M2.66496 7.33427C2.66496 4.75602 4.75504 2.66594 7.33329 2.66594C9.91154 2.66594 12.0016 4.75602 12.0016 7.33427C12.0016 8.5924 11.5039 9.73428 10.6947 10.5738C10.6722 10.5911 10.6505 10.6102 10.6298 10.6308C10.6092 10.6515 10.5902 10.6731 10.5728 10.6957C9.7333 11.5049 8.59142 12.0026 7.33329 12.0026C4.75504 12.0026 2.66496 9.91252 2.66496 7.33427ZM11.0785 12.02C10.0522 12.8414 8.75011 13.3326 7.33329 13.3326C4.02051 13.3326 1.33496 10.6471 1.33496 7.33427C1.33496 4.02148 4.02051 1.33594 7.33329 1.33594C10.6461 1.33594 13.3316 4.02148 13.3316 7.33427C13.3316 8.75109 12.8404 10.0532 12.019 11.0795L14.4703 13.5308C14.73 13.7905 14.73 14.2116 14.4703 14.4713C14.2106 14.731 13.7895 14.731 13.5298 14.4713L11.0785 12.02Z" fill="#74798B"></path>
                            </svg>
                        </i>`,
                searchPlaceholder: "Search",
                lengthMenu: "Result per page  _MENU_",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {

                    "next": "Next  <i class='fa-solid fa-chevron-right'></i>",
                    "previous": "<i class='fa-solid fa-chevron-left'></i>  Back"
                }
            },
            lengthMenu: [
                [10, 25, 50, 100, 250, 500, -1],
                [10, 25, 50, 100, 250, 500, "All"]
            ],
            "layout": {
                topStart: 'search',
                topEnd: null,
                bottomStart: ['pageLength', 'info'],
                bottomEnd: 'paging',
            },
            "ajax": {
                "url": "<?= base_url(); ?>Form_builder_ajax_controller/get_created_modules_ajx",
                "type": "POST",
                "data": function(d) {
                    d.project_id = '<?php echo isset($_GET['project_id']) ? $_GET['project_id'] : ''; ?>';
                }
            },
            "complete": function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
        });
    });
</script>