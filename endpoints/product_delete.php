<?php
  function api_product_delete($request) {
    // Obtendo o slug do produto a ser excluído
    $slug = $request["slug"];

    // Obtendo o ID do produto pelo slug
    $produto_id = get_product_id_by_slug($slug);

    // Obtendo o usuário atualmente logado
    $user = wp_get_current_user();

    // Obtendo o ID do autor do post (produto)
    $author_id = (int) get_post_field("post_author", $produto_id);
    $user_id = (int) $user->ID;

    // Verificando se o usuário atual tem permissão para excluir o produto
    if($user_id === $author_id) {
      // Obtendo as imagens anexadas ao produto
      $images = get_attached_media("image", $produto_id);

      // Excluindo as imagens anexadas, se houver
      if($images) {
        foreach($images as $key => $value) {
          wp_delete_attachment($value->ID, true);
        }
      }
      
      // Excluindo o post (produto)
      $response = wp_delete_post($produto_id, true);
    } else {
      // Retornando um erro de permissão caso o usuário não tenha permissão
      $response = new WP_Error("permissao", "Usuário não possui permissão.", array("status" => 401));
    }

    // Retornando a resposta formatada para a API
    return rest_ensure_response($response);
  }

  // Registrando a rota da API
  function register_api_product_delete() {
    register_rest_route("api", "/produto/(?P<slug>[-\w]+)", array(
      array(
        "methods" => WP_REST_Server::DELETABLE,
        "callback" => "api_product_delete",
      ),
    ));
  }

  // Adicionando a ação de inicialização da API REST
  add_action("rest_api_init", "register_api_product_delete");
?>
