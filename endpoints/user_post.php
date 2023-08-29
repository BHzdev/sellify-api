<?php
  function api_user_post($request){
    $nome = sanitize_text_field($request["nome"]);
    $email = sanitize_email($request["email"]);
    $senha = $request["senha"];
    $estado = sanitize_text_field($request["estado"]);
    $cidade = sanitize_text_field($request["cidade"]);
    $cep = sanitize_text_field($request["cep"]);
    $bairro = sanitize_text_field($request["bairro"]);
    $rua = sanitize_text_field($request["rua"]);
    $numero = sanitize_text_field($request["numero"]);

    $user_exists = username_exists($email);
    $email_exists = email_exists($email);

    if(!$user_exists && !$email_exists && $email && $senha){
      $user_id = wp_create_user($email, $senha, $email);

      $response = array(
        "ID" => $user_id,
        "display_name" => $nome,
        "first_name" => $nome,
        "role" => "subscriber"
      );

      wp_update_user($response);

      update_user_meta($user_id, 'estado', $estado);
      update_user_meta($user_id, 'cidade', $cidade);
      update_user_meta($user_id, 'cep', $cep);
      update_user_meta($user_id, 'bairro', $bairro);
      update_user_meta($user_id, 'rua', $rua);
      update_user_meta($user_id, 'numero', $numero);
    } else {
      $response = new WP_Error("email", "Email já cadastrado.", array("status" => 403));
    }

    return rest_ensure_response($response);
  }

  function register_api_user_post() {
    register_rest_route("api", "/usuario", array(
      array(
        "methods" => WP_REST_Server::CREATABLE,
        "callback" => "api_user_post"
      )
    ));
  }

  add_action('rest_api_init', 'register_api_user_post');
?>