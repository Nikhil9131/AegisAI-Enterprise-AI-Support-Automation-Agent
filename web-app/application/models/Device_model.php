<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Device_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_all($limit = 100, $offset = 0) {
        $this->db->select('d.*, e.full_name as employee_name, e.employee_code, e.department');
        $this->db->from('devices d');
        $this->db->join('employees e', 'e.id = d.employee_id', 'left');
        $this->db->order_by('d.id', 'ASC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_by_employee($employee_id) {
        $this->db->where('employee_id', $employee_id);
        return $this->db->get('devices')->result();
    }

    public function get_by_user_id($user_id) {
        $this->db->select('d.*, e.full_name as employee_name, e.employee_code, e.department');
        $this->db->from('devices d');
        $this->db->join('employees e', 'e.id = d.employee_id');
        $this->db->where('e.user_id', $user_id);
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        $this->db->select('d.*, e.full_name as employee_name, e.employee_code, e.department');
        $this->db->from('devices d');
        $this->db->join('employees e', 'e.id = d.employee_id', 'left');
        $this->db->where('d.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_serial($serial) {
        $this->db->select('d.*, e.full_name as employee_name, e.employee_code, e.department');
        $this->db->from('devices d');
        $this->db->join('employees e', 'e.id = d.employee_id', 'left');
        $this->db->where('d.serial_number', $serial);
        return $this->db->get()->row();
    }
}
