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

            $this->get_sql_file($module_folder, $created_table_name, $fields);
            $this->get_model_file($module_folder, $module_name_used, $model_name, $created_table_name, $fields);
            $this->get_controller_file($module_folder, $module_name_used, $model_name, $controller_name, $redirect_url, $fields);
            $this->get_ajax_controller_file($module_folder, $module_name_used, $model_name, $created_table_name, $ajax_controller_name, $redirect_url, $fields);
            $this->get_route_file($module_folder, $module_name_used, $controller_name);

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
                $this->db->where('is_deleted', '0');
                $this->db->where('id', $field['dependent_module']);
                $dependent_module = $this->db->get('tbl_modules')->row();
                
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

    public function get_route_file($folder, $module_name_used, $controller_name) {
        $content = <<<EOD
            <?php defined("BASEPATH") or exit("No direct script access allowed");

            // Auto-generated routes for module: {$module_name_used}
            \$route['{$module_name_used}'] = '{$controller_name}/add_{$module_name_used}';
            \$route['{$module_name_used}/(:any)'] = '{$controller_name}/add_{$module_name_used}/\$1';
            \$route['{$module_name_used}_list'] = '{$controller_name}/{$module_name_used}_list';
            EOD;

        $file_name = 'routes.php';
        $file_path = FCPATH . $folder . $file_name;
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

    public function get_ajax_controller_file($folder, $module_name_used, $model_name, $table_name, $ajax_controller_name, $redirect_url, $fields) {
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
                \$search = \$this->input->post("search") != "" ? \$this->input->post("search") : (isset(\$search['value']) ? \$search['value'] : '');
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
        EOD;

            foreach ($fields as $field) {
                $column = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
                $ajaxFunction .= "\n                \$sub_array[] = \$print->$column;";
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
                \$this->load->model('$model_name');
            }
        $uniqueMethods
        $ajaxFunction
        }
        EOD;

        $file_name = $ajax_controller_name . '.php';
        $file_path = FCPATH . $folder . $file_name;
        file_put_contents($file_path, $controller);
        return $folder . $file_name;
    }

    public function get_controller_file($folder, $module_name_used, $model_name, $controller_name, $redirect_url, $fields) {
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
            \$this->load->model('$model_name');
            \$this->load->library('form_validation');
        }

        public function add_$module_name_used(){
            $validationString

            if (\$this->form_validation->run() === FALSE) {
                \$data['{$module_name_used}_list'] = \$this->{$model_name}->get_all_{$module_name_used}();
                \$data['single'] = \$this->{$model_name}->get_single_{$module_name_used}();
                \$this->load->view('$module_name_used', \$data);
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
            \$this->load->view('{$module_name_used}_list', \$data);
        }
    }
    EOD;

        $file_name = $controller_name . '.php';
        $file_path = FCPATH . $folder . $file_name;
        file_put_contents($file_path, $controller);
        return $folder . $file_name;
    }

    public function get_sql_file($folder, $table_name, $fields){
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

            $sql .= "  `$column_name` $type $unique$dependent_comment,\n";
        }

        $sql .= "  `status` ENUM('0','1') DEFAULT '1',\n";
        $sql .= "  `is_deleted` ENUM('0','2') DEFAULT '0',\n";
        $sql .= "  `created_on` DATETIME DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "  `updated_on` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
        $sql .= "  PRIMARY KEY (`id`)\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";     

        $file_name = $table_name . '.php';
        $file_path = FCPATH . $folder . $file_name;
        file_put_contents($file_path, $sql);
        return $folder . $file_name;
    }

    public function get_model_file($folder, $module_name_used, $model_name, $created_table_name, $fields){
        $dataFields = [];
        $searchableFields = [];
        $first = true;
        foreach ($fields as $field) {
            $column_name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($field['label_name'])));
            $label_name = $field['label_name'];
            $dataFields[] = "            '$column_name' => \$this->input->post('$column_name')";

            if (in_array(strtolower($field['data_type']), ['text', 'textarea', 'email'])) {
                if ($first) {
                    $searchableFields[] = "                \$this->db->like('$created_table_name.$column_name', \$search);";
                    $first = false;
                } else {
                    $searchableFields[] = "                \$this->db->or_like('$created_table_name.$column_name', \$search);";
                }
            }
        }
        $dataString = implode(",\n", $dataFields);

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
            \$this->db->where('is_deleted', '0');
            \$this->db->order_by('id', 'DESC');
            \$result = \$this->db->get('$created_table_name');
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
            \$this->db->where('$created_table_name.is_deleted', '0');
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
            \$this->db->where('$created_table_name.is_deleted', '0');
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
         
        $file_name = $model_name . '.php';
        $file_path = FCPATH . $folder . $file_name;
        file_put_contents($file_path, $model);
        return $folder . $file_name;
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

}