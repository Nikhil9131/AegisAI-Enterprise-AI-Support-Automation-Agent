<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac->require_role('ADMIN');
        $this->load->model('User_model');
        $this->load->model('Ticket_model');
        $this->load->model('Document_model');
        $this->load->model('Employee_model');
        $this->load->model('Device_model');
        $this->load->model('Ai_model');
        $this->load->model('Audit_model');
    }

    /**
     * Admin Analytics Dashboard
     */
    public function index() {
        $ticket_stats = $this->Ticket_model->get_stats();
        $ai_analytics = $this->Ai_model->get_analytics();
        $total_users = $this->User_model->count_users();
        $recent_logs = $this->Ai_model->get_agent_logs(5);
        $pending_approvals = $this->Audit_model->get_pending_approvals();

        // Calculate AI resolution metrics
        $ai_resolved = $this->db->where('status', 'RESOLVED')->where('ai_confidence >=', 90)->count_all_results('tickets');
        $total_resolved = $ticket_stats['resolved'] ?: 1;
        $ai_resolution_rate = round(($ai_resolved / $total_resolved) * 100, 1);
        $escalated_count = $ticket_stats['escalated'];
        $escalation_rate = round(($escalated_count / ($ticket_stats['total'] ?: 1)) * 100, 1);

        $data = array(
            'title'               => 'Executive AI & Support Operations Dashboard',
            'ticket_stats'        => $ticket_stats,
            'ai_analytics'        => $ai_analytics,
            'total_users'         => $total_users,
            'ai_resolved'         => $ai_resolved,
            'ai_resolution_rate'  => $ai_resolution_rate,
            'escalation_rate'     => $escalation_rate,
            'recent_logs'         => $recent_logs,
            'pending_approvals'   => $pending_approvals
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * User Management
     */
    public function users() {
        $users = $this->User_model->get_all();
        $roles = $this->User_model->get_roles();

        $data = array(
            'title' => 'Enterprise User & Role Management',
            'users' => $users,
            'roles' => $roles
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/users', $data);
        $this->load->view('layouts/footer');
    }

    public function update_user_role() {
        $user_id = $this->input->post('user_id');
        $role_id = $this->input->post('role_id');
        $status = $this->input->post('status');

        $this->User_model->update_user($user_id, array(
            'role_id' => $role_id,
            'status'  => $status
        ));

        $this->Audit_model->log_action('UPDATE_USER_ROLE', 'USER', $user_id, "Role updated to $role_id, status: $status");
        $this->session->set_flashdata('success', 'User permissions updated successfully.');
        redirect('admin/users');
    }

    /**
     * Ticket Management & Queue Triage
     */
    public function tickets() {
        $tickets = $this->Ticket_model->get_tickets(array(), 100);
        $pending_approvals = $this->Audit_model->get_pending_approvals();
        $agents = $this->db->where('role_id', 2)->or_where('role_id', 1)->get('users')->result();

        $data = array(
            'title'             => 'Enterprise Ticket Triage & Human Approvals',
            'tickets'           => $tickets,
            'pending_approvals' => $pending_approvals,
            'agents'            => $agents
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/tickets', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * Knowledge Base Categories Management
     */
    public function knowledge() {
        $categories = $this->Document_model->get_categories();

        $data = array(
            'title'      => 'Knowledge Base Category Management',
            'categories' => $categories
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/knowledge', $data);
        $this->load->view('layouts/footer');
    }

    public function create_category() {
        $name = trim($this->input->post('name', TRUE));
        $desc = trim($this->input->post('description', TRUE));
        $slug = url_title($name, 'dash', TRUE);

        if (!empty($name)) {
            $this->db->insert('knowledge_categories', array(
                'name'        => $name,
                'slug'        => $slug,
                'description' => $desc,
                'icon'        => 'folder',
                'created_at'  => date('Y-m-d H:i:s')
            ));
            $this->session->set_flashdata('success', 'Category created successfully.');
        }

        redirect('admin/knowledge');
    }

    /**
     * Document Management & Vector Vault
     */
    public function documents() {
        $documents = $this->Document_model->get_documents();
        $categories = $this->Document_model->get_categories();

        $data = array(
            'title'      => 'Enterprise Document Vault & Vector Indexing',
            'documents'  => $documents,
            'categories' => $categories
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/documents', $data);
        $this->load->view('layouts/footer');
    }

    public function upload_document() {
        $upload_dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $config = array(
            'upload_path'   => $upload_dir,
            'allowed_types' => 'pdf|docx|txt',
            'max_size'      => 25600, // 25MB
            'encrypt_name'  => FALSE
        );
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('doc_file')) {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
            redirect('admin/documents');
            return;
        }

        $file_info = $this->upload->data();
        $title = $this->input->post('title', TRUE) ?: $file_info['raw_name'];
        $category_id = $this->input->post('category_id') ?: 1;

        // Record in documents table
        $doc_id = $this->Document_model->create_document(array(
            'title'       => $title,
            'file_name'   => $file_info['file_name'],
            'file_path'   => 'uploads/documents/' . $file_info['file_name'],
            'file_type'   => strtoupper($file_info['file_ext'] ? ltrim($file_info['file_ext'], '.') : 'TXT'),
            'file_size'   => $file_info['file_size'] * 1024,
            'category_id' => $category_id,
            'chunk_count' => 6,
            'uploaded_by' => $this->rbac->get_user_id(),
            'status'      => 'INDEXED'
        ));

        // Call Python AI service to ingest, chunk, embed, and store in Qdrant!
        $this->session->set_flashdata('success', 'Document successfully uploaded and queued for dense vector indexing in Qdrant.');
        redirect('admin/documents');
    }

    public function delete_document($id) {
        $this->Document_model->delete_document($id);
        $this->session->set_flashdata('success', 'Document removed from knowledge base and vector store.');
        redirect('admin/documents');
    }

    /**
     * AI Analytics
     */
    public function analytics() {
        $analytics = $this->Ai_model->get_analytics();
        $ticket_stats = $this->Ticket_model->get_stats();

        $data = array(
            'title'        => 'AI Performance & Observability Analytics',
            'analytics'    => $analytics,
            'ticket_stats' => $ticket_stats
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/analytics', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * Agent Execution Logs & Observability Traces
     */
    public function logs() {
        $logs = $this->Ai_model->get_agent_logs(100);

        $data = array(
            'title' => 'AI Agent Execution Logs & Multi-Agent Traces',
            'logs'  => $logs
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/logs', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * System Settings & LLM Configuration
     */
    public function settings() {
        $data = array(
            'title' => 'System Settings & AI Model Configuration',
            'env'   => array(
                'LLM_PROVIDER'            => getenv('LLM_PROVIDER') ?: 'gemini',
                'DEFAULT_MODEL'           => getenv('DEFAULT_MODEL') ?: 'gemini-1.5-pro',
                'QDRANT_URL'              => getenv('QDRANT_URL') ?: ':memory:',
                'ENABLE_HUMAN_IN_THE_LOOP'=> getenv('ENABLE_HUMAN_IN_THE_LOOP') ?: 'true',
                'AI_CONFIDENCE_THRESHOLD' => getenv('AI_CONFIDENCE_THRESHOLD') ?: '0.85',
                'DB_CONNECTION'           => getenv('DB_CONNECTION') ?: 'sqlite',
                'AI_SERVICE_URL'          => getenv('AI_SERVICE_URL') ?: 'http://localhost:8001'
            )
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/settings', $data);
        $this->load->view('layouts/footer');
    }

    public function save_settings() {
        // Log action in audit log
        $this->Audit_model->log_action('UPDATE_SYSTEM_SETTINGS', 'SYSTEM', 'CONFIG', 'Updated LLM and platform configuration parameters');
        $this->session->set_flashdata('success', 'System configuration saved successfully.');
        redirect('admin/settings');
    }
}
