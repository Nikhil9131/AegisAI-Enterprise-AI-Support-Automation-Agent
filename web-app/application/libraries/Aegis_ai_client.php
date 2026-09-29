<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AEGIS AI Client Library
 * Handles secure REST API communication between CodeIgniter 3 and Python FastAPI AI microservice.
 */
class Aegis_ai_client {

    protected $CI;
    protected $api_base_url;
    protected $timeout = 60;

    public function __construct() {
        $this->CI =& get_instance();
        $this->api_base_url = rtrim(getenv('AI_SERVICE_URL') ?: 'http://localhost:8001', '/');
    }

    /**
     * Check AI microservice health
     */
    public function health() {
        return $this->request('GET', '/api/v1/health');
    }

    /**
     * Send chat prompt through LangGraph Supervisor multi-agent pipeline
     */
    public function chat($query, $user_id = null, $conversation_id = null, $mode = 'AUTO') {
        $payload = array(
            'query'           => $query,
            'user_id'         => $user_id ?: ($this->CI->rbac->get_user_id() ?: 1),
            'conversation_id' => $conversation_id,
            'mode'            => $mode
        );
        return $this->request('POST', '/api/v1/chat', $payload);
    }

    /**
     * Direct RAG search query against Qdrant knowledge base
     */
    public function rag_query($query, $top_k = 5, $category = null) {
        $payload = array(
            'query'    => $query,
            'top_k'    => (int)$top_k,
            'category' => $category
        );
        return $this->request('POST', '/api/v1/rag/query', $payload);
    }

    /**
     * AI Ticket Classification, Summarization & Priority Analysis
     */
    public function analyze_ticket($title, $description, $category = null, $user_id = null) {
        $payload = array(
            'title'       => $title,
            'description' => $description,
            'category'    => $category,
            'user_id'     => $user_id ?: ($this->CI->rbac->get_user_id() ?: 1)
        );
        return $this->request('POST', '/api/v1/tickets/analyze', $payload);
    }

    /**
     * Generate grounded resolution steps for a ticket
     */
    public function suggest_resolution($ticket_id, $title, $description, $user_id = null) {
        $payload = array(
            'ticket_id'   => (int)$ticket_id,
            'title'       => $title,
            'description' => $description,
            'user_id'     => $user_id ?: ($this->CI->rbac->get_user_id() ?: 1)
        );
        return $this->request('POST', '/api/v1/tickets/suggest-resolution', $payload);
    }

    /**
     * Execute LangGraph multi-agent workflow
     */
    public function run_agent($query, $user_id = null, $conversation_id = null) {
        $payload = array(
            'query'           => $query,
            'user_id'         => $user_id ?: ($this->CI->rbac->get_user_id() ?: 1),
            'conversation_id' => $conversation_id
        );
        return $this->request('POST', '/api/v1/agent/run', $payload);
    }

    /**
     * Multimodal Image / Screenshot Diagnosis
     */
    public function diagnose_image($image_path, $prompt = null, $user_id = null) {
        $url = $this->api_base_url . '/api/v1/multimodal/diagnose';
        if (!file_exists($image_path)) {
            return array('success' => false, 'error' => 'Local image file not found');
        }

        $cfile = new CURLFile($image_path, mime_content_type($image_path), basename($image_path));
        $post_data = array(
            'file'    => $cfile,
            'prompt'  => $prompt ?: 'Analyze this technical error screenshot and identify issue and troubleshooting steps.',
            'user_id' => $user_id ?: ($this->CI->rbac->get_user_id() ?: 1)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return array('success' => false, 'error' => $err);
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : array('success' => false, 'raw_response' => $response);
    }

    /**
     * Submit user evaluation / feedback (Helpful / Not Helpful)
     */
    public function submit_feedback($log_id, $feedback, $notes = null) {
        $payload = array(
            'log_id'   => (int)$log_id,
            'feedback' => strtoupper($feedback),
            'notes'    => $notes
        );
        return $this->request('POST', '/api/v1/chat/feedback', $payload);
    }

    /**
     * Retrieve execution traces & observability logs
     */
    public function get_agent_logs($limit = 50, $offset = 0) {
        return $this->request('GET', '/api/v1/agent/logs?limit=' . (int)$limit . '&offset=' . (int)$offset);
    }

    /**
     * Internal cURL HTTP request handler
     */
    protected function request($method, $path, $data = null) {
        $url = $this->api_base_url . $path;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $headers = array('Accept: application/json');
        if ($data !== null) {
            $json_payload = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($json_payload);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $start_time = microtime(true);
        $response = curl_exec($ch);
        $latency = round((microtime(true) - $start_time) * 1000);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return array(
                'success'    => false,
                'status'     => $http_code ?: 500,
                'latency_ms' => $latency,
                'error'      => 'Failed to connect to AI Service: ' . $err
            );
        }

        $decoded = json_decode($response, true);
        if ($decoded === null && !empty($response)) {
            return array(
                'success'    => false,
                'status'     => $http_code,
                'latency_ms' => $latency,
                'error'      => 'Invalid JSON response from AI Service',
                'raw'        => $response
            );
        }

        return $decoded;
    }
}
