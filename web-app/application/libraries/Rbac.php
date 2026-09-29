<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AEGIS AI Enterprise Platform - Role-Based Access Control (RBAC) Library
 * Enforces server-side permissions for ADMIN, AGENT, and EMPLOYEE roles.
 */
class Rbac {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Check if a user is currently logged in via session
     */
    public function is_logged_in() {
        return (bool) $this->CI->session->userdata('logged_in');
    }

    /**
     * Get active logged in user ID
     */
    public function get_user_id() {
        return $this->CI->session->userdata('user_id');
    }

    /**
     * Get active user role ID (1=ADMIN, 2=AGENT, 3=EMPLOYEE)
     */
    public function get_role_id() {
        return $this->CI->session->userdata('role_id');
    }

    /**
     * Get active user role name string
     */
    public function get_role_name() {
        return strtoupper($this->CI->session->userdata('role_name') ?: 'GUEST');
    }

    /**
     * Get active user full object / session data
     */
    public function get_user() {
        if (!$this->is_logged_in()) {
            return null;
        }
        return (object) array(
            'id'          => $this->CI->session->userdata('user_id'),
            'role_id'     => $this->CI->session->userdata('role_id'),
            'role_name'   => $this->CI->session->userdata('role_name'),
            'full_name'   => $this->CI->session->userdata('full_name'),
            'email'       => $this->CI->session->userdata('email'),
            'department'  => $this->CI->session->userdata('department'),
            'avatar'      => $this->CI->session->userdata('avatar') ?: 'default_avatar.png'
        );
    }

    public function is_admin() {
        return $this->is_logged_in() && ($this->get_role_id() == 1 || $this->get_role_name() === 'ADMIN');
    }

    public function is_agent() {
        return $this->is_logged_in() && ($this->get_role_id() == 2 || $this->get_role_name() === 'AGENT' || $this->is_admin());
    }

    public function is_employee() {
        return $this->is_logged_in() && ($this->get_role_id() == 3 || $this->get_role_name() === 'EMPLOYEE');
    }

    /**
     * Enforce authentication server-side
     */
    public function require_login($redirect_url = 'auth/login') {
        if (!$this->is_logged_in()) {
            $this->CI->session->set_flashdata('error', 'Authentication required to access this resource.');
            $this->CI->session->set_userdata('redirect_back', current_url());
            redirect($redirect_url);
            exit();
        }
    }

    /**
     * Enforce specific roles server-side
     * @param string|array $allowed_roles e.g. 'ADMIN' or ['ADMIN', 'AGENT']
     */
    public function require_role($allowed_roles, $redirect_url = 'dashboard') {
        $this->require_login();
        
        if (!is_array($allowed_roles)) {
            $allowed_roles = array($allowed_roles);
        }
        $allowed_roles = array_map('strtoupper', $allowed_roles);

        $current_role = $this->get_role_name();

        if (!in_array($current_role, $allowed_roles) && !$this->is_admin()) {
            $this->CI->session->set_flashdata('error', 'Access denied. You do not possess the required permissions for this section.');
            redirect($redirect_url);
            exit();
        }
    }
}
