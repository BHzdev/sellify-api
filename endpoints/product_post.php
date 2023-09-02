<?php
  function api_product_post($request) {
    // Obtendo o usuário atualmente logado
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if($user_id > 0) {
      // Obtendo e sanitizando os dados do request
      $nome = sanitize_text_field($request["nome"]);
      $preco = sanitize_text_field($request["preco"]);
      $descricao = sanitize_text_field($request["descricao"]);

      // Montando os dados para criar um novo post de tipo "produto"
      $response = array(
        "post_author" => $user_id,
        "post_type" => "produto",
        "post_title" => $nome,
        "post_status" => "publish",
        "meta_input" => array(
          "nome" => $nome,
          "preco" => $preco,
          "descricao" => $descricao,
          "usuario_id" => $user->user_login,
          "vendido" => "false",
        ),
      );

      // Inserindo o novo post na base de dados e obtendo o ID
      $produto_id = wp_insert_post($response);
      $response["id"] = get_post_field("post_name", $produto_id);

      // Lidando com uploads de arquivos
      $files = $request->get_file_params();

      if($files) {
        require_once(ABSPATH . "wp-admin/includes/image.php");
        require_once(ABSPATH . "wp-admin/includes/file.php");
        require_once(ABSPATH . "wp-admin/includes/media.php");

        foreach ($files as $file => $array) {
          media_handle_upload($file, $produto_id);
        }
      }
    } else {
      // Retornando erro de permissão
      $response = new WP_Error("permissao", "Usuário não possui permissão.", array("status" => 401));
    }
    // Retornando a resposta formatada para a API
    return rest_ensure_response($response);
  }

  // Registrando a rota da API
  function register_api_product_post() {
    register_rest_route("api", "/produto", array(
      array(
        "methods" => WP_REST_Server::CREATABLE,
        "callback" => "api_product_post",
      ),
    ));
  }

  // Adicionando a ação de inicialização da API REST para registrar a rota
  add_action("rest_api_init", "register_api_product_post");
?>