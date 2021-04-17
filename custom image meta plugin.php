<?php
/*
Plugin Name: DAI-custom image meta plugin
Description: extend the media library with two sortable columns 'photographer' and 'copyright'
Author: Alexander Städtler
*/

////update all images with default values for custom fields "Photographer" and "Copyright"
// Run the loop when the plugin is activated
register_activation_hook(__FILE__, 'as_update_my_metadata');
function as_update_my_metadata(){
    $args = array(
        'post_type' => 'attachment', // Only get the posts
        'post_status' => 'publish' OR 'private', // Only the posts that are published
        'posts_per_page'   => -1 // Get every post
    );
    $posts = get_posts($args);
    foreach ( $posts as $post ) {
        // Run a loop and update every meta data
        update_post_meta( $post->ID, 'photographer', 'unknown' );
        update_post_meta( $post->ID, 'copyright', 'not defined' );
    }
}

//add custom media fields to media attachment view
add_filter( 'attachment_fields_to_edit', 'as_attachment_field_credit', 10, 2 );
function as_attachment_field_credit( $form_fields, $post ) {
    $form_fields['photographer'] = array(
        'label' => 'Photographer',
        'input' => 'text',
        'value' => get_post_meta( $post->ID, 'photographer', true ),
    );

    $form_fields['copyright'] = array(
        'label' => 'Copyright',
        'input' => 'text',
        'value' => get_post_meta( $post->ID, 'copyright', true ),
    );

    return $form_fields;
}

//add custom media fields to media uploader

add_filter( 'attachment_fields_to_save', 'as_attachment_field_credit_save', 10, 2 );
function as_attachment_field_credit_save( $post, $attachment ) {
    if( isset( $attachment['photographer'] ) )
        update_post_meta( $post['ID'], 'photographer', $attachment['photographer'] );

    if( isset( $attachment['copyright'] ) )
        update_post_meta( $post['ID'], 'copyright', $attachment['copyright'] );

    return $post;
}


////add custom media fields to Admin Panel Media view

//register custom columns in the array of columns to be displayed
 
add_filter('manage_media_columns', 'as_media_additional_columns', 1);
function as_media_additional_columns($defaults){
    $defaults['photographer'] = __('Photographer');
    $defaults['copyright'] = __('Copyright');
    return $defaults;
}

//Fill Custom Columns with content

add_action('manage_media_custom_column', 'as_media_custom_columns_attachment_id', 1, 2);
function as_media_custom_columns_attachment_id($column_name, $post_id){
   switch ( $column_name ) {
    case 'photographer':
      echo get_post_meta( $post_id, 'photographer', true );
      break;

    case 'copyright':
      echo get_post_meta( $post_id, 'copyright', true );
      break;
    }
}

////make custom columns sortable

//add custom columns to array sortable columns

add_filter('manage_upload_sortable_columns', 'as_add_custom_column_sortable');
function as_add_custom_column_sortable($columns) {
    $columns['photographer'] = 'photographer';
    $columns['copyright'] = 'copyright';
    return $columns;
}

//alter the post query in case stuff gets sorted by custom columns
add_action( 'pre_get_posts', 'as_custom_column_orderby' );
function as_custom_column_orderby( $query ) {
  if( ! is_admin() || ! $query->is_main_query() ) {
    return;
  }
  switch ($query->get( 'orderby')) {
    case 'photographer':
      $query->set( 'orderby', 'meta_value' );
      $query->set( 'meta_key', 'photographer' );
      break;
    case 'copyright':
      $query->set( 'orderby', 'meta_value' );
      $query->set( 'meta_key', 'copyright' );
      break;   
  }
}
?>