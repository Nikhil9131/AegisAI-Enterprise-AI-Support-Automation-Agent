<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac->require_login();
        $this->load->model('Ticket_model');
        $this->load->model('Device_model');
        $this->load->model('Document_model');
        $this->load->model('Ai_model');
    }

    public function index() {
        $user_id = $this->rbac->get_user_id();

        // If admin, show admin dashboard or redirect
        if ($this->rbac->is_admin()) {
            redirect('admin');
            return;
        }

        $my_tickets = $this->Ticket_model->get_tickets(array('user_id' => $user_id), 5);
        $ticket_stats = array(
            'total'       => $this->Ticket_model->count_tickets(array('user_id' => $user_id)),
            'open'        => $this->Ticket_model->count_tickets(array('user_id' => $user_id, 'status' => 'OPEN')),
            'in_progress' => $this->Ticket_model->count_tickets(array('user_id' => $user_id, 'status' => 'IN_PROGRESS')),
            'resolved'    => $this->Ticket_model->count_tickets(array('user_id' => $user_id, 'status' => 'RESOLVED'))
        );

        $my_devices = $this->Device_model->get_by_user_id($user_id);
        $recent_docs = $this->Document_model->get_documents(null, 'INDEXED', 4);
        $recent_conversations = $this->Ai_model->get_user_conversations($user_id, 3);

        $data = array(
            'title'                => 'Employee Workspace Dashboard',
            'user'                 => $this->rbac->get_user(),
            'my_tickets'           => $my_tickets,
            'ticket_stats'         => $ticket_stats,
            'my_devices'           => $my_devices,
            'recent_docs'          => $recent_docs,
            'recent_conversations' => $recent_conversations
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('user/dashboard', $data);
        $this->load->view('layouts/footer');
    }
}
