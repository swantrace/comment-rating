<?php
/**
 * Plugin Name:         Comment Rating
 * Version:             0.0.1
 * Author:              Fred Hong
 * Author URI:          https://fredhong.ca
 */

if(!defined('WPINC')){
    exit('Do Not access this file directly: ' . basename(__FILE__));
}

function comment_rating_enqueue_style_script()
{ 
    wp_enqueue_style('rateyo-css', plugin_dir_url( __FILE__ ) . 'assets/jquery.rateyo.css');  
    wp_enqueue_script('rateyo-js', plugin_dir_url( __FILE__ ) . 'assets/jquery.rateyo.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('main-js', plugin_dir_url( __FILE__ ) . 'assets/main.js', array('jquery', 'rateyo-js'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'comment_rating_enqueue_style_script');

add_action( 'comment_form_logged_in_after', 'additional_fields' );
add_action( 'comment_form_after_fields', 'additional_fields' );

function additional_fields () {
    if(is_singular( 'place' )):
        echo '<div class="comment-form-rating">'.
             '<label for="rating" style="float: left;">'. __('评分: ') . '<span class="required">*</span></label>'.
             '<div style="flaot: left;" id="comment-rating-field"></div>' . 
             '</div><input name="rating" id="comment-rating-input-field" value="" type="hidden">';
    endif;
}

add_action('comment_post', 'save_comment_meta_data');

function save_comment_meta_data($comment_id){
    if((isset($_POST['rating'])) && ($_POST['rating'] !='')){
        $rating = wp_filter_nohtml_kses($_POST['rating']);
        $rating = $rating;
        add_comment_meta($comment_id, 'rating', $rating);
        $post_id = get_comment($comment_id)->comment_post_ID;
        
        if(empty(get_post_meta($post_id, 'rating_number', true)||empty(get_post_meta($post_id, 'rating_sum', true)))){
            add_post_meta($post_id, 'rating_number', 1);
            add_post_meta($post_id, 'rating_sum', $rating);
            add_post_meta($post_id, 'rating_average', $rating);
        } else {
            $rating_number = (int)(get_post_meta($post_id, 'rating_number', true));
            $rating_number++;
            $rating_sum    = (get_post_meta($post_id, 'rating_sum', true));
            $rating_sum   += $rating;            
            update_post_meta($post_id, 'rating_number', $rating_number);
            update_post_meta($post_id, 'rating_sum', $rating_sum);
            $rating_average = $rating_sum/$rating_number;
            update_post_meta($post_id, 'rating_average', $rating_average);
        }
    }
}

add_action('edit_comment', 'extend_comment_edit_metafields');

function extend_comment_edit_metafields($comment_id){
    $post_id = get_comment($comment_id)->comment_post_ID;
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) {
        return;
    }

    if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ){
        
        $old_rating = get_post_meta($post_id, 'rating', true);
        $old_rating = $old_rating;
        
        $new_rating = wp_filter_nohtml_kses($_POST['rating']);
        $new_rating = $new_rating;
        
        update_comment_meta( $comment_id, 'rating', $new_rating );
        
        if(empty(get_post_meta($post_id, 'rating_number', true)) || get_post_meta($post_id, 'rating_number', true) == 0){
            add_post_meta($post_id, 'rating_number', 1);
            add_post_meta($post_id, 'rating_sum', $new_rating);
            add_post_meta($post_id, 'rating_average', $new_rating);
        } else {
            $rating_sum    = (get_post_meta($post_id, 'rating_sum', true));
            $rating_sum    = $rating_sum - $old_rating + $new_rating;

            $rating_number = (int)(get_post_meta($post_id, 'rating_number', true));
            $rating_average = $rating_sum/$rating_number;          
            update_post_meta($post_id, 'rating_sum', $rating_number);
            update_post_meta($post_id, 'rating_average', $rating_average);

        }
    }
}