<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_all($limit = 100, $offset = 0) {
        $this->db->select('e.*, u.avatar, count(d.id) as device_count');
        $this->db->from('employees e');
        $this->db->join('users u', 'u.id = e.user_id', 'left');
        $this->db->join('devices d', 'd.employee_id = e.id', 'left');
        $this->db->group_by('e.id');
        $this->db->order_by('e.id', 'ASC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('employees')->row();
    }

    public function get_by_user_id($user_id) {
        $this->db->where('user_id', $user_id);
        return $this->db->get('employees')->row();
    }

    public function get_by_code($code) {
        $this->db->where('employee_code', $code);
        return $this->db->get('employees')->row();
    }
}
