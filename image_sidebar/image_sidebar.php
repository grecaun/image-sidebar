<?php

/*
Plugin Name: Image Sidebar
Description: Will display up to two images on the sidebar.  These images are chosen based upon the page displayed.
Author: James Sentinella
Version: 0.1
Author URI: http://jasentin.com
*/

class jasentin_image_sidebar extends WP_Widget {

    function jasentin_image_sidebar() {
        add_action( 'admin_enqueue_scripts', array($this,'scripts'));
        parent::WP_Widget(false, $name = __('Image Sidebar', 'image_sidebar') );
    }

    function form($instance) {
        $pages = isset ( $instance['page_slug'] ) ? $instance['page_slug'] : array();
        $firstimages = isset ( $instance['first_image'] ) ? $instance['first_image'] : array();
        $secondimages = isset ( $instance['second_image'] ) ? $instance['second_image'] : array();
        $page_num = count( $pages );
        $pages[ $page_num + 1 ] = '';
        $output_html = array();
        $pages_counter = 0;
        $default_img_url = plugins_url('image_sidebar')."/images/default.png";

        foreach ( $pages as $name => $value )
        {
            if ($pages_counter !== 0) { $output_html[] = '<hr>'; }
            $output_html[] = '<div class="js-image-sidebar-page-box">';
            $output_html[] = sprintf(
                '<p><label for="%1$s[%2$s]">%4$s</label><input type="text" name="%1$s[%2$s]" value="%3$s" class="widefat"></p>',
                $this->get_field_name( 'page_slug' ),
                $pages_counter,
                esc_attr( $value ),
                'Page Slug'
            );
            $img = $firstimages[$pages_counter];
            if ($img != '') { $output_html[] = sprintf('<div style="width:100%;overflow:hidden;height:150px;background-image:url(%1$s);background-repeat:no-repeat;"></div>', $img); }
            $output_html[] = sprintf(
                '<p><label for="%1$s[%2$s]">%4$s</label><input name="%1$s[%2$s]" type="text" value="%3$s" class="widefat"><button class="upload_image_button button button-primary">Upload Image</button></p>',
                $this->get_field_name( 'first_image' ),
                $pages_counter,
                esc_url( $firstimages[$pages_counter] ),
                'Image 1'
            );
            $img = $secondimages[$pages_counter];
            if ($img != '') { $output_html[] = sprintf('<div style="width:100%;overflow:hidden;height:150px;background-image:url(%1$s);background-repeat:no-repeat;"></div>', $img); }
            $output_html[] = sprintf(
                '<p><label for="%1$s[%2$s]">%4$s</label><input name="%1$s[%2$s]" type="text" value="%3$s" class="widefat"><button class="upload_image_button button button-primary">Upload Image</button></p>',
                $this->get_field_name( 'second_image' ),
                $pages_counter,
                esc_url( $secondimages[$pages_counter] ),
                'Image 2'
            );
            $output_html[] = '</div>';
            $pages_counter += 1;
        }

        print '<p>' . join( '</p>', $output_html );
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['page_slug'] = array();
        if (isset ( $new_instance['page_slug'] )) {
            foreach ($new_instance['page_slug'] as $value) {
                $key = array_search($value, $new_instance['page_slug']);
                if ( '' !== trim($value) ) {
                    $instance['page_slug'][] = $value;
                    if ($new_instance['first_image'][$key] !== '') {
                        $instance['first_image'][$key] = $new_instance['first_image'][$key];
                    }
                    if ($new_instance['second_image'][$key] !== '') {
                        $instance['second_image'][$key] = $new_instance['second_image'][$key];
                    }
                }
            }
        }
        return $instance;
    }

    function widget($args, $instance) {
        extract( $args );
        $slug = get_the_slug();
        wp_enqueue_style( 'image-sidebar-style', plugins_url('image_sidebar.css', __FILE__) );
        echo $before_widget;
        echo '<div class="image_sidebar_box">';
        echo '<div class="widget-textarea">';
        $default_img_url = plugins_url('image_sidebar')."/images/default.png";
        $first_img_url = $default_img_url;
        $key = array_search($slug, $instance['page_slug']);
        if ($key !== false && isset($instance['first_image'][$key])) {
            if ($instance['first_image'][$key] !== '') {
                $first_img_url = $instance['first_image'][$key];
            }
        }
        $second_img_url = $default_img_url;
        if ($key !== false && isset($instance['second_image'][$key])) {
            if ($instance['second_image'][$key] !== '') {
                $second_img_url = $instance['second_image'][$key];
            }
        }
        echo "<img class='img-responsive image-sidebar-image' src='".$first_img_url."'><img class='img-responsive image-sidebar-image' src='".$second_img_url."'>";
        echo '</div>';
        echo $after_widget;
    }

    public function scripts() {
        wp_enqueue_script( 'media-upload' );
        wp_enqueue_media();
        wp_enqueue_script('our_admin', plugins_url('image_sidebar').'/image_sidebar_media_upload.js', array('jquery'));
    }
}

add_action('widgets_init', create_function('', 'return register_widget("jasentin_image_sidebar");'));
?>