<?php

function agileplayer_enqueue_color_picker() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'agileplayer-admin', plugins_url('admin.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
   
}
add_action('load-settings_page_agileplayer-settings', 'agileplayer_enqueue_color_picker');

//function agileplayer_menu() {
//	global $agileplayer_admin;
//	$agileplayer_admin = add_options_page('Agile Player Settings', 'Agile Player Settings', 'manage_options', 'agileplayer-settings', 'agileplayer_settings');
//}
//add_action('admin_menu', 'agileplayer_menu');

//FOR CALL TO IMAGE ADD ACTION
function add_admin_js(){

    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
}

function add_admin_css(){

    wp_enqueue_style('thickbox');
    
}

add_action('admin_print_scripts', 'add_admin_js');
add_action('admin_print_styles', 'add_admin_css');

//FOR CALL TO IMAGE ADD ACTION END

function prash_add_menu_items() {
	global $agileplayer_admin;

    add_menu_page( 'My Videos', 'Agile Video Player', 'manage_options', 'Prash_Video_Player', 'Prash_Video_List' );

    add_submenu_page('null', 'My Videos', 'My Videos', 'manage_options', 'Prash_Video_List', 'Prash_Video_List');    
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_add_video', 'prash_add_video_form');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_new_video', 'prash_add_video_do');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_edit_video', 'prash_edit_video_form');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_edit_video_do', 'prash_edit_video_do');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_delete_video', 'prash_delete_video_form');
    // add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_add_action', 'prash_add_action_form');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_delete_video_do', 'prash_delete_video_do');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_get_full_video_code', 'prash_get_full_video_code_do');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_get_video_shortcode', 'prash_get_video_shortcode_do');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_add_cta', 'prash_add_cta_form');
    // add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_add_fblike', 'prash_add_fblike_form');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_add_fblike_do', 'prash_add_fblike_ok');
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_add_optin', 'prash_add_optin_form');
    add_submenu_page('null', 'Actions', 'Actions', 'mthickbox-cssanage_options', 'prash_add_new_optin', 'prash_add_new_optin_do');

    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_optin_new_do', 'prash_optin_new_do');
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_edit_optin', 'prash_edit_optin_form');
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_add_timed_optin_form', 'prash_add_timed_optin_form');
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_edit_timed_optin_form', 'prash_edit_timed_optin_form');
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_edit_cta_form', 'prash_edit_cta_form');
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_edit_fblike_form', 'prash_edit_fblike_form');
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_add_cta_do', 'prash_add_cta_ok');
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_edit_optin_action', 'prash_edit_optin_action_do');
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_delete_optin', 'prash_delete_optin_form');
    
    add_submenu_page('null', 'Actions', 'Actions', 'manage_options', 'prash_delete_optin_do', 'prash_delete_optin_do');
    
    //  old  $agileplayer_admin = add_options_page('Agile Player Settings', 'Agile Player Settings', 'manage_options', 'agileplayer-settings', 'agileplayer_settings');
}
add_action('admin_menu', 'prash_add_menu_items');

/* Contextual Help */
function agileplayer_help($contextual_help, $screen_in, $screen) {
	global $agileplayer_admin;
	if ($screen_in == $agileplayer_admin) {
		$contextual_help = <<<_end_
		<p><strong>Agile Player Settings Screen</strong></p>
		<p>The values set here will be the default values for all videos, unless you specify differently in the shortcode. Uncheck <em>Use CDN hosted version?</em> if you want to use a self-hosted copy of Agile Player instead of the CDN hosted version. <strong>Using the CDN hosted version is preferable in most situations.</strong></p>
		<p>If you are using a responsive WordPress theme, you may want to check the <em>Responsive Video</em> checkbox.</p>
		<p>Uncheck the <em>Use the [video] shortcode?</em> option <strong>only</strong> if you are using WordPress 3.6+ and wish to use the [video] tag for MediaElement.js. You will still be able to use the [agileplayer] tag to embed videos using Agile Player.</p>
_end_;
	}
	return $contextual_help;
}
add_filter('contextual_help', 'agileplayer_help', 10, 3);


function agileplayer_settings() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	?>
	<div class="wrap">
	<h2>Agile Player Settings</h2>
	
	<form method="post" action="options.php">
	<?php
	settings_fields( 'agileplayer_options' );
	do_settings_sections( 'agileplayer-settings' );
	?>
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	<h2>Using Agile Player</h2>
	<?php echo file_get_contents(plugin_dir_path( __FILE__ ) . 'help.html'); ?>
	</form>
	</div>
	<?php
	
}
add_action('admin_init', 'register_agileplayer_settings');

