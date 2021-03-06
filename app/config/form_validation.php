<?php

$config = array(
  'collectible_define' => array(
    array('field' => 'collectible_name', 'label' => 'collectible name', 'rules' => 'trim|required|max_length[64]|xss_clean'),
  ),

  'collectible_alter' => array(
    array('field' => 'attribute_name[]', 'label' => 'attribute name', 'rules' => 'trim|required|max_length[64]|xss_clean'),
  ),

  'collectibles_import' => array(
    array('field' => 'sql', 'label' => 'SQL', 'rules' => 'trim|required'),
  ),

  'collectible_rename' => array(
    array('field' => 'collectible_name', 'label' => 'collectible name', 'rules' => 'trim|required|max_length[64]|xss_clean'),
  ),

  'operation' => array(
    array('field' => 'role', 'label' => 'role', 'rules' => 'trim|max_length[255]'),
    array('field' => 'name', 'label' => 'operation name', 'rules' => 'trim|required|max_length[255]'),
    array('field' => 'sql_text', 'label' => 'SQL text', 'rules' => 'trim|required'),
    array('field' => 'has_view', 'label' => 'has view', 'rules' => 'is_natural|required'),
    array('field' => 'view_code', 'label' => 'view code', 'rules' => 'trim'),
  ),

  'database_settings' => array(
    array('field' => 'hostname', 'label' => 'Hostname', 'rules' => 'trim|required'),
    array('field' => 'username', 'label' => 'Username', 'rules' => 'trim|required'),
    array('field' => 'password', 'label' => 'Password', 'rules' => 'trim|required'),
    array('field' => 'database', 'label' => 'Database', 'rules' => 'trim|required'),
  ),

  'header_footer' => array(
    array('field' => 'header', 'label' => 'header (HTML)', 'rules' => 'trim|required'),
    array('field' => 'footer', 'label' => 'footer (HTML)', 'rules' => 'trim|required'),
  ),

  'style' => array(
    array('field' => 'style', 'label' => 'style (CSS)', 'rules' => 'trim|required'),
  ),

  'item_save' => array(
    array('field' => 'junk', 'label' => '', 'rules' => 'callback_item_save_valid'),
  ),
);

