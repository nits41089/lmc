<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Login extends CI_Controller {
  function __construct(){
    parent::__construct();
    $this->load->library('Oauth');
  }
  
  function index(){
    $data = array();
    $data['facebook_auth_url'] = $this->oauth->get_auth_url('facebook');
    $this->load->view('login', $data);
  }
  
  function social() {
    if (! $this->input->get('code')) {
      redirect(site_url('login'));
    } else {
      $code = $this->input->get('code');
      $state = $this->input->get('state');
      $token = $this->oauth->get_access_token($state, 'authorization_code', $_GET['code']);
      
      if(isset($token['access_token'])) {
        $url = 'https://graph.facebook.com/me?'.http_build_query(array('access_token' => $token['access_token'])).'&fields=email,name,picture,gender,age_range,bio,birthday';
        $user = json_decode(file_get_contents($url));
        echo '<pre>';
        print_r($user);
        echo '</pre>';
        exit;
      }
    }
  }
}
?>