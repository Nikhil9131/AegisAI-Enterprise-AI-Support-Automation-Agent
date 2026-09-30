<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
    }

    public function index() {
        redirect('auth/login');
    }

    public function login() {
        if ($this->rbac->is_logged_in()) {
            redirect('dashboard');
        }
        $data = array(
            'title' => 'Sign In — Aegis AI Enterprise',
            'view'  => 'auth/login'
        );
        $this->load->view('layouts/auth_layout', $data);
    }

    public function process_login() {
        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password');

        if (empty($email) || empty($password)) {
            $this->session->set_flashdata('error', 'Please enter your corporate email and password.');
            redirect('auth/login');
        }

        $user = $this->User_model->authenticate($email, $password);

        if ($user) {
            $session_data = array(
                'user_id'    => $user->id,
                'role_id'    => $user->role_id,
                'role_name'  => $user->role_name,
                'full_name'  => $user->full_name,
                'email'      => $user->email,
                'department' => $user->department,
                'avatar'     => $user->avatar ?: 'default_avatar.png',
                'logged_in'  => TRUE
            );
            $this->session->set_userdata($session_data);
            $token = $this->rbac->create_auth_token($session_data);
            $this->rbac->set_auth_cookie($token);
            $this->session->set_flashdata('success', 'Welcome back, ' . $user->full_name . '!');

            if ($user->role_name === 'ADMIN') {
                redirect('admin');
            } else {
                redirect('dashboard');
            }
        } else {
            $this->session->set_flashdata('error', 'Invalid email or password. Please verify credentials.');
            redirect('auth/login');
        }
    }

    public function register() {
        if ($this->rbac->is_logged_in()) {
            redirect('dashboard');
        }
        $data = array(
            'title' => 'Register — Aegis AI Enterprise',
            'view'  => 'auth/register'
        );
        $this->load->view('layouts/auth_layout', $data);
    }

    public function process_register() {
        $full_name = $this->input->post('full_name', TRUE);
        $email = $this->input->post('email', TRUE);
        $department = $this->input->post('department', TRUE);
        $password = $this->input->post('password');
        $password_confirm = $this->input->post('password_confirm');

        if (empty($full_name) || empty($email) || empty($password)) {
            $this->session->set_flashdata('error', 'All fields are required.');
            redirect('auth/register');
        }

        if ($password !== $password_confirm) {
            $this->session->set_flashdata('error', 'Passwords do not match.');
            redirect('auth/register');
        }

        if (strlen($password) < 8) {
            $this->session->set_flashdata('error', 'Password must be at least 8 characters long.');
            redirect('auth/register');
        }

        if ($this->User_model->get_by_email($email)) {
            $this->session->set_flashdata('error', 'An account with this email already exists.');
            redirect('auth/register');
        }

        $user_id = $this->User_model->create_user(array(
            'role_id'    => 3, // EMPLOYEE
            'full_name'  => $full_name,
            'email'      => $email,
            'department' => $department,
            'password'   => $password,
            'status'     => 'ACTIVE'
        ));

        if ($user_id) {
            $this->session->set_flashdata('success', 'Account created successfully! You can now sign in.');
            redirect('auth/login');
        } else {
            $this->session->set_flashdata('error', 'Failed to create account. Please try again.');
            redirect('auth/register');
        }
    }

    public function forgot_password() {
        $data = array(
            'title' => 'Password Recovery — Aegis AI Enterprise',
            'view'  => 'auth/forgot_password'
        );
        $this->load->view('layouts/auth_layout', $data);
    }

    public function process_forgot_password() {
        $email = $this->input->post('email', TRUE);
        $this->session->set_flashdata('success', 'If the email matches a registered corporate identity, password reset instructions have been dispatched.');
        redirect('auth/login');
    }

    public function logout() {
        $this->rbac->clear_auth_cookie();
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}
