<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Login extends CI_Controller {
  function __construct(){
    parent::__construct();
    $this->load->library('Oauth');
  }
  
  function index(){
    $data['facebook_auth_url'] = $this->oauth->get_auth_url('facebook');
    $this->load->view('login', $data);
  }
  
  function fetch_code(){
    if ($this->input->get('code')) {
      $token = $this->oauth->get_access_token($provider, 'authorization_code', $_GET['code']);
    }
  }
}
?>