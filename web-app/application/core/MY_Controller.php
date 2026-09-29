<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Application Controller
 * Explicitly initializes core services for PHP 8.2 compatibility
 */
#[\AllowDynamicProperties]
class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('rbac');
        $this->load->library('aegis_ai_client');
        $this->load->helper(array('url', 'form', 'html', 'text', 'date', 'security'));
    }
}
