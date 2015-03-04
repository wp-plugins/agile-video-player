<?php

$plugin_dir = plugin_dir_path( __FILE__ );

/*
 * Add buttons to visual editor
 */

function spp_ddl_tmcebuttons($post) {
	
	// Don't bother if current user doesn't have permissions
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
	
	// Add only in rich editor mode
	if( get_user_option( 'rich_editing' ) == 'true' ) {
		global $typenow;
            if (empty($typenow) && !empty($_GET['post'])) {
                $post = get_post($_GET['post']);
                $typenow = $post->post_type;
			} elseif (empty($typenow) && !empty($_GET['post_type'])) {
                $typenow = $_GET['post_type'];
			}
	}
	if( 'spp_ddl' != $typenow ) {
		add_filter('mce_external_plugins', 'spp_ddl_buttons_mce_plugin');
		add_filter( 'mce_buttons', 'spp_ddl_add_tmce_buttons' );
	}
}

function spp_ddl_add_tmce_buttons( $buttons ) {
    array_push( $buttons, '|', 'videoinsertbuttons' );
    return $buttons;
}
add_action( 'init', 'spp_ddl_tmcebuttons' );

/*
 * Bring in button
 */
 function spp_ddl_buttons_mce_plugin( $plugins ) {
 	$wpversion = get_bloginfo( 'version' );
		// this plugin file will work the magic of our button
 	if( $wpversion > '3.8.1' ) {
        $plugins['videoinsertbuttons'] =  plugins_url( 'js/spp-ddl-shortcode-dialog.js' , __FILE__ );
	} else {
        $plugins['videoinsertbuttons'] = plugins_url( 'js/spp-ddl-shortcode-dialog-3.0.js' , __FILE__ ); 
	}
		return $plugins;
	}

?>