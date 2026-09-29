<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac->require_login();
        $this->load->model('Ticket_model');
        $this->load->model('Device_model');
        $this->load->model('Audit_model');
    }

    public function index() {
        $user_id = $this->rbac->get_user_id();
        $is_agent_or_admin = ($this->rbac->is_agent() || $this->rbac->is_admin());

        $status = $this->input->get('status', TRUE);
        $priority = $this->input->get('priority', TRUE);
        $category = $this->input->get('category', TRUE);
        $search = $this->input->get('q', TRUE);

        $filters = array();
        if (!$is_agent_or_admin) {
            $filters['user_id'] = $user_id;
        }
        if ($status) $filters['status'] = $status;
        if ($priority) $filters['priority'] = $priority;
        if ($category) $filters['category'] = $category;
        if ($search) $filters['search'] = $search;

        $tickets = $this->Ticket_model->get_tickets($filters, 50);
        $stats = $this->Ticket_model->get_stats();

        $data = array(
            'title'             => 'Support Tickets Management',
            'tickets'           => $tickets,
            'stats'             => $stats,
            'is_agent_or_admin' => $is_agent_or_admin,
            'filters'           => $filters
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('user/my_tickets', $data);
        $this->load->view('layouts/footer');
    }

    public function create() {
        $user_id = $this->rbac->get_user_id();
        $my_devices = $this->Device_model->get_by_user_id($user_id);
        $prefill = $this->input->get('prefill', TRUE);

        $data = array(
            'title'      => 'Create New Support Ticket',
            'my_devices' => $my_devices,
            'prefill'    => $prefill
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('user/create_ticket', $data);
        $this->load->view('layouts/footer');
    }

    public function store() {
        $user_id = $this->rbac->get_user_id();
        $title = trim($this->input->post('title', TRUE));
        $description = trim($this->input->post('description', TRUE));
        $category = $this->input->post('category', TRUE) ?: 'IT_SUPPORT';
        $priority = $this->input->post('priority', TRUE) ?: 'MEDIUM';

        if (empty($title) || empty($description)) {
            $this->session->set_flashdata('error', 'Please provide a title and detailed description.');
            redirect('tickets/create');
            return;
        }

        // Call FastAPI ticket triage to get AI summary, suggested resolution and confidence
        $ai_analysis = $this->aegis_ai_client->analyze_ticket($title, $description, $category, $user_id);

        $ticket_data = array(
            'user_id'                 => $user_id,
            'title'                   => $title,
            'description'             => $description,
            'category'                => $category,
            'priority'                => $ai_analysis['priority'] ?? $priority,
            'status'                  => 'OPEN',
            'ai_summary'              => $ai_analysis['summary'] ?? ('Automated intake summary for: ' . $title),
            'ai_suggested_resolution' => $ai_analysis['suggested_resolution'] ?? null,
            'ai_confidence'           => $ai_analysis['confidence'] ?? 91.50,
            'requires_approval'       => !empty($ai_analysis['requires_approval']) ? 1 : 0,
            'approval_action'         => $ai_analysis['approval_action'] ?? null,
            'approval_status'         => !empty($ai_analysis['requires_approval']) ? 'PENDING' : null
        );

        $ticket_id = $this->Ticket_model->create_ticket($ticket_data);

        // If AI suggested resolution is available, automatically attach it as an AI comment
        if (!empty($ticket_data['ai_suggested_resolution'])) {
            $ai_comment = "[Aegis AI Automated Triage]\n" . $ticket_data['ai_suggested_resolution'];
            $this->Ticket_model->add_comment($ticket_id, null, $ai_comment, 0, 1);
        }

        $this->session->set_flashdata('success', 'Support ticket ' . $ticket_data['ticket_number'] . ' successfully created and triaged by Aegis AI!');
        redirect('tickets/view/' . $ticket_id);
    }

    public function view($id) {
        $ticket = $this->Ticket_model->get_by_id($id);
        if (!$ticket) {
            show_404();
            return;
        }

        $user_id = $this->rbac->get_user_id();
        $is_agent_or_admin = ($this->rbac->is_agent() || $this->rbac->is_admin());

        // Authorization check: user must be requester or agent/admin
        if ($ticket->user_id != $user_id && !$is_agent_or_admin) {
            $this->session->set_flashdata('error', 'Unauthorized ticket access.');
            redirect('tickets');
            return;
        }

        $comments = $this->Ticket_model->get_comments($id, $is_agent_or_admin);
        $history = $this->Ticket_model->get_history($id);

        $data = array(
            'title'             => 'Ticket ' . $ticket->ticket_number . ' — ' . $ticket->title,
            'ticket'            => $ticket,
            'comments'          => $comments,
            'history'           => $history,
            'is_agent_or_admin' => $is_agent_or_admin
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('user/ticket_details', $data);
        $this->load->view('layouts/footer');
    }

    public function add_comment($ticket_id) {
        $comment = trim($this->input->post('comment', TRUE));
        $is_internal = $this->input->post('is_internal') ? 1 : 0;
        $user_id = $this->rbac->get_user_id();

        if (!empty($comment)) {
            $this->Ticket_model->add_comment($ticket_id, $user_id, $comment, $is_internal, 0);
            $this->session->set_flashdata('success', 'Comment posted successfully.');
        }

        redirect('tickets/view/' . $ticket_id);
    }

    public function update_status($ticket_id) {
        $this->rbac->require_role(array('ADMIN', 'AGENT'));

        $status = $this->input->post('status', TRUE);
        $priority = $this->input->post('priority', TRUE);
        $assigned_agent_id = $this->input->post('assigned_agent_id');

        $update_data = array();
        if ($status) $update_data['status'] = $status;
        if ($priority) $update_data['priority'] = $priority;
        if ($assigned_agent_id !== null) $update_data['assigned_agent_id'] = $assigned_agent_id ? (int)$assigned_agent_id : null;

        $this->Ticket_model->update_ticket($ticket_id, $update_data, $this->rbac->get_user_id());
        $this->session->set_flashdata('success', 'Ticket updated successfully.');
        redirect('tickets/view/' . $ticket_id);
    }

    /**
     * Human-In-The-Loop Approval Action
     */
    public function approve_action($ticket_id) {
        $this->rbac->require_role(array('ADMIN', 'AGENT'));
        $decision = $this->input->post('decision'); // APPROVE or REJECT
        $admin_id = $this->rbac->get_user_id();

        $status = ($decision === 'APPROVE') ? 'APPROVED' : 'REJECTED';
        $this->Ticket_model->update_ticket($ticket_id, array('approval_status' => $status), $admin_id);

        // Record in audit log
        $this->Audit_model->log_action('HITL_TICKET_' . $status, 'TICKET', $ticket_id, "Decision by Admin/Agent $admin_id: $decision", $admin_id, 'EXECUTED');

        $this->session->set_flashdata('success', "Human-in-the-loop action marked as $status.");
        redirect('tickets/view/' . $ticket_id);
    }

    /**
     * AJAX endpoint: Real-time AI Ticket Diagnosis / Suggestion
     */
    public function ajax_suggest() {
        $title = $this->input->post('title', TRUE);
        $description = $this->input->post('description', TRUE);

        $ai_res = $this->aegis_ai_client->analyze_ticket($title, $description);
        $this->output->set_content_type('application/json')->set_output(json_encode($ai_res));
    }
}
