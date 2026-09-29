<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function authenticate($email, $password) {
        $this->db->select('u.*, r.name as role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id');
        $this->db->where('u.email', $email);
        $this->db->where('u.status', 'ACTIVE');
        $query = $this->db->get();

        if ($query->num_rows() === 1) {
            $user = $query->row();
            if (password_verify($password, $user->password_hash)) {
                // Update last login
                $this->db->where('id', $user->id);
                $this->db->update('users', array('last_login' => date('Y-m-d H:i:s')));
                return $user;
            }
        }
        return false;
    }

    public function get_by_id($id) {
        $this->db->select('u.*, r.name as role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id');
        $this->db->where('u.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_email($email) {
        $this->db->select('u.*, r.name as role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id');
        $this->db->where('u.email', $email);
        return $this->db->get()->row();
    }

    public function create_user($data) {
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function update_user($id, $data) {
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    public function get_all($limit = 100, $offset = 0) {
        $this->db->select('u.*, r.name as role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id');
        $this->db->order_by('u.id', 'ASC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_roles() {
        return $this->db->get('roles')->result();
    }

    public function count_users() {
        return $this->db->count_all('users');
    }
}
