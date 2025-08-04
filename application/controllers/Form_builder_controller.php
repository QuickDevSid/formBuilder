<?php defined('BASEPATH') or exit('No direct script access allowed');
class Form_builder_controller extends CI_Controller
{
	public function __construct(){
		parent::__construct();
	}
    
	public function index(){
		$this->load->view('index');
	}
    
	public function creation(){
        $this->form_validation->set_rules('module_name', 'Module Name', 'required');
        if ($this->form_validation->run() === FALSE) {
			$this->load->view('form_builder');
        } else {
            $res = $this->Form_builder_model->set_module();
            if ($res) {
                $this->session->set_flashdata('success', 'Module Created Successfully !');
                redirect('list');
            } else {
                $this->session->set_flashdata('message', 'Fields Not Found !');
                redirect('creation');
            }
        }
	}

	public function list(){
		$this->load->view('list');
	}
}