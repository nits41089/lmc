<?php
  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
  
  class Oauth {
    
    private static $ALLOWED_OUTH_PROVIDERS = array('facebook','google');
    private static $SOCIAL_MEDIA_CREDENTIALS = array(
      'facebook' => array(
        'client_id' => '903305856426338',
        'client_secret' => 'acf5a61a10f83f83ed21d51f3a05dfc0',
        'base_url' => 'https://www.facebook.com/',
        'graph_url' => 'https://graph.facebook.com/',
        'graph_api_version' => 'v2.4',
        'user_scope' => ['email', 'user_photos']
      )
    );
    private $redirect_url;
    private static $USER_SCOPE = ['email', 'user_photos'];
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $demo = FALSE;
    
    public function __construct() {
      $this->redirect_url = base_url();      
      if (ENVIRONMENT === 'development') {
        $this->demo = TRUE;
      }
    }
    
    public function get_auth_url($provider){
      if(! $provider) {
        log_message('error', __FILE__.' '.__LINE__.' Provider details not provided by the client.');
        return FALSE;
      } else if(!in_array($provider, self::$ALLOWED_OUTH_PROVIDERS)) {
        log_message('error', __FILE__.' '.__LINE__.' Requested Provider is not allowed for Oauth Integration.');
        return FALSE;
      }
      
      if (method_exists($this, 'get_auth_url_for_'.$provider)) {
        $base_url = $this->{'get_auth_url_for_'.$provider}();
        $params = $this->get_auth_paramters($provider);
        $query = http_build_query($params);
        return $this->append_query($base_url, $query);
      } else {
        log_message('error', __FILE__.' '.__LINE__.' Unexpected error occurred!');
        return FALSE;
      }
    }
    
    private function get_auth_url_for_facebook() {
      return self::$SOCIAL_MEDIA_CREDENTIALS['facebook']['base_url'].self::$SOCIAL_MEDIA_CREDENTIALS['facebook']['graph_api_version'].'/dialog/oauth';
    }
    
    private function get_auth_url_for_google(){
      
    }
    
    private function get_auth_paramters($provider) {
      if (! $provider) {
        log_message('error', __FILE__.' '.__LINE__.' Provider details not provided by the client for fetching Auth parameters.');
        return FALSE;
      }
      
      $scope = implode(',', self::$SOCIAL_MEDIA_CREDENTIALS[$provider]['user_scope']);
      
      return [
        'client_id'       => self::$SOCIAL_MEDIA_CREDENTIALS[$provider]['client_id'],
        'redirect_uri'    => $this->redirect_url,
        'scope'           => $scope,
        'response_type'   => 'code',
        'approval_prompt' => 'auto',
      ];
    }
    
    private function append_query($url, $query) {
      $query = trim($query, '?&');
      if ($query) {
        return $url.'?'.$query;
      }
      return $url;
    }
    
    public function get_access_token($provider, $grant = 'authorization_code', array $options = []){
      if (! $provider) {
        log_message('error', __FILE__.' '.__LINE__.' Provider details not provided by the client for fetching Acess Token.');
        return FALSE;
      }
      
      if (isset($options['refresh_token'])) {
        log_message('error', __FILE__.' '.__LINE__.' Facebook does not support token refreshing.');
        return FALSE;
      }
      
      // $grant = $this->verifyGrant($grant);
      
      
      $params = [
        'client_id'     => self::$SOCIAL_MEDIA_CREDENTIALS[$provider]['client_id'],
        'client_secret' => self::$SOCIAL_MEDIA_CREDENTIALS[$provider]['client_secret'],
        'redirect_uri'  => $this->redirect_url,
      ];
      $params   = $grant->prepareRequestParameters($params, $options);
      $request  = $this->getAccessTokenRequest($params);
      $response = $this->getResponse($request);
      $prepared = $this->prepareAccessTokenResponse($response);
      $token    = $this->createAccessToken($prepared, $grant);
      return $token;
    }
  }
?>