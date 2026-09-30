<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AEGIS AI Enterprise Platform - Role-Based Access Control (RBAC) Library
 * Enforces server-side permissions for ADMIN, AGENT, and EMPLOYEE roles.
 * Includes stateless cryptographic cookie authentication for serverless / multi-container reliability.
 */
class Rbac {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Check if a user is currently logged in via session or persistent stateless auth token
     */
    public function is_logged_in() {
        if ($this->CI->session->userdata('logged_in')) {
            return true;
        }
        return $this->try_restore_from_auth_token();
    }

    /**
     * Get active logged in user ID
     */
    public function get_user_id() {
        $this->is_logged_in();
        return $this->CI->session->userdata('user_id');
    }

    /**
     * Get active user role ID (1=ADMIN, 2=AGENT, 3=EMPLOYEE)
     */
    public function get_role_id() {
        $this->is_logged_in();
        return $this->CI->session->userdata('role_id');
    }

    /**
     * Get active user role name string
     */
    public function get_role_name() {
        $this->is_logged_in();
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
     * Enforce authentication server-side (AJAX aware)
     */
    public function require_login($redirect_url = 'auth/login') {
        if (!$this->is_logged_in()) {
            $is_ajax = $this->CI->input->is_ajax_request()
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

            if ($is_ajax) {
                $this->CI->output->set_status_header(401)
                                 ->set_content_type('application/json')
                                 ->set_output(json_encode(array(
                                     'success'  => false,
                                     'error'    => 'Session timed out. Please sign in again.',
                                     'redirect' => base_url('auth/login')
                                 )));
                exit();
            }

            $this->CI->session->set_flashdata('error', 'Authentication required to access this resource.');
            $this->CI->session->set_userdata('redirect_back', current_url());
            redirect($redirect_url);
            exit();
        }
    }

    /**
     * Enforce specific roles server-side
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

    /**
     * Generate signed cryptographic stateless auth token for serverless multi-container instances
     */
    public function create_auth_token($user_data) {
        if (is_object($user_data)) {
            $user_data = (array)$user_data;
        }
        $secret = $this->get_token_secret();
        $payload = base64_encode(json_encode(array(
            'user_id'    => $user_data['user_id'] ?? ($user_data['id'] ?? 1),
            'role_id'    => $user_data['role_id'] ?? 1,
            'role_name'  => $user_data['role_name'] ?? 'ADMIN',
            'full_name'  => $user_data['full_name'] ?? 'Alexander Pierce',
            'email'      => $user_data['email'] ?? 'admin@aegis.enterprise',
            'department' => $user_data['department'] ?? '',
            'avatar'     => $user_data['avatar'] ?? 'default_avatar.png',
            'exp'        => time() + (86400 * 7) // 7 days
        )));
        $sig = hash_hmac('sha256', $payload, $secret);
        return $payload . '.' . $sig;
    }

    /**
     * Set persistent authentication cookie
     */
    public function set_auth_cookie($token) {
        $is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        
        setcookie('aegis_auth_token', $token, [
            'expires'  => time() + (86400 * 7),
            'path'     => '/',
            'domain'   => '',
            'secure'   => $is_secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        $_COOKIE['aegis_auth_token'] = $token;
    }

    /**
     * Clear persistent authentication cookie
     */
    public function clear_auth_cookie() {
        setcookie('aegis_auth_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => '',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE['aegis_auth_token']);
    }

    /**
     * Try restoring session state from signed persistent token
     */
    public function try_restore_from_auth_token() {
        $token = null;
        if (!empty($_COOKIE['aegis_auth_token'])) {
            $token = $_COOKIE['aegis_auth_token'];
        } elseif (!empty($_POST['auth_token'])) {
            $token = trim($_POST['auth_token']);
        } elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = str_replace('Bearer ', '', trim($_SERVER['HTTP_AUTHORIZATION']));
        } elseif ($this->CI->input->get_request_header('Authorization')) {
            $token = str_replace('Bearer ', '', trim($this->CI->input->get_request_header('Authorization')));
        }
            
        if (empty($token) || strpos($token, '.') === false) {
            return false;
        }

        list($payload_b64, $sig) = explode('.', $token, 2);
        $secret = $this->get_token_secret();
        $expected_sig = hash_hmac('sha256', $payload_b64, $secret);

        if (!hash_equals($expected_sig, $sig)) {
            return false;
        }

        $data = json_decode(base64_decode($payload_b64), true);
        if (!$data || !isset($data['user_id']) || ($data['exp'] ?? 0) < time()) {
            return false;
        }

        // Restore into session userdata so rest of application functions seamlessly!
        $session_data = array(
            'user_id'    => $data['user_id'],
            'role_id'    => $data['role_id'],
            'role_name'  => $data['role_name'],
            'full_name'  => $data['full_name'],
            'email'      => $data['email'],
            'department' => $data['department'] ?? '',
            'avatar'     => $data['avatar'] ?? 'default_avatar.png',
            'logged_in'  => TRUE
        );
        $this->CI->session->set_userdata($session_data);
        return true;
    }

    /**
     * Secure secret for HMAC signing
     */
    protected function get_token_secret() {
        return getenv('JWT_SECRET') 
            ?: ($this->CI->config->item('encryption_key') ?: 'aegis_enterprise_hmac_secret_2026');
    }
}
