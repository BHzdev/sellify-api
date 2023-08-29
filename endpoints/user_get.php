<?php
  function api_user_get($request){
    $user = wp_get_current_user();
    return rest_ensure_response($user);
  }

  function register_api_user_get() {
    register_rest_route("api", "/usuario", array(
      array(
        "methods" => WP_REST_Server::READABLE,
        "callback" => "api_user_get"
      )
    ));
  }

  add_action('rest_api_init', 'register_api_user_get');
?>