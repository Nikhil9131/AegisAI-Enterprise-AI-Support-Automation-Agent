<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Document_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_documents($category_id = null, $status = null, $limit = 100, $offset = 0) {
        $this->db->select('d.*, c.name as category_name, c.slug as category_slug, u.full_name as uploader_name');
        $this->db->from('documents d');
        $this->db->join('knowledge_categories c', 'c.id = d.category_id', 'left');
        $this->db->join('users u', 'u.id = d.uploaded_by', 'left');

        if ($category_id) {
            $this->db->where('d.category_id', $category_id);
        }
        if ($status) {
            $this->db->where('d.status', $status);
        }
        $this->db->order_by('d.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        $this->db->select('d.*, c.name as category_name, c.slug as category_slug, u.full_name as uploader_name');
        $this->db->from('documents d');
        $this->db->join('knowledge_categories c', 'c.id = d.category_id', 'left');
        $this->db->join('users u', 'u.id = d.uploaded_by', 'left');
        $this->db->where('d.id', $id);
        return $this->db->get()->row();
    }

    public function create_document($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('documents', $data);
        return $this->db->insert_id();
    }

    public function update_document($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('documents', $data);
    }

    public function delete_document($id) {
        $this->db->where('id', $id);
        return $this->db->delete('documents');
    }

    public function get_categories() {
        $this->db->select('c.*, count(d.id) as doc_count');
        $this->db->from('knowledge_categories c');
        $this->db->join('documents d', 'd.category_id = c.id', 'left');
        $this->db->group_by('c.id');
        $this->db->order_by('c.name', 'ASC');
        return $this->db->get()->result();
    }

    public function search_local($query, $limit = 10) {
        $this->db->select('d.*, c.name as category_name');
        $this->db->from('documents d');
        $this->db->join('knowledge_categories c', 'c.id = d.category_id', 'left');
        $this->db->group_start();
        $this->db->like('d.title', $query);
        $this->db->or_like('d.file_name', $query);
        $this->db->group_end();
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
}
