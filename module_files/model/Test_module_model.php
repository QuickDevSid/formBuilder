<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_module_model extends CI_Model {

    public function add_test_module(){
        $data = array(
            'name' => $this->input->post('name'),
            'mobile_no' => $this->input->post('mobile_no'),
            'email' => $this->input->post('email')
        );

        if($this->input->post('id') == ""){
            $date = array(
                'created_on' => date("Y-m-d H:i:s")
            );
            $new_arr = array_merge($data, $date);
            $this->db->insert('tbl_test_module', $new_arr);
            return 0;
        } else {
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('tbl_test_module', $data);
            return 1;
        }
    }

    public function get_all_test_module(){
        $this->db->where('is_deleted', '0');
        $this->db->order_by('id', 'DESC');
        $result = $this->db->get('tbl_test_module');
        return $result->result();
    }

    public function get_single_test_module(){
        $this->db->where('is_deleted', '0');
        $this->db->where('id', $this->uri->segment(2));
        $result = $this->db->get('tbl_test_module');
        return $result->row();
    }

    public function get_unique_mobile_no(){
        $this->db->where('mobile_no', $this->input->post('mobile_no'));
        if($this->input->post('id') != "0" && $this->input->post('id') != ""){
            $this->db->where('id !=', $this->input->post('id'));
        }
        $this->db->where('is_deleted', '0');
        $result = $this->db->get('tbl_test_module');
        echo $result->num_rows();
    }
    public function get_unique_email(){
        $this->db->where('email', $this->input->post('email'));
        if($this->input->post('id') != "0" && $this->input->post('id') != ""){
            $this->db->where('id !=', $this->input->post('id'));
        }
        $this->db->where('is_deleted', '0');
        $result = $this->db->get('tbl_test_module');
        echo $result->num_rows();
    }

    public function get_all_test_module_ajax($length, $start, $search){
        $this->db->where('tbl_test_module.is_deleted', '0');
        if ($this->input->post('name') != "") {
            $this->db->where('tbl_test_module.name', $this->input->post('name'));
        }
        if ($this->input->post('mobile_no') != "") {
            $this->db->where('tbl_test_module.mobile_no', $this->input->post('mobile_no'));
        }
        if ($this->input->post('email') != "") {
            $this->db->where('tbl_test_module.email', $this->input->post('email'));
        }
        if ($search != "") {
            $this->db->group_start();
                $this->db->like('tbl_test_module.name', $search);
                $this->db->or_like('tbl_test_module.mobile_no', $search);
                $this->db->or_like('tbl_test_module.email', $search);
            $this->db->group_end();
        }
        $this->db->order_by('tbl_test_module.id', 'DESC');
        $this->db->limit($length, $start);
        $result = $this->db->get('tbl_test_module');
        return $result->result();
    }

    public function get_all_test_module_count_ajax($search){
        $this->db->where('tbl_test_module.is_deleted', '0');
        if ($this->input->post('name') != "") {
            $this->db->where('tbl_test_module.name', $this->input->post('name'));
        }
        if ($this->input->post('mobile_no') != "") {
            $this->db->where('tbl_test_module.mobile_no', $this->input->post('mobile_no'));
        }
        if ($this->input->post('email') != "") {
            $this->db->where('tbl_test_module.email', $this->input->post('email'));
        }
        if ($search != "") {
            $this->db->group_start();
                $this->db->like('tbl_test_module.name', $search);
                $this->db->or_like('tbl_test_module.mobile_no', $search);
                $this->db->or_like('tbl_test_module.email', $search);
            $this->db->group_end();
        }
        $result = $this->db->get('tbl_test_module');
        return $result->num_rows();
    }

}