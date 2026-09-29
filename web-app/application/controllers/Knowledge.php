<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Knowledge extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac->require_login();
        $this->load->model('Document_model');
    }

    public function index() {
        $category_id = $this->input->get('cat');
        $categories = $this->Document_model->get_categories();
        $documents = $this->Document_model->get_documents($category_id, 'INDEXED');

        $data = array(
            'title'        => 'Enterprise Knowledge Base',
            'categories'   => $categories,
            'documents'    => $documents,
            'selected_cat' => $category_id
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('user/knowledge_base', $data);
        $this->load->view('layouts/footer');
    }

    public function search() {
        $query = trim($this->input->get('q', TRUE) ?? '');
        $results = array();
        $search_mode = 'SEMANTIC_RAG';

        if (!empty($query)) {
            // Query Python FastAPI RAG endpoint for semantic similarity results from Qdrant!
            $rag_result = $this->aegis_ai_client->rag_query($query, 6);
            if (!empty($rag_result['results'])) {
                $results = $rag_result['results'];
            } else {
                // Fallback to local SQL search
                $results = $this->Document_model->search_local($query);
                $search_mode = 'KEYWORD_FALLBACK';
            }
        }

        $data = array(
            'title'       => 'Semantic Knowledge Search',
            'query'       => $query,
            'results'     => $results,
            'search_mode' => $search_mode
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('user/document_search', $data);
        $this->load->view('layouts/footer');
    }

    public function view_doc($id) {
        $doc = $this->Document_model->get_by_id($id);
        if (!$doc) {
            show_404();
            return;
        }

        // Read document file from sample_docs or uploads
        $content = '';
        $sample_path = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'sample_docs' . DIRECTORY_SEPARATOR . $doc->file_name;
        $upload_path = FCPATH . $doc->file_path;

        if (file_exists($sample_path)) {
            $content = file_get_contents($sample_path);
        } elseif (file_exists($upload_path)) {
            $content = file_get_contents($upload_path);
        } else {
            $content = "Document content is currently indexed in the Qdrant vector database.";
        }

        $data = array(
            'title'   => $doc->title,
            'doc'     => $doc,
            'content' => $content
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('user/view_document', $data);
        $this->load->view('layouts/footer');
    }
}
