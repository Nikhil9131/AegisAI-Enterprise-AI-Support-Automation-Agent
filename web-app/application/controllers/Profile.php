<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac->require_login();
        $this->load->model('User_model');
        $this->load->model('Employee_model');
        $this->load->model('Device_model');
    }

    public function index() {
        $user_id = $this->rbac->get_user_id();
        $user = $this->User_model->get_by_id($user_id);
        $employee = $this->Employee_model->get_by_user_id($user_id);
        $devices = $this->Device_model->get_by_user_id($user_id);

        $data = array(
            'title'    => 'My Enterprise Profile & Hardware',
            'user'     => $user,
            'employee' => $employee,
            'devices'  => $devices
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('user/profile', $data);
        $this->load->view('layouts/footer');
    }

    public function update_password() {
        $user_id = $this->rbac->get_user_id();
        $new_pass = $this->input->post('new_password');
        $confirm = $this->input->post('confirm_password');

        if (strlen($new_pass) < 8 || $new_pass !== $confirm) {
            $this->session->set_flashdata('error', 'Password must be at least 8 characters and match confirmation.');
        } else {
            $this->User_model->update_user($user_id, array('password' => $new_pass));
            $this->session->set_flashdata('success', 'Corporate password updated successfully.');
        }

        redirect('profile');
    }
}
