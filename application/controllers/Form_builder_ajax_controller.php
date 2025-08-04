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
    public function get_created_modules_ajx(){
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $order = $this->input->post("order");
        $search = $this->input->post("search");
        $search = $search['value'];
        $col = 0;
        $dir = "";
        if(!empty($order)){
            foreach($order as $o){
                $col = $o['column'];
                $dir= $o['dir'];
            }
        }
        if($dir != "asc" && $dir != "desc"){
            $dir = "desc";
        }		

        $document = $this->Form_builder_model->get_created_modules_ajx($length, $start, $search);
        
        $data = array();
        if(!empty($document)){
            $page = $start / $length + 1;
            $offset = ($page - 1) * $length + 1;
            foreach($document as $print){
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $print->module_name;
                $sub_array[] = date('d M, Y h:i A',strtotime($print->created_on));
                $sub_array[] = '<a class="btn btn-sm" href="' . base_url() . '' . $print->generated_files . '" download title="Download PHP CI3 Files"><i class="fa fa-download"></i></a>';
                
                $data[] = $sub_array; 
            }
        }
        $TotalProducts = $this->Form_builder_model->get_created_modules_ajx_count($search);
        
        $output = array(
            "draw" 				=> $draw,
            "recordsTotal" 		=> $TotalProducts,
            "recordsFiltered" 	=> $TotalProducts,
            "data" 				=> $data
        );
        echo json_encode($output);
        exit();
    }
}