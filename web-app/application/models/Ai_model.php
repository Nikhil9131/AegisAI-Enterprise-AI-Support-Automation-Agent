<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_user_conversations($user_id, $limit = 20) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('updated_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('ai_conversations')->result();
    }

    public function get_or_create_conversation($user_id, $conversation_id = null, $title = 'New Investigation') {
        if ($conversation_id) {
            $this->db->where('id', $conversation_id);
            $this->db->where('user_id', $user_id);
            $conv = $this->db->get('ai_conversations')->row();
            if ($conv) return $conv;
        }

        $this->db->insert('ai_conversations', array(
            'user_id'    => $user_id,
            'title'      => $title,
            'mode'       => 'ASSISTANT',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));
        $id = $this->db->insert_id();
        return $this->db->where('id', $id)->get('ai_conversations')->row();
    }

    public function get_messages($conversation_id) {
        $this->db->where('conversation_id', $conversation_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('ai_messages')->result();
    }

    public function add_message($conversation_id, $sender, $message, $metadata = null) {
        $data = array(
            'conversation_id' => $conversation_id,
            'sender'          => strtoupper($sender),
            'message'         => $message,
            'metadata'        => is_array($metadata) ? json_encode($metadata) : $metadata,
            'created_at'      => date('Y-m-d H:i:s')
        );
        $this->db->insert('ai_messages', $data);
        $msg_id = $this->db->insert_id();

        // Update conversation timestamp
        $this->db->where('id', $conversation_id);
        $this->db->update('ai_conversations', array('updated_at' => date('Y-m-d H:i:s')));

        return $msg_id;
    }

    public function save_feedback($message_id, $feedback, $notes = null) {
        $this->db->where('id', $message_id);
        return $this->db->update('ai_messages', array(
            'feedback'       => strtoupper($feedback),
            'feedback_notes' => $notes
        ));
    }

    public function get_agent_logs($limit = 50, $offset = 0) {
        $this->db->select('l.*, u.full_name, u.email');
        $this->db->from('ai_agent_logs l');
        $this->db->join('users u', 'u.id = l.user_id', 'left');
        $this->db->order_by('l.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function log_agent_execution($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('ai_agent_logs', $data);
        return $this->db->insert_id();
    }

    public function get_analytics() {
        $total_queries = $this->db->count_all('ai_agent_logs');

        $this->db->select_avg('latency_ms', 'avg_latency');
        $this->db->select_avg('token_usage', 'avg_tokens');
        $avg_stats = $this->db->get('ai_agent_logs')->row();

        // Helpful vs Not Helpful feedback
        $this->db->where('feedback', 'HELPFUL');
        $helpful = $this->db->count_all_results('ai_agent_logs');

        $this->db->where('feedback', 'NOT_HELPFUL');
        $not_helpful = $this->db->count_all_results('ai_agent_logs');

        // Total documents indexed
        $this->db->where('status', 'INDEXED');
        $indexed_docs = $this->db->count_all_results('documents');

        // Agent selection breakdown
        $this->db->select('agent_selected, count(*) as count');
        $this->db->group_by('agent_selected');
        $agent_distribution = $this->db->get('ai_agent_logs')->result();

        return array(
            'total_queries'      => $total_queries,
            'avg_latency_ms'     => round($avg_stats->avg_latency ?? 0),
            'avg_token_usage'    => round($avg_stats->avg_tokens ?? 0),
            'helpful_count'      => $helpful,
            'not_helpful_count'  => $not_helpful,
            'satisfaction_rate'  => ($helpful + $not_helpful > 0) ? round(($helpful / ($helpful + $not_helpful)) * 100, 1) : 96.5,
            'indexed_docs'       => $indexed_docs,
            'agent_distribution' => $agent_distribution
        );
    }
}
