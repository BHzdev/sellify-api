<?php
  // Função para retornar os detalhes de um produto por slug
  function product_scheme($slug) {
    $post_id = get_product_id_by_slug($slug);
    if($post_id) {
      $post_meta = get_post_meta($post_id);

      // Obtendo as imagens anexadas ao produto
      $images = get_attached_media("image", $post_id);
      $images_array = null;

      if($images) {
        $images_array = array();
        foreach($images as $key => $value) {
          $images_array[] = array(
            "titulo" => $value->post_name,
            "src" => $value->guid,
          );
        }
      }

      // Montando a resposta com os detalhes do produto
      $response = array(
        "id" => $slug, 
        "fotos" => $images_array,
        "nome" => $post_meta["nome"][0],
        "preco" => $post_meta["preco"][0],
        "descricao" => $post_meta["descricao"][0],
        "vendido" => $post_meta["vendido"][0],
        "usuario_id" => $post_meta["usuario_id"][0],
      );

    } else {
      $response = new WP_Error("naoexiste", "Produto não encontrado.", array("status" => 404));
    }
    return $response;
  }

  // Função para obter detalhes de um produto através da API GET
  function api_product_get($request) {
    $response = product_scheme($request["slug"]);
    return rest_ensure_response($response);
  }

  // Registrando a rota da API
  function register_api_product_get() {
    register_rest_route("api", "/produto/(?P<slug>[-\w]+)", array(
      array(
        "methods" => WP_REST_Server::READABLE,
        "callback" => "api_product_get",
      ),
    ));
  }
  // Adicionando a ação de inicialização da API REST para registrar a rota
  add_action("rest_api_init", "register_api_product_get");

  // Função para obter lista de produtos através da API GET
  function api_products_get($request) {
    // Obtendo parâmetros da consulta
    $q = sanitize_text_field($request["q"]) ?: "";
    $_page = sanitize_text_field($request["_page"]) ?: 0;
    $_limit = sanitize_text_field($request["_limit"]) ?: 9;
    $usuario_id = sanitize_text_field($request["usuario_id"]);

    // Construindo a consulta para os produtos
    $usuario_id_query = null;
    if($usuario_id) {
      $usuario_id_query = array(
        "key" => "usuario_id",
        "value" => $usuario_id,
        "compare" => "="
      );
    }

    $vendido = array(
      "key" => "vendido",
      "value" => "false",
      "compare" => "="
    );

    $query = array(
      "post_type" => "produto",
      "posts_per_page" => $_limit,
      "paged" => $_page,
      "s" => $q,
      "meta_query" => array(
        $usuario_id_query,
        $vendido,
      )
    );

    // Executando a consulta e montando a resposta
    $loop = new WP_Query($query);
    $posts = $loop->posts;
    $total = $loop->found_posts;

    $produtos = array();
    foreach ($posts as $key => $value) {
      $produtos[] = product_scheme($value->post_name);
    }

    // Montando a resposta formatada para a API
    $response = rest_ensure_response($produtos);
    $response->header("X-Total-Count", $total);

    return $response;
  }

  // Registrando a rota da API
  function register_api_products_get() {
    register_rest_route("api", "/produto", array(
      array(
        "methods" => WP_REST_Server::READABLE,
        "callback" => "api_products_get",
      ),
    ));
  }

  // Adicionando a ação de inicialização da API REST para registrar a rota
  add_action("rest_api_init", "register_api_products_get");
?>