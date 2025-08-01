<?php defined('BASEPATH') or exit('No direct script access allowed');
class Form_builder_ajax_controller extends CI_Controller
{    
	public function get_project_modules_data(){
        $res = $this->Form_builder_model->get_project_modules($this->input->post('project'));
        echo json_encode($res);
        exit;
    }
	public function get_project_module_fields_data(){
        $res = $this->Form_builder_model->get_project_module_fields($this->input->post('project'), $this->input->post('module'), $this->input->post('is_unique'), $this->input->post('is_dependent'));
        echo json_encode($res);
        exit;
    }
}