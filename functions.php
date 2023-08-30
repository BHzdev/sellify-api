<?php

$template_directory = get_template_directory();

require_once($template_directory . "/custom-post-type/product.php");
require_once($template_directory . "/custom-post-type/transaction.php");

require_once($template_directory . "/endpoints/user_post.php");
require_once($template_directory . "/endpoints/user_get.php");

require_once($template_directory . "/endpoints/product_post.php");
require_once($template_directory . "/endpoints/product_get.php");

function expire_token() {
  return time() + (60 * 60 * 24);
}

function get_product_id_by_slug($slug) {
  $query = new WP_Query(array(
    'name' => $slug,
    'post_type' => 'produto',
    'numberposts' => 1,
    'fields' => 'ids'
  ));
  $posts = $query->get_posts();
  return array_shift($posts);
}

add_action("jwt_auth_expire", "expire_token");
?>