function register_agileplayer_settings() {
	register_setting('agileplayer_options', 'agileplayer_options', 'agileplayer_options_validate');
	add_settings_section('agileplayer_defaults', 'Default Settings', 'defaults_output', 'agileplayer-settings');
	
	add_settings_field('agileplayer_width', 'Width', 'width_output', 'agileplayer-settings', 'agileplayer_defaults');
	add_settings_field('agileplayer_height', 'Height', 'height_output', 'agileplayer-settings', 'agileplayer_defaults');
	
	add_settings_field('agileplayer_preload', 'Preload', 'preload_output', 'agileplayer-settings', 'agileplayer_defaults');
	add_settings_field('agileplayer_autoplay', 'Autoplay', 'autoplay_output', 'agileplayer-settings', 'agileplayer_defaults');
	
	add_settings_field('agileplayer_responsive', 'Responsive Video', 'responsive_output', 'agileplayer-settings', 'agileplayer_defaults');
	
	add_settings_field('agileplayer_cdn', 'Use CDN hosted version?', 'cdn_output', 'agileplayer-settings', 'agileplayer_defaults');
	
	add_settings_field('agileplayer_color_one', 'Icon Color', 'color_one_output', 'agileplayer-settings', 'agileplayer_defaults');
	add_settings_field('agileplayer_color_two', 'Progress Color', 'color_two_output', 'agileplayer-settings', 'agileplayer_defaults');
	add_settings_field('agileplayer_color_three', 'Background Color', 'color_three_output', 'agileplayer-settings', 'agileplayer_defaults');
	
	add_settings_field('agileplayer_video_shortcode', 'Use the [video] shortcode?', 'video_shortcode_output', 'agileplayer-settings', 'agileplayer_defaults');
	
	add_settings_field('agileplayer_reset', 'Restore defaults upon plugin deactivation/reactivation', 'reset_output', 'agileplayer-settings', 'agileplayer_defaults');
}

/* Validate our inputs */

function agileplayer_options_validate($input) {
	$newinput['agileplayer_height'] = $input['agileplayer_height'];
	$newinput['agileplayer_width'] = $input['agileplayer_width'];
	$newinput['agileplayer_preload'] = $input['agileplayer_preload'];
	$newinput['agileplayer_autoplay'] = $input['agileplayer_autoplay'];
	$newinput['agileplayer_responsive'] = $input['agileplayer_responsive'];
	$newinput['agileplayer_cdn'] = $input['agileplayer_cdn'];
	$newinput['agileplayer_color_one'] = $input['agileplayer_color_one'];
	$newinput['agileplayer_color_two'] = $input['agileplayer_color_two'];
	$newinput['agileplayer_color_three'] = $input['agileplayer_color_three'];
	$newinput['agileplayer_reset'] = $input['agileplayer_reset'];
	$newinput['agileplayer_video_shortcode'] = $input['agileplayer_video_shortcode'];
	
	if(!preg_match("/^\d+$/", trim($newinput['agileplayer_width']))) {
		 $newinput['agileplayer_width'] = '';
	 }
	 
	 if(!preg_match("/^\d+$/", trim($newinput['agileplayer_height']))) {
		 $newinput['agileplayer_height'] = '';
	 }
	 
	 if(!preg_match("/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/", trim($newinput['agileplayer_color_one']))) {
		 $newinput['agileplayer_color_one'] = '#ccc';
	 }
	 
	 if(!preg_match("/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/", trim($newinput['agileplayer_color_two']))) {
		 $newinput['agileplayer_color_two'] = '#66A8CC';
	 }
	 
	 if(!preg_match("/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/", trim($newinput['agileplayer_color_three']))) {
		 $newinput['agileplayer_color_three'] = '#000';
	 }
	
	return $newinput;
}

/* Display the input fields */

function defaults_output() {
	//echo '';
}

function height_output() {
	$options = get_option('agileplayer_options');
	echo "<input id='agileplayer_height' name='agileplayer_options[agileplayer_height]' size='40' type='text' value='{$options['agileplayer_height']}' />";
}

function width_output() {
	$options = get_option('agileplayer_options');
	echo "<input id='agileplayer_width' name='agileplayer_options[agileplayer_width]' size='40' type='text' value='{$options['agileplayer_width']}' />";
}

