<?php class Form_builder_model extends CI_model
{
    public function set_module(){
        $fields = $this->input->post('fields');
        // echo '<pre>'; print_r($fields); exit;
        if(!empty($fields)){
            $module_name = $this->input->post('module_name');
            $module_name_used = strtolower(str_replace(' ', '_', $this->input->post('module_name')));
            $created_table_name = 'tbl_' . $module_name_used;
            $model_name = ucfirst($module_name_used) . '_model';
            $controller_name = ucfirst($module_name_used) . '_controller';
            $ajax_controller_name = ucfirst($module_name_used) . '_ajax_controller';
            $redirect_url = $module_name_used;

            $module_folder = 'module_files/' . $module_name_used . '/';
            $full_module_path = FCPATH . $module_folder;

            if (!is_dir($full_module_path)) {
                mkdir($full_module_path, 0777, true);
            }

            $this->create_sql_file($module_folder, $created_table_name, $fields);
            $this->create_model_file($module_folder, $module_name_used, $model_name, $created_table_name, $fields);
            $this->create_controller_file($module_folder, $module_name_used, $model_name, $controller_name, $redirect_url, $fields);
            $this->create_ajax_controller_file($module_folder, $module_name_used, $model_name, $created_table_name, $ajax_controller_name, $redirect_url, $fields);
            $this->create_route_file($module_folder, $module_name_used, $controller_name);
            $this->create_form_view_file($module_folder, $model_name, $module_name_used, $fields);
            $this->create_list_view_file($module_folder, $module_name_used, $fields);
            $this->create_js_file($module_folder, $module_name_used, $ajax_controller_name, $fields);

            $data = array(
                'project'           =>  null,
                'module_name'       =>  $module_name,
                'description'       =>  $this->input->post('description'),
                'created_table_name'=>  $created_table_name,
                'created_on'        =>  date('Y-m-d H:i:s')
            );
            $this->db->insert('tbl_modules',$data);
            $module_creation_id = $this->db->insert_id();

            foreach($fields as $field){
                if(isset($field['dependent_module'])){
                    $this->db->where('is_deleted', '0');
                    $this->db->where('id', $field['dependent_module']);
                    $dependent_module = $this->db->get('tbl_modules')->row();
                }

                if(!empty($dependent_module)){
                    $this->db->where('is_deleted', '0');
                    $this->db->where('id', $field['dependent_module_field']);
                    $this->db->where('module_creation_id', $dependent_module->id);
                    $dependent_module_field = $this->db->get('tbl_module_fields')->row();
                }

                $data = array(
                    'module_creation_id'                    =>  $module_creation_id,
                    'label'                                 =>  $field['label_name'],
                    'column_name'                           =>  strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name']))),
                    'field_type'                            =>  $field['data_type'],
                    'values'                                =>  $field['data_type'] == 'dropdown' && $field['values'] != "" ? $field['values'] : null,
                    'length'                                =>  $field['length'],
                    'dependent_module_id'                   =>  !empty($dependent_module) ? $dependent_module->id : null,
                    'dependent_module_field_id'             =>  !empty($dependent_module)  && !empty($dependent_module_field) ? $dependent_module_field->id : null,
                    'dependent_module_field_column_name'    =>  !empty($dependent_module)  && !empty($dependent_module_field) ? $dependent_module_field->column_name : null,
                    'is_unique'                             =>  $field['is_unique'] == 'Yes' ? 'Yes' : 'No',
                    'is_required'                           =>  $field['is_required'] == 'Yes' ? 'Yes' : 'No',
                    'created_on'                            =>  date('Y-m-d H:i:s')
                );
                $this->db->insert('tbl_module_fields',$data);
            }

            $zip_path = FCPATH . 'module_files/' . $module_name_used . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($full_module_path),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath     = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($full_module_path));
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                $zip->close();
            }

            // Copy Files to Current Project Directory Start
            $target_controller_path = APPPATH . 'controllers/' . $module_name_used . '/';
            $target_model_path      = APPPATH . 'models/' . $module_name_used . '/';
            $target_view_path       = APPPATH . 'views/' . $module_name_used . '/';
            $target_js_path         = FCPATH . 'assets/js/modules/' . $module_name_used . '/';

            $paths_to_create = [
                $target_controller_path,
                $target_model_path,
                $target_view_path,
                $target_js_path
            ];

            foreach ($paths_to_create as $path) {
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
            }

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($full_module_path),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($full_module_path));

                    if (strpos($relativePath, '_controller.php') !== false) {
                        copy($filePath, $target_controller_path . basename($relativePath));

                    } elseif (strpos($relativePath, '_model.php') !== false) {
                        copy($filePath, $target_model_path . basename($relativePath));

                    } elseif (strpos($relativePath, '.js') !== false) {
                        copy($filePath, $target_js_path . basename($relativePath));

                    } elseif (strpos($relativePath, '.php') !== false) {
                        copy($filePath, $target_view_path . basename($relativePath));
                    }
                }
            }
            // Copy Files to Current Project Directory End

            function delete_folder_recursive($folder_path) {
                $files = array_diff(scandir($folder_path), array('.', '..'));
                foreach ($files as $file) {
                    $full_path = $folder_path . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($full_path)) {
                        delete_folder_recursive($full_path);
                    } else {
                        unlink($full_path);
                    }
                }
                return rmdir($folder_path);
            }
            delete_folder_recursive($full_module_path);

            $data = array(
                'generated_files' => 'module_files/' . $module_name_used . '.zip'
            );
            $this->db->where('id', $module_creation_id);
            $this->db->update('tbl_modules', $data);

            return true;
        }else{
            return false;
        }
    }

    public function create_form_view_file($folder, $model_name, $module_name_used, $fields){
        $clean_module_name = trim($module_name_used);
        $clean_module_name = str_replace('_', ' ', $clean_module_name);
        $clean_module_name = ucwords($clean_module_name);

        $export_columns = range(0, count($fields));
        $export_columns_js_array = json_encode($export_columns);

        $form_fields_html = '';
        
        foreach ($fields as $field) {
            $label = ucfirst(str_replace('_', ' ', $field['label_name']));
            $column = $field['label_name'];
            $column = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
            $data_type = $field['data_type'];
            $required = ($field['is_required'] ?? false) ? '<b class="require">*</b>' : '';
            $error_id = "{$column}_error";

            switch ($data_type) {
                case 'text':
                case 'email':
                case 'password':
                case 'number':
                case 'url':
                case 'mobile':
                case 'hidden':
                    $form_fields_html .= <<<EOD
        <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <label>{$label} {$required}</label>
            <input type="{$data_type}" class="form-control" name="{$column}" id="{$column}" value="<?php if(!empty(\$single)){ echo \$single->{$column}; }?>" placeholder="Enter {$label}">
            <div class="error" id="{$error_id}"></div>
        </div>

    EOD;
                    break;

                case 'date':
                case 'time':
                case 'datetime':
                    $picker_class = $data_type === 'date' ? 'datepicker' : ($data_type === 'time' ? 'timepicker' : 'datetimepicker');
                    $form_fields_html .= <<<EOD
        <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <label>{$label} {$required}</label>
            <input type="text" class="form-control {$picker_class}" name="{$column}" id="{$column}" value="<?php if(!empty(\$single)){ echo \$single->{$column}; }?>" placeholder="Select {$label}">
            <div class="error" id="{$error_id}"></div>
        </div>

    EOD;
                    break;

                case 'checkbox':
                    $form_fields_html .= <<<EOD
        <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <label><input type="checkbox" name="{$column}" id="{$column}" <?php if(!empty(\$single) && \$single->{$column}){ echo 'checked'; } ?>> {$label}</label>
            <div class="error" id="{$error_id}"></div>
        </div>

    EOD;
                    break;

                case 'radio':
                    $form_fields_html .= <<<EOD
        <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <label>{$label} {$required}</label><br>
            <label><input type="radio" name="{$column}" value="1" <?php if(!empty(\$single) && \$single->{$column} == 1){ echo 'checked'; } ?>> Yes</label>
            <label><input type="radio" name="{$column}" value="0" <?php if(!empty(\$single) && \$single->{$column} == 0){ echo 'checked'; } ?>> No</label>
            <div class="error" id="{$error_id}"></div>
        </div>

    EOD;
                    break;

                case 'file':
                    $form_fields_html .= <<<EOD
        <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <label>{$label} {$required}</label>
            <input type="file" class="form-control" name="{$column}" id="{$column}">
            <?php if(!empty(\$single) && !empty(\$single->{$column})){ ?>
                <a href="<?php echo base_url(\$single->{$column}); ?>" target="_blank">View File</a>
            <?php } ?>
            <div class="error" id="{$error_id}"></div>
        </div>

    EOD;
                    break;

                case 'dropdown':
        $values = explode(',', $field['values'] ?? '');
        $form_fields_html .= <<<EOD
            <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
                <label>{$label} {$required}</label>
                <select class="form-control chosen-select" name="{$column}" id="{$column}">
                    <option value="">Select {$label}</option>\n
        EOD;

        foreach ($values as $value) {
            $value = trim($value);
            $form_fields_html .= "<option value=\"{$value}\" <?=!empty(\$single) && \$single->{$column} == '{$value}' ? 'selected' : ''; ?>>{$value}</option>" . PHP_EOL;
        }

        $form_fields_html .= <<<EOD
                </select>
                <div class="error" id="{$error_id}"></div>
            </div>
    EOD;
                    break;

                case 'dropdown-dependent':
        $related_module_id = $field['dependent_module'] ?? null;
        $related_field_id = $field['dependent_module_field'] ?? null;

        if (!empty($related_module_id) && !empty($related_field_id)) {
            $ref_table = $this->get_table_name_by_module_id($related_module_id);
            $ref_column = $this->get_column_name_by_field_id($related_field_id);

            $form_fields_html .= <<<EOD
                <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
                    <label>{$label} {$required}</label>
                    <select class="form-control chosen-select" name="{$column}" id="{$column}">
                        <option value="">Select {$label}</option>
                        <?php
                            \$records = \$this->{$model_name}->get_dependent_data_all('{$ref_table}');
                            if(!empty(\$records)){
                                foreach(\$records as \$row){
                                    ?>
                                    <option value="<?=\$row->id; ?>" <?=!empty(\$single) && \$single->{$column} == \$row->id ? 'selected' : ''; ?>>
                                        <?=\$row->{$ref_column}; ?>
                                    </option>
                                    <?php
                                }
                            }
                        ?>
                    </select>
                    <div class="error" id="{$error_id}"></div>
                </div>
    EOD;
        }
                break;


                case 'textarea':
                    $form_fields_html .= <<<EOD
        <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <label>{$label} {$required}</label>
            <textarea class="form-control" name="{$column}" id="{$column}" placeholder="Enter {$label}"><?php if(!empty(\$single)){ echo \$single->{$column}; }?></textarea>
            <div class="error" id="{$error_id}"></div>
        </div>

    EOD;
                    break;

                case 'color':
                case 'range':
                    $form_fields_html .= <<<EOD
        <div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <label>{$label} {$required}</label>
            <input type="{$data_type}" class="form-control" name="{$column}" id="{$column}" value="<?php if(!empty(\$single)){ echo \$single->{$column}; }?>">
            <div class="error" id="{$error_id}"></div>
        </div>

    EOD;
                    break;
            }
        }

        $html = <<<EOD
            <?php include(APPPATH . 'views/header.php'); ?>
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
                                <h3 class="mb-3">Add {$clean_module_name}</h3>
                            </div>
                        </div>
                        <div class="client-body p-3">
                            <form method="post" name="master_form" id="master_form" enctype="multipart/form-data">
                                <input type="hidden" name="hidden_id" id="hidden_id" value="<?php if(!empty(\$single)){ echo \$single->id; }?>">
                                <div class="row">
                                    {$form_fields_html}
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <button style="margin-top: 10px;" type="submit" id="{$module_name_used}_submit" class="btn btn-success">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>  
                    </div>
                </div>
            </div>

            <?php include(APPPATH . 'views/footer.php'); ?>
            <!-- External JS Includes -->
            <script>
                var BASE_URL = '<?=base_url(); ?>';
                var formElement = 'master_form';
                var tableElement = 'example';
                var fileName = '{$module_name_used}-list';
                var submitBtn = '{$module_name_used}_submit';
                var exportColumns = {$export_columns_js_array};
            </script>
            <script src="<?= base_url('assets/js/modules/{$module_name_used}/custom_js.js') ?>"></script>
            EOD;
        // echo '<pre>' . htmlspecialchars($html) . '</pre>'; exit;

        $module_folder = $folder . '/views/';
        $full_module_path = FCPATH . $module_folder;

        if (!is_dir($full_module_path)) {
            mkdir($full_module_path, 0777, true);
        }

        $file_name = $module_name_used . '_form.php';
        $file_path = FCPATH . $module_folder . $file_name;
        file_put_contents($file_path, $html);
        return $folder . $file_name;
    }

    public function create_list_view_file($folder, $module_name_used, $fields){
        $clean_module_name = trim($module_name_used);
        $clean_module_name = str_replace('_', ' ', $clean_module_name);
        $clean_module_name = ucwords($clean_module_name);

        $export_columns = range(0, count($fields));
        $export_columns_js_array = json_encode($export_columns);

        $table_headers_html = '';
        $table_columns_count = count($fields);

        $sr_no_header = '<th>Sr. No.</th>';
        $action_header = '<th>Action</th>';

        foreach ($fields as $field) {
            $label = ucfirst(str_replace('_', ' ', $field['label_name']));
            $table_headers_html .= "<th>{$label}</th>\n";
        }

        $html = <<<EOD
            <?php include(APPPATH . 'views/header.php'); ?>
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
                                <h3 class="mb-3">{$clean_module_name} List</h3>
                            </div>
                        </div>
                        <div class="client-body p-3">
                                <table class="table table-striped responsive-utilities jambo_table" style="width: 100%;" id="example">
                                    <thead>
                                        <tr>
                                            {$sr_no_header}
                                            {$table_headers_html}
                                            {$action_header}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data to be populated via AJAX or controller -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include(APPPATH . 'views/footer.php'); ?>
            <!-- External JS Includes -->
            <script>
                var BASE_URL = '<?=base_url(); ?>';
                var formElement = 'master_form';
                var tableElement = 'example';
                var fileName = '{$module_name_used}-list';
                var submitBtn = '{$module_name_used}_submit';
                var exportColumns = {$export_columns_js_array};
            </script>
            <script src="<?= base_url('assets/js/modules/{$module_name_used}/custom_js.js') ?>"></script>
            EOD;
        // echo '<pre>' . htmlspecialchars($html) . '</pre>'; exit;

        $module_folder = $folder . '/views/';
        $full_module_path = FCPATH . $module_folder;

        if (!is_dir($full_module_path)) {
            mkdir($full_module_path, 0777, true);
        }

        $file_name = $module_name_used . '_list.php';
        $file_path = FCPATH . $module_folder . $file_name;
        file_put_contents($file_path, $html);

        return $folder . $file_name;
    }

    public function create_js_file($folder, $module_name_used, $ajax_url_path, $fields){
        $rules = [];
        $messages = [];
        $unique_checks_js = '';

        foreach ($fields as $field) {
            $label = ucfirst(str_replace('_', ' ', $field['label_name']));
            $column = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
            $data_type = strtolower($field['data_type']);

            if (!empty($field['is_required']) && strtolower($field['is_required']) === 'yes') {
                $rules[] = "            '{$column}': { required: true }";
                $msg = ($data_type === 'dropdown' || $data_type === 'select') ?
                    "Please select {$label}!" : "Please enter {$label}!";
                $messages[] = "            '{$column}': { required: '{$msg}' }";
            }

            if (!empty($field['is_unique']) && strtolower($field['is_unique']) === 'yes') {
                $unique_checks_js .= <<<EOD
                    $('#{$column}').on('keyup change', function () {
                        $.ajax({
                            type: "POST",
                            url: BASE_URL + "{$module_name_used}/{$ajax_url_path}/get_unique_{$column}",
                            data: {
                                '{$column}': $('#{$column}').val(),
                                id: $('#hidden_id').val() || ''
                            },
                            success: function (data) {
                                if (data == "0") {
                                    $('#{$column}_error').html('');
                                    $('#submit').show();
                                } else {
                                    $('#{$column}_error').html('This {$label} is already added');
                                    $('#submit').hide();
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                console.log(textStatus, errorThrown);
                            }
                        });
                    });
                EOD;
                }
            }

            $rules_js = implode(",\n", $rules);
            $messages_js = implode(",\n", $messages);

            $js = <<<EOD
        $(document).ready(function () {
            var oldExportAction = function (self, e, dt, button, config) {
                if (button[0].className.indexOf('buttons-excel') >= 0) {
                    if (\$.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)) {
                        \$.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config);
                    } else {
                        \$.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                    }
                } else if (button[0].className.indexOf('buttons-print') >= 0) {
                    \$.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
                }
            };

            var newExportAction = function (e, dt, button, config) {
                var self = this;
                var oldStart = dt.settings()[0]._iDisplayStart;
                dt.one('preXhr', function (e, s, data) {
                    data.start = 0;
                    data.length = 2147483647;
                    dt.one('preDraw', function (e, settings) {
                        oldExportAction(self, e, dt, button, config);
                        dt.one('preXhr', function (e, s, data) {
                            settings._iDisplayStart = oldStart;
                            data.start = oldStart;
                        });
                        setTimeout(dt.ajax.reload, 0);
                        return false;
                    });
                });
                dt.ajax.reload();
            };

            // DataTable initialization
            var table = $('#' + tableElement).DataTable({
                lengthChange: true,
                lengthMenu: [10, 25, 50, 100, 200],
                searching: true,
                responsive: true,
                processing: true,
                serverSide: true,
                cache: false,
                order: [],
                columnDefs: [
                    { orderable: false, targets: "_all" }
                ],
                buttons: [
                    {
                        extend: "excelHtml5",
                        messageBottom: '',
                        filename: fileName,
                        exportOptions: {
                            columns: exportColumns,
                            modifier: {
                                search: 'applied',
                                order: 'applied'
                            }
                        },
                        action: newExportAction
                    }
                ],
                dom: "Blfrtip",
                scrollX: true,
                ajax: {
                    url: BASE_URL + "{$module_name_used}/{$ajax_url_path}/get_{$module_name_used}_data_ajx",
                    type: "POST",
                    data: function (d) {}
                },
                complete: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            // Form validation
            $('#' + formElement).validate({
                ignore: [],
                rules: {
        {$rules_js}
                },
                messages: {
        {$messages_js}
                },
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                },
                submitHandler: function (form) {
                    form.submit();
                    $('#' + submitBtn).remove();
                }
            });
        });
        {$unique_checks_js}
        EOD;
        // echo '<pre>' . htmlspecialchars($js) . '</pre>'; exit;

        $js_folder = $folder . '/js/';
        $full_js_path = FCPATH . $js_folder;

        if (!is_dir($full_js_path)) {
            mkdir($full_js_path, 0777, true);
        }

        $file_name = 'custom_js.js';
        $file_path = $full_js_path . $file_name;
        file_put_contents($file_path, $js);

        return $js_folder . $file_name;
    }

    public function create_route_file($folder, $module_name_used, $controller_name) {
        $content = <<<EOD
            <?php defined("BASEPATH") or exit("No direct script access allowed");

            // Auto-generated routes for module: {$module_name_used}
            \$route['{$module_name_used}'] = '{$controller_name}/add_{$module_name_used}';
            \$route['{$module_name_used}/(:any)'] = '{$controller_name}/add_{$module_name_used}/\$1';
            \$route['{$module_name_used}_list'] = '{$controller_name}/{$module_name_used}_list';
            EOD;

        $module_folder = $folder . '/routes/';
        $full_module_path = FCPATH . $module_folder;

        if (!is_dir($full_module_path)) {
            mkdir($full_module_path, 0777, true);
        }

        $file_name = 'routes.php';
        $file_path = FCPATH . $module_folder . $file_name;
        file_put_contents($file_path, $content);
        return $folder . $file_name;
    }

    public function get_project_modules($project){
        $this->db->where('is_deleted', '0');
        if($project != ""){
            $this->db->where('project', $project);
        }
        $this->db->order_by('id', 'DESC');
        $result = $this->db->get('tbl_modules')->result();
        return $result;
    }

    public function get_project_module_fields($project, $module, $is_unique, $is_dependent){
        $this->db->distinct();
        $this->db->select('tbl_module_fields.*, tbl_modules.module_name, tbl_modules.description');
        $this->db->join('tbl_modules', 'tbl_modules.id = tbl_module_fields.module_creation_id');
        $this->db->where('tbl_module_fields.is_deleted', '0');
        $this->db->where('tbl_modules.is_deleted', '0');
        if($project != ""){
            $this->db->where('tbl_modules.project', $project);
        }
        if($module != ""){
            $this->db->where('tbl_module_fields.module_creation_id', $module);
        }
        if($is_unique != ""){
            $this->db->where('tbl_module_fields.is_unique', $is_unique);
        }
        if($is_dependent == "No"){
            $this->db->where('tbl_module_fields.dependent_module_id', null);
        }
        $this->db->order_by('id', 'DESC');
        $this->db->group_by('id', 'DESC');
        $result = $this->db->get('tbl_module_fields')->result();
        return $result;
    }

    public function create_ajax_controller_file($folder, $module_name_used, $model_name, $table_name, $ajax_controller_name, $redirect_url, $fields) {
        $uniqueMethods = '';
        foreach ($fields as $field) {
            if (strtolower($field['is_unique']) === 'yes') {
                $column_name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
                $uniqueMethods .= <<<EOD

            public function get_unique_{$column_name}() {
                \$this->{$model_name}->get_unique_{$column_name}();
            }
    EOD;
            }
        }

        $ajaxFunction = <<<EOD

            public function get_{$module_name_used}_data_ajx(){
                \$draw = intval(\$this->input->post("draw"));
                \$start = intval(\$this->input->post("start"));
                \$length = intval(\$this->input->post("length"));
                \$order = \$this->input->post("order");
                \$search = isset(\$this->input->post("search")['value']) ? \$this->input->post("search")['value'] : null;
                \$col = 0;
                \$dir = "";
                if(!empty(\$order)){
                    foreach(\$order as \$o){
                        \$col = \$o['column'];
                        \$dir= \$o['dir'];
                    }
                }
                if(\$dir != "asc" && \$dir != "desc"){
                    \$dir = "desc";
                }

                \$records = \$this->{$model_name}->get_all_{$module_name_used}_ajax(\$length, \$start, \$search);

                \$data = array();
                if(!empty(\$records)){
                    \$page = \$start / \$length + 1;
                    \$offset = (\$page - 1) * \$length + 1;
                    foreach(\$records as \$print){
                        \$sub_array = array();
                        \$sub_array[] = \$offset++;
    EOD;

        foreach ($fields as $field) {
            $column = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));

            $has_dependency = !empty($field['dependent_module']) && !empty($field['dependent_module_field']);

            if ($has_dependency) {
                $dep_module_id = $field['dependent_module'];
                $dep_field_id = $field['dependent_module_field'];
                $ref_table = $this->get_table_name_by_module_id($dep_module_id);
                $ref_column = $this->get_column_name_by_field_id($dep_field_id);
                $alias_column = "ref_{$column}_{$ref_column}";

                $ajaxFunction .= "\n                    \$sub_array[] = isset(\$print->$alias_column) ? \$print->$alias_column : '';";
            } else {
                $ajaxFunction .= "\n                    \$sub_array[] = \$print->$column;";
            }
        }

        $ajaxFunction .= <<<EOD

                        \$sub_array[] = '<span class="inline-action-btns">
                            <a href="' . base_url('{$redirect_url}/' . \$print->id) . '" class="edit-link" data-bs-toggle="tooltip" title="Edit">
                                <i class="icon"></i>
                            </a>
                            <a class="trigger-delete" data-title="Are you sure to delete this record?" data-message="" href="' . base_url('delete/' . \$print->id . '/{$table_name}') . '" data-bs-toggle="tooltip" title="Delete">
                                <i class="icon"></i>
                            </a>
                        </span>';

                        \$data[] = \$sub_array;
                    }
                }

                \$total = \$this->{$model_name}->get_all_{$module_name_used}_count_ajax(\$search);

                \$output = array(
                    "draw" => \$draw,
                    "recordsTotal" => \$total,
                    "recordsFiltered" => \$total,
                    "data" => \$data
                );
                echo json_encode(\$output);
                exit();
            }
    EOD;

        $controller = <<<EOD
    <?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class $ajax_controller_name extends CI_Controller {

        public function __construct(){
            parent::__construct();
            \$this->load->model('$module_name_used/$model_name');
        }
    $uniqueMethods
    $ajaxFunction
    }
    EOD;
        // echo '<pre>' . htmlspecialchars($controller) . '</pre>'; exit;

        $module_folder = $folder . '/controllers/';
        $full_module_path = FCPATH . $module_folder;

        if (!is_dir($full_module_path)) {
            mkdir($full_module_path, 0777, true);
        }

        $file_name = $ajax_controller_name . '.php';
        $file_path = FCPATH . $module_folder . $file_name;
        file_put_contents($file_path, $controller);
        return $folder . $file_name;
    }

    public function create_controller_file($folder, $module_name_used, $model_name, $controller_name, $redirect_url, $fields) {
        $validationRules = [];
        foreach ($fields as $field) {
            $column_name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
            $label = ucfirst(trim($field['label_name']));
            if (strtolower($field['is_required']) === 'yes') {
                $validationRules[] = "\$this->form_validation->set_rules('$column_name', '$label', 'required');";
            }
        }
        $validationString = implode("\n\t\t", $validationRules);

        $controller = <<<EOD
    <?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class $controller_name extends CI_Controller {

        public function __construct(){
            parent::__construct();
            \$this->load->model('$module_name_used/$model_name');
            \$this->load->library('form_validation');
        }

        public function add_$module_name_used(){
            $validationString

            if (\$this->form_validation->run() === FALSE) {
                \$data['{$module_name_used}_list'] = \$this->{$model_name}->get_all_{$module_name_used}();
                \$data['single'] = \$this->{$model_name}->get_single_{$module_name_used}();
                \$this->load->view('$module_name_used/{$module_name_used}_form', \$data);
            } else {
                \$result = \$this->{$model_name}->add_{$module_name_used}();

                if (\$result == "0") {
                    \$this->session->set_flashdata('success', 'Record inserted successfully');
                } else {
                    \$this->session->set_flashdata('success', 'Record updated successfully');
                }

                redirect('$redirect_url');
            }
        }

        public function {$module_name_used}_list() {
            \$data['{$module_name_used}_list'] = \$this->{$model_name}->get_all_{$module_name_used}();
            \$this->load->view('$module_name_used/{$module_name_used}_list', \$data);
        }
    }
    EOD;

        $module_folder = $folder . '/controllers/';
        $full_module_path = FCPATH . $module_folder;

        if (!is_dir($full_module_path)) {
            mkdir($full_module_path, 0777, true);
        }

        $file_name = $controller_name . '.php';
        $file_path = FCPATH . $module_folder . $file_name;
        file_put_contents($file_path, $controller);
        return $folder . $file_name;
    }

    public function create_sql_file($folder, $table_name, $fields){
        $sql = "CREATE TABLE `$table_name` (\n";
        $sql .= "  `id` INT(11) NOT NULL AUTO_INCREMENT,\n";

        foreach ($fields as $field) {
            $dependent_comment = '';
            $column_name = strtolower(str_replace(' ', '_', $field['label_name']));
            $type = $this->map_data_type($field['data_type'], $field['length']);
            $unique = (isset($field['is_unique']) && $field['is_unique'] === 'Yes') ? 'UNIQUE' : '';

            if (!empty($field['dependent_module']) && !empty($field['dependent_module_field'])) {
                $this->db->where('is_deleted', '0');
                $this->db->where('id', $field['dependent_module']);
                $dependent_module = $this->db->get('tbl_modules')->row();

                if (!empty($dependent_module)) {
                    $this->db->where('is_deleted', '0');
                    $this->db->where('id', $field['dependent_module_field']);
                    $this->db->where('module_creation_id', $dependent_module->id);
                    $dependent_module_field = $this->db->get('tbl_module_fields')->row();

                    if (!empty($dependent_module_field)) {
                        $ref_table = $dependent_module->created_table_name;
                        $ref_column = $dependent_module_field->column_name;
                        $dependent_comment = " COMMENT 'Depends on $ref_table.$ref_column'";
                    }
                }
            }

            $sql .= "  `$column_name` $type DEFAULT NULL $unique$dependent_comment,\n";
        }

        $sql .= "  `status` ENUM('0','1') DEFAULT '1',\n";
        $sql .= "  `is_deleted` ENUM('0','2') DEFAULT '0',\n";
        $sql .= "  `created_on` DATETIME DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "  `updated_on` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
        $sql .= "  PRIMARY KEY (`id`)\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";   

        $module_folder = $folder . '/database/';
        $full_module_path = FCPATH . $module_folder;

        if (!is_dir($full_module_path)) {
            mkdir($full_module_path, 0777, true);
        }

        $file_name = $table_name . '.sql';
        $file_path = FCPATH . $module_folder . $file_name;
        file_put_contents($file_path, $sql);
        
        $this->db->query($sql);

        return $folder . $file_name;
    }

    public function create_model_file($folder, $module_name_used, $model_name, $created_table_name, $fields) {
        $dataFields = [];
        $searchableFields = [];
        $selectFields = ["'$created_table_name.*'"];
        $joins = [];
        $first = true;

        foreach ($fields as $field) {
            $column_name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
            $dataFields[] = "            '$column_name' => \$this->input->post('$column_name')";

            $isTextType = in_array(strtolower($field['data_type']), ['text', 'textarea', 'email']);
            $dep_module_id = $field['dependent_module'] ?? null;
            $dep_field_id = $field['dependent_module_field'] ?? null;

            if (!empty($dep_module_id) && !empty($dep_field_id)) {
                $ref_table = $this->get_table_name_by_module_id($dep_module_id);
                $ref_column = $this->get_column_name_by_field_id($dep_field_id);
                $join_alias = "ref_{$column_name}";

                $joins[$join_alias] = [
                    'table' => $ref_table,
                    'alias' => $join_alias,
                    'on' => "$join_alias.id = $created_table_name.$column_name"
                ];

                $selectFields[] = "`$join_alias`.`$ref_column` AS {$join_alias}_{$ref_column}";
                $searchableFields[] = "                \$this->db->" . ($first ? "like" : "or_like") . "('$join_alias.$ref_column', \$search);";
            } else {
                $searchableFields[] = "                \$this->db->" . ($first ? "like" : "or_like") . "('$created_table_name.$column_name', \$search);";
            }
            $first = false;
        }

        $dataString = implode(",\n", $dataFields);
        $selectString = implode(",\n            ", $selectFields);

        $uniqueFunctions = '';
        foreach ($fields as $field) {
            if (strtolower($field['is_unique']) === 'yes') {
                $column_name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
                $uniqueFunctions .= <<<EOD

            public function get_unique_{$column_name}(){
                \$this->db->where('$column_name', \$this->input->post('$column_name'));
                if(\$this->input->post('id') != "0" && \$this->input->post('id') != ""){
                    \$this->db->where('id !=', \$this->input->post('id'));
                }
                \$this->db->where('is_deleted', '0');
                \$result = \$this->db->get('$created_table_name');
                echo \$result->num_rows();
            }
        EOD;
            }
        }

        $joinString = '';
        foreach ($joins as $j) {
            $joinString .= "        \$this->db->join('{$j['table']} as {$j['alias']}', '{$j['on']}', 'left');\n";
        }

        $searchCondition = '';
        if (!empty($searchableFields)) {
            $searchCondition = "\n        if (\$search != \"\") {\n            \$this->db->group_start();\n" .
                implode("\n", $searchableFields) .
                "\n            \$this->db->group_end();\n        }";
        }

        $model = <<<EOD
    <?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class $model_name extends CI_Model {

        public function add_$module_name_used(){
            \$data = array(
    $dataString
            );

            if(\$this->input->post('id') == ""){
                \$date = array(
                    'created_on' => date("Y-m-d H:i:s")
                );
                \$new_arr = array_merge(\$data, \$date);
                \$this->db->insert('$created_table_name', \$new_arr);
                return 0;
            } else {
                \$this->db->where('id', \$this->input->post('id'));
                \$this->db->update('$created_table_name', \$data);
                return 1;
            }
        }

        public function get_all_$module_name_used(){
            \$this->db->select(
                $selectString
            );
    $joinString        \$this->db->where('$created_table_name.is_deleted', '0');
            \$this->db->order_by('$created_table_name.id', 'DESC');
            \$result = \$this->db->get('$created_table_name');
            return \$result->result();
        }
            
        public function get_dependent_data_all(\$table_name){
            \$this->db->where('is_deleted', '0');
            \$result = \$this->db->get(\$table_name);
            return \$result->result();
        }

        public function get_single_$module_name_used(){
            \$this->db->where('is_deleted', '0');
            \$this->db->where('id', \$this->uri->segment(2));
            \$result = \$this->db->get('$created_table_name');
            return \$result->row();
        }
    $uniqueFunctions

        public function get_all_{$module_name_used}_ajax(\$length, \$start, \$search){
            \$this->db->select(
                $selectString
            );
    $joinString        \$this->db->where('$created_table_name.is_deleted', '0');
    EOD;

        foreach ($fields as $field) {
            $column_name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
            $model .= "\n        if (\$this->input->post('$column_name') != \"\") {\n            \$this->db->where('$created_table_name.$column_name', \$this->input->post('$column_name'));\n        }";
        }

        $model .= $searchCondition;

        $model .= <<<EOD

            \$this->db->order_by('$created_table_name.id', 'DESC');
            \$this->db->limit(\$length, \$start);
            \$result = \$this->db->get('$created_table_name');
            return \$result->result();
        }

        public function get_all_{$module_name_used}_count_ajax(\$search){
    $joinString        \$this->db->where('$created_table_name.is_deleted', '0');
    EOD;

        foreach ($fields as $field) {
            $column_name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
            $model .= "\n        if (\$this->input->post('$column_name') != \"\") {\n            \$this->db->where('$created_table_name.$column_name', \$this->input->post('$column_name'));\n        }";
        }

        $model .= $searchCondition;

        $model .= <<<EOD

            \$result = \$this->db->get('$created_table_name');
            return \$result->num_rows();
        }

    }
    EOD;
        // echo '<pre>' . htmlspecialchars($model) . '</pre>'; exit;

        $module_folder = $folder . '/model/';
        $full_module_path = FCPATH . $module_folder;

        if (!is_dir($full_module_path)) {
            mkdir($full_module_path, 0777, true);
        }

        $file_name = $model_name . '.php';
        $file_path = FCPATH . $module_folder . $file_name;
        file_put_contents($file_path, $model);
        return $folder . $file_name;
    }

    private function get_table_name_by_module_id($module_id) {
        $query = $this->db->get_where('tbl_modules', ['id' => $module_id]);
        return $query->num_rows() > 0 ? $query->row()->created_table_name : null;
    }

    private function get_column_name_by_field_id($field_id) {
        $query = $this->db->get_where('tbl_module_fields', ['id' => $field_id]);
        return $query->num_rows() > 0 ? $query->row()->column_name : null;
    }

    private function map_data_type($input_type, $length){
        switch ($input_type) {
            case 'text':
            case 'email':
            case 'password':
            case 'url':
            case 'color':
            case 'mobile':
            case 'file':
            case 'dropdown':
                return "VARCHAR($length)";
            case 'dropdown-dependent':
                return "INT($length)";
            case 'number':
            case 'range':
                return "INT($length)";
            case 'textarea':
                return "TEXT";
            case 'date':
                return "DATE";
            case 'time':
                return "TIME";
            case 'datetime':
                return "DATETIME";
            case 'checkbox':
            case 'radio':
            case 'hidden':
                return "TINYINT(1)";
            default:
                return "VARCHAR(255)";
        }
    }

	public function get_created_modules_ajx($length, $start, $search){
		$this->db->select('tbl_modules.*');		
		$this->db->join('tbl_module_fields','tbl_modules.id = tbl_module_fields.module_creation_id','left');
        
		if($search !=""){
            $this->db->group_start();
			$this->db->or_like('tbl_modules.module_name',$search);
			$this->db->or_like('tbl_modules.description',$search);
            $this->db->group_end();
		}	

        if($this->input->post('project_id') != ''){
            $this->db->where('tbl_modules.project_id',$this->input->post('project_id'));
        }	
	
        $this->db->where('tbl_modules.is_deleted','0');
		$this->db->order_by('tbl_modules.id','DESC');
		$this->db->group_by('tbl_modules.id','DESC');
		$this->db->limit($length,$start);
		$result = $this->db->get('tbl_modules');
		return $result->result();		
	}
	public function get_created_modules_ajx_count($search){
		$this->db->select('tbl_modules.*');		
		$this->db->join('tbl_module_fields','tbl_modules.id = tbl_module_fields.module_creation_id','left');

		if($search !=""){
            $this->db->group_start();
			$this->db->or_like('tbl_modules.module_name',$search);
			$this->db->or_like('tbl_modules.description',$search);
            $this->db->group_end();
		}	

        if($this->input->post('project_id') != ''){
            $this->db->where('tbl_modules.project_id',$this->input->post('project_id'));
        }	
	
        $this->db->where('tbl_modules.is_deleted','0');
		$this->db->order_by('tbl_modules.id','DESC');
		$this->db->group_by('tbl_modules.id','DESC');
		$result = $this->db->get('tbl_modules');
		return $result->num_rows();		
	}

    public function delete(){
        $data = array(
            'is_deleted' => '1'
        );
        $this->db->where('id', base64_decode($this->uri->segment(2)));
        $this->db->update('tbl_modules', $data);

        $this->db->where('module_creation_id', base64_decode($this->uri->segment(2)));
        $this->db->update('tbl_module_fields', $data);

        return true;
    }
}