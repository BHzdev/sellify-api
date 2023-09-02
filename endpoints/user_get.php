<?php
  function api_user_get($request){
    // Obtendo o usuário atualmente logado
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if($user_id > 0) {
      // Se o usuário estiver logado, obter seus metadados personalizados
      $user_meta = get_user_meta($user_id);

      // Montando a resposta com os detalhes do usuário e seus metadados personalizados
      $response = array(
        "id" => $user->user_login,
        "nome" => $user->display_name,
        "email" => $user->user_email,
        "estado" => $user_meta["estado"][0],
        "cidade" => $user_meta["cidade"][0],
        "cep" => $user_meta["cep"][0],
        "bairro" => $user_meta["bairro"][0],
        "numero" => $user_meta["numero"][0],
        "rua" => $user_meta["rua"][0],
      );
    }else {
      // Se não houver usuário logado, retornar um erro de permissão
      $response = new WP_Error("permissao", "Usuário não possui permissão", array("status" => 401));
    }
    // Retornar a resposta formatada para a API
    return rest_ensure_response($response);
  }

  // Registrando a rota da API
  function register_api_user_get() {
    register_rest_route("api", "/usuario", array(
      array(
        "methods" => WP_REST_Server::READABLE,
        "callback" => "api_user_get"
      )
    ));
  }
  
  // Adicionando a ação de inicialização da API REST para registrar a rota
  add_action("rest_api_init", "register_api_user_get");
?>