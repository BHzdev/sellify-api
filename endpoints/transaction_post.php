<?php
function api_transaction_post($request) {
  // Obtém o usuário atualmente autenticado no WordPress.
  $user = wp_get_current_user();
  $user_id = $user->ID;
  $produto_vendido = $request['produto']['vendido'] === 'false';

  // Verifica se o usuário está autenticado.
  if ($user_id > 0) {
    // Obtém informações dos produtos, comprador, vendedor, endereço e os converte em texto seguro.
    $produto_slug = sanitize_text_field($request["produto"]["id"]);
    $produto_nome = sanitize_text_field($request["produto"]["nome"]);
    $comprador_id = sanitize_text_field($request["comprador_id"]);
    $vendedor_id = sanitize_text_field($request["vendedor_id"]);
    $endereco = json_encode($request["endereco"], JSON_UNESCAPED_UNICODE);
    $produto = json_encode($request["produto"], JSON_UNESCAPED_UNICODE);

    // Obtém o ID do produto com base no slug.
    $produto_id = get_product_id_by_slug($produto_slug);

    // Atualiza um campo personalizado do produto para indicar que ele foi vendido.
    update_post_meta($produto_id, "vendido", "true");

    // Define os dados para criar um novo post do tipo 'transacao'.
    $response = array(
      "post_author" => $user_id,
      "post_type" => "transacao",
      "post_title" => $comprador_id . " - " . $produto_nome,
      "post_status" => "publish",
      "meta_input" => array(
        "comprador_id" => $comprador_id,
        "vendedor_id" => $vendedor_id,
        "endereco" => $endereco,
        "produto" => $produto,
      ),
    );

    // Insere o novo post 'transacao' no banco de dados.
    $post_id = wp_insert_post($response);
  } else {
    $response = new WP_Error("permissao", "Usuário não possui permissão.", array("status" => 401));
  }

  return rest_ensure_response($response);
}

// Registrando a rota da API
function register_api_transaction_post() {
  register_rest_route("api", "/transacao", array(
    array(
      "methods" => WP_REST_Server::CREATABLE,
      "callback" => "api_transaction_post", 
    ),
  ));
}

// Adicionando a ação de inicialização da API REST para registrar a rota
add_action("rest_api_init", "register_api_transaction_post");

?>