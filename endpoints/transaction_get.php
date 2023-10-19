<?php
function api_transaction_get($request) {
  // Obtém o tipo de consulta (por padrão, "comprador_id" é usado se nenhum tipo for especificado).
  $tipo = sanitize_text_field($request["tipo"]) ?: "comprador_id";

  // Obtém o usuário atualmente autenticado no WordPress.
  $user = wp_get_current_user();
  $user_id = $user->ID;

  // Verifica se o usuário está autenticado.
  if ($user_id) {
    // Obtém o nome de usuário (login) do usuário autenticado.
    $login = get_userdata($user_id)->user_login;

    // Constrói uma consulta para buscar transações com base no tipo de consulta e no nome de usuário.
    $meta_query = null;
    if ($tipo) {
      $meta_query = array(
        "key" => $tipo,
        "value" => $login,
        "compare" => "="
      );
    }

    $query = array(
      "post_type" => "transacao", // Define o tipo de post a ser consultado.
      "orderby" => "date", // Ordena os resultados por data.
      "posts_per_page" => -1, // Retorna todas as transações (sem limite de quantidade).
      "meta_query" => array(
        $meta_query // Define a consulta personalizada com base no tipo e no nome de usuário.
      )
    );

    // Executa a consulta.
    $loop = new WP_Query($query);
    $posts = $loop->posts;

    $response = array();
    // Monta a resposta com as informações das transações encontradas.
    foreach ($posts as $key => $value) {
      $post_id = $value->ID;
      $post_meta = get_post_meta($post_id);

      $response[] = array(
        "comprador_id" => $post_meta["comprador_id"][0],
        "vendedor_id" => $post_meta["vendedor_id"][0],
        "endereco" => json_decode($post_meta["endereco"][0]),
        "produto" => json_decode($post_meta["produto"][0]),
        "data" => $value->post_date,
      );
    }
  } else {
    $response = new WP_Error("permissao", "Usuário não possui permissão.", array("status" => 401));
  }

  return rest_ensure_response($response);
}

// Registrando a rota da API
function register_api_transaction_get() {
  register_rest_route("api", "/transacao", array(
    array(
      "methods" => WP_REST_Server::READABLE, 
      "callback" => "api_transaction_get", 
    ),
  ));
}

// Adicionando a ação de inicialização da API REST para registrar a rota
add_action("rest_api_init", "register_api_transaction_get");

?>