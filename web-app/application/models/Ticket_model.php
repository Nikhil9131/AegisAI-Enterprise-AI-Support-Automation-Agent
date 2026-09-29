<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_tickets($filters = array(), $limit = 50, $offset = 0) {
        $this->db->select('t.*, u.full_name as requester_name, u.email as requester_email, u.department as requester_dept, a.full_name as agent_name');
        $this->db->from('tickets t');
        $this->db->join('users u', 'u.id = t.user_id', 'left');
        $this->db->join('users a', 'a.id = t.assigned_agent_id', 'left');

        if (!empty($filters['user_id'])) {
            $this->db->where('t.user_id', $filters['user_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('t.status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $this->db->where('t.priority', $filters['priority']);
        }
        if (!empty($filters['category'])) {
            $this->db->where('t.category', $filters['category']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $this->db->group_start();
            $this->db->like('t.ticket_number', $search);
            $this->db->or_like('t.title', $search);
            $this->db->or_like('t.description', $search);
            $this->db->group_end();
        }

        $this->db->order_by('t.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function count_tickets($filters = array()) {
        $this->db->from('tickets t');
        if (!empty($filters['user_id'])) {
            $this->db->where('t.user_id', $filters['user_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('t.status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $this->db->where('t.priority', $filters['priority']);
        }
        if (!empty($filters['category'])) {
            $this->db->where('t.category', $filters['category']);
        }
        return $this->db->count_all_results();
    }

    public function get_by_id($id) {
        $this->db->select('t.*, u.full_name as requester_name, u.email as requester_email, u.department as requester_dept, a.full_name as agent_name');
        $this->db->from('tickets t');
        $this->db->join('users u', 'u.id = t.user_id', 'left');
        $this->db->join('users a', 'a.id = t.assigned_agent_id', 'left');
        $this->db->where('t.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_number($ticket_number) {
        $this->db->select('t.*, u.full_name as requester_name, u.email as requester_email, a.full_name as agent_name');
        $this->db->from('tickets t');
        $this->db->join('users u', 'u.id = t.user_id', 'left');
        $this->db->join('users a', 'a.id = t.assigned_agent_id', 'left');
        $this->db->where('t.ticket_number', $ticket_number);
        return $this->db->get()->row();
    }

    public function create_ticket($data) {
        if (empty($data['ticket_number'])) {
            $this->db->select_max('id');
            $row = $this->db->get('tickets')->row();
            $next_id = ($row && $row->id) ? $row->id + 1 : 1;
            $data['ticket_number'] = 'TICK-' . (8000 + $next_id);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert('tickets', $data);
        $ticket_id = $this->db->insert_id();

        // Log history
        $this->log_history($ticket_id, $data['user_id'], 'CREATED', null, 'Ticket created with priority ' . ($data['priority'] ?? 'MEDIUM'));
        return $ticket_id;
    }

    public function update_ticket($id, $data, $user_id = null) {
        $old = $this->get_by_id($id);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (!empty($data['status']) && $data['status'] === 'RESOLVED' && empty($old->resolved_at)) {
            $data['resolved_at'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', $id);
        $updated = $this->db->update('tickets', $data);

        if ($updated && $old) {
            if (!empty($data['status']) && $data['status'] !== $old->status) {
                $this->log_history($id, $user_id, 'STATUS_CHANGE', $old->status, $data['status']);
            }
            if (!empty($data['priority']) && $data['priority'] !== $old->priority) {
                $this->log_history($id, $user_id, 'PRIORITY_CHANGE', $old->priority, $data['priority']);
            }
            if (isset($data['assigned_agent_id']) && $data['assigned_agent_id'] !== $old->assigned_agent_id) {
                $this->log_history($id, $user_id, 'ASSIGNMENT_CHANGE', $old->assigned_agent_id, $data['assigned_agent_id']);
            }
        }

        return $updated;
    }

    public function add_comment($ticket_id, $user_id, $comment, $is_internal = 0, $is_ai = 0) {
        $data = array(
            'ticket_id'      => $ticket_id,
            'user_id'        => $user_id,
            'is_internal'    => (int)$is_internal,
            'comment'        => $comment,
            'is_ai_generated'=> (int)$is_ai,
            'created_at'     => date('Y-m-d H:i:s')
        );
        $this->db->insert('ticket_comments', $data);
        $comment_id = $this->db->insert_id();

        // Touch ticket updated_at
        $this->db->where('id', $ticket_id);
        $this->db->update('tickets', array('updated_at' => date('Y-m-d H:i:s')));

        return $comment_id;
    }

    public function get_comments($ticket_id, $include_internal = true) {
        $this->db->select('c.*, u.full_name, u.email, r.name as role_name');
        $this->db->from('ticket_comments c');
        $this->db->join('users u', 'u.id = c.user_id', 'left');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('c.ticket_id', $ticket_id);
        if (!$include_internal) {
            $this->db->where('c.is_internal', 0);
        }
        $this->db->order_by('c.id', 'ASC');
        return $this->db->get()->result();
    }

    public function log_history($ticket_id, $user_id, $action, $old_val, $new_val) {
        $this->db->insert('ticket_history', array(
            'ticket_id'  => $ticket_id,
            'user_id'    => $user_id,
            'action'     => $action,
            'old_value'  => (string)$old_val,
            'new_value'  => (string)$new_val,
            'created_at' => date('Y-m-d H:i:s')
        ));
    }

    public function get_history($ticket_id) {
        $this->db->select('h.*, u.full_name');
        $this->db->from('ticket_history h');
        $this->db->join('users u', 'u.id = h.user_id', 'left');
        $this->db->where('h.ticket_id', $ticket_id);
        $this->db->order_by('h.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_stats() {
        $total = $this->db->count_all('tickets');

        $this->db->where('status', 'OPEN');
        $open = $this->db->count_all_results('tickets');

        $this->db->where('status', 'IN_PROGRESS');
        $in_progress = $this->db->count_all_results('tickets');

        $this->db->where('status', 'RESOLVED');
        $resolved = $this->db->count_all_results('tickets');

        $this->db->where('status', 'CLOSED');
        $closed = $this->db->count_all_results('tickets');

        $this->db->where('status', 'ESCALATED');
        $escalated = $this->db->count_all_results('tickets');

        $this->db->where('priority', 'CRITICAL');
        $critical = $this->db->count_all_results('tickets');

        // Status counts breakdown
        $this->db->select('status, count(*) as count');
        $this->db->group_by('status');
        $status_breakdown = $this->db->get('tickets')->result();

        // Priority breakdown
        $this->db->select('priority, count(*) as count');
        $this->db->group_by('priority');
        $priority_breakdown = $this->db->get('tickets')->result();

        // Category breakdown
        $this->db->select('category, count(*) as count');
        $this->db->group_by('category');
        $category_breakdown = $this->db->get('tickets')->result();

        return array(
            'total'             => $total,
            'open'              => $open,
            'in_progress'       => $in_progress,
            'resolved'          => $resolved,
            'closed'            => $closed,
            'escalated'         => $escalated,
            'critical'          => $critical,
            'status_breakdown'  => $status_breakdown,
            'priority_breakdown'=> $priority_breakdown,
            'category_breakdown'=> $category_breakdown
        );
    }
}