function preload_output() {
	$options = get_option('agileplayer_options');
	if($options['agileplayer_preload']) { $checked = ' checked="checked" '; } else { $checked = ''; }
	echo "<input ".$checked." id='agileplayer_preload' name='agileplayer_options[agileplayer_preload]' type='checkbox' />";
}

function autoplay_output() {
	$options = get_option('agileplayer_options');
	if($options['agileplayer_autoplay']) { $checked = ' checked="checked" '; } else { $checked = ''; }
	echo "<input ".$checked." id='agileplayer_autoplay' name='agileplayer_options[agileplayer_autoplay]' type='checkbox' />";
}

function responsive_output() {
	$options = get_option('agileplayer_options');
	if($options['agileplayer_responsive']) { $checked = ' checked="checked" '; } else { $checked = ''; }
	echo "<input ".$checked." id='agileplayer_responsive' name='agileplayer_options[agileplayer_responsive]' type='checkbox' />";
}

function cdn_output() {
	$options = get_option('agileplayer_options');
	if($options['agileplayer_cdn']) { $checked = ' checked="checked" '; } else { $checked = ''; }
	echo "<input ".$checked." id='agileplayer_cdn' name='agileplayer_options[agileplayer_cdn]' type='checkbox' />";
}

function color_one_output() {
	$options = get_option('agileplayer_options');
	echo "<input id='agileplayer_color_one' name='agileplayer_options[agileplayer_color_one]' size='40' type='text' value='{$options['agileplayer_color_one']}' data-default-color='#ccc' class='agileplayer-color-field' />";
}

function color_two_output() {
	$options = get_option('agileplayer_options');
	echo "<input id='agileplayer_color_two' name='agileplayer_options[agileplayer_color_two]' size='40' type='text' value='{$options['agileplayer_color_two']}' data-default-color='#66A8CC' class='agileplayer-color-field' />";
}

function color_three_output() {
	$options = get_option('agileplayer_options');
	echo "<input id='agileplayer_color_three' name='agileplayer_options[agileplayer_color_three]' size='40' type='text' value='{$options['agileplayer_color_three']}' data-default-color='#000' class='agileplayer-color-field' />";
}

function video_shortcode_output() {
	$options = get_option('agileplayer_options');
	if(array_key_exists('agileplayer_video_shortcode', $options)){
		if($options['agileplayer_video_shortcode']) { $checked = ' checked="checked" '; } else { $checked = ''; }
	} else { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='agileplayer_video_shortcode' name='agileplayer_options[agileplayer_video_shortcode]' type='checkbox' />";
}

function reset_output() {
	$options = get_option('agileplayer_options');
	if($options['agileplayer_reset']) { $checked = ' checked="checked" '; } else { $checked = ''; }
	echo "<input ".$checked." id='agileplayer_reset' name='agileplayer_options[agileplayer_reset]' type='checkbox' />";
}


/* Set Defaults */
register_activation_hook(plugin_dir_path( __FILE__ ) . 'app.php', 'add_defaults_fn');

function add_defaults_fn() {
	$tmp = get_option('agileplayer_options');
    if(($tmp['agileplayer_reset']=='on')||(!is_array($tmp))) {
		$arr = array("agileplayer_height"=>"264","agileplayer_width"=>"640","agileplayer_preload"=>"","agileplayer_autoplay"=>"","agileplayer_responsive"=>"","agileplayer_cdn"=>"on","agileplayer_color_one"=>"#ccc","agileplayer_color_two"=>"#66A8CC","agileplayer_color_three"=>"#000","agileplayer_video_shortcode"=>"on","agileplayer_reset"=>"");
		update_option('agileplayer_options', $arr);
		update_option("agileplayer_db_version", "1.0");
	}
}


/* Plugin Updater */
function update_agileplayer() {
	$agileplayer_db_version = "1.0";
	
	if( get_option("agileplayer_db_version") != $agileplayer_db_version ) { //We need to update our database options
		$options = get_option('agileplayer_options');
		
		//Set the new options to their defaults
		$options['agileplayer_color_one'] = "#ccc";
		$options['agileplayer_color_two'] = "#66A8CC";
		$options['agileplayer_color_three'] = "#000";
		$options['agileplayer_video_shortcode'] = "on";
		
		update_option('agileplayer_options', $options);
		
		update_option("agileplayer_db_version", $agileplayer_db_version); //Update the database version setting
	}
}
add_action('admin_init', 'update_agileplayer');


?>
