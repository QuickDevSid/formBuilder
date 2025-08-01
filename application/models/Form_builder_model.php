<?php class Form_builder_model extends CI_model
{
    public function set_module(){
        $fields = $this->input->post('fields');
        // echo '<pre>'; print_r($fields); exit;
        if(!empty($fields)){
            $module_name = $this->input->post('module_name');
            $module_name_used = strtolower(str_replace(' ', '_', $this->input->post('module_name')));
            $created_table_name = 'tbl_' . $module_name_used;

            $sql_file_path = $this->get_sql_file($created_table_name, $fields);

            $data = array(
                'project'           =>  null,
                'module_name'       =>  $module_name,
                'description'       =>  $this->input->post('description'),
                'created_table_name'=>  $created_table_name,
                'created_on'        =>  date('Y-m-d H:i:s')
            );
            $this->db->insert('tbl_modules',$data);
            $module_creation_id = $this->db->insert_id();

            $generated_files = null;
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
                    'created_on'                            =>  date('Y-m-d H:i:s')
                );
                $this->db->insert('tbl_module_fields',$data);
            }
            
            $data = array(
                'generated_files'   =>  $sql_file_path
            );
            $this->db->where('id', $module_creation_id);
            $this->db->update('tbl_modules',$data);

            return true;
        }else{
            return false;
        }
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

    public function get_sql_file($table_name, $fields){
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

        $path = 'module_files/sql/';
        $sql_dir = FCPATH . $path;
        if (!is_dir($sql_dir)) {
            mkdir($sql_dir, 0777, true);
        }
        $file_name = $table_name . '.sql';
        $file_path = $sql_dir . $file_name;
        $path = $path . $file_name;

        file_put_contents($file_path, $sql);

        return $path;
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