<?php

function product_scheme($slug) {
  $post_id = get_product_id_by_slug($slug);
  if($post_id) {
    $post_meta = get_post_meta($post_id);

    $images = get_attached_media('image', $post_id);
    $images_array = null;

    if($images) {
      $images_array = array();
      foreach($images as $key => $value) {
        $images_array[] = array(
          'titulo' => $value->post_name,
          'src' => $value->guid,
        );
      }
    }

    $response = array(
      "id" => $slug, 
      "fotos" => $images_array,
      "nome" => $post_meta['nome'][0],
      "preco" => $post_meta['preco'][0],
      "descricao" => $post_meta['descricao'][0],
      "vendido" => $post_meta['vendido'][0],
      "usuario_id" => $post_meta['usuario_id'][0],
    );

  } else {
    $response = new WP_Error('naoexiste', 'Produto não encontrado.', array('status' => 404));
  }
  return $response;
}

function api_product_get($request) {
  $response = product_scheme($request["slug"]);
  return rest_ensure_response($response);
}

function register_api_product_get() {
  register_rest_route('api', '/produto/(?P<slug>[-\w]+)', array(
    array(
      'methods' => WP_REST_Server::READABLE,
      'callback' => 'api_product_get',
    ),
  ));
}
add_action('rest_api_init', 'register_api_product_get');

?>