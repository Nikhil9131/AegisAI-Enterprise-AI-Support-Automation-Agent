<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function log_action($action, $entity_type, $entity_id, $details = null, $user_id = null, $status = 'EXECUTED') {
        $data = array(
            'user_id'     => $user_id ?: ($this->session->userdata('user_id') ?: null),
            'action'      => $action,
            'entity_type' => $entity_type,
            'entity_id'   => (string)$entity_id,
            'details'     => is_array($details) ? json_encode($details) : $details,
            'ip_address'  => $this->input->ip_address(),
            'status'      => $status,
            'created_at'  => date('Y-m-d H:i:s')
        );

        if ($status === 'APPROVED' || $status === 'EXECUTED') {
            $data['approved_by'] = $data['user_id'];
            $data['approved_at'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('audit_logs', $data);
        return $this->db->insert_id();
    }

    public function get_logs($limit = 50, $offset = 0) {
        $this->db->select('a.*, u.full_name as user_name, u.email as user_email, app.full_name as approver_name');
        $this->db->from('audit_logs a');
        $this->db->join('users u', 'u.id = a.user_id', 'left');
        $this->db->join('users app', 'app.id = a.approved_by', 'left');
        $this->db->order_by('a.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_pending_approvals() {
        $this->db->select('a.*, u.full_name as user_name, u.email as user_email');
        $this->db->from('audit_logs a');
        $this->db->join('users u', 'u.id = a.user_id', 'left');
        $this->db->where('a.status', 'PENDING');
        $this->db->order_by('a.id', 'DESC');
        return $this->db->get()->result();
    }

    public function resolve_approval($audit_id, $decision, $admin_id) {
        $status = ($decision === 'APPROVE') ? 'APPROVED' : 'REJECTED';
        $this->db->where('id', $audit_id);
        return $this->db->update('audit_logs', array(
            'status'      => $status,
            'approved_by' => $admin_id,
            'approved_at' => date('Y-m-d H:i:s')
        ));
    }
}
