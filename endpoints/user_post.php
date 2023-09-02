<?php
  function api_user_post($request){
    // Obtendo os dados do request e sanitizando-os para evitar problemas de segurança
    $nome = sanitize_text_field($request["nome"]);
    $email = sanitize_email($request["email"]);
    $senha = $request["senha"];
    $estado = sanitize_text_field($request["estado"]);
    $cidade = sanitize_text_field($request["cidade"]);
    $cep = sanitize_text_field($request["cep"]);
    $bairro = sanitize_text_field($request["bairro"]);
    $rua = sanitize_text_field($request["rua"]);
    $numero = sanitize_text_field($request["numero"]);

    // Verificando se o usuário ou o email já existem no sistema
    $user_exists = username_exists($email);
    $email_exists = email_exists($email);

    if(!$user_exists && !$email_exists && $email && $senha){
      // Criando um novo usuário se não houver conflitos e os campos obrigatórios estiverem preenchidos
      $user_id = wp_create_user($email, $senha, $email);

      // Definindo os dados do usuário
      $response = array(
        "ID" => $user_id,
        "display_name" => $nome,
        "first_name" => $nome,
        "role" => "subscriber"
      );

      // Atualizando os dados do usuário
      wp_update_user($response);

      // Atualizando metadados personalizados do usuário
      update_user_meta($user_id, "estado", $estado);
      update_user_meta($user_id, "cidade", $cidade);
      update_user_meta($user_id, "cep", $cep);
      update_user_meta($user_id, "bairro", $bairro);
      update_user_meta($user_id, "rua", $rua);
      update_user_meta($user_id, "numero", $numero);
    } else {
      // Se o usuário ou o email já existirem, retornar um erro
      $response = new WP_Error("email", "Email já cadastrado.", array("status" => 403));
    }
    // Retornar a resposta, garantindo que esteja no formato apropriado para a API
    return rest_ensure_response($response);
  }

  // Registrando a rota da API
  function register_api_user_post() {
    register_rest_route("api", "/usuario", array(
      array(
        "methods" => WP_REST_Server::CREATABLE,
        "callback" => "api_user_post"
      )
    ));
  }

  // Adicionando a ação de inicialização da API REST para registrar a rota
  add_action("rest_api_init", "register_api_user_post");
?>