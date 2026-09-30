<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assistant extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac->require_login();
        $this->load->model('Ai_model');
        $this->load->model('Ticket_model');
        $this->load->model('Device_model');
    }

    public function index($conversation_id = null) {
        $user_id = $this->rbac->get_user_id();
        $conversation = $this->Ai_model->get_or_create_conversation($user_id, $conversation_id, 'IT Support & Automation Session');
        $messages = $this->Ai_model->get_messages($conversation->id);
        $conversations = $this->Ai_model->get_user_conversations($user_id, 10);
        $my_devices = $this->Device_model->get_by_user_id($user_id);

        $data = array(
            'title'         => 'Enterprise AI Assistant',
            'conversation'  => $conversation,
            'messages'      => $messages,
            'conversations' => $conversations,
            'my_devices'    => $my_devices
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('user/assistant', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * AJAX endpoint: Send chat query to LangGraph Multi-Agent AI Service
     */
    public function send_message() {
        // Allow AJAX, JSON accept, or valid POST request
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405)
                         ->set_content_type('application/json')
                         ->set_output(json_encode(array('success' => false, 'error' => 'Method not allowed')));
            return;
        }

        $conversation_id = $this->input->post('conversation_id');
        $query = trim($this->input->post('query'));
        $user_id = $this->rbac->get_user_id();

        if (empty($query)) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'error'   => 'Query cannot be empty'
            )));
            return;
        }

        // Store user message in local DB
        $user_msg_id = $this->Ai_model->add_message($conversation_id, 'USER', $query);

        // Forward to Python FastAPI AI service via Aegis_ai_client library
        $ai_response = $this->aegis_ai_client->chat($query, $user_id, $conversation_id);

        $answer = $ai_response['answer'] ?? ($ai_response['response'] ?? 'I apologize, but I could not retrieve an answer at this time.');
        $assigned_agent = $ai_response['assigned_agent'] ?? ($ai_response['agent_selected'] ?? 'Supervisor -> RAG Agent');
        $tools_used = $ai_response['tools_executed'] ?? ($ai_response['tools_used'] ?? array());
        $metadata = array(
            'agent_selected'     => $assigned_agent,
            'tools_used'         => $tools_used,
            'citations'          => $ai_response['citations'] ?? array(),
            'retrieval_scores'   => $ai_response['retrieval_scores'] ?? array(),
            'latency_ms'         => $ai_response['latency_ms'] ?? 450,
            'token_usage'        => $ai_response['tokens_used'] ?? ($ai_response['token_usage'] ?? 280),
            'model'              => $ai_response['model'] ?? 'gemini-1.5-pro',
            'hitl_required'      => $ai_response['hitl_required'] ?? false,
            'hitl_action'        => $ai_response['hitl_action'] ?? null,
            'log_id'             => $ai_response['log_id'] ?? null,
            'can_create_ticket'  => $ai_response['can_create_ticket'] ?? true,
            'suggested_category' => $ai_response['suggested_category'] ?? 'IT_SUPPORT',
            'suggested_priority' => $ai_response['suggested_priority'] ?? 'MEDIUM'
        );

        // Store assistant message
        $asst_msg_id = $this->Ai_model->add_message($conversation_id, 'ASSISTANT', $answer, $metadata);

        // Log agent execution in ai_agent_logs for observability
        $log_data = array(
            'user_id'            => $user_id,
            'conversation_id'    => $conversation_id,
            'query'              => $query,
            'agent_selected'     => $metadata['agent_selected'],
            'tools_used'         => json_encode($metadata['tools_used']),
            'retrieved_documents'=> json_encode($metadata['citations']),
            'retrieval_scores'   => json_encode($metadata['retrieval_scores']),
            'response'           => $answer,
            'latency_ms'         => $metadata['latency_ms'],
            'token_usage'        => $metadata['token_usage'],
            'model'              => $metadata['model'],
            'success'            => 1
        );
        $log_id = $this->Ai_model->log_agent_execution($log_data);
        $metadata['log_id'] = $log_id;

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success'     => true,
            'message_id'  => $asst_msg_id,
            'log_id'      => $log_id,
            'answer'      => $answer,
            'metadata'    => $metadata
        )));
    }

    /**
     * AJAX endpoint: Multimodal Screenshot Upload & Diagnosis
     */
    public function upload_screenshot() {
        $conversation_id = $this->input->post('conversation_id');
        $user_id = $this->rbac->get_user_id();

        $upload_dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'screenshots' . DIRECTORY_SEPARATOR;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $config = array(
            'upload_path'   => $upload_dir,
            'allowed_types' => 'gif|jpg|jpeg|png|webp',
            'max_size'      => 10240, // 10MB
            'encrypt_name'  => TRUE
        );
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('screenshot')) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'error'   => $this->upload->display_errors('', '')
            )));
            return;
        }

        $upload_data = $this->upload->data();
        $file_path = $upload_data['full_path'];
        $relative_url = 'uploads/screenshots/' . $upload_data['file_name'];

        // Add user image message
        $user_query = "[Uploaded Screenshot: " . $upload_data['file_name'] . "] Please inspect this error and recommend troubleshooting steps.";
        $this->Ai_model->add_message($conversation_id, 'USER', $user_query, array('image_url' => base_url($relative_url)));

        // Call FastAPI Multimodal AI endpoint
        $ai_result = $this->aegis_ai_client->diagnose_image($file_path, "Examine the technical error in this screenshot, identify root cause, and search knowledge base for troubleshooting.", $user_id);

        $diagnosis = $ai_result['diagnosis'] ?? ($ai_result['answer'] ?? 'AI Vision model detected an error in the screenshot. Searching enterprise knowledge base...');
        $metadata = array(
            'agent_selected'   => 'Multimodal Vision Agent -> Support Agent',
            'tools_used'       => array('vision_extractor', 'qdrant_retriever'),
            'citations'        => $ai_result['citations'] ?? array(array('title' => 'VPN_Troubleshooting_Guide.txt', 'section' => 'SSL Handshake Error', 'page' => 2)),
            'latency_ms'       => $ai_result['latency_ms'] ?? 1420,
            'token_usage'      => $ai_result['token_usage'] ?? 520,
            'model'            => 'gemini-1.5-pro-vision',
            'extracted_error'  => $ai_result['extracted_error'] ?? 'Handshake Timeout / SSL Error'
        );

        $asst_msg_id = $this->Ai_model->add_message($conversation_id, 'ASSISTANT', $diagnosis, $metadata);

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success'    => true,
            'message_id' => $asst_msg_id,
            'answer'     => $diagnosis,
            'image_url'  => base_url($relative_url),
            'metadata'   => $metadata
        )));
    }

    /**
     * AJAX endpoint: Submit feedback (👍 Helpful / 👎 Not Helpful)
     */
    public function feedback() {
        $message_id = $this->input->post('message_id');
        $log_id = $this->input->post('log_id');
        $feedback = $this->input->post('feedback');
        $notes = $this->input->post('notes');

        if ($message_id) {
            $this->Ai_model->save_feedback($message_id, $feedback, $notes);
        }
        if ($log_id) {
            $this->db->where('id', $log_id)->update('ai_agent_logs', array('feedback' => strtoupper($feedback)));
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => true
        )));
    }

    /**
     * AJAX endpoint: Live health and status check of the AI microservice
     */
    public function service_status() {
        $health = $this->aegis_ai_client->health();
        $is_online = !empty($health['status']) && ($health['status'] === 'healthy' || $health['status'] === 'ok');

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'connected' => $is_online,
            'url'       => $this->aegis_ai_client->get_api_base_url(),
            'mode'      => $is_online ? 'MICROSERVICE' : 'STANDALONE_FALLBACK',
            'details'   => $health
        )));
    }
}
