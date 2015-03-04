<?php
/**
 * @package Agile Video Player
 * @version 1.0
 */
/*
Plugin Name: Agile Video Player Lite
Plugin URI: http://agilevideoplayer.com/
Description: A useful video player for WordPress.
Author: Prashant Mavinkurve
Version: 1.0
*/

$plugin_dir = plugin_dir_path( __FILE__ );

// require $plugin_dir . 'upgrademe.php';

/* The options page */
include_once($plugin_dir . 'admin.php');
include_once($plugin_dir . 'editor.php');

/* Useful Functions */
// include_once($plugin_dir . 'lib.php');

if(!class_exists('WP_List_Table')) :
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;

//**************New Code Start*************




class Videotable extends WP_List_Table {
    
    
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'video',     //singular name of the listed records
            'plural'    => 'videos',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }



    
     function column_default($item, $column_name){
        switch($column_name){
            case 'title':
//            case 'director':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


 
    function column_title($item){
        
        //Build row actions
        $actions = array(
            
            'edit'      => sprintf('<a href="?page=%s&action=%s&video=%s">Edit</a>','prash_edit_video','edit',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&video=%s">Delete</a>','prash_delete_video','delete',$item['id']),
            'get_full_video_code'    => sprintf('<a href="?page=%s&action=%s&video=%s">Get Video Code(Full)</a>','prash_get_full_video_code','get_full_video_code',$item['id']),
            'get_video_shortcode'    => sprintf('<a href="?page=%s&action=%s&video=%s">Get Video Shortcode</a>','prash_get_video_shortcode','get_video_shortcode',$item['id']),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }


    
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }


    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => 'Title'
            
        );
        return $columns;
    }


    
    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false)     //true means it's already sorted
            
        );
        return $sortable_columns;
    }


    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }



    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        
        $all_videos_sql = "select id, title, vid from " . $wpdb->prefix . 'prash_videos';
        
        $all_videos = $wpdb->get_results($all_videos_sql);
        
        $all_videos_arr = array();
        
        foreach($all_videos as $my_video){
         
            $temp_array = array(
                'id'        => $my_video->id,
                'title'     => $my_video->title
                
            );
            
            array_push($all_videos_arr, $temp_array);
            
        }
        
        
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;
        
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        
        $this->process_bulk_action();
        
        $data = $this->example_data;
        
        $data = $all_videos_arr;
                
        
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}



class Optinformtable extends WP_List_Table {
    
    
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'video',     //singular name of the listed records
            'plural'    => 'videos',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }



    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'title':
//            case 'director':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_title($item){
        
        //Build row actions
        $actions = array(
//            'edit'      => sprintf('<a href="?page=%s&action=%s&video=%s">Edit</a>',$_REQUEST['page'],'edit',$item['id']),
//            'delete'    => sprintf('<a href="?page=%s&action=%s&video=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
            
            'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>','prash_edit_optin','edit',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>','prash_delete_optin','delete',$item['id']),
//            'add_action'    => sprintf('<a href="?page=%s&action=%s&video=%s">Manage Actions</a>','prash_add_action','add_action',$item['id']),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => 'Title'
            
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false)     //true means it's already sorted
            
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        
        $all_videos_sql = "select id, name from " . $wpdb->prefix . 'prash_optins';
        
        $all_videos = $wpdb->get_results($all_videos_sql);
        
        $all_videos_arr = array();
        
        foreach($all_videos as $my_video){
         
            $temp_array = array(
                'id'        => $my_video->id,
                'title'     => $my_video->name
                
            );
            
            array_push($all_videos_arr, $temp_array);
            
        }
        
        
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        
        $data = $this->example_data;
        
        $data = $all_videos_arr;
                
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}



// function app_upgrademe()
// {
//     return 'http://agilevideoplayer.com/latest.php';
// }



function plugin_tables_install() {
	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	global $wpdb;
    
	$db_table_actions = $wpdb->prefix . 'prash_actions';
    $db_table_optins = $wpdb->prefix . 'prash_optins';
    $db_table_relations = $wpdb->prefix . 'prash_relations';
    $db_table_videos = $wpdb->prefix . 'prash_videos';
    
	if( $wpdb->get_var( "SHOW TABLES LIKE '$db_table_actions'" ) != $db_table_actions ) {
		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE $wpdb->collate";

		$sql_actions = "CREATE TABLE " . $db_table_actions . " (
			`id` int(11) NOT NULL AUTO_INCREMENT,
  `vid_id` int(11) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `cta_text_color` varchar(10) DEFAULT NULL,
  `cta_bg_color` varchar(10) DEFAULT NULL,
  `cta_text` text NOT NULL,
  `img_url` text,
  `img_link` text,
  `act_type` varchar(10) NOT NULL,
  `show_seconds` int(3) NOT NULL DEFAULT '0',
  `entry_anim` varchar(15) DEFAULT NULL,
  `exit_anim` varchar(15) DEFAULT NULL,
  `on_pause` int(11) NOT NULL DEFAULT '0',
  `on_end` int(11) NOT NULL DEFAULT '0',
  `skipfb` int(11) NOT NULL DEFAULT '0',
  `skip_fb_text` varchar(50) NOT NULL,
  `skip_fb_text_color` varchar(10) DEFAULT NULL,
  `fbid` text,
  `fb_title` varchar(50) NOT NULL,
  `fb_title_color` varchar(10) DEFAULT NULL,
  `buy_now_code` int(11) NOT NULL DEFAULT '0',
  `buy_now_tp` text,
  `buy_now_link` text,
  `ct_bt_code` int(11) NOT NULL DEFAULT '0',
  `ct_bt_bgcolor` varchar(10) NOT NULL,
  `ct_bt_bcolor` varchar(10) NOT NULL,
  `ct_bt_tcolor` varchar(10) NOT NULL,
  `ct_bt_text` text,
  `ct_bt_link` text,
  `cta_template` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`),
  KEY `vid_id` (`vid_id`)
		) $charset_collate;";
            
		dbDelta( $sql_actions );
	}
    
    if( $wpdb->get_var( "SHOW TABLES LIKE '$db_table_optins'" ) != $db_table_optins ) {
		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE $wpdb->collate";
 
		$sql_optins = "CREATE TABLE " . $db_table_optins . " (
			`id` int(11) NOT NULL AUTO_INCREMENT,
  `pre_form_name` varchar(10) NOT NULL,
  `ar` varchar(2) NOT NULL,
  `name` varchar(30) NOT NULL,
  `bg` varchar(10) NOT NULL,
  `headcolor` varchar(10) NOT NULL,
  `headtext` varchar(50) NOT NULL,
  `msgcolor` varchar(10) NOT NULL,
  `msgtext` varchar(100) NOT NULL,
  `butcolor` varchar(10) NOT NULL,
  `buttext` varchar(20) NOT NULL,
  `buttextcolor` varchar(10) NOT NULL,
  `emailvalidtxtcolor` varchar(10) NOT NULL,
  `skip_optin_text` int(11) NOT NULL DEFAULT '0',
  `skip_bt_text` varchar(50) NOT NULL,
  `optinskiptxtcolor` varchar(10) NOT NULL,
  `optinbordercolor` varchar(10) NOT NULL,
  `gr_key` varchar(50) DEFAULT NULL,
  `gr_campaign` varchar(50) DEFAULT NULL,
  `gr_track_id` varchar(100) DEFAULT NULL,
  `aw_key` varchar(50) DEFAULT NULL,
  `aw_secret` varchar(50) DEFAULT NULL,
  `aw_accessid` varchar(50) DEFAULT NULL,
  `aw_access_secret` varchar(50) DEFAULT NULL,
  `aw_ac_id` varchar(50) DEFAULT NULL,
  `aw_list_id` varchar(50) DEFAULT NULL,
  `aw_track_id` varchar(50) DEFAULT NULL,
  `mc_key` varchar(50) DEFAULT NULL,
  `mc_listid` varchar(50) DEFAULT NULL,
  `mc_trackid` varchar(100) DEFAULT NULL,
  `ic_key` varchar(50) DEFAULT NULL,
  `ic_list_id` varchar(50) DEFAULT NULL,
  `ic_uname` varchar(50) DEFAULT NULL,
  `ic_pass` varchar(50) DEFAULT NULL,
  `oa_key` varchar(50) DEFAULT NULL,
  `oa_sq` text,
  `oa_tag_name` varchar(100) DEFAULT NULL,
  `is_key` varchar(50) DEFAULT NULL,
  `is_fus_id` text,
  `is_tag_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
		) $charset_collate;";
		dbDelta( $sql_optins );
	}
    
    if( $wpdb->get_var( "SHOW TABLES LIKE '$db_table_relations'" ) != $db_table_relations ) {
		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE $wpdb->collate";
 
		$sql_relations = "CREATE TABLE " . $db_table_relations . " (
		`id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
		) $charset_collate;";
		dbDelta( $sql_relations );
	}
    
    if( $wpdb->get_var( "SHOW TABLES LIKE '$db_table_videos'" ) != $db_table_videos ) {
		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE $wpdb->collate";
 
		$sql_videos = "CREATE TABLE " . $db_table_videos . " (
			`id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `vid` varchar(20) NOT NULL,
  `youtube` varchar(200) DEFAULT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `dim` int(11) NOT NULL DEFAULT '0',
  `scroll_pause` int(11) NOT NULL DEFAULT '0',
  `autoplay` int(11) NOT NULL DEFAULT '0',
  `controls` int(11) NOT NULL DEFAULT '1',
  `auto_hide_cb` varchar(10) NOT NULL,
  `theme` varchar(10) NOT NULL,
  `vbordercolor` varchar(10) NOT NULL,
  `social_share` int(11) NOT NULL DEFAULT '0',
  `video_type` varchar(10) NOT NULL,
  `class` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mp4` varchar(200) DEFAULT NULL,
  `webm` varchar(200) DEFAULT NULL,
  `ogg` varchar(200) DEFAULT NULL,
  `poster` varchar(200) DEFAULT NULL,
  `loop` int(11) NOT NULL DEFAULT '0',
  `preload` int(11) NOT NULL DEFAULT '0',
  `responsive` int(11) DEFAULT NULL,
  `logo_brand_code` int(11) NOT NULL DEFAULT '0',
  `logo_pick` text,
  `logo_link` text,
  `logo_ps` int(11) NOT NULL DEFAULT '0',   
  PRIMARY KEY (`id`)
		) $charset_collate;";
		dbDelta( $sql_videos );
	}

}
register_activation_hook(__FILE__, 'plugin_tables_install');



////CREATING TABLES WHEN PLUGIN IS ACTIVATED END

function Prash_Video_List(){
    
    ?>
    <p><strong><a target="_blank" href="http://agilevideoplayer.com" >Upgrade to the full version for awesome marketing features. Click here >></a></strong></p> 
    <?php
    
    if(isset($_REQUEST['success_msg'])){

     if($_REQUEST['success_msg']){
    
         $success_msg_image = plugins_url( 'images/success.png' , __FILE__ );
         
        ?>
    
       <div class="s_msg" style="width:300px;border: 1px solid;margin: 10px 0px;
padding:15px 10px 15px 50px;background-repeat: no-repeat;background-position: 10px center;color: #4F8A10;background-color: #DFF2BF;background-image:url('<?php echo $success_msg_image;?>');"><b><?php echo $_REQUEST['success_msg'];?></b></div>

<?php
    }else if($_REQUEST['error_msg']){
     
     $error_msg_image = plugins_url( 'images/cancel.png' , __FILE__ );

?>
     <div class="e_msg" style="width:300px;border: 1px solid;margin: 10px 0px;padding:15px 10px 15px 50px;background-repeat: no-repeat;background-position: 10px center;color: #D8000C;background-color: #FFBABA;background-image: url('<?php echo $error_msg_image;?>');"><b><?php echo $_REQUEST['error_msg'];?></b></div>
       
  <?php      
    }
    
    else {
     
        //Do Nothing
    }
}
 
     //Create an instance of our package class...
    $testListTable = new Videotable();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();
    
    ?>

    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2 style="float:left;">My Videos</h2> <br><a style="float:left;" href="admin.php?page=prash_add_video" class="btn btn-primary">Add Video</a> 
        
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $testListTable->display() ?>
        </form>
        
    </div>

    <?php
 
        
}





function prash_action_list(){
 ?>
<br>
<h2>Available Actions</h2>

<br><a style="float:left;" href="admin.php?page=prash_select_new_action">Create new action</a> 
<?php 
    
}


function prash_optin_forms_do(){
 
    if($_REQUEST['success_msg']){
    
         $success_msg_image = plugins_url( 'images/success.png' , __FILE__ );
         
        ?>
     
       <div class="s_msg" style="width:300px;border: 1px solid;margin: 10px 0px;
padding:15px 10px 15px 50px;background-repeat: no-repeat;background-position: 10px center;color: #4F8A10;background-color: #DFF2BF;background-image:url('<?php echo $success_msg_image;?>');"><b><?php echo $_REQUEST['success_msg'];?></b></div>

<?php
    }else if($_REQUEST['error_msg']){
     
     $error_msg_image = plugins_url( 'images/cancel.png' , __FILE__ );

?>
     <div class="e_msg" style="width:300px;border: 1px solid;margin: 10px 0px;padding:15px 10px 15px 50px;background-repeat: no-repeat;background-position: 10px center;color: #D8000C;background-color: #FFBABA;background-image: url('<?php echo $error_msg_image;?>');"><b><?php echo $_REQUEST['error_msg'];?></b></div>
       
  <?php      
    }
    
    else {
     
        //Do Nothing
    }
    
    global $wpdb; 
    
    if($_POST['mode'] == 'edit'){
        
        

        if(isset($_POST['id'])){
        
        $optin_id = $_POST['id'];
        
        
            $save_check= $wpdb->update($wpdb->prefix.'prash_optins', array(
		
                    
                    'name'    => stripslashes_deep($_POST['name']),
                    'pre_form_name'  => stripslashes_deep($_POST['pre_forms']),    
                    'ar'    => stripslashes_deep($_POST['ar']),
                    'bg'    => stripslashes_deep($_POST['bg']),
                    'headcolor'    => stripslashes_deep($_POST['headcolor']),
                    'headtext'      => stripslashes_deep($_POST['headtext']),
                    'msgcolor'    => stripslashes_deep($_POST['msgcolor']),
                    'msgtext' => stripslashes_deep($_POST['msgtext']),
                    'butcolor'      => stripslashes_deep($_POST['butcolor']),
                    'buttext'   => stripslashes_deep($_POST['buttext']),
                    'buttextcolor'   => stripslashes_deep($_POST['buttextcolor']),
                    'emailvalidtxtcolor'   => stripslashes_deep($_POST['emailvalidtxtcolor']),
                'optinskiptxtcolor'   => stripslashes_deep($_POST['optinskiptxtcolor']),
                'skip_optin_text' => stripslashes_deep($_POST['skip_optin_form']),
                'skip_bt_text' => stripslashes_deep($_POST['skip_optin_form_text']),
                'optinbordercolor' => stripslashes_deep($_POST['o_border_color']),
                
                'gr_key'   => stripslashes_deep($_POST['grkey']),
                'gr_campaign'   => stripslashes_deep($_POST['getr_ca_id']),
                'gr_track_id'   => stripslashes_deep($_POST['getr_track_id']),
                'aw_key'   => stripslashes_deep($_POST['awkey']),
                'aw_secret'   => stripslashes_deep($_POST['awsecret']),
                'aw_accessid'   => stripslashes_deep($_POST['aweb_acc_id']),
                'aw_access_secret'   => stripslashes_deep($_POST['aweb_acc_sec']),
                'aw_ac_id'   => stripslashes_deep($_POST['aweb_acco_id']),
                'aw_list_id'   => stripslashes_deep($_POST['aweb_list_id']),

                'aw_track_id'   => stripslashes_deep($_POST['aweb_trac_id']),
                'mc_key'   => stripslashes_deep($_POST['mckey']),
                'mc_listid'   => stripslashes_deep($_POST['mailch_list_id']),
                'mc_trackid'   => stripslashes_deep($_POST['mailch_track_id']),
                'ic_key'   => stripslashes_deep($_POST['ickey']),
                'ic_list_id'   => stripslashes_deep($_POST['icon_list_id']),
                'ic_uname'   => stripslashes_deep($_POST['icon_api_user']),
                'ic_pass'   => stripslashes_deep($_POST['icon_api_pass']),

                'oa_key'   => stripslashes_deep($_POST['oakey']),
                'oa_sq'   => stripslashes_deep($_POST['offa_sq']),
                'oa_tag_name'   => stripslashes_deep($_POST['offa_tag_name']),
                'is_key'   => stripslashes_deep($_POST['iskey']),
                'is_fus_id'   => stripslashes_deep($_POST['insoft_fus_id']),
                'is_tag_id'   => stripslashes_deep($_POST['insoft_tag_id'])
                
            ), array('id' => $optin_id) , array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%s','%s',
                                               
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s',
                                                '%s') );
            
            if(0 < $save_check){
                
//                echo "Optin form edited successfully";
                
                wp_redirect("admin.php?page=prash_optin_forms&success_msg=" . urlencode("Optin form edited successfully")); exit();
                
                return true;
                
            }
            else if(0 === $save_check){
                
//                echo "Optin form edited successfully";
                
                wp_redirect("admin.php?page=prash_optin_forms&success_msg=" . urlencode("Optin form edited successfully")); exit();

                return true;

            }else if(false === $save_check){
                
//                echo "Error while editing optin form";
                
                wp_redirect("admin.php?page=prash_optin_forms&error_msg=" . urlencode("Error while editing optin form")); exit();

                return false;
            }
            else{
            
                //Do Nothing
            }
                
//                if($save_check){
//                echo "Optin form edited successfully";
//                    
//                    return true;
//                    
//                }else{                    
//
//                     echo "Error while editing optin form";
//                    
////                echo $wpdb->print_error();
//                    
//                    return false;
//                }
                
        
    }else{
    
        echo "Optin Form not found";
    
    }
        
        
        
        
    } elseif($_POST['mode'] == 'new'){
        
        
                    $save_check= $wpdb->insert($wpdb->prefix.'prash_optins', array(
		
                    'name'    => stripslashes_deep($_POST['name']),
                    'pre_form_name'  => stripslashes_deep($_POST['pre_forms']),    
                    'ar'    => stripslashes_deep($_POST['ar']),
                    'bg'    => stripslashes_deep($_POST['bg']),
                    'headcolor'    => stripslashes_deep($_POST['headcolor']),
                    'headtext'      => stripslashes_deep($_POST['headtext']),
                    'msgcolor'    => stripslashes_deep($_POST['msgcolor']),
                    'msgtext' => stripslashes_deep($_POST['msgtext']),
                    'butcolor'      => stripslashes_deep($_POST['butcolor']),
                    'buttext'   => stripslashes_deep($_POST['buttext']),
                    'buttextcolor'   => stripslashes_deep($_POST['buttextcolor']),
                    'emailvalidtxtcolor'   => stripslashes_deep($_POST['emailvalidtxtcolor']),
                    'optinskiptxtcolor'   => stripslashes_deep($_POST['optinskiptxtcolor']),
                    'skip_optin_text' => stripslashes_deep($_POST['skip_optin_form']),
                    'skip_bt_text' => stripslashes_deep($_POST['skip_optin_form_text']),
                    'optinbordercolor' => stripslashes_deep($_POST['o_border_color']),        

                        'gr_key'   => stripslashes_deep($_POST['grkey']),
                        'gr_campaign'   => stripslashes_deep($_POST['getr_ca_id']),
                        'gr_track_id'   => stripslashes_deep($_POST['getr_track_id']),
                        'aw_key'   => stripslashes_deep($_POST['awkey']),
                        'aw_secret'   => stripslashes_deep($_POST['awsecret']),
                        'aw_accessid'   => stripslashes_deep($_POST['aweb_acc_id']),
                        'aw_access_secret'   => stripslashes_deep($_POST['aweb_acc_sec']),
                        'aw_ac_id'   => stripslashes_deep($_POST['aweb_acco_id']),
                        'aw_list_id'   => stripslashes_deep($_POST['aweb_list_id']),
                        
                        'aw_track_id'   => stripslashes_deep($_POST['aweb_trac_id']),
                        'mc_key'   => stripslashes_deep($_POST['mckey']),
                        'mc_listid'   => stripslashes_deep($_POST['mailch_list_id']),
                        'mc_trackid'   => stripslashes_deep($_POST['mailch_track_id']),
                        'ic_key'   => stripslashes_deep($_POST['ickey']),
                        'ic_list_id'   => stripslashes_deep($_POST['icon_list_id']),
                        'ic_uname'   => stripslashes_deep($_POST['icon_api_user']),
                        'ic_pass'   => stripslashes_deep($_POST['icon_api_pass']),
                        
                        'oa_key'   => stripslashes_deep($_POST['oakey']),
                        'oa_sq'   => stripslashes_deep($_POST['offa_sq']),
                        'oa_tag_name'   => stripslashes_deep($_POST['offa_tag_name']),
                        'is_key'   => stripslashes_deep($_POST['iskey']),
                        'is_fus_id'   => stripslashes_deep($_POST['insoft_fus_id']),
                        'is_tag_id'   => stripslashes_deep($_POST['insoft_tag_id'])
                        
                    ),
                            array(
                            '%s',
                            '%s',    
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',    
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%s',    
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s'
                                
                            ));
        
          if(0 < $save_check){

              
              wp_redirect("admin.php?page=prash_optin_forms&success_msg=" . urlencode("Optin Form Created Successfully")); exit();

                return true;
                
            }
            else if(0 === $save_check){
                
                
                wp_redirect("admin.php?page=prash_optin_forms&success_msg=" . urlencode("Optin Form Created Successfully")); exit();

                return true;

            }else if(false === $save_check){
                    
                wp_redirect("admin.php?page=prash_optin_forms&error_msg=" . urlencode("Error creating optin form")); exit();

                return false;
            }
            else{
            
                //Do Nothing
            }
                         
    }

    
     //Create an instance of our package class...
    $optinListTable = new Optinformtable();
    //Fetch, prepare, sort, and filter our data...
    $optinListTable->prepare_items();
    
    ?>

    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2 style="float:left;">My Optin Forms</h2> <br><a style="float:left;" href="admin.php?page=prash_add_optin" class="btn btn-primary">Create</a> 
        
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $optinListTable->display() ?>
        </form>
        
    </div>
    <?php
 
        
}

function prash_delete_optin_do(){
     
     global $wpdb;

     $form_id = $_REQUEST['id'];
    
     $delete_optin_form = $wpdb->delete( $wpdb->prefix ."prash_optins", array('id' => $form_id ));
    
     $delete_action_optin_form = $wpdb->delete( $wpdb->prefix ."prash_actions", array('form_id' => $form_id ));

    if($delete_optin_form){
        
        wp_redirect("admin.php?page=prash_optin_forms&success_msg=" . urlencode("Optin Form deleted successfully.")); exit();
        
//        echo "Optin Form deleted successfully.";
        
        return true;
    }else{
        
        wp_redirect("admin.php?page=prash_optin_forms&error_msg=" . urlencode("Error while deleting Optin Form")); exit();
        
//        echo "Error while deleting Optin Form";
        
        return false;
    } 
   
    
}

function prash_delete_optin_form(){
    
     global $wpdb;

     $form_id = $_REQUEST['id'];
  
    ?>

    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<h2>Are you sure want to delete Optin form?</h2>

<form action="admin.php?page=prash_delete_optin_do&noheader=true" method="post" name="del_optin" id="del_optin">
    
    <input type="hidden" name="id" value="<?php echo $form_id ?>" class="o_input">
    <input type="submit" value="Yes" name="submit" onclick="submitDetailsOptin();" class="btn btn-danger">
    <input type="button" value="No" name="button" onclick="history.go(-1);" class="btn btn-success"><br>
    
</form> 

  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
 <script language="javascript" type="text/javascript">
    function submitDetailsOptin() {
       jQuery("form").submit();
    }
</script>

<?php
    
}

function prash_edit_optin_form(){ 
 
    global $wpdb;
    
    $optin_table = $wpdb->prefix . 'prash_optins';
    
    $optin_id = $_REQUEST['id'];
        
    $optin_edit = $wpdb->get_row("SELECT * FROM " . $optin_table . " where id = " .$optin_id);
    
?>

<style>

html{

background:#FFFFFF;
 height: 480px;
}

</style>

<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<h2>Edit Optin Form</h2>
<form action="admin.php?page=prash_optin_forms&noheader=true" name="editoptindetails" id="editoptindetails" method="post" class="form-horizontal">

<table width="100%">
<tr><td>    
<table style="text-align: left; width: 60%;" border="0"
cellpadding="2" cellspacing="2">
<tbody>
    
    
    <tr>
<td style="vertical-align: top;">Form name<br>
</td>
<td style="vertical-align: top;">
    <input id="fname" name="name" type="text" placeholder="placeholder" class="form-control" style="width:80%;" value="<?php echo $optin_edit->name ?>">
    <br>
</td>        
</tr>
        
<!--PREBUILD FORM LIST-->

 <tr>
<td style="vertical-align: top;">Prebuild Forms<br>
</td>
<td style="vertical-align: top;">
    <select id="pre_forms" name="pre_forms" class="form-control" style="width:80%;">            
<option value="pre_form1" <?php if($optin_edit->pre_form_name == 'pre_form1') echo ' selected '?> >Form 1</option>
<option value="pre_form2" <?php if($optin_edit->pre_form_name == 'pre_form2') echo ' selected '?> >Form 2</option>
<option value="pre_form3" <?php if($optin_edit->pre_form_name == 'pre_form3') echo ' selected '?> >Form 3</option>
<option value="pre_form4" <?php if($optin_edit->pre_form_name == 'pre_form4') echo ' selected '?> >Form 4</option>
<option value="pre_form5" <?php if($optin_edit->pre_form_name == 'pre_form5') echo ' selected '?> >Form 5</option>
</select>
    <br>  
</td>
</tr>    
        
<!--PREBUILD FORM LIST END-->
    

    <!-- AUTORESPONDER SELECT JQUERY -->
     
<!-- AUTORESPONDER SELECT JQUERY END -->
    <tr>
<td style="vertical-align: top;">Autoresponder<br>
</td>
<td style="vertical-align: top;">
    <select id="ar" name="ar" class="form-control" style="width:80%;">
<option value="sa" <?php if($optin_edit->ar == 'sa') echo ' selected '?> >Select Autoresponder</option>    
<option id="gr" value="gr" <?php if($optin_edit->ar == 'gr') echo ' selected '?> >GetResponse</option>
<option id="aw" value="aw" <?php if($optin_edit->ar == 'aw') echo ' selected '?> >Aweber</option>
<option id="mc" value="mc" <?php if($optin_edit->ar == 'mc') echo ' selected '?> >MailChimp</option>
<option id="ic" value="ic" <?php if($optin_edit->ar == 'ic') echo ' selected '?> >iContact</option>
<option id="oa" value="oa" <?php if($optin_edit->ar == 'oa') echo ' selected '?> >OfficeAutopilot/Ontraport</option>
<!-- <option value="tw" <?php if($optin_edit->ar == 'tw') echo ' selected '?> >TrafficWave.net</option> -->
<option id="is" value="is" <?php if($optin_edit->ar == 'is') echo ' selected '?> >Infusionsoft</option>
</select>
    <br>
</td>
</tr>

<!-- AUTORESPONDERS REQUIRED FIELDS FOR SUBMISSION -->
    
    <!--PREBUILD FORM LIST JQUERY-->

<!--PREBUILD FORM LIST JQUERY END-->   

<tr id="aweber-fields-aweb_list_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">AWeber List ID<br>
</td>
    <td style="vertical-align: top;"><input name="aweb_list_id" id="aweb_list_id" value="<?php echo $optin_edit->aw_list_id?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>
    
<tr id="aweber-fields-aweb_trac_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">AWeber Tracking ID<br>
</td>
    <td style="vertical-align: top;"><input name="aweb_trac_id" id="aweb_trac_id" value="<?php echo $optin_edit->aw_track_id?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr> 

<tr id="getresp-fields-ca_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">GetResponse Campaign Name<br>
</td>
    <td style="vertical-align: top;"><input name="getr_ca_id" id="getr_ca_id" value="<?php echo $optin_edit->gr_campaign?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>

<!-- <tr id="getresp-fields-track_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">GetResponse Track ID<br>
</td>
    <td style="vertical-align: top;"><input name="getr_track_id" id="getr_track_id" value="<?php echo $optin_edit->gr_track_id?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr> -->

<tr id="mailch-fields-mailch_list_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">MailChimp List ID<br>
</td>
    <td style="vertical-align: top;"><input name="mailch_list_id" id="mailch_list_id" value="<?php echo $optin_edit->mc_listid?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>

<!-- <tr id="mailch-fields-mailch_track_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">MailChimp Track ID<br>
</td>
    <td style="vertical-align: top;"><input name="mailch_track_id" id="mailch_track_id" value="<?php echo $optin_edit->mc_trackid?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>  -->

<tr id="icont-fields-icon_list_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">IContact List ID<br>
</td>
    <td style="vertical-align: top;"><input name="icon_list_id" id="icon_list_id" value="<?php echo $optin_edit->ic_list_id?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr> 

<tr id="insoft-fields-insoft_fus_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">Infusionsoft Follow-Up<br>Sequence ID<br>
</td>
    <td style="vertical-align: top;"><input name="insoft_fus_id" id="insoft_fus_id" value="<?php echo $optin_edit->is_fus_id?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>

<tr id="insoft-fields-insoft_tag_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">Infusionsoft Tag ID<br>
</td>
    <td style="vertical-align: top;"><input name="insoft_tag_id" id="insoft_tag_id" value="<?php echo $optin_edit->is_tag_id?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>

<tr id="offap-fields-offa_sq" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">Ontraport Sequence ID<br>(*comma seperated list)<br>
</td>
    <td style="vertical-align: top;"><input name="offa_sq" id="offa_sq" value="<?php echo $optin_edit->oa_sq?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>

<tr id="offap-fields-offa_tag_name" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">Ontraport Tag Name<br>(*comma seperated list)<br>
</td>
    <td style="vertical-align: top;"><input name="offa_tag_name" id="offa_tag_name" value="<?php echo $optin_edit->oa_tag_name?>" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>  
  
<!-- AUTORESPONDERS REQUIRED FIELDS FOR SUBMISSION END -->
        
<tr>
<td style="vertical-align: top;">Form background color<br>
</td>
<td style="vertical-align: top;"><input name="bg" id="bg" value="<?php echo $optin_edit->bg ?>"><br><br>
</td>
</tr>
<tr>
<td style="vertical-align: top;">Heading color<br>
</td>
<td style="vertical-align: top;"><input name="headcolor" id="headcolor" value="<?php echo $optin_edit->headcolor ?>" ><br><br>
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Heading text<br>
</td>
<td style="vertical-align: top;"><input name="headtext" id="headtext" value="<?php echo $optin_edit->headtext ?>" type="text" class="form-control" style="width:80%;" maxlength="23">
<div id="max_ch_headtext" style="color:black;"></div><br>    
</td>
</tr>
<tr>
<td style="vertical-align: top;">Message color<br>
</td>
<td style="vertical-align: top;"><input name="msgcolor" id="msgcolor" value="<?php echo $optin_edit->msgcolor ?>"><br><br>
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Message&nbsp; text<br>
</td>
<td style="vertical-align: top;"><input name="msgtext" id="msgtext" value="<?php echo $optin_edit->msgtext ?>" type="text" class="form-control" style="width:80%;" maxlength="48">
<div id="max_ch_msgtext" style="color:black;"></div><br>
</td>
</tr>
<tr>
<td style="vertical-align: top;">Button color<br>
</td>
<td style="vertical-align: top;"><input name="butcolor" id="buttoncolor" value="<?php echo $optin_edit->butcolor ?>"><br><br>
</td>
</tr>
    <tr>
<td style="vertical-align: top;">Button text color<br>
</td>
<td style="vertical-align: top;"><input name="buttextcolor" id="buttontextcolor" value="<?php echo $optin_edit->buttextcolor ?>"><br><br>
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Button text<br>
</td>
<td style="vertical-align: top;"><input name="buttext" id="buttext" value="<?php echo $optin_edit->buttext ?>" type="text" class="form-control" style="width:80%;" maxlength="10">
<div id="max_ch_buttext" style="color:black;"></div><br>
</td>
</tr>

<tr>
<td style="vertical-align: top;">Email validation text color<br>
</td>
<td style="vertical-align: top;"><input name="emailvalidtxtcolor" id="emailvalidtextcolor" value="<?php echo $optin_edit->emailvalidtxtcolor ?>"><br><br>
</td>
</tr>
    
<tr id="optin_border_color">
<td style="vertical-align: top;">Optin Border color<br>    
</td>
<td style="vertical-align: top;"><input name="o_border_color" id="o_border_color" value="<?php echo $optin_edit->optinbordercolor ?>" ><br><br>
</td>
</tr>          
    
<tr>
<td style="vertical-align: top;">Skip Optin form?<br><br>
</td>
<td style="vertical-align: top;">
<select name="skip_optin_form" id="skip_optin_form" class="form-control" style="width:70%;"> 
<option value="1" <?php if($optin_edit->skip_optin_text == 1) echo " selected "; ?> >Yes</option>
<option value="0" <?php if($optin_edit->skip_optin_text == 0) echo " selected "; ?> >No</option>        
</select>
<br>
</td>
</tr>

<tr id="optin_skip_text">
<td style="vertical-align: top;">Skip Optin form text<br>
</td>
<td style="vertical-align: top;"><input name="skip_optin_form_text" id="skip_optin_form_text" value="<?php echo $optin_edit->skip_bt_text ?>" type="text" class="form-control" style="width:80%;" maxlength="27">
<div id="max_ch_skipbttext" style="color:black;"></div><br>
</td>
</tr>    
    
<tr id="optin_skip_text_color">
<td style="vertical-align: top;">Skip optin text color<br>
</td>
<td style="vertical-align: top;"><input name="optinskiptxtcolor" id="skipoptintextcolor" value="<?php echo $optin_edit->optinskiptxtcolor ?>"><br>
</td>
</tr>    
    
</tbody>    
</table>
    </td>
    <td>
       
       <!--custom added - for refresh button form -->          
        <center><button type="button" id="refresh_bt_edit" class="btn btn-success">Refresh Preview</button></center>
        <br/><br/>
         <!--custom added - for refresh button form end-->
        
<!-- FORM PREVIEW-->
<div id="y-form" class="y-form" required style="background:#FFFFFF;border:1;width:93%;height:100%;top: 0;bottom: 0;left: 0;right: 0;margin: auto;text-align:center;padding:5px; -webkit-border-radius: 20px;-moz-border-radius: 20px;border-radius: 20px;border:4px solid #2F4F4F;">
    
   <div class="form-text-title" id="form-text-title" style="line-height:35px;color:#FF0000;font-size: 18px;text-align: center;margin-top: 1%;word-wrap:break-word;">Header</div>
     
         <div class="form-text-content" id="form-text-content" style="color:#0000FF;text-align: center;word-wrap:break-word;font-size:15px;">Message</div>
    
    <div id="error-em" class="error-em" style="text-align: center;font-size: 14px;color:#D00;font-style: italic;">Please enter a valid email id</div>
           
            <form name="vform" class="yt-form" action="http://www.google.co.in" method="get" target="_blank" style="margin-top:1%">
         <div id="em_submit" style="width:90%;padding:5px;display:inline-block;">
        <input id="em" name="email" type="email" placeholder="Email" style="width:70%;margin-right:1%;display:inline-block;">
         <a href="#" id="form_submit_bt" class="form_submit_bt" style="display:inline-block;font-size:10px;font-family:Arial;font-weight:bold;-moz-border-radius:8px;-webkit-border-radius:8px;border-radius:8px;padding-left:2%;padding-right:2%;padding-top:5px;padding-bottom:5px;text-decoration:none;background-color:#3d94f6;color:#ffffff;text-align:center;">Button text</a>
             </div>
                <br/>
                <a href="#" id="close-optin" class="close-optin" style="padding: 2px;text-align: center;position: relative;text-align:center;color:#FF0000;font-weight:bold;text-decoration: none;">Skip this step >></a>
</form>   
</div>  

        
        
<!-- FORM PREVIEW END-->    
    </td>
    </tr>
    </table>
    <p><b>** - Please select any autoresponder to save optin form</b></p>
<br>
<input id="optin_form_sb" name="Submit" value="Submit" type="submit" class="btn btn-primary"><br>
    
    <input type="hidden" name="mode" value="edit">
    <input type="hidden" name="id" value="<?php echo $optin_edit->id ?>">
<br>
<br>
</form>

<!--FORM VALIDATIONS-->
    
    
<!--FORM VALIDATIONS END-->


<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/jquery.validity.css' , __FILE__ ); ?>" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> 
<script src="<?php echo plugins_url( 'js/jquery.validity.min.js' , __FILE__ ); ?>"></script>
<script type="text/javascript">
    
    jQuery(document).ready(function($){
        
          var bg_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formbgcolor = jQuery(this).wpColorPicker('color');    
        
    jQuery('#y-form').css('background',formbgcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_title_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formtitlecolor = jQuery(this).wpColorPicker('color');    
        
    jQuery('#form-text-title').css('color',formtitlecolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_content_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formcontentcolor = jQuery(this).wpColorPicker('color');    
        
   jQuery('#form-text-content').css('color',formcontentcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_bt_bg_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formsubmitbgcolor = jQuery(this).wpColorPicker('color');    
        
    jQuery('#form_submit_bt').css('background-color',formsubmitbgcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_bt_text_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formsubmittextcolor = jQuery(this).wpColorPicker('color');    
        
   jQuery('#form_submit_bt').css('color',formsubmittextcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_error_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formerrorcolor = jQuery(this).wpColorPicker('color');    
        
   jQuery('#error-em').css('color',formerrorcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_border_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formbordercolor = jQuery(this).wpColorPicker('color');    
        
    jQuery('#y-form').css('border-color',formbordercolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_close_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formclosecolor = jQuery(this).wpColorPicker('color');    
        
   jQuery('#close-optin').css('color',formclosecolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
        
        
    jQuery('#bg').wpColorPicker(bg_options);
    jQuery('#headcolor').wpColorPicker(form_title_options);
    jQuery('#msgcolor').wpColorPicker(form_content_options);
    jQuery('#buttoncolor').wpColorPicker(form_bt_bg_options);    
    jQuery('#buttontextcolor').wpColorPicker(form_bt_text_options); 
    jQuery('#emailvalidtextcolor').wpColorPicker(form_error_options);
    jQuery('#skipoptintextcolor').wpColorPicker(form_close_options);
    jQuery('#o_border_color').wpColorPicker(form_border_options);     
});
    
      jQuery(document).ready(function(){

         if ('<?php echo $optin_edit->ar ?>' == 'sa'){
            
            jQuery('#getresp-fields-ca_id').hide();
            jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        } 
                
                
             else if ('<?php echo $optin_edit->ar ?>' == 'gr'){
            
            jQuery('#getresp-fields-ca_id').show();
             jQuery('#getresp-fields-track_id').show();
            jQuery('#getresp-fields-getr_api_key').show();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        } 
                else if ('<?php echo $optin_edit->ar ?>' == 'aw'){
            
           jQuery('#getresp-fields-ca_id').hide();
            jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').show();
            jQuery('#aweber-fields-aweb_api_sec').show();
            jQuery('#aweber-fields-aweb_acc_id').show();
            jQuery('#aweber-fields-aweb_acc_sec').show();
            jQuery('#aweber-fields-aweb_acco_id').show();
            jQuery('#aweber-fields-aweb_list_id').show();
            jQuery('#aweber-fields-aweb_trac_id').show();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }
                else if ('<?php echo $optin_edit->ar ?>' == 'mc'){
           jQuery('#getresp-fields-ca_id').hide();
            jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').show();
            jQuery('#mailch-fields-mailch_list_id').show();
            jQuery('#mailch-fields-mailch_track_id').show();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }        
                else if ('<?php echo $optin_edit->ar ?>' == 'ic'){
          jQuery('#getresp-fields-ca_id').hide();
           jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').show();
            jQuery('#icont-fields-icon_api_sec').show();
            jQuery('#icont-fields-icon_api_user').show();
            jQuery('#icont-fields-icon_api_pass').show();
            jQuery('#icont-fields-icon_list_id').show();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }
                else if ('<?php echo $optin_edit->ar ?>' == 'oa'){
           jQuery('#getresp-fields-ca_id').hide();
            jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').show();
            jQuery('#offap-fields-offa_sq').show();
            jQuery('#offap-fields-offa_tag_name').show();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }
                else if ('<?php echo $optin_edit->ar ?>' == 'tw'){
           jQuery('#getresp-fields-ca_id').hide();
            jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').show();
            jQuery('#trafw-fields-trafw_ca_id').show();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }
                else if ('<?php echo $optin_edit->ar ?>' == 'is'){
         jQuery('#getresp-fields-ca_id').hide();
          jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').show();
            jQuery('#insoft-fields-insoft_fus_id').show();
            jQuery('#insoft-fields-insoft_tag_id').show();
        }        
        else
        {
           //Do Nothing
        } 

});  
    
      jQuery(document).ready(function(){

            

            jQuery('#ar').change(function () {

                if (jQuery('#ar option:selected').val() == 'sa'){
                    jQuery('#getresp-fields-ca_id').hide();
                     jQuery('#getresp-fields-track_id').hide();
                    jQuery('#getresp-fields-getr_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_sec').hide();
                    jQuery('#aweber-fields-aweb_acc_id').hide();
                    jQuery('#aweber-fields-aweb_acc_sec').hide();
                    jQuery('#aweber-fields-aweb_acco_id').hide();
                    jQuery('#aweber-fields-aweb_list_id').hide();
                    jQuery('#aweber-fields-aweb_trac_id').hide();
                    jQuery('#mailch-fields-mailch_api_key').hide();
                    jQuery('#mailch-fields-mailch_list_id').hide();
                    jQuery('#mailch-fields-mailch_track_id').hide();
                    jQuery('#icont-fields-icon_api_key').hide();
                    jQuery('#icont-fields-icon_api_sec').hide();
                    jQuery('#icont-fields-icon_api_user').hide();
                    jQuery('#icont-fields-icon_api_pass').hide();
                    jQuery('#icont-fields-icon_list_id').hide();
                    jQuery('#offap-fields-offa_api_key').hide();
                    jQuery('#offap-fields-offa_sq').hide();
                    jQuery('#offap-fields-offa_tag_name').hide();
                    jQuery('#trafw-fields-trafw_api_key').hide();
                    jQuery('#trafw-fields-trafw_ca_id').hide();
                    jQuery('#insoft-fields-insoft_api_key').hide();
                    jQuery('#insoft-fields-insoft_fus_id').hide();
                    jQuery('#insoft-fields-insoft_tag_id').hide(); 

                   
                } 
              
                else if (jQuery('#ar option:selected').val() == 'gr'){
                    jQuery('#getresp-fields-ca_id').show();
                     jQuery('#getresp-fields-track_id').show();
                    jQuery('#getresp-fields-getr_api_key').show();
                    jQuery('#aweber-fields-aweb_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_sec').hide();
                    jQuery('#aweber-fields-aweb_acc_id').hide();
                    jQuery('#aweber-fields-aweb_acc_sec').hide();
                    jQuery('#aweber-fields-aweb_acco_id').hide();
                    jQuery('#aweber-fields-aweb_list_id').hide();
                    jQuery('#aweber-fields-aweb_trac_id').hide();
                    jQuery('#mailch-fields-mailch_api_key').hide();
                    jQuery('#mailch-fields-mailch_list_id').hide();
                    jQuery('#mailch-fields-mailch_track_id').hide();
                    jQuery('#icont-fields-icon_api_key').hide();
                    jQuery('#icont-fields-icon_api_sec').hide();
                    jQuery('#icont-fields-icon_api_user').hide();
                    jQuery('#icont-fields-icon_api_pass').hide();
                    jQuery('#icont-fields-icon_list_id').hide();
                    jQuery('#offap-fields-offa_api_key').hide();
                    jQuery('#offap-fields-offa_sq').hide();
                    jQuery('#offap-fields-offa_tag_name').hide();
                    jQuery('#trafw-fields-trafw_api_key').hide();
                    jQuery('#trafw-fields-trafw_ca_id').hide();
                    jQuery('#insoft-fields-insoft_api_key').hide();
                    jQuery('#insoft-fields-insoft_fus_id').hide();
                    jQuery('#insoft-fields-insoft_tag_id').hide();

                   
                } 
                else if (jQuery('#ar option:selected').val() == 'aw'){
                    jQuery('#getresp-fields-ca_id').hide();
                     jQuery('#getresp-fields-track_id').hide();
                    jQuery('#getresp-fields-getr_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_key').show();
                    jQuery('#aweber-fields-aweb_api_sec').show();
                    jQuery('#aweber-fields-aweb_acc_id').show();
                    jQuery('#aweber-fields-aweb_acc_sec').show();
                    jQuery('#aweber-fields-aweb_acco_id').show();
                    jQuery('#aweber-fields-aweb_list_id').show();
                    jQuery('#aweber-fields-aweb_trac_id').show();
                    jQuery('#mailch-fields-mailch_api_key').hide();
                    jQuery('#mailch-fields-mailch_list_id').hide();
                    jQuery('#mailch-fields-mailch_track_id').hide();
                    jQuery('#icont-fields-icon_api_key').hide();
                    jQuery('#icont-fields-icon_api_sec').hide();
                    jQuery('#icont-fields-icon_api_user').hide();
                    jQuery('#icont-fields-icon_api_pass').hide();
                    jQuery('#icont-fields-icon_list_id').hide();
                    jQuery('#offap-fields-offa_api_key').hide();
                    jQuery('#offap-fields-offa_sq').hide();
                    jQuery('#offap-fields-offa_tag_name').hide();
                    jQuery('#trafw-fields-trafw_api_key').hide();
                    jQuery('#trafw-fields-trafw_ca_id').hide();
                    jQuery('#insoft-fields-insoft_api_key').hide();
                    jQuery('#insoft-fields-insoft_fus_id').hide();
                    jQuery('#insoft-fields-insoft_tag_id').hide();

                    
                }
                else if (jQuery('#ar option:selected').val() == 'mc'){
                    jQuery('#getresp-fields-ca_id').hide();
                     jQuery('#getresp-fields-track_id').hide();
                    jQuery('#getresp-fields-getr_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_sec').hide();
                    jQuery('#aweber-fields-aweb_acc_id').hide();
                    jQuery('#aweber-fields-aweb_acc_sec').hide();
                    jQuery('#aweber-fields-aweb_acco_id').hide();
                    jQuery('#aweber-fields-aweb_list_id').hide();
                    jQuery('#aweber-fields-aweb_trac_id').hide();
                    jQuery('#mailch-fields-mailch_api_key').show();
                    jQuery('#mailch-fields-mailch_list_id').show();
                    jQuery('#mailch-fields-mailch_track_id').show();
                    jQuery('#icont-fields-icon_api_key').hide();
                    jQuery('#icont-fields-icon_api_sec').hide();
                    jQuery('#icont-fields-icon_api_user').hide();
                    jQuery('#icont-fields-icon_api_pass').hide();
                    jQuery('#icont-fields-icon_list_id').hide();
                    jQuery('#offap-fields-offa_api_key').hide();
                    jQuery('#offap-fields-offa_sq').hide();
                    jQuery('#offap-fields-offa_tag_name').hide();
                    jQuery('#trafw-fields-trafw_api_key').hide();
                    jQuery('#trafw-fields-trafw_ca_id').hide();
                    jQuery('#insoft-fields-insoft_api_key').hide();
                    jQuery('#insoft-fields-insoft_fus_id').hide();
                    jQuery('#insoft-fields-insoft_tag_id').hide();
                }        
                else if (jQuery('#ar option:selected').val() == 'ic'){
                    jQuery('#getresp-fields-ca_id').hide();
                     jQuery('#getresp-fields-track_id').hide();
                    jQuery('#getresp-fields-getr_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_sec').hide();
                    jQuery('#aweber-fields-aweb_acc_id').hide();
                    jQuery('#aweber-fields-aweb_acc_sec').hide();
                    jQuery('#aweber-fields-aweb_acco_id').hide();
                    jQuery('#aweber-fields-aweb_list_id').hide();
                    jQuery('#aweber-fields-aweb_trac_id').hide();
                    jQuery('#mailch-fields-mailch_api_key').hide();
                    jQuery('#mailch-fields-mailch_list_id').hide();
                    jQuery('#mailch-fields-mailch_track_id').hide();
                    jQuery('#icont-fields-icon_api_key').show();
                    jQuery('#icont-fields-icon_api_sec').show();
                    jQuery('#icont-fields-icon_api_user').show();
                    jQuery('#icont-fields-icon_api_pass').show();
                    jQuery('#icont-fields-icon_list_id').show();
                    jQuery('#offap-fields-offa_api_key').hide();
                    jQuery('#offap-fields-offa_sq').hide();
                    jQuery('#offap-fields-offa_tag_name').hide();
                    jQuery('#trafw-fields-trafw_api_key').hide();
                    jQuery('#trafw-fields-trafw_ca_id').hide();
                    jQuery('#insoft-fields-insoft_api_key').hide();
                    jQuery('#insoft-fields-insoft_fus_id').hide();
                    jQuery('#insoft-fields-insoft_tag_id').hide();
                }
                else if (jQuery('#ar option:selected').val() == 'oa'){
                    jQuery('#getresp-fields-ca_id').hide();
                     jQuery('#getresp-fields-track_id').hide();
                    jQuery('#getresp-fields-getr_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_sec').hide();
                    jQuery('#aweber-fields-aweb_acc_id').hide();
                    jQuery('#aweber-fields-aweb_acc_sec').hide();
                    jQuery('#aweber-fields-aweb_acco_id').hide();
                    jQuery('#aweber-fields-aweb_list_id').hide();
                    jQuery('#aweber-fields-aweb_trac_id').hide();
                    jQuery('#mailch-fields-mailch_api_key').hide();
                    jQuery('#mailch-fields-mailch_list_id').hide();
                    jQuery('#mailch-fields-mailch_track_id').hide();
                    jQuery('#icont-fields-icon_api_key').hide();
                    jQuery('#icont-fields-icon_api_sec').hide();
                    jQuery('#icont-fields-icon_api_user').hide();
                    jQuery('#icont-fields-icon_api_pass').hide();
                    jQuery('#icont-fields-icon_list_id').hide();
                    jQuery('#offap-fields-offa_api_key').show();
                    jQuery('#offap-fields-offa_sq').show();
                    jQuery('#offap-fields-offa_tag_name').show();
                    jQuery('#trafw-fields-trafw_api_key').hide();
                    jQuery('#trafw-fields-trafw_ca_id').hide();
                    jQuery('#insoft-fields-insoft_api_key').hide();
                    jQuery('#insoft-fields-insoft_fus_id').hide();
                    jQuery('#insoft-fields-insoft_tag_id').hide();
                }
                else if (jQuery('#ar option:selected').val() == 'tw'){
                    jQuery('#getresp-fields-ca_id').hide();
                     jQuery('#getresp-fields-track_id').hide();
                    jQuery('#getresp-fields-getr_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_sec').hide();
                    jQuery('#aweber-fields-aweb_acc_id').hide();
                    jQuery('#aweber-fields-aweb_acc_sec').hide();
                    jQuery('#aweber-fields-aweb_acco_id').hide();
                    jQuery('#aweber-fields-aweb_list_id').hide();
                    jQuery('#aweber-fields-aweb_trac_id').hide();
                    jQuery('#mailch-fields-mailch_api_key').hide();
                    jQuery('#mailch-fields-mailch_list_id').hide();
                    jQuery('#mailch-fields-mailch_track_id').hide();
                    jQuery('#icont-fields-icon_api_key').hide();
                    jQuery('#icont-fields-icon_api_sec').hide();
                    jQuery('#icont-fields-icon_api_user').hide();
                    jQuery('#icont-fields-icon_api_pass').hide();
                    jQuery('#icont-fields-icon_list_id').hide();
                    jQuery('#offap-fields-offa_api_key').hide();
                    jQuery('#offap-fields-offa_sq').hide();
                    jQuery('#offap-fields-offa_tag_name').hide();
                    jQuery('#trafw-fields-trafw_api_key').show();
                    jQuery('#trafw-fields-trafw_ca_id').show();
                    jQuery('#insoft-fields-insoft_api_key').hide();
                    jQuery('#insoft-fields-insoft_fus_id').hide();
                    jQuery('#insoft-fields-insoft_tag_id').hide();
                }
                else if (jQuery('#ar option:selected').val() == 'is'){
                    jQuery('#getresp-fields-ca_id').hide();
                     jQuery('#getresp-fields-track_id').hide();
                    jQuery('#getresp-fields-getr_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_key').hide();
                    jQuery('#aweber-fields-aweb_api_sec').hide();
                    jQuery('#aweber-fields-aweb_acc_id').hide();
                    jQuery('#aweber-fields-aweb_acc_sec').hide();
                    jQuery('#aweber-fields-aweb_acco_id').hide();
                    jQuery('#aweber-fields-aweb_list_id').hide();
                    jQuery('#aweber-fields-aweb_trac_id').hide();
                    jQuery('#mailch-fields-mailch_api_key').hide();
                    jQuery('#mailch-fields-mailch_list_id').hide();
                    jQuery('#mailch-fields-mailch_track_id').hide();
                    jQuery('#icont-fields-icon_api_key').hide();
                    jQuery('#icont-fields-icon_api_sec').hide();
                    jQuery('#icont-fields-icon_api_user').hide();
                    jQuery('#icont-fields-icon_api_pass').hide();
                    jQuery('#icont-fields-icon_list_id').hide();
                    jQuery('#offap-fields-offa_api_key').hide();
                    jQuery('#offap-fields-offa_sq').hide();
                    jQuery('#offap-fields-offa_tag_name').hide();
                    jQuery('#trafw-fields-trafw_api_key').hide();
                    jQuery('#trafw-fields-trafw_ca_id').hide();
                    jQuery('#insoft-fields-insoft_api_key').show();
                    jQuery('#insoft-fields-insoft_fus_id').show();
                    jQuery('#insoft-fields-insoft_tag_id').show();
                }        
                else
                {
                    //Do Nothing
                } 

            });  

        });
    
       jQuery(document).ready(function($){
      
         jQuery('#pre_forms').change(function () {
             
        if (jQuery('#pre_forms option:selected').val() == 'pre_form1'){
             
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#3B6C7C');
         
             
       jQuery('#bg').wpColorPicker('color', '#3B6C7C');
                    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#FFFFFF');
    
       jQuery('#headcolor').wpColorPicker('color', '#FFFFFF');
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#FFFFFF');
    
       jQuery('#msgcolor').wpColorPicker('color', '#FFFFFF');   
            
       //FORM MESSAGE TEXT
         
            jQuery('#form-text-content').text('sample text');
    
            jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#DA452D');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#DA452D');      
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#FFFFFF');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#FFFFFF');    
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');     
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#FFFFFF');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#FFFFFF');  

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');   
            
        }
        else if(jQuery('#pre_forms option:selected').val() == 'pre_form2'){
            
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#3E4346');
            
       jQuery('#bg').wpColorPicker('color', '#3E4346');             
    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#72A75E');
    
       jQuery('#headcolor').wpColorPicker('color', '#72A75E');             
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#FFFFFF');
    
       jQuery('#msgcolor').wpColorPicker('color', '#FFFFFF');             
            
       //FORM MESSAGE TEXT
         
       jQuery('#form-text-content').text('sample text');
    
       jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#3CB371');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#3CB371');             
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#F5FFFA');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#F5FFFA');             
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');             
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#87CEEB');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#87CEEB'); 

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');            
            
        }else if(jQuery('#pre_forms option:selected').val() == 'pre_form3'){
            
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#F8F8F8');
            
       jQuery('#bg').wpColorPicker('color', '#F8F8F8');             
    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#A2B8CB');
    
       jQuery('#headcolor').wpColorPicker('color', '#A2B8CB');             
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#A2B8CB');
    
       jQuery('#msgcolor').wpColorPicker('color', '#A2B8CB');                 
            
       //FORM MESSAGE TEXT
         
            jQuery('#form-text-content').text('sample text');
    
            jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#FED373');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#FED373');                 
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#C59D5A');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#C59D5A');                 
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');                 
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#A2B8CB');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#A2B8CB');

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');                 
            
        }else if(jQuery('#pre_forms option:selected').val() == 'pre_form4'){
            
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#FFFFFF');
            
       jQuery('#bg').wpColorPicker('color', '#FFFFFF');                 
    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#191919');
    
       jQuery('#headcolor').wpColorPicker('color', '#191919');                 
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#191919');
    
       jQuery('#msgcolor').wpColorPicker('color', '#191919');                     
            
       //FORM MESSAGE TEXT
         
            jQuery('#form-text-content').text('sample text');
    
            jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#ADB9D3');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#ADB9D3');                     
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#FFFFFF');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#FFFFFF');                     
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');                     
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#ADB9D3');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#ADB9D3');  

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');                     
            
        }else if(jQuery('#pre_forms option:selected').val() == 'pre_form5'){
            
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#F3F3F3');
            
       jQuery('#bg').wpColorPicker('color', '#F3F3F3');                     
    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#919955.50%;9A');
    
       jQuery('#headcolor').wpColorPicker('color', '#91999A');                     
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#91999A');
    
       jQuery('#msgcolor').wpColorPicker('color', '#91999A');                     
            
       //FORM MESSAGE TEXT
         
            jQuery('#form-text-content').text('sample text');
    
            jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#56C2E1');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#56C2E1');                     
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#FFFFFF');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#FFFFFF');                     
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');                     
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#A1A29F');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#A1A29F'); 

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');                      
            
        }else{
            
            //Do Nothing
        }
        
         });
        
    });
    
    jQuery( document ).ready(function() {    

    jQuery('#max_ch_headtext').text('max characters: 23');
jQuery('#headtext').keyup(function () {
    var max = 23;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_headtext').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_headtext').text('max characters: '+ch);
    }
    
     jQuery('#form-text-title').html(jQuery(this).val());
});
    
});

       jQuery(document).ready(function(){

             <?php if(get_option('gr_api_key')){ ?>
                    jQuery('#gr').show();    
                <?php }else {?>
                     jQuery('#gr').hide();    
                <?php }?>

                 <?php if(get_option('consumerKey') && get_option('consumerSecret') && get_option('accessKey') && get_option('accessSecret')){ ?>
                    jQuery('#aw').show();    
                <?php }else {?>
                     jQuery('#aw').hide();    
                <?php }?>

                 <?php if(get_option('mchimp_api_key')){ ?> 
                    jQuery('#mc').show();   
                <?php }else {?>
                     jQuery('#mc').hide();    
                <?php }?>

                 <?php if(get_option('ontraport_api_key') && get_option('ontraport_app_id')){ ?>
                    jQuery('#oa').show();    
                <?php }else {?>
                     jQuery('#oa').hide();    
                <?php }?>
                  
                 <?php if(get_option('icontact_key') && get_option('icontact_api_pass') && get_option('icontact_api_username')){ ?>
                    jQuery('#ic').show();    
                <?php }else {?>
                     jQuery('#ic').hide();    
                <?php }?>

                 <?php if(get_option('isoft_api_key') && get_option('isoft_app_name')){ ?>
                    jQuery('#is').show();    
                <?php }else {?>
                     jQuery('#is').hide();    
                <?php }?>

            });
    
    jQuery( document ).ready(function() {    

    jQuery('#max_ch_msgtext').text('max characters: 48');
jQuery('#msgtext').keyup(function () {
    var max = 48;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_msgtext').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_msgtext').text('max characters: '+ch);
    }
    
     jQuery('#form-text-content').html(jQuery(this).val());
});
    
});

     jQuery( document ).ready(function() {    

    jQuery('#max_ch_skipbttext').text('max characters: 27');
jQuery('#skip_optin_form_text').keyup(function () {
    var max = 27;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_skipbttext').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_skipbttext').text('max characters: '+ch);
    }
    
     jQuery('#close-optin').html(jQuery(this).val());
});
    
});
    
    jQuery( document ).ready(function() {    

    jQuery('#max_ch_buttext').text('max characters: 10');
jQuery('#buttext').keyup(function () {
    var max = 10;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_buttext').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_buttext').text('max characters: '+ch);
    }
    
     jQuery('#form_submit_bt').html(jQuery(this).val());
});
    
});
    
    jQuery(document).ready(function(){
    
    jQuery('#optin_skip_text_color').hide();
    jQuery('#close-optin').hide();
    jQuery('#optin_skip_text').hide();
    
   jQuery('#skip_optin_form').change(function () {
        if (jQuery('#skip_optin_form option:selected').val() == 0){    
           jQuery('#optin_skip_text_color').hide();
           jQuery('#close-optin').hide();  
           jQuery('#optin_skip_text').hide();  
        }
       
       else if(jQuery('#skip_optin_form option:selected').val() == 1){    
           jQuery('#optin_skip_text_color').show();
           jQuery('#close-optin').show();
           jQuery('#optin_skip_text').show();
       }
       else{
           
           //Do Nothing
       }
});
    
    
    if ('<?php echo $optin_edit->skip_optin_text ?>' == 0){
        jQuery('#optin_skip_text_color').hide();
           jQuery('#close-optin').hide();  
        jQuery('#optin_skip_text').hide();
    }
    else if('<?php echo $optin_edit->skip_optin_text ?>' == 1){
        jQuery('#optin_skip_text_color').show();
           jQuery('#close-optin').show();
        jQuery('#optin_skip_text').show();
    }
    else{
        //Do Nothing
        
    }
    
    
});
    
       jQuery("#editoptindetails").validity(function() {
            
                  if(jQuery("#getr_api_key").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#getr_api_key").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#getr_ca_id ").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#getr_ca_id ").require('This field is required');             
                    
                 }

                  if(jQuery("#getr_track_id ").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#getr_track_id ").require('This field is required');             
                    
                 }
                 
                if(jQuery("#aweb_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_api_key").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_api_sec").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_api_sec").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_acc_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_acc_id").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_acc_sec").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_acc_sec").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_acco_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_acco_id").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_list_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_list_id").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#aweb_trac_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_trac_id").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#mailch_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#mailch_api_key").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#mailch_list_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#mailch_list_id").require('This field is required');             
                    
                 }

                  if(jQuery("#mailch_track_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#mailch_track_id").require('This field is required');             
                    
                 }
                 
                    if(jQuery("#icon_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_api_key").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#icon_api_sec").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_api_sec").require('This field is required');             
                    
                 }
                 
                 if(jQuery("#icon_api_user").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_api_user").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#icon_api_pass").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_api_pass").require('This field is required');             
                    
                 }

                 if(jQuery("#icon_list_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_list_id").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#offa_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#offa_api_key").require('This field is required');             
                    
                 }
                 
                    if(jQuery("#offa_sq").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#offa_sq").require('This field is required');             
                    
                 }

                   if(jQuery("#offa_tag_name").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#offa_tag_name").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#trafw_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#trafw_api_key").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#trafw_ca_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#trafw_ca_id").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#insoft_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#insoft_api_key").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#insoft_fus_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#insoft_fus_id").require('This field is required');             
                    
                 }

                 if(jQuery("#insoft_tag_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#insoft_tag_id").require('This field is required');             
                    
                 }


                 
            jQuery("#fname").require('Form name is required.');
            jQuery("#headtext").require('Header text is required.').maxLength(23);
            jQuery("#msgtext").require('Message text is required.').maxLength(48);
            jQuery("#buttext").require('Button text is required.').maxLength(10);
            jQuery("#skip_optin_form_text").require('Skip optin form text is required.').maxLength(27);
                 
        });
    
      jQuery(document).ready(function($){
           
       jQuery('#refresh_bt_edit').click(function(){
     
       //FORM BACKGROUND COLOR
       var optin_form_bg_color = jQuery('#bg').val();
        
       jQuery('#y-form').css('background',optin_form_bg_color);
    
       //FORM HEADER COLOR
       var optin_form_head_color = jQuery('#headcolor').val();
        
       jQuery('#form-text-title').css('color',optin_form_head_color);
    
       //FORM HEADER TEXT
       var optin_form_head_text = jQuery('#headtext').val();
        
       jQuery('#form-text-title').text(optin_form_head_text);
    
       //FORM MESSAGE COLOR
       var optin_form_msg_color = jQuery('#msgcolor').val();
        
       jQuery('#form-text-content').css('color',optin_form_msg_color);
    
       //FORM MESSAGE TEXT
       var optin_form_msg_text = jQuery('#msgtext').val();
        
       jQuery('#form-text-content').text(optin_form_msg_text);
    
       //FORM BUTTON COLOR
       var optin_form_bt_color = jQuery('#buttoncolor').val();
        
       jQuery('#form_submit_bt').css('background-color',optin_form_bt_color);
    
       //FORM BUTTON TEXT COLOR
       var optin_form_bt_text_color = jQuery('#buttontextcolor').val();
        
       jQuery('#form_submit_bt').css('color',optin_form_bt_text_color);
    
       //FORM BUTTON TEXT
       var optin_form_bt_text = jQuery('#buttext').val();
        
       jQuery('#form_submit_bt').text(optin_form_bt_text);
           
       // EMAIL VALIDATION TEXT COLOR
       var optin_form_email_valid_text_color = jQuery('#emailvalidtextcolor').val();
        
       jQuery('#error-em').css('color',optin_form_email_valid_text_color); 
           
       // OPTIN BORDER COLOR
       var optin_border_color = jQuery('#o_border_color').val();
        
       jQuery('#y-form').css('border-color',optin_border_color);        
    
       //OPTIN FORM SKIP TEXT COLOR
       var optin_form_skip_text_color = jQuery('#skipoptintextcolor').val();
        
       jQuery('#close-optin').css('color',optin_form_skip_text_color); 

       //OPTIN FORM SKIP TEXT 

       var optin_form_skip_text = jQuery('#skip_optin_form_text').val(); 

       jQuery('#close-optin').text(optin_form_skip_text);
           
});
           
           //Saved values for preview forms
           jQuery('#y-form').css('background','<?php echo $optin_edit->bg ?>');
           jQuery('#form-text-title').css('color','<?php echo $optin_edit->headcolor ?>');
           jQuery('#form-text-title').text('<?php echo $optin_edit->headtext ?>');
           jQuery('#form-text-content').css('color','<?php echo $optin_edit->msgcolor ?>');
           jQuery('#form-text-content').text('<?php echo $optin_edit->msgtext ?>');
           jQuery('#form_submit_bt').css('background-color','<?php echo $optin_edit->butcolor ?>');
           jQuery('#form_submit_bt').css('color','<?php echo $optin_edit->buttextcolor ?>');
           jQuery('#form_submit_bt').text('<?php echo $optin_edit->buttext ?>');
           jQuery('#error-em').css('color','<?php echo $optin_edit->emailvalidtxtcolor ?>');
           jQuery('#close-optin').css('color','<?php echo $optin_edit->optinskiptxtcolor ?>');
           jQuery('#close-optin').text('<?php echo $optin_edit->skip_bt_text ?>');
           jQuery('#y-form').css('border-color','<?php echo $optin_edit->optinbordercolor ?>');
           
       });

          jQuery(document).ready(function($){

          if (jQuery("#ar option:selected").val()=='gr' || jQuery("#ar option:selected").val()=='aw' || jQuery("#ar option:selected").val()=='mc' || jQuery("#ar option:selected").val()=='ic' || jQuery("#ar option:selected").val()=='oa' || jQuery("#ar option:selected").val()=='is')
            {
                jQuery('#optin_form_sb').removeAttr('disabled');
                  
            }
            else
            {

                jQuery('#optin_form_sb').attr('disabled', 'disabled');
            } 


                jQuery("#ar").change(function () {
          
          if (jQuery("#ar option:selected").val()=='gr' || jQuery("#ar option:selected").val()=='aw' || jQuery("#ar option:selected").val()=='mc' || jQuery("#ar option:selected").val()=='ic' || jQuery("#ar option:selected").val()=='oa' || jQuery("#ar option:selected").val()=='is')
            {
                jQuery('#optin_form_sb').removeAttr('disabled');
                  
            }
            else
            {

                jQuery('#optin_form_sb').attr('disabled', 'disabled');
            }
        });

           });

    
    </script>

<?php
    
}

function prash_add_optin_form(){
    
//    global $shortcode_tags;
//echo "<pre>"; print_r($shortcode_tags); echo "</pre>";
 
?>

<style>

html{

background:#FFFFFF;
height: 1000px;
}

</style>

<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<h2>Create new Optin Form</h2>
<span>
    Select from pre-created optin forms to start creating beautiful customizable forms for your videos. </span><br><br>

<form action="admin.php?page=prash_optin_forms&noheader=true" class="optindetails" id="addoptindetails" name="optindetails" method="post">

<table style="text-align: left; width: 100%;" border="0"
cellpadding="2" cellspacing="2">
<tr>
    <td>
    
<table style="text-align: left; width: 70%;" border="0"
cellpadding="2" cellspacing="2">
<tbody>
    
    
    <tr>
<td style="vertical-align: top;">Form name<br>
</td>
<td style="vertical-align: top;"><input name="name" id="fname" type="text" class="form-control" style="width:50%;">
    <br>
</td>
</tr>
    

    
    
        
<!--PREBUILD FORM LIST-->

 <tr>
<td style="vertical-align: top;">Prebuild Forms<br>
</td>
<td style="vertical-align: top;">
    <select id="pre_forms" name="pre_forms" class="form-control" style="width:35%;">        
<option value="pre_form1" selected>Form 1</option>
<option value="pre_form2">Form 2</option>
<option value="pre_form3">Form 3</option>
<option value="pre_form4">Form 4</option>
<option value="pre_form5">Form 5</option>
</select>
    <br> 
</td>
</tr>    
        
<!--PREBUILD FORM LIST END-->
    
        
<!-- AUTORESPONDER SELECT JQUERY -->
        
<!-- AUTORESPONDER SELECT JQUERY END -->
    <tr>
<td style="vertical-align: top;">Autoresponder<br>
</td>
<td style="vertical-align: top;">
    <select id="ar" name="ar" class="form-control" style="width:70%;">
<option value="sa" selected>Select Autoresponder</option>
<option id="gr" value="gr">GetResponse</option>
<option id="aw" value="aw">Aweber</option>
<option id="mc" value="mc">MailChimp</option>
<option id="ic" value="ic">iContact</option>
<option id="oa" value="oa">OfficeAutopilot/Ontraport</option>
<!-- <option value="tw">TrafficWave.net</option> -->
<option id="is" value="is">Infusionsoft</option>
</select>
    <br>
</td>
</tr>
            
<!-- AUTORESPONDERS REQUIRED FIELDS FOR SUBMISSION -->

<tr id="aweber-fields-aweb_list_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">AWeber List ID<br>
</td>
<td style="vertical-align: top;"><input name="aweb_list_id" id="aweb_list_id" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>
    
<tr id="aweber-fields-aweb_trac_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">AWeber Tracking ID<br>
</td>
<td style="vertical-align: top;"><input name="aweb_trac_id" id="aweb_trac_id" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>

<tr id="getresp-fields-ca_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">GetResponse Campaign Name<br>
</td>
    <td style="vertical-align: top;"><input name="getr_ca_id" id="getr_ca_id" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr> 

<!-- <tr id="getresp-fields-track_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">GetResponse Track ID<br>
</td>
    <td style="vertical-align: top;"><input name="getr_track_id" id="getr_track_id" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr> -->

<tr id="mailch-fields-mailch_list_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">MailChimp List ID<br>
</td>
    <td style="vertical-align: top;"><input name="mailch_list_id" id="mailch_list_id" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr> 

<!-- <tr id="mailch-fields-mailch_track_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">MailChimp Track ID<br>
</td>
    <td style="vertical-align: top;"><input name="mailch_track_id" id="mailch_track_id" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>  -->

<tr id="icont-fields-icon_list_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">IContact List ID<br>
</td>
    <td style="vertical-align: top;"><input name="icon_list_id" id="icon_list_id" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>

<tr id="insoft-fields-insoft_fus_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">Infusionsoft Follow-Up<br>Sequence ID<br>
</td>
    <td style="vertical-align: top;"><input name="insoft_fus_id" id="insoft_fus_id" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>

<tr id="insoft-fields-insoft_tag_id" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">Infusionsoft Tag ID<br>
</td>
    <td style="vertical-align: top;"><input name="insoft_tag_id" id="insoft_tag_id" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr> 
        
<tr id="offap-fields-offa_sq" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">Ontraport Sequence ID<br>(*comma seperated list)<br>
</td>
    <td style="vertical-align: top;"><input name="offa_sq" id="offa_sq" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>  

<tr id="offap-fields-offa_tag_name" style="vertical-align: top;display:none;">    
<td style="vertical-align: top;">Ontraport Tag Name<br>(*comma seperated list)<br>
</td>
    <td style="vertical-align: top;"><input name="offa_tag_name" id="offa_tag_name" type="text" class="form-control" style="width:80%;">
<br>
</td>
</tr>

<!-- AUTORESPONDERS REQUIRED FIELDS FOR SUBMISSION END -->
    
<!--PREBUILD FORM LIST JQUERY-->
    
<!--PREBUILD FORM LIST JQUERY END-->    
    
<tr>
<td style="vertical-align: top;">Form background color<br>
</td>
<td style="vertical-align: top;"><input name="bg" id="bg">
   <br><br>
</td>
</tr>
<tr>
<td style="vertical-align: top;">Heading color<br>
</td>
<td style="vertical-align: top;"><input name="headcolor" id="headcolor"><br><br>
</td>
</tr>

<tr>
<td style="vertical-align: top;">Heading text<br>
</td>
<td style="vertical-align: top;"><input name="headtext" id="headtext" type="text" class="form-control" style="width:80%;" maxlength="23">
<div id="max_ch_headtext" style="color:black;"></div><br></td>
</tr>
<tr>
<td style="vertical-align: top;">Message color<br>
</td>
<td style="vertical-align: top;"><input name="msgcolor" id="msgcolor"><br><br>
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Message&nbsp; text<br>
</td>
<td style="vertical-align: top;"><input name="msgtext" id="msgtext" type="text" class="form-control" style="width:80%;" maxlength="48">
<div id="max_ch_msgtext" style="color:black;"></div><br></td>
</tr>
<tr>
<td style="vertical-align: top;">Button color<br>
</td>
<td style="vertical-align: top;"><input name="butcolor" id="buttoncolor"><br><br>
</td>
</tr>
    <tr>
<td style="vertical-align: top;">Button text color<br>
</td>
<td style="vertical-align: top;"><input name="buttextcolor" id="buttontextcolor"><br><br>
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Button text<br>
</td>
<td style="vertical-align: top;"><input name="buttext" id="buttext" type="text" class="form-control" style="width:80%;" maxlength="10">
<div id="max_ch_buttext" style="color:black;"></div><br></td>
</tr>

<tr>
<td style="vertical-align: top;">Email validation text color<br>
</td>
<td style="vertical-align: top;"><input name="emailvalidtxtcolor" id="emailvalidtextcolor"><br><br>
</td>
</tr>
        
<tr id="optin_border_color">
<td style="vertical-align: top;">Optin Border color<br>    
</td>
<td style="vertical-align: top;"><input name="o_border_color" id="o_border_color" ><br><br>
</td>
</tr>        
    
<tr>
<td style="vertical-align: top;">Skip Optin form?<br>
</td>
<td style="vertical-align: top;">
<select name="skip_optin_form" id="skip_optin_form" class="form-control" style="width:30%;"> 
<option value="1">Yes</option>
<option value="0" selected>No</option>        
</select>
<br><br>
</td>
</tr>    

<tr id="optin_skip_text">
<td style="vertical-align: top;">Skip Optin form text<br>
</td>
<td style="vertical-align: top;"><input name="skip_optin_form_text" id="skip_optin_form_text" type="text" class="form-control" style="width:80%;" maxlength="27">
<div id="max_ch_skipbttext" style="color:black;"></div><br>
</td>
</tr>   
    
<tr id="optin_skip_text_color">
<td style="vertical-align: top;">Skip optin text color<br>
</td>
<td style="vertical-align: top;"><input name="optinskiptxtcolor" id="skipoptintextcolor"><br><br>
</td>
</tr>     
</tbody>
</table>
<p><b>** - Please select any autoresponder to create optin form.<br>If there is no option to select any autoresponder then save <br>your autoresponder credentials in Autoresponder Settings<br> for selection.</b></p>
<span id="errorm"></span>        
<br>
    
<input id="optin_form_sb" name="Submit" value="Submit" type="submit" class="btn btn-primary"><br>
<br> <input type="hidden" name="mode" value="new">
<br>
</form>  
    
    <!--FORM VALIDATIONS-->
     
<!--FORM VALIDATIONS END--> 
    
    
</td>
    
     <!--custom added - jquery for refreshing the form button -->
    
    <!--custom added - jquery for refreshing the form button end-->
    <td>
       
       <!--custom added - for refresh button form -->
        
        <center> <button type="button" id="refresh_bt" class="btn btn-success">Refresh Preview</button></center>
        <br/><br/>
         <!--custom added - for refresh button form end-->
        
<!-- FORM PREVIEW-->
<div id="y-form" class="y-form" required style="background:#FFFFFF;border:1;width:93%;height:100%;top: 0;bottom: 0;left: 0;right: 0;margin: auto;text-align:center;padding:5px; -webkit-border-radius: 20px;-moz-border-radius: 20px;border-radius: 20px;border:4px solid #2F4F4F;">
    
   <div class="form-text-title" id="form-text-title" style="line-height:35px;color:#FF0000;font-size: 18px;text-align: center;margin-top: 1%;word-wrap:break-word;">Header</div>
     
         <div class="form-text-content" id="form-text-content" style="color:#0000FF;text-align: center;word-wrap:break-word;font-size:15px;">Message</div>
    
    <div id="error-em" class="error-em" style="text-align: center;font-size: 14px;color:#D00;font-style: italic;">Please enter a valid email id</div>
           
    <form name="vform" class="yt-form" action="#" method="get" target="_blank" style="margin-top:1%">
         <div id="em_submit" style="width:90%;padding:5px;display:inline-block;">
        <input id="em" name="email" type="email" placeholder="Email" style="width:70%;margin-right:1%;display:inline-block;">
       <a href="#" id="form_submit_bt" class="form_submit_bt" style="display:inline-block;font-size:10px;font-family:Arial;font-weight:bold;-moz-border-radius:8px;-webkit-border-radius:8px;border-radius:8px;padding-left:2%;padding-right:2%;padding-top:5px;padding-bottom:5px;text-decoration:none;background-color:#3d94f6;color:#ffffff;text-align:center;">Button text</a>
             
             </div>
                <br/>
                <a href="#" id="close-optin" class="close-optin" style="padding: 2px;text-align: center;position: relative;text-align:center;color:#FF0000;font-weight:bold;text-decoration: none;">Skip this step >></a>
</form>   
</div>  

        
        
<!-- FORM PREVIEW END-->    
    </td>
    
     <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/jquery.validity.css' , __FILE__ ); ?>" />
     <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> 
     <script src="<?php echo plugins_url( 'js/jquery.validity.min.js' , __FILE__ ); ?>"></script>
     <script type="text/javascript">
         
              jQuery(document).ready(function($){
                  
                  var bg_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formbgcolor = jQuery(this).wpColorPicker('color');    
        
    jQuery('#y-form').css('background',formbgcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_title_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formtitlecolor = jQuery(this).wpColorPicker('color');    
        
    jQuery('#form-text-title').css('color',formtitlecolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_content_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formcontentcolor = jQuery(this).wpColorPicker('color');    
        
   jQuery('#form-text-content').css('color',formcontentcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_bt_bg_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formsubmitbgcolor = jQuery(this).wpColorPicker('color');    
        
    jQuery('#form_submit_bt').css('background-color',formsubmitbgcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_bt_text_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formsubmittextcolor = jQuery(this).wpColorPicker('color');    
        
   jQuery('#form_submit_bt').css('color',formsubmittextcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_error_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formerrorcolor = jQuery(this).wpColorPicker('color');    
        
   jQuery('#error-em').css('color',formerrorcolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_border_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formbordercolor = jQuery(this).wpColorPicker('color');    
        
    jQuery('#y-form').css('border-color',formbordercolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                    var form_close_options = {
    // you can declare a default color here,
    // or in the data-default-color attribute on the input
    defaultColor: false,
    // a callback to fire whenever the color changes to a valid color
    change: function(event, ui){
    
    var formclosecolor = jQuery(this).wpColorPicker('color');    
        
   jQuery('#close-optin').css('color',formclosecolor);
    
    },
    // a callback to fire when the input is emptied or an invalid color
    clear: function() {},
    // hide the color picker controls on load
    hide: true,
    // show a group of common colors beneath the square
    // or, supply an array of colors to customize further
    palettes: true
};
                  
                  
                  
    jQuery('#bg').wpColorPicker(bg_options);
    jQuery('#headcolor').wpColorPicker(form_title_options);
    jQuery('#msgcolor').wpColorPicker(form_content_options);
    jQuery('#buttoncolor').wpColorPicker(form_bt_bg_options);    
    jQuery('#buttontextcolor').wpColorPicker(form_bt_text_options);
    jQuery('#emailvalidtextcolor').wpColorPicker(form_error_options);
    jQuery('#skipoptintextcolor').wpColorPicker(form_close_options); 
    jQuery('#o_border_color').wpColorPicker(form_border_options);     
});
        
        
         jQuery(document).ready(function(){

            jQuery('#getresp-fields-ca_id').hide();
             jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
            
            
            jQuery('#ar').change(function () {

         if (jQuery('#ar option:selected').val() == 'sa'){
            jQuery('#getresp-fields-ca_id').hide();
            jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        } 

        else if (jQuery('#ar option:selected').val() == 'gr'){
            jQuery('#getresp-fields-ca_id').show();
            jQuery('#getresp-fields-track_id').show();
            jQuery('#getresp-fields-getr_api_key').show();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        } 
        else if (jQuery('#ar option:selected').val() == 'aw'){
           jQuery('#getresp-fields-ca_id').hide();
           jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').show();
            jQuery('#aweber-fields-aweb_api_sec').show();
            jQuery('#aweber-fields-aweb_acc_id').show();
            jQuery('#aweber-fields-aweb_acc_sec').show();
            jQuery('#aweber-fields-aweb_acco_id').show();
            jQuery('#aweber-fields-aweb_list_id').show();
            jQuery('#aweber-fields-aweb_trac_id').show();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }
        else if (jQuery('#ar option:selected').val() == 'mc'){
           jQuery('#getresp-fields-ca_id').hide();
           jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').show();
            jQuery('#mailch-fields-mailch_list_id').show();
            jQuery('#mailch-fields-mailch_track_id').show();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }        
        else if (jQuery('#ar option:selected').val() == 'ic'){
          jQuery('#getresp-fields-ca_id').hide();
          jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').show();
            jQuery('#icont-fields-icon_api_sec').show();
            jQuery('#icont-fields-icon_api_user').show();
            jQuery('#icont-fields-icon_api_pass').show();
            jQuery('#icont-fields-icon_list_id').show();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }
        else if (jQuery('#ar option:selected').val() == 'oa'){
           jQuery('#getresp-fields-ca_id').hide();
           jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').show();
            jQuery('#offap-fields-offa_sq').show();
            jQuery('#offap-fields-offa_tag_name').show();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }
        else if (jQuery('#ar option:selected').val() == 'tw'){
           jQuery('#getresp-fields-ca_id').hide();
           jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').show();
            jQuery('#trafw-fields-trafw_ca_id').show();
            jQuery('#insoft-fields-insoft_api_key').hide();
            jQuery('#insoft-fields-insoft_fus_id').hide();
            jQuery('#insoft-fields-insoft_tag_id').hide();
        }
        else if (jQuery('#ar option:selected').val() == 'is'){
         jQuery('#getresp-fields-ca_id').hide();
         jQuery('#getresp-fields-track_id').hide();
            jQuery('#getresp-fields-getr_api_key').hide();
            jQuery('#aweber-fields-aweb_api_key').hide();
            jQuery('#aweber-fields-aweb_api_sec').hide();
            jQuery('#aweber-fields-aweb_acc_id').hide();
            jQuery('#aweber-fields-aweb_acc_sec').hide();
            jQuery('#aweber-fields-aweb_acco_id').hide();
            jQuery('#aweber-fields-aweb_list_id').hide();
            jQuery('#aweber-fields-aweb_trac_id').hide();
            jQuery('#mailch-fields-mailch_api_key').hide();
            jQuery('#mailch-fields-mailch_list_id').hide();
            jQuery('#mailch-fields-mailch_track_id').hide();
            jQuery('#icont-fields-icon_api_key').hide();
            jQuery('#icont-fields-icon_api_sec').hide();
            jQuery('#icont-fields-icon_api_user').hide();
            jQuery('#icont-fields-icon_api_pass').hide();
            jQuery('#icont-fields-icon_list_id').hide();
            jQuery('#offap-fields-offa_api_key').hide();
            jQuery('#offap-fields-offa_sq').hide();
            jQuery('#offap-fields-offa_tag_name').hide();
            jQuery('#trafw-fields-trafw_api_key').hide();
            jQuery('#trafw-fields-trafw_ca_id').hide();
            jQuery('#insoft-fields-insoft_api_key').show();
            jQuery('#insoft-fields-insoft_fus_id').show();
            jQuery('#insoft-fields-insoft_tag_id').show();
        }        
        else
        {
           //Do Nothing
        } 

});  
            
    });
       
          jQuery(document).ready(function($){
    
               //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#3B6C7C');
         
             
       jQuery('#bg').wpColorPicker('color', '#3B6C7C');
                    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#FFFFFF');
    
       jQuery('#headcolor').wpColorPicker('color', '#FFFFFF');
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#FFFFFF');
    
       jQuery('#msgcolor').wpColorPicker('color', '#FFFFFF');   
            
       //FORM MESSAGE TEXT
         
              jQuery('#form-text-content').text('sample text');
    
              jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#DA452D');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#DA452D');      
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#FFFFFF');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#FFFFFF');    
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');     
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#FFFFFF');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#FFFFFF');   

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');     
        
         jQuery('#pre_forms').change(function () {
             
        if (jQuery('#pre_forms option:selected').val() == 'pre_form1'){
             
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#3B6C7C');
         
             
       jQuery('#bg').wpColorPicker('color', '#3B6C7C');
                    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#FFFFFF');
    
       jQuery('#headcolor').wpColorPicker('color', '#FFFFFF');
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#FFFFFF');
    
       jQuery('#msgcolor').wpColorPicker('color', '#FFFFFF');   
            
       //FORM MESSAGE TEXT
         
            jQuery('#form-text-content').text('sample text');
    
            jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#DA452D');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#DA452D');      
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#FFFFFF');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#FFFFFF');    
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');     
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#FFFFFF');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#FFFFFF'); 

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');       
            
        }
        else if(jQuery('#pre_forms option:selected').val() == 'pre_form2'){
            
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#3E4346');
            
       jQuery('#bg').wpColorPicker('color', '#3E4346');             
    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#72A75E');
    
       jQuery('#headcolor').wpColorPicker('color', '#72A75E');             
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#FFFFFF');
    
       jQuery('#msgcolor').wpColorPicker('color', '#FFFFFF');             
            
       //FORM MESSAGE TEXT
         
       jQuery('#form-text-content').text('sample text');
    
       jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#3CB371');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#3CB371');             
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#F5FFFA');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#F5FFFA');             
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');             
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#87CEEB');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#87CEEB');  

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');              
            
        }else if(jQuery('#pre_forms option:selected').val() == 'pre_form3'){
            
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#F8F8F8');
            
       jQuery('#bg').wpColorPicker('color', '#F8F8F8');             
    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#A2B8CB');
    
       jQuery('#headcolor').wpColorPicker('color', '#A2B8CB');             
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#A2B8CB');
    
       jQuery('#msgcolor').wpColorPicker('color', '#A2B8CB');                 
            
       //FORM MESSAGE TEXT
         
            jQuery('#form-text-content').text('sample text');
    
            jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#FED373');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#FED373');                 
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#C59D5A');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#C59D5A');                 
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');                 
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#A2B8CB');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#A2B8CB'); 

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');                   
            
        }else if(jQuery('#pre_forms option:selected').val() == 'pre_form4'){
            
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#FFFFFF');
            
       jQuery('#bg').wpColorPicker('color', '#FFFFFF');                 
    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#191919');
    
       jQuery('#headcolor').wpColorPicker('color', '#191919');                 
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#191919');
    
       jQuery('#msgcolor').wpColorPicker('color', '#191919');                     
            
       //FORM MESSAGE TEXT
         
            jQuery('#form-text-content').text('sample text');
    
            jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#ADB9D3');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#ADB9D3');                     
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#FFFFFF');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#FFFFFF');                     
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');                     
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#ADB9D3');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#ADB9D3');   

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');                     
            
        }else if(jQuery('#pre_forms option:selected').val() == 'pre_form5'){
            
              //FORM BACKGROUND COLOR
     
       jQuery('#y-form').css('background','#F3F3F3');
            
       jQuery('#bg').wpColorPicker('color', '#F3F3F3');                     
    
       //FORM HEADER COLOR
        
       jQuery('#form-text-title').css('color','#91999A');
    
       jQuery('#headcolor').wpColorPicker('color', '#91999A');                     
            
       //FORM HEADER TEXT
        
       jQuery('#form-text-title').text('Please Sign Up!');
    
       jQuery('#headtext').val('Please Sign Up!');    
            
       //FORM MESSAGE COLOR
        
       jQuery('#form-text-content').css('color','#91999A');
    
       jQuery('#msgcolor').wpColorPicker('color', '#91999A');                     
            
       //FORM MESSAGE TEXT
         
            jQuery('#form-text-content').text('sample text');
    
            jQuery('#msgtext').val('sample text');    
            
       //FORM BUTTON COLOR
             
       jQuery('#form_submit_bt').css('background-color','#56C2E1');
    
       jQuery('#buttoncolor').wpColorPicker('color', '#56C2E1');                     
            
       //FORM BUTTON TEXT COLOR
        
       jQuery('#form_submit_bt').css('color','#FFFFFF');
    
       jQuery('#buttontextcolor').wpColorPicker('color', '#FFFFFF');                     
            
       //FORM BUTTON TEXT
             
       jQuery('#form_submit_bt').text('Sign Up!');
           
       jQuery('#buttext').val('Sign Up!');    
            
       // EMAIL VALIDATION TEXT COLOR
             
       jQuery('#error-em').css('color','#FF0000'); 
    
       jQuery('#emailvalidtextcolor').wpColorPicker('color', '#FF0000');                     
            
       //OPTIN FORM SKIP TEXT COLOR
        
       jQuery('#close-optin').css('color','#A1A29F');       
        
       jQuery('#skipoptintextcolor').wpColorPicker('color', '#A1A29F');  

       //OPTIN FORM SKIP TEXT 

       jQuery('#close-optin').text('Skip this step >>');
           
       jQuery('#skip_optin_form_text').val('Skip this step >>');                      
            
        }else{
            
            //Do Nothing
        }
        
         });
        
    });

            jQuery(document).ready(function(){

             <?php if(get_option('gr_api_key')){ ?>
                    jQuery('#gr').show();    
                <?php }else {?>
                     jQuery('#gr').hide();    
                <?php }?>

                 <?php if(get_option('consumerKey') && get_option('consumerSecret') && get_option('accessKey') && get_option('accessSecret')){ ?>
                    jQuery('#aw').show();    
                <?php }else {?>
                     jQuery('#aw').hide();    
                <?php }?>

                 <?php if(get_option('mchimp_api_key')){ ?> 
                    jQuery('#mc').show();   
                <?php }else {?>
                     jQuery('#mc').hide();    
                <?php }?>

                 <?php if(get_option('ontraport_api_key') && get_option('ontraport_app_id')){ ?>
                    jQuery('#oa').show();    
                <?php }else {?>
                     jQuery('#oa').hide();    
                <?php }?>

                 <?php if(get_option('icontact_key') && get_option('icontact_api_pass') && get_option('icontact_api_username')){ ?>
                    jQuery('#ic').show();    
                <?php }else {?>
                     jQuery('#ic').hide();    
                <?php }?>

                 <?php if(get_option('isoft_api_key') && get_option('isoft_app_name')){ ?>
                    jQuery('#is').show();    
                <?php }else {?>
                     jQuery('#is').hide();    
                <?php }?>

            });
        
          jQuery(document).ready(function(){
    
    jQuery('#y-form').css('border-color','#1E90FF');    
        
    jQuery('#o_border_color').wpColorPicker('color', '#1E90FF');   
        
    });
        
        jQuery( document ).ready(function() {    

    jQuery('#max_ch_headtext').text('max characters: 23');
jQuery('#headtext').keyup(function () {
    var max = 23;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_headtext').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_headtext').text('max characters: '+ch);
    }
    
     jQuery('#form-text-title').html(jQuery(this).val());
      
});
    
});

         jQuery( document ).ready(function() {    

    jQuery('#max_ch_skipbttext').text('max characters: 27');
jQuery('#skip_optin_form_text').keyup(function () {
    var max = 27;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_skipbttext').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_skipbttext').text('max characters: '+ch);
    }
    
     jQuery('#close-optin').html(jQuery(this).val());
      
});
    
});
        
        jQuery( document ).ready(function() {    

    jQuery('#max_ch_msgtext').text('max characters: 48');
jQuery('#msgtext').keyup(function () {
    var max = 48;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_msgtext').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_msgtext').text('max characters: '+ch);
    }
    
     jQuery('#form-text-content').html(jQuery(this).val());
    
});
    
});
        
        jQuery( document ).ready(function() {    

    jQuery('#max_ch_buttext').text('max characters: 10');
jQuery('#buttext').keyup(function () {
    var max = 10;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_buttext').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_buttext').text('max characters: '+ch);
    }
    
    jQuery('#form_submit_bt').html(jQuery(this).val());
});
    
});
        
        jQuery(document).ready(function(){
    
    jQuery('#optin_skip_text_color').hide();
    jQuery('#close-optin').hide();
    jQuery('#optin_skip_text').hide();
    
   jQuery('#skip_optin_form').change(function () {
        if (jQuery('#skip_optin_form option:selected').val() == 0){    
           jQuery('#optin_skip_text_color').hide();
           jQuery('#close-optin').hide(); 
           jQuery('#optin_skip_text').hide();   
        }
       
       else if(jQuery('#skip_optin_form option:selected').val() == 1){    
           jQuery('#optin_skip_text_color').show();
           jQuery('#close-optin').show();
           jQuery('#optin_skip_text').show();
       }
       else{
           
           //Do Nothing
       }
});
    
});
         
           jQuery("#addoptindetails").validity(function() {
            
                  if(jQuery("#getr_api_key").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#getr_api_key").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#getr_ca_id ").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#getr_ca_id ").require('This field is required');             
                    
                 }

                  if(jQuery("#getr_track_id ").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#getr_track_id ").require('This field is required');             
                    
                 }
                 
                if(jQuery("#aweb_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_api_key").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_api_sec").is(':hidden')){
                    
                    
                }
                 else{
                    jQuery("#aweb_api_sec").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_acc_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_acc_id").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_acc_sec").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_acc_sec").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_acco_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_acco_id").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#aweb_list_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_list_id").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#aweb_trac_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#aweb_trac_id").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#mailch_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#mailch_api_key").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#mailch_list_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#mailch_list_id").require('This field is required');             
                    
                 }

                   if(jQuery("#mailch_track_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#mailch_track_id").require('This field is required');             
                    
                 }
                 
                    if(jQuery("#icon_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_api_key").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#icon_api_sec").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_api_sec").require('This field is required');             
                    
                 }
                 
                 if(jQuery("#icon_api_user").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_api_user").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#icon_api_pass").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_api_pass").require('This field is required');             
                    
                 }

                 if(jQuery("#icon_list_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#icon_list_id").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#offa_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#offa_api_key").require('This field is required');             
                    
                 }
                 
                    if(jQuery("#offa_sq").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#offa_sq").require('This field is required');             
                    
                 }

                  if(jQuery("#offa_tag_name").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#offa_tag_name").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#trafw_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#trafw_api_key").require('This field is required');             
                    
                 }
                 
                   if(jQuery("#trafw_ca_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#trafw_ca_id").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#insoft_api_key").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#insoft_api_key").require('This field is required');             
                    
                 }
                 
                  if(jQuery("#insoft_fus_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#insoft_fus_id").require('This field is required');             
                    
                 }

                 if(jQuery("#insoft_tag_id").is(':hidden')){
                    
                      
                }
                 else{
                    jQuery("#insoft_tag_id").require('This field is required');             
                    
                 }
                 
            jQuery("#fname").require('Form name is required.');
            jQuery("#headtext").require('Header text is required.').maxLength(23);
            jQuery("#msgtext").require('Message text is required.').maxLength(48);
            jQuery("#buttext").require('Button text is required.').maxLength(10);
            jQuery("#skip_optin_form_text").require('Skip optin form text is required.').maxLength(27);
                 
        });
         
           jQuery(document).ready(function($){
jQuery('#refresh_bt').click(function(){
     
       //FORM BACKGROUND COLOR
       var optin_form_bg_color = jQuery('#bg').val();
        
       jQuery('#y-form').css('background',optin_form_bg_color);
    
       //FORM HEADER COLOR
       var optin_form_head_color = jQuery('#headcolor').val();
        
       jQuery('#form-text-title').css('color',optin_form_head_color);
    
       //FORM HEADER TEXT
       var optin_form_head_text = jQuery('#headtext').val();
        
       jQuery('#form-text-title').text(optin_form_head_text);
    
       //FORM MESSAGE COLOR
       var optin_form_msg_color = jQuery('#msgcolor').val();
        
       jQuery('#form-text-content').css('color',optin_form_msg_color);
    
       //FORM MESSAGE TEXT
       var optin_form_msg_text = jQuery('#msgtext').val();
        
       jQuery('#form-text-content').text(optin_form_msg_text);
    
       //FORM BUTTON COLOR
       var optin_form_bt_color = jQuery('#buttoncolor').val();
        
       jQuery('#form_submit_bt').css('background-color',optin_form_bt_color);
    
       //FORM BUTTON TEXT COLOR
       var optin_form_bt_text_color = jQuery('#buttontextcolor').val();
        
       jQuery('#form_submit_bt').css('color',optin_form_bt_text_color);
    
       //FORM BUTTON TEXT
       var optin_form_bt_text = jQuery('#buttext').val();
        
       jQuery('#form_submit_bt').text(optin_form_bt_text);
    
       // EMAIL VALIDATION TEXT COLOR
       var optin_form_email_valid_text_color = jQuery('#emailvalidtextcolor').val();
        
       jQuery('#error-em').css('color',optin_form_email_valid_text_color);
    
       // OPTIN BORDER COLOR
       var optin_border_color = jQuery('#o_border_color').val();
        
       jQuery('#y-form').css('border-color',optin_border_color); 
    
       //OPTIN FORM SKIP TEXT COLOR
       var optin_form_skip_text_color = jQuery('#skipoptintextcolor').val();
        
       jQuery('#close-optin').css('color',optin_form_skip_text_color);

       //OPTIN FORM SKIP TEXT 

       var optin_form_skip_text = jQuery('#skip_optin_form_text').val(); 

       jQuery('#close-optin').text(optin_form_skip_text);
    
});
       });

           jQuery(document).ready(function($){

            jQuery('#optin_form_sb').attr('disabled', 'disabled');

                jQuery("#ar").change(function () {
          
          if (jQuery("#ar option:selected").val()=='gr' || jQuery("#ar option:selected").val()=='aw' || jQuery("#ar option:selected").val()=='mc' || jQuery("#ar option:selected").val()=='ic' || jQuery("#ar option:selected").val()=='oa' || jQuery("#ar option:selected").val()=='is')
            {
                jQuery('#optin_form_sb').removeAttr('disabled');
                  
            }
            else
            {

                jQuery('#optin_form_sb').attr('disabled', 'disabled');
            }
        });

           });

         
     
    </script>    
<?php
    
}

function prash_select_new_action_form(){
 ?> 
<br>
<h2>Create actions</h2>
    <p>Actions are interactive elements/features that you can add to your videos. Select from the list below to create a new action.</p>
<ul>
<li>
    <a href="admin.php?page=prash_add_cta">New call to action</a>
</li> 
<li>
    <a href="admin.php?page=prash_add_optin">New optin form </a>
</li>    
</ul>

<?php 
    
}



function prash_add_video_do(){
        
    global $wpdb;
    $table_name = $wpdb->prefix . "prash_videos";

    $video_url = esc_html($_POST["youtube_url"]);
    
    if(strpos($video_url, 'http') === 0)    {

    }else{
        $video_url = "http://" . $video_url;

        echo "inloop";
    }
    
    echo "video url: " . $video_url;
    
    $videoid = getYouTubeVideoId($video_url);
    
    echo "video id: " . $videoid;
    
    $wpdb->show_errors();
                
    
    if($videoid != "" && $videoid != null){
    
                $save_check= $wpdb->insert($wpdb->prefix.'prash_videos', array(
		
                    'vid'    => uniqid(),
                    'title'    => esc_html($_POST["title"]),
                    'youtube'    => $videoid,
                    'width'      => $_POST["width"],
                    'height'    => $_POST["height"],
                    'autoplay' => $_POST["autoplay"],
                    'dim'      => $_POST["dim"],
                    'scroll_pause'      => $_POST["scroll"],
                    'controls'   => $_POST["show_controls"],
                    'auto_hide_cb'   => $_POST["auto_hide_control_bar"],
                    'theme'   => $_POST["player_theme"],
                    'vbordercolor'   => $_POST["v_border_color"],
                    'social_share'   => $_POST["s_share"],
                    'logo_brand_code'   => $_POST["logo_brand"],
                    'logo_ps'   => $_POST["logo_brand_position"],
                    'logo_pick'   => $_POST["logo_pick"],
                    'logo_link'   => $_POST["lk_logo"]
                    
                            ),
                            array(
                            '%s',
                            '%s',
                            '%s',
                            '%d',
                            '%d',
                            '%d',
                            '%d',
                            '%d',    
                            '%d',
                            '%s',
                            '%s',
                            '%s',    
                            '%d',
                            '%d',
                            '%d',    
                            '%s',
                            '%s'    
                            ));
                 
                            
    }else{
    
        echo "Please check the Youtube video URL and try again." ;

        //                    echo $wpdb->print_error();

        return false;
        
    }
    
    
      if(0 < $save_check){
          
          wp_redirect("admin.php?page=Prash_Video_List&success_msg=" . urlencode("Video Created Successfully")); exit();
                
//                echo "Video Created Successfully";

                return true;
                
            }
            else if(0 === $save_check){
                
                wp_redirect("admin.php?page=Prash_Video_List&success_msg=" . urlencode("Video Created Successfully")); exit();
                
//                echo "Video Created Successfully";

                return true;

            }else if(false === $save_check){
                
                wp_redirect("admin.php?page=Prash_Video_List&error_msg=" . urlencode("Error creating video")); exit();
                
//                echo "Error creating video";

                return false;
            }
            else{
            
                //Do Nothing
            }
                    
}
    

function prash_choose_action_form(){
    ?>

<h3>Choose Feature</h3>

<ul>
    <li>Dim the background while playing</li>
    <li>Google Analytics</li>
    </ul>
<?php 
    
}






function prash_add_video_form(){
 ?>
    <p><strong><a target="_blank" href="http://agilevideoplayer.com" >Upgrade to the full version of Agile Video Player for awesome marketing features. Click here >></a></strong></p> 
    

<style>

html{

background:#FFFFFF;
 height: 480px;
}

</style>

<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<h2>Add New Video</h2>
<form action="admin.php?page=prash_new_video&noheader=true" method="post" name="adminForm" id="addadminForm">
    
    <table style="text-align: left; width: 100%;" border="0" cellpadding="2"
cellspacing="2">
<tbody>
<tr>
<td style="vertical-align: top;">Title<br>
</td>
<td style="vertical-align: top;"><input name="title" id="addvtitle" type="text" class="form-control" style="width:40%;"><br>
</td>
</tr>
<tr class="vurl_yt">
<td style="vertical-align: top;">Youtube Video URL<br>
</td>
<td style="vertical-align: top;"><input name="youtube_url" id="addvid" type="text" class="form-control" style="width:40%;"><br></td>
</tr>   
</tr>
<tr>
<td style="vertical-align: top;">Width<br>
</td>
<td style="vertical-align: top;"><input name="width" id="width" type="text" class="form-control" style="width:40%;"><br></td>
</tr>
<tr>
<td style="vertical-align: top;">Height<br>
</td>
<td style="vertical-align: top;"><input name="height" id="height" type="text" class="form-control" style="width:40%;"><br></td>
</tr>
<tr>
<td style="vertical-align: top;">Autoplay<br>
</td>
<td style="vertical-align: top;"><input name="autoplay"
type="checkbox" value=1 ><br><br>
</td>
</tr>

    
<tr>
<td style="vertical-align: top;">Dim background while playing<br>
</td>
<td style="vertical-align: top;"><input name="dim"
type="checkbox" value=1><br><br>
</td>
</tr>

<!--
<tr>
<td style="vertical-align: top;">Pause when user scrolls<br>
</td>
<td style="vertical-align: top;"><input name="scroll"
type="checkbox" value=1><br>
</td>
</tr>
-->
    
<tr>
<td style="vertical-align: top;">Show Controls<br>
</td>
<td style="vertical-align: top;"><input name="show_controls"
type="checkbox" value=1><br><br>
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Auto Hide Control Bar<br>
</td>
<td style="vertical-align: top;">
<select name="auto_hide_control_bar" class="form-control" style="width:10%;"> 
<option value="true" selected >Yes</option>
<option value="false">No</option>        
</select>
<br>    
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Theme<br>
</td>
<td style="vertical-align: top;">
<select name="player_theme" class="form-control" style="width:15%;"> 
<option value="dark" selected>Dark</option>
<option value="light">Light</option>        
</select>
<br>  
</td>
</tr>
    
<tr id="video_border_color">
<td style="vertical-align: top;">Video Border color<br>    
</td>
<td style="vertical-align: top;"><input name="v_border_color" id="v_border_color" ><br><br>
</td>
</tr>    
    
<!--FOR SOCAIL SHARE-->
<tr>
<td style="vertical-align: top;">Share Video option?<br>
</td>
<td style="vertical-align: top;"><input name="s_share"
type="checkbox" value=1><br><br>
</td>
</tr>
<!--FOR SOCAIL SHARE END-->
    
<!-- FOR LOGO BRANDING -->    
    
<tr>
<td style="vertical-align: top;">Add Logo For Branding?<br>
</td>
<td style="vertical-align: top;">
<select name="logo_brand" id="logo_brand" class="form-control" style="width:10%;"> 
<option value="1">Yes</option>
<option value="0" selected >No</option>        
</select>
<br>
</td>
</tr>
    
<tr id="tr_logo_brand_position">
<td style="vertical-align: top;">Logo Position:<br>
</td>
<td style="vertical-align: top;">
<select name="logo_brand_position" id="logo_brand_position" class="form-control" style="width:20%;"> 
<option value="1">Top-Right</option>
<option value="0" selected >Top-Left</option>        
</select>
<br>
</td>
</tr>    
           
<tr id="tr_pick_logo">
<td style="vertical-align: top;">Select Logo:<br>
</td>
<td style="vertical-align: top;">   
<input id="logo_pick" class="form-control" style="width:50%;" type="text" name="logo_pick" />
<button class="logo_pick_bt btn btn-success" name="logo_pick_bt" type="button">Browse</button>
<br>    
<span style="color:blue;font-size:15px;">**Logo Size must be 60x40</span>    
<br><br>
</td>
</tr>    
    
<tr id="tr_lk_logo">
<td style="vertical-align: top;">Link For Logo:<br>
</td>
<td style="vertical-align: top;"><input name="lk_logo" id="lk_logo" type="text" class="form-control" style="width:30%;"><br><br></td>
</tr>    
    
<!-- FOR LOGO BRANDING END-->    
    
</tbody>
</table>
<br>

<input type="submit" value="Submit" name="Submit" class="btn btn-primary"><br>
<br>
    
    </form>

 <!--FORM VALIDATIONS-->
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/jquery.validity.css' , __FILE__ ); ?>" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="<?php echo plugins_url( 'js/jquery.validity.min.js' , __FILE__ ); ?>"></script>

        <script>
            
              jQuery(document).ready(function($){
    jQuery('#v_border_color').wpColorPicker();
});

             jQuery(document).ready(function($){
    jQuery('#v_border_color').wpColorPicker('color', '#00CED1');
     });
            
            jQuery(document).ready(function(e) {
    
jQuery('#tr_pick_logo').hide();
jQuery('#tr_lk_logo').hide();
jQuery('#tr_logo_brand_position').hide();    
    
   jQuery('#logo_brand').change(function () {
        if (jQuery('#logo_brand option:selected').val() == 0){    
        
            jQuery('#tr_pick_logo').hide();
            jQuery('#tr_lk_logo').hide();
            jQuery('#tr_logo_brand_position').hide();  
        }
       
       else if(jQuery('#logo_brand option:selected').val() == 1){    
        
           jQuery('#tr_pick_logo').show();
           jQuery('#tr_lk_logo').show();
           jQuery('#tr_logo_brand_position').show();  
       }
       else{
           
           //Do Nothing
       }        
});
   

}); 
               jQuery(document).ready(function() {
          
jQuery('.logo_pick_bt').click(function() {
formfield = jQuery('#logo_pick').attr('name');
tb_show('', 'media-upload.php?type=image&TB_iframe=true');
    
    window.send_to_editor = function(html) {
imgurl = jQuery('img',html).attr('src');
jQuery('#logo_pick').val(imgurl);
tb_remove();
        
    }  
return false;
});
          
});

             jQuery("#addadminForm").validity(function() {
                
                jQuery("#addvtitle").require('Title is required.');     
                jQuery("#addvid").require('Youtube URL is required');  
                jQuery("#height").require('Min height 300').match('number').range(300,10000);     
                jQuery("#width").require('Min width 300').match('number').range(300,10000);
                 
                   if(jQuery("#lk_logo").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#lk_logo").require('Logo link required').match('url');             
                    
                 }
                 
                 if(jQuery("#logo_pick").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#logo_pick").require('Logo is required');             
                    
                 }
        });
            
            
                     
    </script>
        
    
<!--FORM VALIDATIONS END--> 

<?php 
}





function prash_add_timed_optin_form(){
 
    global $wpdb;
    
    $wpdb->show_errors();
    
    $video_id = $_REQUEST['video'];
    
    
    $mode = $_POST['mode'];
    
    
    if($mode == 'add'){
      
    
    
            $save_check= $wpdb->insert($wpdb->prefix.'prash_actions', array(
		
                    'vid_id'    => stripslashes_deep($_POST['video']),
                    'form_id'    => stripslashes_deep($_POST['form_id']),
                    'act_type'    => stripslashes_deep($_POST['action_type']),
                    'show_seconds'    => stripslashes_deep($_POST['seconds_in']),
                    'entry_anim'    => stripslashes_deep($_POST['entry_anim']),
                    'exit_anim'      => stripslashes_deep($_POST['exit_anim']),
                    'on_pause'    => stripslashes_deep($_POST['show_pause']),
                    'on_end' => stripslashes_deep($_POST['show_end'])
                    ),
                            array(
                            '%d',
                            '%d',    
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%d',
                            '%d'                            
                            ));
                 
                
                if(0 < $save_check){
                    
                    wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Action Created Successfully")); exit();
                
//                echo "Action Created Successfully";

                return true;
                
            }
            else if(0 === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Action Created Successfully")); exit();
                
//                echo "Action Created Successfully";

                return true;

            }else if(false === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&error_msg=" . urlencode("Error creating action")); exit();
                
//                echo "Error creating action";

                return false;
            }
            else{
            
                //Do Nothing
            } 
                             
    }
    
    
    $video_table = $wpdb->prefix.'prash_videos';
    
    $optin_forms_table = $wpdb->prefix.'prash_optins';
    
    $video_row = $wpdb->get_row("SELECT title FROM " . $video_table . " where id = " .$video_id);

    $all_optins_sql = "select id, name from " . $optin_forms_table;
        
    $all_optins = $wpdb->get_results($all_optins_sql);
    
    
    ?>

    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>


<h2>Add optin form to video</h2>
<span>
Add an optin form to your video. The optin form will be overlayed after the number of seconds entered below.
</span><br><br>
<p><strong>Video: <?php echo $video_row->title ?> </strong></p>
<form method="post" action="admin.php?page=prash_add_timed_optin_form&noheader=true" name="addoptin" id="addoptintovideo">

    <input type="hidden" name="mode" value="add">
    <input type="hidden" name="action_type" value="optin">
    <input type="hidden" name="video" value="<?php echo $video_id ?>">
    
<table style="text-align: left; width: 60%;" border="0"
cellpadding="2" cellspacing="2">
<tbody>

<script>
    
    jQuery(document).ready(function(){
    
<?php if(count($all_optins) > 0){ ?>
    
    jQuery('#create_optin_message').hide();
  <?php }else {?>
    
    jQuery('#create_optin_message').show();
    
<?php }?>
        
    });
    
</script>    
    
<tr>
<td style="vertical-align: top;">Choose Optin Form:<br>
</td>
<td style="vertical-align: top;">
    
    <select name="form_id" class="form-control" style="width:40%;">
<?php foreach($all_optins as $single_optin){ ?>        
    <option value="<?php echo $single_optin->id ?>"><?php echo $single_optin->name ?></option> 
<?php }?>    
    </select>
    <span id="create_optin_message" style="color:#FF0000;display:none;">**No forms found! Please click "Optin Forms" to create.</span>
    <br><br>
</td>
</tr>
        
<tr>
<td style="vertical-align: top;">Show in seconds:<br>
</td>
    <td style="vertical-align: top;"><input name="seconds_in" id="seconds" type="text" class="form-control" style="width:20%;" style="width:40%;">seconds<br><br>
</td>
</tr>
    

<tr>
<td style="vertical-align: top;">Show on pause? <br>
</td>
<td style="vertical-align: top;">
    
    <select name="show_pause" class="form-control" style="width:15%;"> 
        
  <option value="1">Yes</option>
  <option value="0" selected >No</option>
        
</select>
    <br>
</td>
</tr>
<tr>
<td style="vertical-align: top;">Show at end of video?<br>
</td>
<td style="vertical-align: top;">
    
    <select name="show_end" class="form-control" style="width:15%;"> 
        
  <option value="1">Yes</option>
  <option value="0" selected >No</option>
        
</select>
    <br><br>
    </td>
</tr>
</tbody>
</table>
<br>
<br>
<input name="Submit" value="Submit" type="submit" class="btn btn-primary"><br>
</form>

<!--FORM VALIDATIONS-->
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/jquery.validity.css' , __FILE__ ); ?>" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="<?php echo plugins_url( 'js/jquery.validity.min.js' , __FILE__ ); ?>"></script>

        <script>


             jQuery("#addoptintovideo").validity(function() {
                
             jQuery("#seconds").require('Seconds required').match('number');
                  
        });
                     
    </script>
        
    
<!--FORM VALIDATIONS END--> 


    <?php
    
}




function prash_edit_timed_optin_form(){
 
    global $wpdb;
    
    $wpdb->show_errors();
    
    $video_id = $_REQUEST['video'];
    
    
    $mode = $_REQUEST['mode'];
    
    $action_id = $_REQUEST['id'];
    
    
    if($mode == 'edit'){
      
        
            $save_check = $wpdb->update( 
                        $wpdb->prefix.'prash_actions', 
                        array( 
                    'vid_id'    => stripslashes_deep($_POST['video']),
                    'form_id'    => stripslashes_deep($_POST['form_id']),
                    'act_type'    => stripslashes_deep($_POST['action_type']),
                    'show_seconds'    => stripslashes_deep($_POST['seconds_in']),
                    'entry_anim'    => stripslashes_deep($_POST['entry_anim']),
                    'exit_anim'      => stripslashes_deep($_POST['exit_anim']),
                    'on_pause'    => stripslashes_deep($_POST['show_pause']),
                    'on_end' => stripslashes_deep($_POST['show_end'])
                        ), 
                        array( 'ID' => $action_id ), 
                        array( 
                           '%d',
                            '%d',    
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%d',
                            '%d'       
                        ), 
                        array( '%d' ) 
                    );
        
                
                if(0 < $save_check){
                    
                    wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Action Edited Successfully")); exit();
                
//                echo "Action Edited Successfully";

                return true;
                
            }
            else if(0 === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Action Edited Successfully")); exit();
                
//                echo "Action Edited Successfully";

                return true;

            }else if(false === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&error_msg=" . urlencode("Error editing action")); exit();
                
//                echo "Error editing action";

                return false;
            }
            else{
            
                //Do Nothing
            }
                      
    }
    
    
    $video_table = $wpdb->prefix.'prash_videos';
    
    $actions_table = $wpdb->prefix.'prash_actions';
    
    $optin_forms_table = $wpdb->prefix.'prash_optins';
    
    $video_row = $wpdb->get_row("SELECT title FROM " . $video_table . " where id = " .$video_id);

    $all_optins_sql = "select * from " . $optin_forms_table ;
        
    $all_optins = $wpdb->get_results($all_optins_sql);
    
    $action_row = $wpdb->get_row("select * from " . $actions_table . " where id=" . $action_id);
    
    
    
    
    ?>

    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<h2>Edit optin form action </h2>

<p><strong>Video: <?php echo $video_row->title ?> </strong></p>

<form method="post" action="admin.php?page=prash_edit_timed_optin_form&noheader=true" name="addoptin" id="editoptintovideo">

    <input type="hidden" name="mode" value="edit">
    <input type="hidden" name="action_type" value="optin">
    <input type="hidden" name="id" value="<?php echo $action_id ?>">
    <input type="hidden" name="video" value="<?php echo $video_id ?>">
    
<table style="text-align: left; width: 60%;" border="0"
cellpadding="2" cellspacing="2">
<tbody>
    
<tr>
<td style="vertical-align: top;">Choose Optin Form:<br>
</td>
<td style="vertical-align: top;">
    
    <select name="form_id" class="form-control" style="width:40%;">
<?php foreach($all_optins as $single_optin){ ?>        
    <option value="<?php echo $single_optin->id ?>" <?php if($action_row->form_id == $single_optin->id) echo " selected "; ?> ><?php echo $single_optin->name ?></option>

<?php }?>        
    </select>
    
    <br>
</td>
</tr>
    
    
    
<tr>
<td style="vertical-align: top;">Show optin in seconds:<br>
</td>
<td style="vertical-align: top;"><input name="seconds_in" value="<?php echo $action_row->show_seconds; ?>" id="seconds" type="text" class="form-control" style="width:30%;"><br><br>
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Show optin on pause? <br>
</td>
<td style="vertical-align: top;">
    
    <select name="show_pause" class="form-control" style="width:15%;"> 
        
  <option value="1" <?php if($action_row->on_pause == 1) echo " selected "; ?> >Yes</option>
  <option value="0" <?php if($action_row->on_pause == 0) echo " selected "; ?> >No</option>
        
</select>
    <br>
</td>
</tr>
<tr>
<td style="vertical-align: top;">Show optin at end of video?<br>
</td>
<td style="vertical-align: top;">
    
    <select name="show_end" class="form-control" style="width:15%;"> 
        
  <option value="1" <?php if($action_row->on_end == 1) echo " selected "; ?> >Yes</option>
  <option value="0" <?php if($action_row->on_end == 0) echo " selected "; ?> >No</option>
        
</select>
    <br><br>
    </td>
</tr>
</tbody>
</table>
<br>
<br>
<input name="Submit" value="Submit" type="submit" class="btn btn-primary"><br>
</form>

<!--FORM VALIDATIONS-->
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/jquery.validity.css' , __FILE__ ); ?>" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <script src="<?php echo plugins_url( 'js/jquery.validity.min.js' , __FILE__ ); ?>"></script>

        <script>


             jQuery("#editoptintovideo").validity(function() {
                
             jQuery("#seconds").require('Seconds required').match('number');
                  
        });
                     
    </script>
        
    
<!--FORM VALIDATIONS END--> 

    <?php
    
}


function prash_add_fblike_ok(){


    global $wpdb;

    $wpdb->show_errors();

    $fbappid = stripslashes_deep($_POST['fbappid']);
    
    $seconds = stripslashes_deep($_POST['seconds']);
    
    $vid_id = stripslashes_deep($_POST['video_id']);
        
    $save_check = null;
    
    if(strlen($fbappid) > 3){
        
        $save_check= $wpdb->insert($wpdb->prefix.'prash_actions', array(
        
            'vid_id'    => stripslashes_deep($_POST['video_id']),
            'act_type'    => 'fblike',
            'show_seconds'    => stripslashes_deep($_POST['in_seconds']),
            'skipfb' => stripslashes_deep($_POST['skipfblike']),
            'skip_fb_text' => stripslashes_deep($_POST['fb_skip_text']),
            'skip_fb_text_color' => stripslashes_deep($_POST['fb_skip_text_color']),
            'fbid' => stripslashes_deep($_POST['fbappid']),
            'fb_title' => stripslashes_deep($_POST['fb_title']),
            'fb_title_color' => stripslashes_deep($_POST['fb_tcolor'])
             
            
        ), array('%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s'));
        
          if(0 < $save_check){
            
              wp_redirect("admin.php?page=prash_add_action&video=". $vid_id ."&success_msg=" . urlencode("Saved successfully")); exit();
                
//                echo "Saved successfully";

                return true;
                
            }
            else if(0 === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $vid_id ."&success_msg=" . urlencode("Saved successfully")); exit();
                
//                echo "Saved successfully";

                return true;

            }else if(false === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $vid_id ."&error_msg=" . urlencode("Error while saving form")); exit();
                
//                echo "Error while saving form";

                return false;
            }
            else{
            
                //Do Nothing
            }
        
                
    }else{
    
        $save_check= $wpdb->insert($wpdb->prefix.'prash_actions', array(

            'vid_id'    => stripslashes_deep($_POST['video_id']),
            'act_type'    => 'fblike',
            'show_seconds'    => stripslashes_deep($_POST['in_seconds']),
            'skipfb' => stripslashes_deep($_POST['skipfblike']),
            'skip_fb_text' => stripslashes_deep($_POST['fb_skip_text']),
            'skip_fb_text_color' => stripslashes_deep($_POST['fb_skip_text_color']),
            'fb_title' => stripslashes_deep($_POST['fb_title']),
            'fb_title_color' => stripslashes_deep($_POST['fb_tcolor'])
            
        ), array('%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s'));
        
        
        if(0 < $save_check){
            
            wp_redirect("admin.php?page=prash_add_action&video=". $vid_id ."&success_msg=" . urlencode("Saved successfully")); exit();
                
//                echo "Saved successfully";

                return true;
                
            }
            else if(0 === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $vid_id ."&success_msg=" . urlencode("Saved successfully")); exit();
                
//                echo "Saved successfully";

                return true;

            }else if(false === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $vid_id ."&error_msg=" . urlencode("Error while saving form")); exit();
                
//                echo "Error while saving form";

                return false;
            }
            else{
            
                //Do Nothing
            }
        
            
    }
 
    
}


function prash_add_cta_ok(){

    global $wpdb;
    
    $wpdb->show_errors();
    
    $action_type = stripslashes_deep($_POST['cta_type']);
    
    
    $video_id = $_REQUEST['video_id'];
    
    if($action_type == 'ctat'){
    
        
        $save_check= $wpdb->insert($wpdb->prefix.'prash_actions', array(

                    'vid_id'    => stripslashes_deep($_POST['video_id']),
                    'act_type'    => stripslashes_deep($_POST['cta_type']),
                    'cta_text'    => stripslashes_deep($_POST['call_to_text']),
                    'img_link'    => stripslashes_deep($_POST['img_link']),
                    'show_seconds'    => stripslashes_deep($_POST['seconds_in']),
                    'cta_text_color'    => stripslashes_deep($_POST['cta_text_color']),
                    'cta_bg_color'    => stripslashes_deep($_POST['cta_bg_color']),
                    'buy_now_code'    => stripslashes_deep($_POST['buy_now_req']),
                    'buy_now_tp'    => stripslashes_deep($_POST['buy_now_bt']),
                    'buy_now_link'    => stripslashes_deep($_POST['buy_now_link']),
                    'ct_bt_code'    => stripslashes_deep($_POST['custom_bt_req']),
                    'ct_bt_bgcolor'    => stripslashes_deep($_POST['custom_bt_color']),
                    'ct_bt_bcolor'    => stripslashes_deep($_POST['custom_bt_border_color']),
                    'ct_bt_tcolor'    => stripslashes_deep($_POST['custom_bt_text_color']),
                    'ct_bt_text'    => stripslashes_deep($_POST['custom_bt_text']),
                    'ct_bt_link'    => stripslashes_deep($_POST['custom_bt_link']),
                    'cta_template'    => stripslashes_deep($_POST['cta_temp'])
                    ),
                            array(
                            '%d',
                            '%s',
                            '%s',
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%s'    
                            ));
                 
        
                  if(0 < $save_check){
                      
                      wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Call-To-Action added successfully")); exit();
                
//                echo "Call-To-Action added successfully";

                return true;
                
            }
            else if(0 === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Call-To-Action added successfully")); exit();
                
//                echo "Call-To-Action added successfully";

                return true;

            }else if(false === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&error_msg=" . urlencode("Error adding call to action")); exit();
                
//                echo "Error adding call to action";

                return false;
            }
            else{
            
                //Do Nothing
            }
                            
                
    }
          
}

function prash_edit_fblike_form(){


    global $wpdb;
    
    $wpdb->show_errors();

    $video_id = $_REQUEST['video'];

    $action_id = $_REQUEST['id'];

//    echo "Action: " . $action_id;
    
//    echo "Video: " . $video_id;
    
    if($video_id == null || $action_id == null || $video_id == '' || $action_id == '' ){

        
        echo "Error: Incomplete values. Please try again.";

    
    }else { 

        
        if($_REQUEST['mode'] == 'edit'){
        
            
            $save_check = $wpdb->update( 
                $wpdb->prefix.'prash_actions', 
                array( 
                    'show_seconds'    => stripslashes_deep($_POST['seconds_in']),
                    'skipfb'    => stripslashes_deep($_POST['skipfblike']),
                    'skip_fb_text' => stripslashes_deep($_POST['fb_skip_text']),
                    'skip_fb_text_color' => stripslashes_deep($_POST['fb_skip_text_color']),
                    'fbid'    => stripslashes_deep($_POST['fbappid']),
                    'fb_title' => stripslashes_deep($_POST['fb_title']),
                    'fb_title_color' => stripslashes_deep($_POST['fb_tcolor']) 
                    
                ), 
                array( 'id' => $action_id ), 
                array( 
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                ), 
                array('%d')
            );
            
            if(0 < $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Edited successfully")); exit();
                
//                echo "Edited successfully";

                return true;
                
            }
            else if(0 === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Edited successfully")); exit();
                
//                echo "Edited successfully";

                return true;

            }else if(false === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&error_msg=" . urlencode("Error editing Call to action. Please try again")); exit();
                
//                echo "Error editing Call to action. Please try again";

                return false;
            }
            else{
            
                //Do Nothing
            }
            
        }
        
        $action_table = $wpdb->prefix . 'prash_actions';

        $action_row = $wpdb->get_row("SELECT * FROM " . $action_table . " where id = " .$action_id); 
        
         $video_table = $wpdb->prefix . 'prash_videos';

    $video_row = $wpdb->get_row("SELECT title FROM " . $video_table . " where id = " .$video_id);
        
?>

<style>

html{

background:#FFFFFF;
 height: 480px;
}

</style>

<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<h2>Edit Facebook "Like" Button</h2> 
<p><strong>Over video: <?php echo $video_row->title?></strong></p>

<div class="container">

<form method="post" action="admin.php?page=prash_edit_fblike_form&noheader=true" name="editfbtovideo" id="editfbtovideo"><br>
    <input type="hidden" name="video" value="<?php echo $video_id; ?>">
    <input type="hidden" name="id" value="<?php echo $action_id; ?>">
 
     <table style="text-align: left; width: 60%;" border="0"
cellpadding="2" cellspacing="2">
<tbody>
    
     <tr>  
    <td>
     Facebook App ID (optional):
    </td>    
    <td>
   <input id="fbappid" name="fbappid" value="<?php echo $action_row->fbid ?>" type="text" placeholder="Facebook App ID" class="form-control" style="width:40%;">
        <br>
    </td>
</tr> 
    
     <tr>  
    <td>
     Show after (in seconds):
    </td>    
    <td>
   <input id="fb_like_seconds" name="seconds_in" value="<?php echo $action_row->show_seconds ?>" type="text" placeholder="Enter number of seconds" class="form-control" style="width:55%;" required="">
        <br>
    </td>
</tr> 
    
     <tr>  
    <td>
     Allow user to "skip":
    </td>    
    <td>
    <input type="checkbox" <?php if($action_row->skipfb == 1) echo " checked " ?>  name="skipfblike" id="checkboxes-0" value=1>
        <br><br>
    </td>
</tr> 
    
    <tr>  
    <td>
        Skip fb text:
    </td>    
    <td>
     <input id="fb_skip_text" name="fb_skip_text" type="text" value="<?php echo $action_row->skip_fb_text ?>" placeholder="Enter Skip fb text" class="form-control" style="width:60%;" required="" maxlength="25">
        <div id="max_ch_fb_skip_text" style="color:black;"></div><br>
    </td>
</tr>

<tr>  
    <td>
        Skip fb text color:
    </td>    
    <td>
        <input name="fb_skip_text_color" id="fb_skip_text_color" value="<?php echo $action_row->skip_fb_text_color ?>">
        <br><br>
    </td>
</tr>

    <tr>  
    <td>
        Message text:
    </td>    
    <td>
     <input id="fb_title" name="fb_title" type="text" value="<?php echo $action_row->fb_title ?>" placeholder="Enter Title" class="form-control" style="width:60%;" required="" maxlength="50">
        <div id="max_ch_fb_title" style="color:black;"></div><br>
    </td>
</tr>
       
     <tr>  
    <td>
        Message color:
    </td>    
    <td>
        <input name="fb_tcolor" id="fb_tcolor" value="<?php echo $action_row->fb_title_color ?>">
        <br><br>
    </td>
</tr>
    
    </tbody>
         </table>

        <!-- Button -->
        <div class="control-group">
            <label class="control-label" for="singlebutton"></label>
            <div class="controls">
                <button id="singlebutton" name="singlebutton" class="btn btn-primary">Submit</button>
            </div>
        </div>
        <input type="hidden" name="mode" value="edit" >
        
<!--    </fieldset>-->
</form>
</div>
<!--FORM VALIDATIONS-->
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/jquery.validity.css' , __FILE__ ); ?>" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="<?php echo plugins_url( 'js/jquery.validity.min.js' , __FILE__ ); ?>"></script>

        <script>
            
               jQuery(document).ready(function(){
    jQuery('#fb_tcolor').wpColorPicker();
    jQuery('#fb_skip_text_color').wpColorPicker();
    
});

            jQuery( document ).ready(function() {    
    
    jQuery('#max_ch_fb_title').text('max characters: 50');
jQuery('#fb_title').keyup(function () {
    var max = 50;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_fb_title').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_fb_title').text('max characters: '+ch);
    }
    
});
    
});

               jQuery( document ).ready(function() {    
    
    jQuery('#max_ch_fb_skip_text').text('max characters: 25');
jQuery('#fb_skip_text').keyup(function () {
    var max = 25;
    var len = jQuery(this).val().length;
    if (len >= max) {
        jQuery('#max_ch_fb_skip_text').text('you have reached the limit');
    } else {
        var ch = max - len;
        jQuery('#max_ch_fb_skip_text').text('max characters: '+ch);
    }
    
});
    
});

             jQuery("#editfbtovideo").validity(function() {
                
             jQuery("#fb_like_seconds").require('Seconds required').match('number');
             jQuery("#fb_title").require('FB Title is required.').maxLength(50);
             jQuery("#fb_skip_text").require('Skip FB text required.').maxLength(25);       
                  
        });
                     
    </script>
        
    
<!--FORM VALIDATIONS END--> 

        
        
        
<?php        
    }
    
}



function prash_edit_cta_form(){
 
    global $wpdb;
    
    $video_id = $_REQUEST['video'];
    
    $action_id = $_REQUEST['id'];
    
    
    if($video_id == null || $action_id == null || $video_id == '' || $action_id == '' ){
    
    echo "Error: Incomplete values. Please try again.";
    
    }else { 
        
        if($_REQUEST['mode'] == 'edit'){
    
//    echo "EDITED";
    
     $save_check = $wpdb->update( 
                    $wpdb->prefix.'prash_actions', 
                    array( 
                    'vid_id'    => stripslashes_deep($_POST['video']),   
                    'act_type'    => stripslashes_deep($_POST['cta_type']),
                    'cta_text'    => stripslashes_deep($_POST['call_to_text']),
                    'img_link'    => stripslashes_deep($_POST['img_link']),
//                    'img_url'    => stripslashes_deep($_POST['img_url']),    
                    'show_seconds'    => stripslashes_deep($_POST['seconds_in']),
                    'cta_text_color'    => stripslashes_deep($_POST['cta_text_color']),
                    'cta_bg_color'    => stripslashes_deep($_POST['cta_bg_color']),
                    'buy_now_code'    => stripslashes_deep($_POST['buy_now_req']),
                    'buy_now_tp'    => stripslashes_deep($_POST['buy_now_bt']),
                    'buy_now_link'    => stripslashes_deep($_POST['buy_now_link']),
                    'ct_bt_code'    => stripslashes_deep($_POST['custom_bt_req']),
                    'ct_bt_bgcolor'    => stripslashes_deep($_POST['custom_bt_color']),
                    'ct_bt_bcolor'    => stripslashes_deep($_POST['custom_bt_border_color']),
                    'ct_bt_tcolor'    => stripslashes_deep($_POST['custom_bt_text_color']),
                    'ct_bt_text'    => stripslashes_deep($_POST['custom_bt_text']),
                    'ct_bt_link'    => stripslashes_deep($_POST['custom_bt_link']),
                    'cta_template'    => stripslashes_deep($_POST['cta_temp'])    
                    ), 
                    array( 'ID' => $action_id ), 
                    array( 
                        '%d',
                        '%s',
                        '%s',
                        '%s',
//                        '%s',
                        '%d',
                        '%s',
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ), 
                    array( '%d' ) 
                );
            
            
            if(0 < $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Call to action edited successfully")); exit();
                
//                echo "Call to action edited successfully";

                return true;
                
            }
            else if(0 === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&success_msg=" . urlencode("Call to action edited successfully")); exit();
                
//                echo "Call to action edited successfully";

                return true;

            }else if(false === $save_check){
                
                wp_redirect("admin.php?page=prash_add_action&video=". $video_id ."&error_msg=" . urlencode("Error editing Call to action. Please try again")); exit();
                
//                echo "Error editing Call to action. Please try again";

                return false;
            }
            else{
            
                //Do Nothing
            }
                
    }
        
    $video_table = $wpdb->prefix . 'prash_videos';
        
    $action_table = $wpdb->prefix . 'prash_actions';
    
    $video_row = $wpdb->get_row("SELECT * FROM " . $video_table . " where id = " .$video_id);
        
    $action_row = $wpdb->get_row("SELECT * FROM " . $action_table . " where id = " .$action_id); 
                
    ?>

<style>

 @media all {
.lightbox { display: none; }
}

html{

background:#FFFFFF;
 height: 480px;
}

</style>

<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<h2>Edit Call-To-Action</h2> 
<p><strong>On video: <?php echo $video_row->title?></strong></p>

<form method="post" action="admin.php?page=prash_edit_cta_form&noheader=true" name="editctatovideo" id="editctatovideo"><br>
    <input type="hidden" name="video" value="<?php echo $video_id; ?>">
    <input type="hidden" name="id" value="<?php echo $action_id; ?>">
    <input type="hidden" name="mode" value="edit">
    
<table style="text-align: left; width: 100%;" border="0"
cellpadding="2" cellspacing="2">
<tbody>    
<tr>
    
    <td>        
<table style="text-align: left; width: 70%;" border="0"
cellpadding="2" cellspacing="2">
<tbody>
      
<tr style="display:none;">  
    <td>
 Select type:
    </td>    
    <td>
    <select id="call_to_select" name="cta_type" class="input-xlarge"> 
    <option <?php if($action_row->act_type == 'ctat') echo ' selected '; ?> value="ctat">Call to Action</option>
   <option <?php if($action_row->act_type == 'ctai') echo ' selected '; ?> value="ctai" >Image with link</option>
    </select>
        <br><br>
    </td>
</tr>    
    
    <tr id="tr_cta_bg_color">
<td style="vertical-align: top;">Background Color<br>
    
</td>
<td style="vertical-align: top;"><input name="cta_bg_color" id="cta_bg_color" value="<?php echo $action_row->cta_bg_color ?>"><br><br>
</td>
</tr>

<tr>
<td>
    Select Template:
    </td>   
    <td>
        <label><input type="radio" name="cta_temp" value="cta_temp_custom" <?php if($action_row->cta_template == 'cta_temp_custom') echo ' checked="checked" '?>>Custom Template</label>
        <label><input type="radio" name="cta_temp" value="cta_temp1" <?php if($action_row->cta_template == 'cta_temp1') echo ' checked="checked" '?>>Template 1</label>
        <label><input type="radio" name="cta_temp" value="cta_temp2" <?php if($action_row->cta_template == 'cta_temp2') echo ' checked="checked" '?>>Template 2</label>
        <label><input type="radio" name="cta_temp" value="cta_temp3" <?php if($action_row->cta_template == 'cta_temp3') echo ' checked="checked" '?>>Template 3</label>
        <label><input type="radio" name="cta_temp" value="cta_temp4" <?php if($action_row->cta_template == 'cta_temp4') echo ' checked="checked" '?>>Template 4</label>
        <br><br>
    </td>
<tr>      
 
    <tr id="call_text">
<td style="vertical-align: top;">Call to action Content:<br>
</td>
 <td id="call_to_text" style="vertical-align: top;">
<!--<input name="call_to_text" value="<?php echo $action_row->cta_text ?>" id="cta_call_to_text" type="text" class="input-xlarge">-->
<!--<textarea id="cta_editor" name="call_to_text" class="ckeditor"><?php echo $action_row->cta_text ?></textarea>-->
<button class="add_media_cta btn btn-success" name="add_media_cta" type="button">Add media to the editor</button>
<textarea id="cta_editor" name="call_to_text" class="cta_editor" style="width:100%;"><?php echo $action_row->cta_text ?></textarea> 
<br>
<!--<div id="max_ch_cta_call_to_text" style="color:black;"></div>-->
<center><a id="cta_preview" class="btn btn-success" href="#" data-featherlight="#cta-container">Preview</a></center>     
<br><br>     
</td>
</tr>
           
<tr>
<td style="vertical-align: top;">Show CTA in seconds:<br>
</td>
<td style="vertical-align: top;"><input name="seconds_in" value="<?php echo $action_row->show_seconds ?>" id="seconds" type="text" class="form-control" style="width:20%;"><br><br>
</td>
</tr>
    
</tbody>
</table>
</td>
</tr>
<tr>    
<td style="text-align:center;">    
<!--<a id="cta_preview" class="btn btn-success" href="#" data-featherlight="#cta-container">Click to Preview CTA</a>-->

<div class="lightbox" id="cta-container" style="width:<?php echo $video_row->width?>px;text-align:center;">

    <div id="cta_bg" style="width:<?php echo $video_row->width?>px;background:white;text-align:center;">
      
         <div style="width:<?php echo $video_row->width?>px;text-align:center;">  
             
           <div id="cta_code"></div>

           </div>  
    </div>
</div>
</td>    
</tr>        
</tbody>
</table>    
<br>
<input value="Submit" name="Submit" type="submit" class="btn btn-primary"><br>
<br>
</form>

<!--FORM VALIDATIONS-->
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/jquery.validity.css' , __FILE__ ); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/featherlight.min.css' , __FILE__ ); ?>" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="<?php echo plugins_url( 'js/featherlight.min.js' , __FILE__ ); ?>"></script>
    <script src="<?php echo plugins_url( 'js/jquery.validity.min.js' , __FILE__ ); ?>"></script>
    <script src="<?php echo plugins_url( 'js/tinymce.min.js' , __FILE__ ); ?>"></script>  

        <script>

         jQuery(document).ready(function() {
                      
            jQuery('.add_media_cta').click(function() {
            formfield = jQuery('#cta_editor').attr('name');
            tb_show('', 'media-upload.php?type=image&TB_iframe=true');
                
                window.send_to_editor = function(html) {
            imgurl = jQuery('img',html).attr('src');
            tinyMCE.execCommand('mceInsertContent', false, "<img src="+imgurl+" style=\"-moz-box-shadow:none;-webkit-box-shadow:none;box-shadow:none;\">");
            tb_remove();
                    
                }  
            return false;
            });
                      
            });
            
              jQuery(document).ready(function(){
    jQuery('#cta_bg_color').wpColorPicker();
//    jQuery('#cta_text_color').wpColorPicker();
});
            
             jQuery(document).ready(function(){ 
            
             jQuery('input[type="radio"][name=cta_temp]').click(function(){
                           
            if(jQuery(this).attr("value")=="cta_temp_custom"){
              tinymce.activeEditor.setContent("");
            }     
            if(jQuery(this).attr("value")=="cta_temp1"){
              tinymce.activeEditor.setContent("<center><p><span style=\"font-size: 18pt;\">Add Header</span><br/><span style=\"font-size: 12pt;\"<strong>Put some text to describe</strong></span></p></center><br/>");
            }
            if(jQuery(this).attr("value")=="cta_temp2"){
              tinymce.activeEditor.setContent("<center><p><span style=\"font-size: 16pt;color:#008080;\">Add Header</span><br/><span style=\"font-size: 12pt;color:\"<strong><a href=\"#\" target=\"_blank\" style=\" text-decoration: none !important;color:#FFA500;\">Add some call to action</a></strong></span><br/><span style=\"font-size: 10pt;\"<strong>Add some description</strong></span></p></center><br/>");
            }
            if(jQuery(this).attr("value")=="cta_temp3"){
               tinymce.activeEditor.setContent("<center><p><a href=\"#\" target=\"_blank\" style=\" display: block;width: 150px;padding:10px;line-height: 1.4;background-color: #778899;border: 1px solid black;color:#FFFF00;font-size: 15px;text-decoration: none;text-align: center;\">Add Button Text</a><br/><span style=\"font-size: 22pt;color:#4169E1;\">Header</span><br/><span style=\"font-size: 16pt;color:#4169E1;\"<strong><a href=\"#\" target=\"_blank\" style=\" text-decoration: none !important;color:#FF6347;\">Add some call to action</a></strong></span></p></center><br/>");
            }
            if(jQuery(this).attr("value")=="cta_temp4"){
               tinymce.activeEditor.setContent("<center><p><span style=\"font-size: 18pt;color:#FFA500;\"<strong>Add Header</strong></span><a href=\"#\" target=\"_blank\" style=\" display: block;width:150px;padding:8px;line-height: 1.4;background-color: #191970;border: 1px solid black;color:#00FA9A;font-size: 15px;text-decoration: none;text-align: center;\">Add Button Text</a></p></center><br/>");
            }     
        });
             
    });


            tinymce.init({
        selector: "#cta_editor",
        height: "200",        
        plugins: [
                "advlist autolink autosave link image lists charmap preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "table contextmenu directionality emoticons template textcolor paste textcolor colorpicker textpattern"
        ],

        toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
        toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
        toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",

        menubar: false,
        toolbar_items_size: 'small',

        style_formats: [
                {title: 'Bold text', inline: 'b'},
                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                {title: 'Example 1', inline: 'span', classes: 'example1'},
                {title: 'Example 2', inline: 'span', classes: 'example2'},
                {title: 'Table styles'},
                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
        ],

        templates: [
                {title: 'Test template 1', content: 'Test 1'},
                {title: 'Test template 2', content: 'Test 2'}
        ],
        forced_root_block : "", 
        force_br_newlines : true,
        force_p_newlines : false,
        relative_urls : false,
        remove_script_host : false,
        convert_urls : true        
                     
        });
     
            
             jQuery("#editctatovideo").validity(function() {
                
//             jQuery("#cta_call_to_text").require('Call to action text required').maxLength(100);
             jQuery("#seconds").require('Call to action seconds required').match('number');
                 
                 if(jQuery("#buy_now_link").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#buy_now_link").require('Buy Now Button link required').match('url');             
                    
                 }
                 
                   if(jQuery("#custom_bt_text").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#custom_bt_text").require('Custom Button text required');             
                    
                 }
                 
                  if(jQuery("#custom_bt_link").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#custom_bt_link").require('Custom Button link required').match('url');             
                    
                 }
                    
//             jQuery("#linkurl").require('Call to action link required').match('url');     
        });
                     
              
               jQuery('#cta_preview').on('click',function(){
                   
                  
    var cta_bg = jQuery('#cta_bg_color').val();
        
    jQuery('#cta_bg').css('background',cta_bg); 
                  
        var cta_code = tinymce.get('cta_editor').getContent();
                          
         jQuery('#cta_code').html(cta_code); 
              

     });
    
    
            
</script>
<!--FORM VALIDATIONS END--> 

<?php
    
}
}





function getYouTubeVideoId($url)
{
    $video_id = false;
    $url = parse_url($url);
    if (strcasecmp($url['host'], 'youtu.be') === 0)
    {
        #### (dontcare)://youtu.be/<video id>
        $video_id = substr($url['path'], 1);
    }
    elseif (strcasecmp($url['host'], 'www.youtube.com') === 0)
    {
        if (isset($url['query']))
        {
            parse_str($url['query'], $url['query']);
            if (isset($url['query']['v']))
            {
                #### (dontcare)://www.youtube.com/(dontcare)?v=<video id>
                $video_id = $url['query']['v'];
            }
        }
        if ($video_id == false)
        {
            $url['path'] = explode('/', substr($url['path'], 1));
            if (in_array($url['path'][0], array('e', 'embed', 'v')))
            {
                #### (dontcare)://www.youtube.com/(whitelist)/<video id>
                $video_id = $url['path'][1];
            }
        }
    }
    return $video_id;
}



function prash_delete_video_do(){

     global $wpdb;

     $video_id = $_REQUEST['video'];
    
     $delete_video = $wpdb->delete( $wpdb->prefix ."prash_videos", array('id' => $video_id ));

     $delete_action_video = $wpdb->delete( $wpdb->prefix ."prash_actions", array('vid_id' => $video_id ));     
    
    if($delete_video){
        
        wp_redirect("admin.php?page=Prash_Video_List&success_msg=" . urlencode ("Video deleted successfully.")); exit();
        
//        echo "Video deleted successfully.";
        
        return true;
    }else{
        
        wp_redirect("admin.php?page=Prash_Video_List&error_msg=" . urlencode("Error while deleting video")); exit();
        
//        echo "Error while deleting video";
        
        return false;
    }
    
}



function prash_delete_video_form(){
    
      global $wpdb;
    
     $video_id = $_REQUEST['video'];
    
?>

<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<h2>Are you sure want to delete Video?</h2>

<form action="admin.php?page=prash_delete_video_do&noheader=true" method="post" name="del_video" id="del_video">
    
    <input type="hidden" name="video" value="<?php echo $video_id ?>" class="v_input">
    <input type="submit" value="Yes" name="submit" onclick="submitDetailsForm();" class="btn btn-danger">
    <input type="button" value="No" name="button" onclick="history.go(-1);" class="btn btn-success"><br>
    
</form> 

  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
 <script language="javascript" type="text/javascript">
    function submitDetailsForm() {
       jQuery("form").submit();
    }
</script>

<?php
    
}



function prash_edit_video_do(){
     
    global $wpdb;       
    
    $wpdb->show_errors();
    
    $tablename = $wpdb->prefix . 'prash_videos';
    
    $video_url = esc_html($_POST["youtube_url"]);
        
    if(isset($_POST['video'])){
        
        $video_id = $_POST['video'];
        
        $video_type = $_POST['video_type'];
        
        if(strpos($video_url, 'http') === 0)    {

        }else{
            $video_url = "http://" . $video_url;

            echo "inloop";
        }
        
        echo "VIDEO URL: " . $video_url;
        
        $youtube_id = getYouTubeVideoId($video_url);
        
        echo "VIDEO ID: " . $youtube_id;        
        
        if($youtube_id == "" || null == $youtube_id){
        
            echo "Please check the Youtube URL and try again.";
            
            return false;
        }

        
            $save_check= $wpdb->update($wpdb->prefix.'prash_videos', array(
		
                    
                    'title'    => esc_html($_POST["title"]),
                    'vid' => uniqid(),
                    'youtube'    => $youtube_id,
                    'width'      => $_POST["width"],
                    'height'    => $_POST["height"],
                    'autoplay' => $_POST["autoplay"],
                    'dim'      => $_POST["dim"],
                    'scroll_pause'      => $_POST["scroll_pause"],
                    'controls'   => $_POST["show_controls"],
                    'auto_hide_cb'   => $_POST["auto_hide_control_bar"],
                    'theme'   => $_POST["player_theme"],
                    'vbordercolor'   => $_POST["v_border_color"],
                    'social_share'   => $_POST["s_share"],
                    'logo_brand_code'   => $_POST["logo_brand"],
                    'logo_ps'   => $_POST["logo_brand_position"],
                    'logo_pick'   => $_POST["logo_pick"],
                    'logo_link'   => $_POST["lk_logo"]
                
            ), array('id' => $video_id) , array('%s', '%s', '%s', '%d','%d','%d','%d','%d', '%d', '%s', '%s','%s', '%d', '%d', '%d', '%s', '%s') );
                
        
                 if(0 < $save_check){
                     
                     wp_redirect("admin.php?page=Prash_Video_List&success_msg=" . urlencode("Video edited successfully")); exit();
                
//                echo "Video edited successfully";

                return true;
                
            }
            else if(0 === $save_check){
                
                wp_redirect("admin.php?page=Prash_Video_List&success_msg=" . urlencode("Video edited successfully")); exit();
                
//                echo "Video edited successfully";

                return true;

            }else if(false === $save_check){
                
                wp_redirect("admin.php?page=Prash_Video_List&error_msg=" . urlencode("Error editing the video. Please try again")); exit();
                
//                echo "Error editing the video. Please try again";

                return false;
            }
            else{
            
                //Do Nothing
            }
                
    }else{
    
        echo "Video ID not found";
    
    }
}



function prash_edit_video_form(){

    ?>

<p><strong><a target="_blank" href="http://agilevideoplayer.com" >Add in-video optin forms and Facebook Like Buttons - Upgrade Now! >></a></strong></p> 

    <?php
    
    global $wpdb;
    
    $tablename = $wpdb->prefix . 'prash_videos';
        
    $vid_id = null;
        
    if(isset($_REQUEST['video'])){
        
        $vid_id = $_REQUEST['video'];
    }else{
     
      

        echo "Video not found! Please try again.";


        return false;
 }
    

    $video_edit = $wpdb->get_row("SELECT * FROM " . $tablename . " where id = " .$vid_id);
    
    ?>

    <style>

html{

background:#FFFFFF;
 height: 480px;
}

</style>

<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<h2>Edit Video</h2>
<!--<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js'></script>-->

<form action="admin.php?page=prash_edit_video_do&noheader=true" method="post" name="adminForm" id="editadminForm">
    
    <table style="text-align: left; width: 100%;" border="0" cellpadding="2"
cellspacing="2">
<tbody>
<tr>
<td style="vertical-align: top;">Title<br>
</td>
<td style="vertical-align: top;"><input name="title" value="<?php echo $video_edit->title ?>" id="editvtitle" type="text" class="form-control" style="width:40%;"><br>
</td>
</tr>

<tr>
    <td style="vertical-align: top;">Youtube Video URL<br>
</td>
    <td style="vertical-align: top;"><input name="youtube_url" value="http://www.youtube.com/watch?v=<?php echo $video_edit->youtube ?>" id="editvid" type="text" class="form-control" style="width:40%;"> 
        
        <?php if(strlen($video_edit->youtube) > 3) { ?>
        
        <a id="a_yt_url" target="_blank" href="http://www.youtube.com/watch?v=<?php echo $video_edit->youtube ?>"> Click to view</a>  
                                                                        <?php }?>                                                                  
<br><br>        
</td>
</tr>

<tr>
<td style="vertical-align: top;">Width<br>
</td>
<td style="vertical-align: top;"><input name="width" value="<?php echo $video_edit->width ?>" id="width" type="text" class="form-control" style="width:40%;"><br> </td>
</tr>
<tr>
<td style="vertical-align: top;">Height<br>
</td>
<td style="vertical-align: top;"><input name="height" value="<?php echo $video_edit->height ?>" id="height" type="text" class="form-control" style="width:40%;"><br> </td>
</tr>
    <tr>
<td style="vertical-align: top;">Dim background while playing<br>
</td>
<td style="vertical-align: top;"><input name="dim"
type="checkbox" value=1 <?php if($video_edit->dim == 1) echo " checked" ?> ><br><br>
</td>
</tr>

<!--
    <tr>
<td style="vertical-align: top;">Pause when user scrolls<br>
</td>
<td style="vertical-align: top;"><input name="scroll_pause"
type="checkbox" value=1 <?php if($video_edit->scroll_pause == 1) echo " checked" ?>><br>
</td>
</tr>
-->
    
<tr>
<td style="vertical-align: top;">Autoplay<br>
</td>
<td style="vertical-align: top;"><input name="autoplay"
type="checkbox" value=1 <?php if($video_edit->autoplay == 1) echo " checked" ?> ><br><br>
</td>
</tr>
<tr>
<td style="vertical-align: top;">Show Controls<br>
</td>
<td style="vertical-align: top;"><input name="show_controls"
type="checkbox" value=1 <?php if($video_edit->controls == 1) echo " checked" ?> ><br><br>
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Auto Hide Control Bar<br>
</td>
<td style="vertical-align: top;">
<select name="auto_hide_control_bar" class="form-control" style="width:10%;"> 
<option value="true" <?php if($video_edit->auto_hide_cb == 'true') echo ' selected '?> >Yes</option>
<option value="false" <?php if($video_edit->auto_hide_cb == 'false') echo ' selected '?> >No</option>        
</select>
<br> 
</td>
</tr>
    
<tr>
<td style="vertical-align: top;">Theme<br>
</td>
<td style="vertical-align: top;">
<select name="player_theme" class="form-control" style="width:10%;"> 
<option value="dark" <?php if($video_edit->theme == 'dark') echo ' selected '?> >Dark</option>
<option value="light" <?php if($video_edit->theme == 'light') echo ' selected '?> >Light</option>        
</select>
<br><br>    
</td>
</tr>  
    
<tr id="video_border_color">
<td style="vertical-align: top;">Video Border color<br>    
</td>
<td style="vertical-align: top;"><input name="v_border_color" id="v_border_color" value="<?php echo $video_edit->vbordercolor ?>" ><br><br>
</td>
</tr>        
    
<!--FOR SOCAIL SHARE-->
<tr>
<td style="vertical-align: top;">Share Video option?<br>
</td>
<td style="vertical-align: top;"><input name="s_share"
type="checkbox" value=1 <?php if($video_edit->social_share == 1) echo " checked" ?> ><br><br>
</td>
</tr>
<!--FOR SOCAIL SHARE END-->
    
<!-- FOR LOGO BRANDING -->    
    
<tr>
    <td style="vertical-align: top;">Logo (Branding)<br>
</td>
<td style="vertical-align: top;">
<select name="logo_brand" id="logo_brand" class="form-control" style="width:10%;"> 
<option value="1" <?php if($video_edit->logo_brand_code == 1) echo " selected "; ?> >Yes</option>
<option value="0" <?php if($video_edit->logo_brand_code == 0) echo " selected "; ?> >No</option>        
</select>
<br>
</td>
</tr>
    
<tr id="tr_logo_brand_position">
<td style="vertical-align: top;">Logo Position:<br>
</td>
<td style="vertical-align: top;">
<select name="logo_brand_position" id="logo_brand_position" class="form-control" style="width:15%;"> 
<option value="1" <?php if($video_edit->logo_ps == 1) echo " selected "; ?> >Top-Right</option>
<option value="0" <?php if($video_edit->logo_ps == 0) echo " selected "; ?> >Top-Left</option>        
</select>
<br>
</td>
</tr>     
               
<tr id="tr_pick_logo">
<td style="vertical-align: top;">Select Logo:<br>
</td>
<td style="vertical-align: top;">
<input id="logo_pick" class="form-control" style="width:40%;" type="text" name="logo_pick" value="<?php echo $video_edit->logo_pick ?>"/><br>
<button class="logo_pick_bt btn btn-success" name="logo_pick_bt" type="button">Browse</button>        
<br>    
    <span style="color:blue;font-size:15px;">**Max logo size: 60x40 pixels. Larger images will be resized accordingly.</span>    
<br><br>
</td>
</tr>    
    
<tr id="tr_lk_logo">
<td style="vertical-align: top;">Link For Logo:<br>
</td>
<td style="vertical-align: top;"><input name="lk_logo" id="lk_logo" type="text" class="form-control" style="width:30%;" value="<?php echo $video_edit->logo_link ?>"><br><br></td>
</tr>    
    
<!-- FOR LOGO BRANDING END-->    
    
</tbody>
</table>
<br>

<input type="hidden" name="video" value="<?php echo $video_edit->id ?>" class="yt_input">
<input type="submit" value="Submit" name="Submit" class="btn btn-primary"><br>
<br>

    </form>

 <!--FORM VALIDATIONS-->
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/jquery.validity.css' , __FILE__ ); ?>" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="<?php echo plugins_url( 'js/jquery.validity.min.js' , __FILE__ ); ?>"></script>

        <script>

              jQuery('#editvid').keyup(function()
{
    if( jQuery(this).val() ) {
          jQuery('#a_yt_url').show();
        
        var get_yturl = jQuery(this).val();
        
        jQuery('#a_yt_url').attr('href',get_yturl);
    }
    else {
     
        jQuery('#a_yt_url').hide();
    }
});
            jQuery(document).ready(function($){
    jQuery('#v_border_color').wpColorPicker();
});
            
            jQuery(document).ready(function(e) {
    
jQuery('#tr_pick_logo').hide();
jQuery('#tr_lk_logo').hide();
jQuery('#tr_logo_brand_position').hide();     
    
   jQuery('#logo_brand').change(function () {
        if (jQuery('#logo_brand option:selected').val() == 0){    
        
            jQuery('#tr_pick_logo').hide();
            jQuery('#tr_lk_logo').hide();
            jQuery('#tr_logo_brand_position').hide(); 
        }
       
       else if(jQuery('#logo_brand option:selected').val() == 1){    
        
           jQuery('#tr_pick_logo').show();
           jQuery('#tr_lk_logo').show();
           jQuery('#tr_logo_brand_position').show(); 
       }
       else{
           
           //Do Nothing
       }        
});
   
      if (<?php echo $video_edit->logo_brand_code ?> == 0){    
        
            jQuery('#tr_pick_logo').hide();
            jQuery('#tr_lk_logo').hide();
            jQuery('#tr_logo_brand_position').hide(); 
        }
       
       else if(<?php echo $video_edit->logo_brand_code ?> == 1){    
        
           jQuery('#tr_pick_logo').show();
           jQuery('#tr_lk_logo').show();
           jQuery('#tr_logo_brand_position').show(); 
       }
       else{
           
           //Do Nothing
       } 
   

}); 
            
              jQuery(document).ready(function() {
          
jQuery('.logo_pick_bt').click(function() {
formfield = jQuery('#logo_pick').attr('name');
tb_show('', 'media-upload.php?type=image&TB_iframe=true');
    
    window.send_to_editor = function(html) {
imgurl = jQuery('img',html).attr('src');
jQuery('#logo_pick').val(imgurl);
tb_remove();
        
    }  
return false;
});
          
});

             jQuery("#editadminForm").validity(function() {
                
                jQuery("#editvtitle").require('Title is required.');     
                jQuery("#editvid").require('Youtube URL is required');  
                jQuery("#height").require('Min height 300').match('number').range(300,10000);     
                jQuery("#width").require('Min width 300').match('number').range(300,10000);
                 
                  if(jQuery("#lk_logo").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#lk_logo").require('Logo link required').match('url');             
                    
                 }
                 
                  if(jQuery("#logo_pick").is(':hidden')){
                  
                    
                }
                 else{
                    jQuery("#logo_pick").require('Logo is required');             
                    
                 }
        });
                     
    </script>
        
    
<!--FORM VALIDATIONS END--> 


<?php 
}



function prash_get_full_video_code_do(){

    ?>
<p><strong><a target="_blank" href="http://agilevideoplayer.com" >Upgrade to the full version of Agile Video Player for awesome marketing features. Click here >></a></strong></p> 
    <?php 
    
 global $wpdb;
    
    $id = $_REQUEST['video'];
    
    $yt_final_code = "";
        
    $youtube_video_table = $wpdb->prefix . 'prash_videos';
    
    $actions_table = $wpdb->prefix . 'prash_actions';
    
    $optins_table = $wpdb->prefix . 'prash_optins';
         
    $youtube_video_record_sql = "SELECT * from " . $youtube_video_table. " WHERE id = " .$id;
    
    $youtube_video_record = $wpdb->get_row($youtube_video_record_sql);
    
    $ytid = $youtube_video_record->youtube;
    
    $action_records_sql = "SELECT * from ". $actions_table . " where vid_id = " .$id;

    $action_records = $wpdb->get_results($action_records_sql);
    
//    $action_count = mysql_num_rows($action_records);
    
    $action_count = count($action_records);
    
    
    if($action_count == 0){
            
        $yt_final_code = video_shortcode($id);    
    
    }else if($action_count == 1){
        
        foreach($action_records as $single_action){
        
        if($single_action->act_type == 'optin'){
            
            $yt_final_code = video_optin_shortcode($id);
        
        }
        else if($single_action->act_type == 'fblike'){
            
            $yt_final_code = video_fb_shortcode($id);
        }    
        else{
        
            $yt_final_code = video_cta_shortcode($id);
        }
    
        }
    
    }else if($action_count == 2){
          
     $rec1 = $action_records[0];
     $rec1_type = $rec1->act_type;    
        
     $rec2 = $action_records[1];
     $rec2_type = $rec2->act_type;    
        
        
        if($rec1_type == 'optin' || $rec2_type == 'optin'){
        
            $yt_final_code = video_optin_plus_cta_shortcode($id);
            
        }else{
        
            $yt_final_code = video_fb_plus_cta_shortcode($id);
        }
            
    }
  
?>    

<style>

html{

background:#FFFFFF;
 height: 480px;
}

</style>

<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>
    
<h2>Video Code</h2>
<textarea rows="40" cols="100">
    
<!-- CSS FOR OPTIN FORMS ONLY -->
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/cta.css' , __FILE__ ); ?>" />

<!-- CSS FOR OPTIN FORMS ONLY END-->
    
<!-- FOR JQUERY-->    
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<!-- FOR JQUERY END-->
    
<!-- JQUERY FOR VIDEO PLAYER-->    
<script type="text/javascript" src="<?php echo plugins_url( 'js/jQuery.tubeplayer.min.js' , __FILE__ ); ?>"></script>
<!-- JQUERY FOR VIDEO PLAYER END-->

<!-- JQUERY FOR OPTIN FORM AND FACEBOOK LIKE -->    
<script type="text/javascript" src="<?php echo plugins_url( 'js/jquery.cookie.js' , __FILE__ ); ?>"></script>
<!-- JQUERY FOR OPTIN FORM AND FACEBOOK LIKE END-->

<!-- JQUERY FOR SHARE VIDEO ONLY -->    
<script type="text/javascript" src="<?php echo plugins_url( 'js/share.min.js' , __FILE__ ); ?>"></script>
<!-- JQUERY FOR SHARE VIDEO ONLY END-->
     
<?php echo $yt_final_code?>
</textarea>

    
<?php    
}



function prash_get_video_shortcode_do(){

    ?>
<p><strong><a target="_blank" href="http://agilevideoplayer.com" >Upgrade to the full version of Agile Video Player for awesome marketing features. Click here >></a></strong></p> 
    <?php

 global $wpdb;
    
 $id = $_REQUEST['video'];

?>

<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap.min.css' , __FILE__ ); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/bootstrap-theme.min.css' , __FILE__ ); ?>" />
<script src="<?php echo plugins_url( 'js/bootstrap.min.js' , __FILE__ ); ?>"></script>

<br/>
<h2>Video Shortcode:</h2>
Copy Paste the shortcode into page.
<br/><br/>
<textarea id="shortcode" class="form-control" style="width:20%;"><?php echo '[yt_video id = ' .$id. ']'; ?></textarea>
<br/>
<input type="button" value="Back" name="button" onclick="history.go(-1);" class="btn btn-success">
<?php

}




function load_video_scripts(){
    
    global $post;
    if( function_exists('has_shortcode') || has_shortcode( $post->post_content, 'yt_video')) {

    wp_register_style( 'font_cta', plugins_url( 'css/cta.css' , __FILE__ )  );
    wp_enqueue_style( 'font_cta' );
                
    wp_register_script( 
        'tubescript', 
        plugins_url( 'js/jQuery.tubeplayer.min.js' , __FILE__ ), 
        array( 'jquery' )
    );
    wp_enqueue_script( 'tubescript' );
        
    wp_register_script( 
        'jscook', 
        plugins_url( 'js/jquery.cookie.js' , __FILE__ ), 
        array( 'jquery' )
    );
    wp_enqueue_script( 'jscook' );   
        
     wp_register_script( 
        'jsshare', 
        plugins_url( 'js/share.min.js' , __FILE__ ), 
        array( 'jquery' )
    );
    wp_enqueue_script( 'jsshare' );  
              
    }
    
}

add_action('wp_enqueue_scripts', 'load_video_scripts');



add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );

function mw_enqueue_color_picker( $hook_suffix ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'my-script-handle', plugins_url('my-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
    wp_enqueue_media();
    wp_enqueue_script( 'custom-header' );
}

/***** FOR SHORTCODE EDITOR *****/

/*Add this code to your functions.php file of current theme OR plugin file if you're making a plugin*/
//add the button to the tinymce editor
add_action('media_buttons_context','add_my_tinymce_media_button');
function add_my_tinymce_media_button($context){

$app_logo = plugins_url( 'js/app_logo.png' , __FILE__ );

return $context.=__("
<a href=\"#TB_inline?width=480&inlineId=my_shortcode_popup&width=640&height=513\" class=\"button thickbox\" id=\"my_shortcode_popup_button\" title=\"Insert Agile Video Player Shortcode\"><img src='" . $app_logo . "'/></a>");
}

add_action('admin_footer','my_shortcode_media_button_popup');
//Generate inline content for the popup window when the "my shortcode" button is clicked
function my_shortcode_media_button_popup(){

global $wpdb;

$youtube_video_table = $wpdb->prefix . 'prash_videos';

$youtube_video_record_sql = "SELECT * from " . $youtube_video_table ;

$video_records = $wpdb->get_results($youtube_video_record_sql);

?>
  <div id="my_shortcode_popup" style="display:none;">
    <!--".wrap" class div is needed to make thickbox content look good-->
    <div class="wrap">
      <div>
        <h2>Select Video from the list below:</h2>
        <br/>
        <div class="my_shortcode_add">
        <select name="item" id="item">
                    <?php
                    foreach( $video_records as $video_record ){
                        echo '<option id="'.$video_record->id.'" value="'.$video_record->id.'">'.                                         $video_record->title.'</option>';
                              
                    }
                    ?>
                </select>
                <br/><br/>
          <!-- <input type="text" id="id_of_textbox_user_typed_in"> -->
          <button class="button-primary" id="id_of_button_clicked">Insert</button>
        </div>
      </div>
    </div>
  </div>
<?php
}

//javascript code needed to make shortcode appear in TinyMCE edtor
add_action('admin_footer','my_shortcode_add_shortcode_to_editor');
function my_shortcode_add_shortcode_to_editor(){?>
<script>
jQuery('#id_of_button_clicked ').on('click',function(){
  // var user_content = jQuery('#id_of_textbox_user_typed_in').val();
  var user_content = jQuery('#item').find(':selected').val();
  var shortcode = '[yt_video id="'+user_content+'"/]';
  if( !tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
    jQuery('textarea#content').val(shortcode);
  } else {
    tinyMCE.execCommand('mceInsertContent', false, shortcode);
  }
  //close the thickbox after adding shortcode to editor
  self.parent.tb_remove();
});
</script>
<?php
}

/***** FOR SHORTCODE EDITOR *****/

function youtube_video_shortcode($atts, $content=null){
    
    
    global $wpdb;
    
    $yt_final_code = "";
        
    $youtube_video_table = $wpdb->prefix . 'prash_videos';
    
    $actions_table = $wpdb->prefix . 'prash_actions';
    
    $optins_table = $wpdb->prefix . 'prash_optins';
    
    
    extract(shortcode_atts( array('id' => ''), $atts));
        
    $youtube_video_record_sql = "SELECT * from " . $youtube_video_table. " WHERE id = " .$id;
    
    $youtube_video_record = $wpdb->get_row($youtube_video_record_sql);
    
    $ytid = $youtube_video_record->youtube;
    
    $action_records_sql = "SELECT * from ". $actions_table . " where vid_id = ". $id;

    $action_records = $wpdb->get_results($action_records_sql);
    
//    $action_count = mysql_num_rows($action_records);
    
    $action_count = count($action_records);
    
    
    if($action_count == 0){
            
        $yt_final_code = video_shortcode($id);    
    
    }else if($action_count == 1){
        
        foreach($action_records as $single_action){
        
        if($single_action->act_type == 'optin'){
            
            $yt_final_code = video_optin_shortcode($id);
        
        }
        else if($single_action->act_type == 'fblike'){
            
            $yt_final_code = video_fb_shortcode($id);
        }    
        else{
        
            $yt_final_code = video_cta_shortcode($id);
        }
    
        }
    
    }else if($action_count == 2){
    
      
     $rec1 = $action_records[0];
     $rec1_type = $rec1->act_type;    
        
     $rec2 = $action_records[1];
     $rec2_type = $rec2->act_type;    
        
        
        if($rec1_type == 'optin' || $rec2_type == 'optin'){
        
            $yt_final_code = video_optin_plus_cta_shortcode($id);
            
        }else{
        
            $yt_final_code = video_fb_plus_cta_shortcode($id);
        }
            
    }

    
    return $yt_final_code;
            
}

add_shortcode('yt_video', 'youtube_video_shortcode');



function video_shortcode($v_id){
    
    global $wpdb;
    
    $youtube_video_table = $wpdb->prefix . 'prash_videos';
    
    $actions_table = $wpdb->prefix . 'prash_actions';
    
    $optins_table = $wpdb->prefix . 'prash_optins';
        
    $youtube_video_record_sql = "SELECT * from " . $youtube_video_table. " WHERE id = " .$v_id;
    
    $youtube_video_record = $wpdb->get_row($youtube_video_record_sql);
    
    $ytid = $youtube_video_record->vid;
    $yt_id_player = $youtube_video_record->youtube;
    $ytw = $youtube_video_record->width;
    $yth = $youtube_video_record->height;
    $autoplay = $youtube_video_record->autoplay;
    
    $show_controls = $youtube_video_record->controls;
    
    $auto_hide_cb = $youtube_video_record->auto_hide_cb;
    
    $player_theme = $youtube_video_record->theme;
    
    $video_border_color = $youtube_video_record->vbordercolor;
    
    $dim_flag = $youtube_video_record->dim;
    
    $social_share = $youtube_video_record->social_share;
    
    $logo_brand_code = $youtube_video_record->logo_brand_code;
    
    $logo_brand_pick = $youtube_video_record->logo_pick;
    
    $logo_brand_link = $youtube_video_record->logo_link;
    
    $logo_brand_ps = $youtube_video_record->logo_ps;
    
     if($dim_flag == 1){
    
    $dim_image = plugins_url( 'images/dim.png' , __FILE__ );
    
    $yt_dim_code = "<div id=\"dim_video_{$ytid}\" style=\"position:fixed;background-image:url({$dim_image});left:0; top:0; width:100%;\"></div>
    
    <script>
     jQuery(document).ready(function(){
               
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();            
        });
   
    </script>
    
    ";
    
     $yt_video_full .= $yt_dim_code;
    
     }
        
     $yt_video_full .= "

     <style>
     #yt-{$ytid} {
    position:relative;
    padding-bottom:55.50%;
    height:0;
}

#yt-{$ytid} iframe, #yt-{$ytid} object, #yt-{$ytid} embed {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
}
#ytvideowrap-{$ytid}{
    width:100%;
    height:100%;
    max-width: {$ytw}px;
}

        </style>
            
         <div id=\"ytvideowrap-{$ytid}\" style=\"z-index:102;\">
         <div id=\"yt-{$ytid}\" class=\"yt-{$ytid}\" style=\"border:4px solid {$video_border_color};\">
    ";
    
    if($logo_brand_code == 1){
     
        if($logo_brand_ps == 0){
            
             $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;left:0;top:0;z-index:101;\"></a>";
            
        }
        else if($logo_brand_ps == 1){
            
            $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;right:0;top:0;z-index:101;\"></a>";
            
        }
        else{
            //Do Nothing
        }
        
       $yt_video_full .=  $yt_logo_brand_code;
        
    }
    
    if($social_share == 1){
        
        $share_image = plugins_url( 'images/share_video.png' , __FILE__ );
        $fb_share_image = plugins_url( 'images/fb.png' , __FILE__ );
        $tw_share_image = plugins_url( 'images/tw.png' , __FILE__ );
        $gplus_share_image = plugins_url( 'images/gplus.png' , __FILE__ );
        $close_share_image = plugins_url( 'images/close.png' , __FILE__ );
        
        $yt_social_share = "<div class=\"share-video-{$ytid}\" style=\"display:none;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;width:40px;
background: black;height: 30px;z-index:100;\">
        <a href=\"#\" class=\"share-video-bt-{$ytid}\" style=\"cursor: pointer;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;\"><img style=\"border:0;\" src=\"{$share_image}\" alt=\"share-video\" width=\"32\" height=\"32\"></a>
    </div>

    <div class=\"share-video-options-{$ytid}\" style=\"width: auto;height: 40px;background: black;position: absolute;top: 0;left: 0;right: 0;z-index:102;
margin: auto;text-align:center;display: none;\">
        
     <div class=\"share-options-{$ytid}\">
        <input type=\"text\" id=\"yt-link-{$ytid}\" style=\"position: absolute;left: 5px;width:45%;font-family:Arial;font-size:1.0em;height:auto;
 background:#008B8B;border:2px solid #000000;color: #FFFFFF;\"> 
         <div class=\"share-bt-main#DC143C-{$ytid}\" style=\"position: absolute;margin-top: 8px;margin-left: 55%;\">
    <div class='share-button-{$ytid}'></div>
             </div>
    <a href=\"#\" class=\"share-close-bt-{$ytid}\" style=\"position: absolute;right: 5px;margin-top: 5px;cursor: pointer;\"><img style=\"border:0;\" src=\"{$close_share_image}\" alt=\"close_share\" width=\"30\" height=\"30\"></a>   
  </div>
    
    </div>    
    ";
    
    $yt_video_full .= $yt_social_share;
        
        
    }
    
     $yt_video_code = "
           <div id='{$ytid}'></div> 
        </div>
        </div>
      
    <script type=\"text/javascript\">
      
    var foroptin_d_{$ytid} = false; 
    var foroptin_t_{$ytid};
    
    var forcta_d_{$ytid} = false; 
    var forcta_t_{$ytid};
    
jQuery(\"#{$ytid}\").tubeplayer({
	width: {$ytw}, 
	height: {$yth}, 
	allowFullScreen: \"true\", 
	initialVideo: \"{$yt_id_player}\", 
    start: 0, 
	preferredQuality: \"default\",
    showControls: {$show_controls},
	showRelated: 0, 
	autoPlay: {$autoplay}, 
	autoHide: {$auto_hide_cb}, //for controller 
	theme: \"{$player_theme}\", 
	color: \"red\", 
	showinfo: false, 
	modestbranding: true, 
	wmode: \"transparent\", // note: transparent maintains z-index, but disables GPU acceleration
	swfobjectURL: \"http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js\",
	loadSWFObject: true, 
	iframed: true,         
    ";
    
    $yt_video_full .= $yt_video_code;
    
      if($dim_flag == 1){
        
        
        $yt_video_full .= "onPlayerPlaying: function(){
                
        jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).show();    
        
            },
    
        onPlayerPaused: function(){
        
        jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();    
        
        },";
            
      }
    
      $yt_video_full .= "});";
    
    if($social_share == 1){
        
     $yt_social_script_code = " 
    
                 jQuery( '.share-close-bt-{$ytid}' ).click(function(event) {
                   event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').hide();
             });
             
            jQuery('.yt-{$ytid}').mouseover(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').show();
            });

            jQuery('.yt-{$ytid}').mouseout(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').hide();
            });  
  
             jQuery( '.share-video-bt-{$ytid}' ).click(function(event) {
             event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').fadeIn();
                 jQuery( '#yt-link-{$ytid}' ).val(jQuery('#{$ytid}').tubeplayer('data').videoURL);
                 
             });
             
        
             config{$ytid} = {
              
              networks: {
              
                  google_plus: {
                    enabled: true,        
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}' 
                   },
                   
                  twitter: {
                    enabled: true, 
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}'    
                   },
                   
                  facebook: {
                    enabled: true,
                    load_sdk: false,         
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}',
                    app_id: 145148092322424,
                   },
                   
                  pinterest: {
                    enabled: false
                   },
                   
                  email: {
                    enabled: false
                   }
               }
             } 
             
             var share{$ytid} = new Share('.share-button-{$ytid} ',config{$ytid});
            
             ";
        
        $yt_video_full .= $yt_social_script_code; 
    }

    $yt_video_full .= "</script>";    
    
    return $yt_video_full;
        
}



function video_optin_shortcode($v_id){
    
     global $wpdb;
    
    $youtube_video_table = $wpdb->prefix . 'prash_videos';
    
    $actions_table = $wpdb->prefix . 'prash_actions';
    
    $optins_table = $wpdb->prefix . 'prash_optins';

    $youtube_video_record_sql = "SELECT * from " . $youtube_video_table. " WHERE id = " .$v_id;
    
    $youtube_video_record = $wpdb->get_row($youtube_video_record_sql);
    
    $ytid = $youtube_video_record->vid;
    $yt_id_player = $youtube_video_record->youtube;
    $ytw = $youtube_video_record->width;
    $yth = $youtube_video_record->height;
    
     $autoplay = $youtube_video_record->autoplay;
    
    $show_controls = $youtube_video_record->controls;
    
    $auto_hide_cb = $youtube_video_record->auto_hide_cb;
    
    $player_theme = $youtube_video_record->theme;
    
    $video_border_color = $youtube_video_record->vbordercolor;
    
     $dim_flag = $youtube_video_record->dim;
    
     $social_share = $youtube_video_record->social_share;
    
     $logo_brand_code = $youtube_video_record->logo_brand_code;
    
    $logo_brand_pick = $youtube_video_record->logo_pick;
    
    $logo_brand_link = $youtube_video_record->logo_link;
    
    $logo_brand_ps = $youtube_video_record->logo_ps;
    
     if($dim_flag == 1){
    
    $dim_image = plugins_url( 'images/dim.png' , __FILE__ );
    
    $yt_dim_code = "<div id=\"dim_video_{$ytid}\" style=\"position:fixed;background-image:url({$dim_image});left:0; top:0; width:100%;\"></div>
    
    <script>
     jQuery(document).ready(function(){
               
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();            
        });
   
    </script>
    
    ";
    
     $yt_video_full .= $yt_dim_code;
    
     }

        
     $yt_video_full .= "
    
     <style>
     #yt-{$ytid} {
    position:relative;
    padding-bottom:55.50%;
    height:0;
}

#yt-{$ytid} iframe, #yt-{$ytid} object, #yt-{$ytid} embed {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
}
#ytvideowrap-{$ytid}{
    width:100%;
    height:100%;
    max-width: {$ytw}px;
}

@media only screen and (max-width : 480px) {
    .yt-form-{$ytid} button[type=\"submit\"] {
        margin: 0.7em 0 0;
    }

    .yt-form-{$ytid} input:not([type]),
    .yt-form-{$ytid} input[type=\"text\"],
    .yt-form-{$ytid} input[type=\"password\"],
    .yt-form-{$ytid} input[type=\"email\"],
    .yt-form-{$ytid} input[type=\"url\"],
    .yt-form-{$ytid} input[type=\"date\"],
    .yt-form-{$ytid} input[type=\"month\"],
    .yt-form-{$ytid} input[type=\"time\"],
    .yt-form-{$ytid} input[type=\"datetime\"],
    .yt-form-{$ytid} input[type=\"datetime-local\"],
    .yt-form-{$ytid} input[type=\"week\"],
    .yt-form-{$ytid} input[type=\"number\"],
    .yt-form-{$ytid} input[type=\"search\"],
    .yt-form-{$ytid} input[type=\"tel\"],
    .yt-form-{$ytid} input[type=\"color\"],
    .yt-form-{$ytid} label {
        margin-bottom: 0.3em;
        display: block;
    }

    .pure-group input:not([type]),
    .pure-group input[type=\"text\"],
    .pure-group input[type=\"password\"],
    .pure-group input[type=\"email\"],
    .pure-group input[type=\"url\"],
    .pure-group input[type=\"date\"],
    .pure-group input[type=\"month\"],
    .pure-group input[type=\"time\"],
    .pure-group input[type=\"datetime\"],
    .pure-group input[type=\"datetime-local\"],
    .pure-group input[type=\"week\"],
    .pure-group input[type=\"number\"],
    .pure-group input[type=\"search\"],
    .pure-group input[type=\"tel\"],
    .pure-group input[type=\"color\"] {
        margin-bottom: 0;
    }

    .yt-form-{$ytid}-aligned .pure-control-group label {
        margin-bottom: 0.3em;
        text-align: left;
        display: block;
        width: 100%;
    }

    .yt-form-{$ytid}-aligned .pure-controls {
        margin: 1.5em 0 0 0;
    }

    .yt-form-{$ytid} .pure-help-inline,
    .yt-form-{$ytid}-message-inline,
    .yt-form-{$ytid}-message {
        display: block;
        font-size: 0.75em;
        padding: 0.2em 0 0.8em;
    }
}

.yt-form-{$ytid} input[type=\"text\"],
.yt-form-{$ytid} input[type=\"password\"],
.yt-form-{$ytid} input[type=\"email\"],
.yt-form-{$ytid} input[type=\"url\"],
.yt-form-{$ytid} input[type=\"date\"],
.yt-form-{$ytid} input[type=\"month\"],
.yt-form-{$ytid} input[type=\"time\"],
.yt-form-{$ytid} input[type=\"datetime\"],
.yt-form-{$ytid} input[type=\"datetime-local\"],
.yt-form-{$ytid} input[type=\"week\"],
.yt-form-{$ytid} input[type=\"number\"],
.yt-form-{$ytid} input[type=\"search\"],
.yt-form-{$ytid} input[type=\"tel\"],
.yt-form-{$ytid} input[type=\"color\"],
.yt-form-{$ytid} select,
.yt-form-{$ytid} textarea {
    padding: 0.5em 0.6em;
    display: inline-block;
    border: 1px solid #ccc;
    box-shadow: inset 0 1px 3px #ddd;
    border-radius: 4px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}

.yt-form-{$ytid} input:not([type]) {
    padding: 0.5em 0.6em;
    display: inline-block;
    border: 1px solid #ccc;
    box-shadow: inset 0 1px 3px #ddd;
    border-radius: 4px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}


.yt-form-{$ytid} input[type=\"color\"] {
    padding: 0.2em 0.5em;
}


.yt-form-{$ytid} input[type=\"text\"]:focus,
.yt-form-{$ytid} input[type=\"password\"]:focus,
.yt-form-{$ytid} input[type=\"email\"]:focus,
.yt-form-{$ytid} input[type=\"url\"]:focus,
.yt-form-{$ytid} input[type=\"date\"]:focus,
.yt-form-{$ytid} input[type=\"month\"]:focus,
.yt-form-{$ytid} input[type=\"time\"]:focus,
.yt-form-{$ytid} input[type=\"datetime\"]:focus,
.yt-form-{$ytid} input[type=\"datetime-local\"]:focus,
.yt-form-{$ytid} input[type=\"week\"]:focus,
.yt-form-{$ytid} input[type=\"number\"]:focus,
.yt-form-{$ytid} input[type=\"search\"]:focus,
.yt-form-{$ytid} input[type=\"tel\"]:focus,
.yt-form-{$ytid} input[type=\"color\"]:focus,
.yt-form-{$ytid} select:focus,
.yt-form-{$ytid} textarea:focus {
    outline: 0;
    outline: thin dotted \9; /* IE6-9 */
    border-color: #129FEA;
}

.yt-form-{$ytid} input:not([type]):focus {
    outline: 0;
    outline: thin dotted \9; /* IE6-9 */
    border-color: #129FEA;
}

.yt-form-{$ytid} input[type=\"file\"]:focus,
.yt-form-{$ytid} input[type=\"radio\"]:focus,
.yt-form-{$ytid} input[type=\"checkbox\"]:focus {
    outline: thin dotted #333;
    outline: 1px auto #129FEA;
}
.yt-form-{$ytid} .pure-checkbox,
.yt-form-{$ytid} .pure-radio {
    margin: 0.5em 0;
    display: block;
}

.yt-form-{$ytid} input[type=\"text\"][disabled],
.yt-form-{$ytid} input[type=\"password\"][disabled],
.yt-form-{$ytid} input[type=\"email\"][disabled],
.yt-form-{$ytid} input[type=\"url\"][disabled],
.yt-form-{$ytid} input[type=\"date\"][disabled],
.yt-form-{$ytid} input[type=\"month\"][disabled],
.yt-form-{$ytid} input[type=\"time\"][disabled],
.yt-form-{$ytid} input[type=\"datetime\"][disabled],
.yt-form-{$ytid} input[type=\"datetime-local\"][disabled],
.yt-form-{$ytid} input[type=\"week\"][disabled],
.yt-form-{$ytid} input[type=\"number\"][disabled],
.yt-form-{$ytid} input[type=\"search\"][disabled],
.yt-form-{$ytid} input[type=\"tel\"][disabled],
.yt-form-{$ytid} input[type=\"color\"][disabled],
.yt-form-{$ytid} select[disabled],
.yt-form-{$ytid} textarea[disabled] {
    cursor: not-allowed;
    background-color: #eaeded;
    color: #cad2d3;
}

.yt-form-{$ytid} input:not([type])[disabled] {
    cursor: not-allowed;
    background-color: #eaeded;
    color: #cad2d3;
}
.yt-form-{$ytid} input[readonly],
.yt-form-{$ytid} select[readonly],
.yt-form-{$ytid} textarea[readonly] {
    background: #eee; /* menu hover bg color */
    color: #777; /* menu text color */
    border-color: #ccc;
}

.yt-form-{$ytid} input:focus:invalid,
.yt-form-{$ytid} textarea:focus:invalid,
.yt-form-{$ytid} select:focus:invalid {
    color: #b94a48;
    border-color: #ee5f5b;
}
.yt-form-{$ytid} input:focus:invalid:focus,
.yt-form-{$ytid} textarea:focus:invalid:focus,
.yt-form-{$ytid} select:focus:invalid:focus {
    border-color: #e9322d;
}
.yt-form-{$ytid} input[type=\"file\"]:focus:invalid:focus,
.yt-form-{$ytid} input[type=\"radio\"]:focus:invalid:focus,
.yt-form-{$ytid} input[type=\"checkbox\"]:focus:invalid:focus {
    outline-color: #e9322d;
}
.yt-form-{$ytid} select {
    border: 1px solid #ccc;
    background-color: white;
}
.yt-form-{$ytid} select[multiple] {
    height: auto;
}
.yt-form-{$ytid} label {
    margin: 0.5em 0 0.2em;
}
.yt-form-{$ytid} fieldset {
    margin: 0;
    padding: 0.35em 0 0.75em;
    border: 0;
}
.yt-form-{$ytid} legend {
    display: block;
    width: 100%;
    padding: 0.3em 0;
    margin-bottom: 0.3em;
    color: #333;
    border-bottom: 1px solid #e5e5e5;
}

.yt-form-{$ytid}-stacked input[type=\"text\"],
.yt-form-{$ytid}-stacked input[type=\"password\"],
.yt-form-{$ytid}-stacked input[type=\"email\"],
.yt-form-{$ytid}-stacked input[type=\"url\"],
.yt-form-{$ytid}-stacked input[type=\"date\"],
.yt-form-{$ytid}-stacked input[type=\"month\"],
.yt-form-{$ytid}-stacked input[type=\"time\"],
.yt-form-{$ytid}-stacked input[type=\"datetime\"],
.yt-form-{$ytid}-stacked input[type=\"datetime-local\"],
.yt-form-{$ytid}-stacked input[type=\"week\"],
.yt-form-{$ytid}-stacked input[type=\"number\"],
.yt-form-{$ytid}-stacked input[type=\"search\"],
.yt-form-{$ytid}-stacked input[type=\"tel\"],
.yt-form-{$ytid}-stacked input[type=\"color\"],
.yt-form-{$ytid}-stacked select,
.yt-form-{$ytid}-stacked label,
.yt-form-{$ytid}-stacked textarea {
    display: block;
    margin: 0.25em 0;
}

.yt-form-{$ytid}-stacked input:not([type]) {
    display: block;
    margin: 0.25em 0;
}
.yt-form-{$ytid}-aligned input,
.yt-form-{$ytid}-aligned textarea,
.yt-form-{$ytid}-aligned select,
.yt-form-{$ytid}-aligned .pure-help-inline,
.yt-form-{$ytid}-message-inline {
    display: inline-block;
    *display: inline;
    *zoom: 1;
    vertical-align: middle;
}
.yt-form-{$ytid}-aligned textarea {
    vertical-align: top;
}

/* Aligned Forms */
.yt-form-{$ytid}-aligned .pure-control-group {
    margin-bottom: 0.5em;
}
.yt-form-{$ytid}-aligned .pure-control-group label {
    text-align: right;
    display: inline-block;
    vertical-align: middle;
    width: 10em;
    margin: 0 1em 0 0;
}
.yt-form-{$ytid}-aligned .pure-controls {
    margin: 1.5em 0 0 10em;
}

/* Rounded Inputs */
.yt-form-{$ytid} input.pure-input-rounded,
.yt-form-{$ytid} .pure-input-rounded {
    border-radius: 2em;
    padding: 0.5em 1em;
}

/* Grouped Inputs */
.yt-form-{$ytid} .pure-group fieldset {
    margin-bottom: 10px;
}
.yt-form-{$ytid} .pure-group input {
    display: block;
    padding: 10px;
    margin: 0;
    border-radius: 0;
    position: relative;
    top: -1px;
}
.yt-form-{$ytid} .pure-group input:focus {
    z-index: 2;
}
.yt-form-{$ytid} .pure-group input:first-child {
    top: 1px;
    border-radius: 4px 4px 0 0;
}
.yt-form-{$ytid} .pure-group input:last-child {
    top: -2px;
    border-radius: 0 0 4px 4px;
}
.yt-form-{$ytid} .pure-group button {
    margin: 0.35em 0;
}

.yt-form-{$ytid} .pure-input-1 {
    width: 100%;
}
.yt-form-{$ytid} .pure-input-2-3 {
    width: 66%;
}
.yt-form-{$ytid} .pure-input-1-2 {
    width: 50%;
}
.yt-form-{$ytid} .pure-input-1-3 {
    width: 33%;
}
.yt-form-{$ytid} .pure-input-1-4 {
    width: 25%;
}

/* Inline help for forms */

.yt-form-{$ytid} .pure-help-inline,
.yt-form-{$ytid}-message-inline {
    display: inline-block;
    padding-left: 0.3em;
    color: #666;
    vertical-align: middle;
    font-size: 0.875em;
}

/* Block help for forms */
.yt-form-{$ytid}-message {
    display: block;
    color: #666;
    font-size: 0.875em;
}

        </style>
    
         <div id=\"ytvideowrap-{$ytid}\" style=\"z-index:102;\">
         <div id=\"yt-{$ytid}\" class=\"yt-{$ytid}\" style=\"border:4px solid {$video_border_color};\">";
    
     if($logo_brand_code == 1){
     
        if($logo_brand_ps == 0){
            
             $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;left:0;top:0;z-index:101;\"></a>";
            
        }
        else if($logo_brand_ps == 1){
            
            $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;right:0;top:0;z-index:101;\"></a>";
            
        }
        else{
            //Do Nothing
        }
        
       $yt_video_full .=  $yt_logo_brand_code;
        
    }
    
    if($social_share == 1){
        
        $share_image = plugins_url( 'images/share_video.png' , __FILE__ );
        $fb_share_image = plugins_url( 'images/fb.png' , __FILE__ );
        $tw_share_image = plugins_url( 'images/tw.png' , __FILE__ );
        $gplus_share_image = plugins_url( 'images/gplus.png' , __FILE__ );
        $close_share_image = plugins_url( 'images/close.png' , __FILE__ );
        
        $yt_social_share = " <div class=\"share-video-{$ytid}\" style=\"display: none;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;width: 40px;z-index:100;
background: black;height: 30px;\">
        <a href=\"#\" class=\"share-video-bt-{$ytid}\" style=\"cursor: pointer;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;\"><img style=\"border:0;\" src=\"{$share_image}\" alt=\"share-video\" width=\"32\" height=\"32\"></a>
    </div>
    
    <div class=\"share-video-options-{$ytid}\" style=\"width: auto;height: 40px;background: black;position: absolute;top: 0;left: 0;right: 0;z-index:102;
margin: auto;text-align:center;display: none;\">
        
     <div class=\"share-options-{$ytid}\">
        <input type=\"text\" id=\"yt-link-{$ytid}\" style=\"position: absolute;left: 5px;width:45%;font-family:Arial;font-size:1.0em;height:auto;
 background:#008B8B;border:2px solid #000000;color: #FFFFFF;\"> 
         <div class=\"share-bt-main-{$ytid}\" style=\"position: absolute;margin-top: 8px;margin-left: 55%;\">
       <div class='share-button-{$ytid}'></div>
             </div>
    <a href=\"#\" class=\"share-close-bt-{$ytid}\" style=\"position: absolute;right: 5px;margin-top: 5px;cursor: pointer;\"><img style=\"border:0;\" src=\"{$close_share_image}\" alt=\"close_share\" width=\"30\" height=\"30\"></a>   
  </div>
    
    </div>";
    
    $yt_video_full .= $yt_social_share;
        
        
    }
    
    
    $yt_video_code = "
          <div id='{$ytid}'></div> 
        
      ";

    $yt_video_full .= $yt_video_code;
    
   $optin_form_code = "";
    
    $cta_code = "";
    
    $entry_anim = "";
    
    $exit_anim = "";
    
    $form_bg = "";
    
    $title_color = "";
    $title_text = "";
    
    $content_color = "";
    $content_text = "";
    
    $button_color = "";
    $button_text = "";
    $button_text_color = "";
    
    $emailvalid_text_color = "";
    
    $optin_border_color = "";
    
    $optin_skip_text_color = "";
    
    $show_seconds = 0;
    
    $on_pause_show_optin = 0;
    
    $on_end_show_optin = 0;
    
    $optin_form_submit_url = "";
    
    
    $youtube_actions_record_sql = "SELECT * from " . $actions_table. " WHERE vid_id = " .$v_id;
    
    $actions_records = $wpdb->get_results($youtube_actions_record_sql);
    
        foreach($actions_records as $single_action){
        
            //set all variables to use 
            
            $entry_anim = $single_action->entry_anim;
            
            $exit_anim = $single_action->exit_anim;
            
            $on_pause = $single_action->on_pause;
            
            $on_end_show_optin = $single_action->on_end;
            
            $show_seconds = $single_action->show_seconds;
            
            $on_pause_show_optin = $single_action->on_pause;
            
            $optin_form_submit_url = plugins_url( 'emailmanager.php' , __FILE__ );
            
            $optin_form_id = $single_action->form_id;
            
                             
                $optin_detail = $wpdb->get_row("SELECT * from " . $optins_table. " WHERE id = " .$single_action->form_id);
                
                $form_bg = $optin_detail->bg;
    
                $title_color = $optin_detail->headcolor;
                $title_text =  $optin_detail->headtext;
    
                $content_color = $optin_detail->msgcolor;
                $content_text = $optin_detail->msgtext;
                
                $button_color = $optin_detail->butcolor;
                $button_text = $optin_detail->buttext;
                $button_text_color = $optin_detail->buttextcolor;
                
                $emailvalid_text_color = $optin_detail->emailvalidtxtcolor;
            
                $optin_border_color = $optin_detail->optinbordercolor;
            
                $optin_skip_text_color = $optin_detail->optinskiptxtcolor;

                $optin_skip_text = $optin_detail->skip_bt_text;
            
                $optin_timer_value = $single_action->show_seconds;
                
                $skip_optin_form = $optin_detail->skip_optin_text;
            
                $ar_type_optin_form = $optin_detail->ar;
             
        }
            
            
$optin_form_fragment1 = "

          <div id=\"{$ytid}-box\" class=\"{$ytid}-box\" style=\"width:100%;height:100%;position: absolute;display: none;bottom: 0;left: 0;right:0;margin: auto;text-align:center;-webkit-border-radius: 0px;-moz-border-radius: 0px;border-radius: 0px;background:rgba(255, 0, 0, 0);z-index:101;\"></div>
                
         <div id=\"{$ytid}-form\" class=\"{$ytid}-form\" required style=\"background:{$form_bg};position: absolute;width:93%;height:145px;top: 0;bottom: 0;left: 0;right: 0;margin: auto;text-align:center;display: none;padding:5px; -webkit-border-radius: 20px;-moz-border-radius: 20px;border-radius: 20px;border:4px solid {$optin_border_color};z-index:101;\">";
            
            $yt_video_full .= $optin_form_fragment1; 
            
            
                     if($skip_optin_form == 1){
                         
                  $yt_video_full .="       
                     <a href=\"#\" id=\"{$ytid}-close-optin\" style=\"padding:1px;text-align: center;position: absolute;bottom: 0;
left: 0;right: 0;margin: auto;text-align:center;color:{$optin_skip_text_color};font-weight:bold;text-decoration: none;width: -moz-fit-content;
width: -webkit-fit-content;width: fit-content;\">{$optin_skip_text}</a>";
                         
                }


         $yt_video_full .="
                         
   <div class=\"{$ytid}-form-text-title\" id=\"{$ytid}-form-text-title\" style=\"color:{$title_color};font-size: 16px;text-align: center;margin-top: 1%;word-wrap:break-word;\">{$title_text}</div>
     
         <div class=\"{$ytid}-form-text-content\" id=\"{$ytid}-form-text-content\" style=\"color:{$content_color};text-align: center;word-wrap:break-word;font-size:15px;line-height: 100%;\">{$content_text}</div>
     
     <div id=\"{$ytid}-error-em\" class=\"{$ytid}-error-em\" style=\"display: none;text-align: center;font-size: 14px;color:{$emailvalid_text_color};font-style: italic;\">Please enter a valid email id</div>
           
            <form name=\"{$ytid}-cform\" id=\"{$ytid}-cform\" class=\"yt-form-{$ytid}\" action=\"#\" method=\"post\" target=\"_blank\" style=\"margin-top:1%\">
         <div id=\"em_submit\" style=\"width:100%;padding:2px;display:inline-block;\"> 
         <input id=\"{$ytid}-em\" name=\"email\" type=\"email\" placeholder=\"Email\" style=\"width:60%;margin-right:1%;display:inline-block;\">
         <input type=\"hidden\" name=\"optinformid\" value=\"{$optin_form_id}\">
    <a href=\"#\" id=\"{$ytid}-form_submit_bt\" class=\"{$ytid}-form_submit_bt\" style=\"display:inline-block;font-size:10px;font-family:Arial;font-weight:bold;-moz-border-radius:8px;-webkit-border-radius:8px;border-radius:8px;text-decoration:none;background-color:{$button_color};color:{$button_text_color};padding-left:1%;padding-right:1%;padding-top:7px;padding-bottom:7px;text-align:center;\">{$button_text}</a>
          </div>
</form>  
</div>
</div>
</div>";
                        
   $yt_video_full .="<script type=\"text/javascript\">
      
    var foroptin_d_{$ytid} = false; 
    var foroptin_t_{$ytid};
    
    var forcta_d_{$ytid} = false; 
    var forcta_t_{$ytid};
    
jQuery(\"#{$ytid}\").tubeplayer({
	width: {$ytw}, 
	height: {$yth}, 
	allowFullScreen: \"true\", 
	initialVideo: \"{$yt_id_player}\", 
    start: 0, 
	preferredQuality: \"default\",
    showControls: {$show_controls},
	showRelated: 0, 
	autoPlay: {$autoplay}, 
	autoHide: {$auto_hide_cb}, //for controller 
	theme: \"{$player_theme}\", 
	color: \"red\", 
	showinfo: false, 
	modestbranding: true, 
	wmode: \"transparent\", // note: transparent maintains z-index, but disables GPU acceleration
	swfobjectURL: \"http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js\",
	loadSWFObject: true, 
	iframed: true,
    
    onPlayerPlaying: function(){
            
        t_{$ytid} = setInterval(hndoptin_{$ytid},100);
         
    ";
   
        if($dim_flag == 1){
        
            $yt_dim = "
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).show();";    
        
         $yt_video_full .= $yt_dim;
            
        }
        
        $yt_video_full .=  "},"; //closes onPlayerPlaying
    
        $add_on_pause = "
        onPlayerPaused: function(){
        
        ";
            
             $yt_video_full .= $add_on_pause;
        
            
            if($dim_flag == 1){
            
                $add_on_pause_dim .= " jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();";
                    
            $yt_video_full .= $add_on_pause_dim;     
            }
            
            
            if($on_pause_show_optin == 1){
            
                
                $showing_optin_pause = " 
                
                 if(jQuery.cookie('{$ytid}') == '{$ytid}'){
                 
               jQuery('#{$ytid}-form').hide();
               jQuery('#{$ytid}-box').hide(); 
             }
             else{
             
               jQuery('#{$ytid}-form').show();
               jQuery('#{$ytid}-box').show(); 
             
             }
                ";
                
                $yt_video_full .= $showing_optin_pause;
            }
            
        $close_on_pause = "},";
            
        $yt_video_full .= $close_on_pause;    
        
        //for on ended
        
        if($on_end_show_optin == 1){
        
            $start_on_end = "
            
            onPlayerEnded: function(){
            
            if(jQuery.cookie('{$ytid}') == '{$ytid}'){
        
            jQuery('#{$ytid}-form').hide();
             jQuery('#{$ytid}-box').hide();
             
             }else
             {
              jQuery('#{$ytid}-form').show();
             jQuery('#{$ytid}-box').show();
             
             }
             },";          
            
            $yt_video_full .= $start_on_end;
        }
        
        
        //for on ended close
        
        
     $yt_video_full .= "});"; //closes main video code
          
        $optin_tmp_show_seconds = $optin_timer_value + 1;
    
        $handle_optin = "
        
        function hndoptin_{$ytid}() {
        
         if(jQuery.cookie('{$ytid}') == '{$ytid}'){
                   jQuery('#{$ytid}-form').hide();
                   jQuery('#{$ytid}-box').hide(); 
                    }

            else {    
        
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$optin_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$optin_tmp_show_seconds} && !foroptin_d_{$ytid}) {
              jQuery(\"#{$ytid}\").tubeplayer(\"pause\");
             jQuery('#{$ytid}-form').show();
             jQuery('#{$ytid}-box').show();  
             foroptin_d_{$ytid} = true;
        }
        
        }
    }
    
           jQuery( \"#{$ytid}-cform\" ).submit(function( event ) {
           event.preventDefault();
           
           function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
    return pattern.test(emailAddress);
};
           
           var emailaddress = jQuery(\"#{$ytid}-em\").val();
          
             if( isValidEmailAddress( emailaddress ) )  
          
          {
         
           jQuery('#{$ytid}-form').hide(); jQuery('#{$ytid}').tubeplayer('play');jQuery('#{$ytid}-box').hide(); jQuery.cookie('{$ytid}', '{$ytid}',{ expires: 7, path: '/' }); jQuery('#{$ytid}-error-em').hide();
          
           var email = jQuery(\"#{$ytid}-em\").val();
//                    var dataString = 'email=' + email + '&id=' + {$optin_form_id};
                    
                    jQuery.ajax({
                            type: \"POST\",
                            url: \"{$optin_form_submit_url}\",
                            data: jQuery('#{$ytid}-cform').serialize(),
                            success: function(data){
                            console.log('TEST DATA' + data);
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                            console.log(xhr.status);
                            console.log(xhr.responseText);
                            console.log(thrownError);
                            }

                            });
                
          
          
          return true;
          
          }
          else{
               
         jQuery('#{$ytid}-error-em').show(); 
         jQuery(\"#{$ytid}-em\").css({'border-color':'#FF0000','color':'#FF0000'});
         
          return false;
          
          }
          
                 
           });
           
            jQuery('#{$ytid}-form_submit_bt').click(function(e)
                {
                    e.preventDefault();
                    jQuery(\"form#{$ytid}-cform\").submit();
 
                });";
    
          $yt_video_full .= $handle_optin;

                      
               if($skip_optin_form == 1){
                   
                   $yt_video_full .= "
                   jQuery( \"#{$ytid}-close-optin\" ).click(function(e) {
                e.preventDefault();
        jQuery(\".{$ytid}-form\").hide();      
        jQuery(\"#{$ytid}\").tubeplayer(\"play\");
        jQuery('#{$ytid}-box').hide();

});

        "; 
    
                          }
    
    
    if($social_share == 1){
        
     $yt_social_script_code = " jQuery( '.share-close-bt-{$ytid}' ).click(function(event) {
                  event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').hide();
             });
             
            jQuery('.yt-{$ytid}').mouseover(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').show();
            });

            jQuery('.yt-{$ytid}').mouseout(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').hide();
            });  
 
             jQuery( '.share-video-bt-{$ytid}' ).click(function(event) {
                 event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').fadeIn();
                 jQuery( '#yt-link-{$ytid}' ).val((jQuery('#{$ytid}').tubeplayer('data').videoURL));
             });
             
              config{$ytid} = {
              
              networks: {
              
                  google_plus: {
                    enabled: true,        
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}' 
                   },
                   
                  twitter: {
                    enabled: true, 
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}'    
                   },
                   
                  facebook: {
                    enabled: true,
                    load_sdk: false,         
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}',
                    app_id: 145148092322424,
                   },
                   
                  pinterest: {
                    enabled: false
                   },
                   
                  email: {
                    enabled: false
                   }
               }
             } 
             
             var share{$ytid} = new Share('.share-button-{$ytid}',config{$ytid});
             
    
             ";
        
        $yt_video_full .= $yt_social_script_code; 
    }
                
    
    $yt_video_full .= "</script>";    
    
    
     return $yt_video_full;
    
}








function video_cta_shortcode($v_id){
    
      global $wpdb;
    
    $youtube_video_table = $wpdb->prefix . 'prash_videos';
    
    $actions_table = $wpdb->prefix . 'prash_actions';
    
    $optins_table = $wpdb->prefix . 'prash_optins';
        
    $youtube_video_record_sql = "SELECT * from " . $youtube_video_table. " WHERE id = " .$v_id;
    
    $youtube_video_record = $wpdb->get_row($youtube_video_record_sql);
    
    $ytid = $youtube_video_record->vid;
    $yt_id_player = $youtube_video_record->youtube;
    $ytw = $youtube_video_record->width;
    $yth = $youtube_video_record->height;
    
       $autoplay = $youtube_video_record->autoplay;
    
    $show_controls = $youtube_video_record->controls;
    
    $auto_hide_cb = $youtube_video_record->auto_hide_cb;
    
    $player_theme = $youtube_video_record->theme;
    
    $video_border_color = $youtube_video_record->vbordercolor;
    
     $dim_flag = $youtube_video_record->dim;
    
     $social_share = $youtube_video_record->social_share;
    
      $logo_brand_code = $youtube_video_record->logo_brand_code;
    
    $logo_brand_pick = $youtube_video_record->logo_pick;
    
    $logo_brand_link = $youtube_video_record->logo_link;
    
    $logo_brand_ps = $youtube_video_record->logo_ps;
    
     if($dim_flag == 1){
    
    $dim_image = plugins_url( 'images/dim.png' , __FILE__ );
    
    $yt_dim_code = "<div id=\"dim_video_{$ytid}\" style=\"position:fixed;background-image:url({$dim_image});left:0; top:0; width:100%;\"></div>
    
    <script>
     jQuery(document).ready(function(){
               
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();            
        });
   
    </script>
    
    ";
    
     $yt_video_full .= $yt_dim_code;
    
     }
        
     $yt_video_full .= "
    
      <style>
     #yt-{$ytid} {
    position:relative;
    padding-bottom:55.50%;
    height:0;
}

#yt-{$ytid} iframe, #yt-{$ytid} object, #yt-{$ytid} embed {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
}
#ytvideowrap-{$ytid}{
    width:100%;
    height:100%;
    max-width: {$ytw}px;
}

        </style>
           
         <div id=\"ytvideowrap-{$ytid}\" style=\"z-index:102;\">
         <div id=\"yt-{$ytid}\" class=\"yt-{$ytid}\" style=\"border:4px solid {$video_border_color};\">";
    
     if($logo_brand_code == 1){
     
        if($logo_brand_ps == 0){
            
             $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;left:0;top:0;z-index:101;\"></a>";
            
        }
        else if($logo_brand_ps == 1){
            
            $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;right:0;top:0;z-index:101;\"></a>";
            
        }
        else{
            //Do Nothing
        }
        
       $yt_video_full .=  $yt_logo_brand_code;
        
    }
    
     if($social_share == 1){
         
        $share_image = plugins_url( 'images/share_video.png' , __FILE__ );
        $fb_share_image = plugins_url( 'images/fb.png' , __FILE__ );
        $tw_share_image = plugins_url( 'images/tw.png' , __FILE__ );
        $gplus_share_image = plugins_url( 'images/gplus.png' , __FILE__ );
        $close_share_image = plugins_url( 'images/close.png' , __FILE__ );
        
        $yt_social_share = " <div class=\"share-video-{$ytid}\" style=\"display: none;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;width: 40px;z-index:100;
background: black;height: 30px;\">
        <a href=\"#\" class=\"share-video-bt-{$ytid}\" style=\"cursor: pointer;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;\"><img style=\"border:0;\" src=\"{$share_image}\" alt=\"share-video\" width=\"32\" height=\"32\"></a>
    </div>
    
    <div class=\"share-video-options-{$ytid}\" style=\"width: auto;height: 40px;background: black;position: absolute;top: 0;left: 0;right: 0;z-index:102;
margin: auto;text-align:center;display: none;\">
        
     <div class=\"share-options-{$ytid}\">
        <input type=\"text\" id=\"yt-link-{$ytid}\" style=\"position: absolute;left: 5px;width:45%;font-family:Arial;font-size:1.0em;height:auto;
 background:#008B8B;border:2px solid #000000;color: #FFFFFF;\"> 
         <div class=\"share-bt-main-{$ytid}\" style=\"position: absolute;margin-top: 8px;margin-left: 55%;\">
      <div class='share-button-{$ytid}'></div>
             </div>
    <a href=\"#\" class=\"share-close-bt-{$ytid}\" style=\"position: absolute;right: 5px;margin-top: 5px;cursor: pointer;\"><img style=\"border:0;\" src=\"{$close_share_image}\" alt=\"close_share\" width=\"30\" height=\"30\"></a>   
  </div>
    
    </div>";
    
    $yt_video_full .= $yt_social_share;
        
        
    }
    
    
    $yt_video_code = "
          <div id='{$ytid}'></div> 
    </div>
      ";

    $yt_video_full .= $yt_video_code;
    
     $optin_form_code = "";
    
    $cta_code = "";
    
    $entry_anim = "";
    
    $exit_anim = "";
    
    $form_bg = "";
    
    $title_color = "";
    $title_text = "";
    
    $content_color = "";
    $content_text = "";
    
    $button_color = "";
    $button_text = "";
    $button_text_color = "";
    
    $emailvalid_text_color = "";
    $optin_skip_text_color = "";
    
    $buy_now_link = "";
    
    $buy_now_tp  = "";
    
    $show_seconds = 0;
    
    $on_pause_show_optin = 0;
    
    $on_end_show_optin = 0;
    
    $optin_form_submit_url = "";
    
    $buy_now_show = "";
    
    $youtube_actions_record_sql = "SELECT * from " . $actions_table. " WHERE vid_id = " .$v_id;
    
    $actions_records = $wpdb->get_results($youtube_actions_record_sql);
    
        foreach($actions_records as $single_action){
        
            //set all variables to use 
            
            $entry_anim = $single_action->entry_anim;
            
            $exit_anim = $single_action->exit_anim;
            
            $on_pause = $single_action->on_pause;
            
            $on_end_show_optin = $single_action->on_end;
            
            $show_seconds = $single_action->show_seconds;
            
            $on_pause_show_optin = $single_action->on_pause;
            
            $optin_form_submit_url = plugins_url( 'emailmanager.php' , __FILE__ );
            
            $optin_form_id = $single_action->form_id;
            
            if($single_action->act_type == 'ctat'){
                
                $cta_text = $single_action->cta_text;
                $cta_link = $single_action->img_link;
                $cta_bg_color = $single_action->cta_bg_color;
                $cta_text_color = $single_action->cta_text_color;
                $buy_now_link = $single_action->buy_now_link;
                $buy_now_tp = $single_action->buy_now_tp;
                $buy_now_show = $single_action->buy_now_code;
                $custom_bt_code = $single_action->ct_bt_code;
                $custom_bt_bgcolor = $single_action->ct_bt_bgcolor;
                $custom_bt_bcolor = $single_action->ct_bt_bcolor;
                $custom_bt_tcolor = $single_action->ct_bt_tcolor;
                $custom_bt_text = $single_action->ct_bt_text;
                $custom_bt_link = $single_action->ct_bt_link;
                
            $ctat_fragment = "
            
            <div id=\"{$ytid}_call_to_action\" style=\"position:relative;width:auto;height:auto;\">
              
              <i class=\"open_cta_bt_{$ytid} icon-cta-open\" style=\"position: absolute;right:0;color: #00008B; 
 cursor: pointer;display: none;z-index: 1;\"></i>
              <i class=\"close_cta_bt_{$ytid} icon-cta-close\" style=\"position: absolute;right:0;cursor: pointer;display: none;color: #00008B;z-index: 1;\"></i>
              
            <div id=\"{$ytid}_cta_text\" style=\"width:auto;height:auto;-webkit-border-radius: 0px;-moz-border-radius: 0px;
border-radius: 0px;background-color:{$cta_bg_color};word-wrap:break-word;display: none;padding-bottom:5px;padding-left:2px;padding-right:2px;\">

           <div style=\"padding:6px;\"></div>

            <div style=\"width:100%;max-width:{$ytw}px;\">";   
           
             $yt_video_full .= $ctat_fragment;   
           
            if($buy_now_show == 1){
                
           $yt_video_full .= "<div><a href=\"{$buy_now_link}\" target=\"_blank\" id=\"buy_now_link_{$ytid}\" style=\"background: url({$buy_now_tp}) no-repeat center;width: 150px;height:50px;display: inline-block;text-align:center;\"></a></div>
           ";
           
           }
                
                if($custom_bt_code == 1){
                
           $yt_video_full .= "<a href=\"{$custom_bt_link}\" target=\"_blank\" id=\"custom_bt_{$ytid}\" class=\"custom_bt_{$ytid}\" style=\"background-color:{$custom_bt_bgcolor};-moz-border-radius:28px;-webkit-border-radius:28px;border-radius:28px;display:inline-block;cursor:pointer;color:{$custom_bt_tcolor};font-family:Courier New;font-size:14px;font-weight:bold;padding:4px 8px;font-style:italic;text-decoration:none;border:2px solid {$custom_bt_bcolor};text-align:center;\">{$custom_bt_text}</a> 
               <br><br>";
           
           }
                
           $yt_video_full .= "
           <div>{$cta_text}</div>
           </div>
           </div>
           </div>
           </div>
            ";    
            
            }else if($single_action->act_type == 'ctai'){
            
                $cta_image = $single_action->img_url;
                $cta_link = $single_action->img_link;
                
            $ctai_fragment = "
            
            <div id=\"{$ytid}_call_to_action\" style=\"position:relative;width:auto;height:auto;\">
              
              <i class=\"open_cta_bt_{$ytid} icon-cta-open\" style=\"position: absolute;right: 0;color: #00008B; 
 cursor: pointer;display: none;z-index: 1;\"></i>
              <i class=\"close_cta_bt_{$ytid} icon-cta-close\" style=\"position: absolute;right: 0;cursor: pointer;display: none;color: #00008B;z-index: 1;\"></i>
             
           <a href=\"{$cta_link}\" target=\"_blank\ style=\"width:auto;height:auto;\"><center>
           <img src=\"{$cta_image}\" id=\"{$ytid}_cta_image\" style=\"display:none;cursor:pointer;width:100%;height:100%;\">
           </center>
           </a>
            
              </div>
              </div>
            ";    
            
                
             $yt_video_full .= $ctai_fragment;
                
            }
            
        }
            
            
        $video_code = "<script type=\"text/javascript\">
      
    var foroptin_d_{$ytid} = false; 
    var foroptin_t_{$ytid};
    
    var forcta_d_{$ytid} = false; 
    var forcta_t_{$ytid};
    
jQuery(\"#{$ytid}\").tubeplayer({
	width: {$ytw}, 
	height: {$yth}, 
	allowFullScreen: \"true\", 
	initialVideo: \"{$yt_id_player}\", 
    start: 0, 
	preferredQuality: \"default\",
    showControls: {$show_controls},
	showRelated: 0, 
	autoPlay: {$autoplay}, 
	autoHide: {$auto_hide_cb}, //for controller 
	theme: \"{$player_theme}\", 
	color: \"red\", 
	showinfo: false, 
	modestbranding: true, 
	wmode: \"transparent\", // note: transparent maintains z-index, but disables GPU acceleration
	swfobjectURL: \"http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js\",
	loadSWFObject: true, 
	iframed: true,     
    ";
    
    $yt_video_full .= $video_code;
    
    $cta_timer_value = 0;
     
    $cta_type_code = "";
        
        $yt_video_full .= "onPlayerPlaying: function(){
    
        ";
        
        foreach($actions_records as $single_action_2){
    
       if($single_action_2->act_type == 'ctai' || $single_action_2->act_type == 'ctat'){
            
            $cta_timer_value = $single_action_2->show_seconds;
            
            $cta_type_code = $single_action_2->act_type;
            
            $cta_player = "
            
            forcta_t_{$ytid} = setInterval(hndcta_{$ytid},100);
            
            ";
            
            $yt_video_full .= $cta_player;
        
        }
        
        if($dim_flag == 1){
        
            $yt_video_full .= "
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).show();    
            ";
        
        }
            
    } //main actions loop
        
        $yt_video_full .= "},"; //closes onPlayerPlaying
          
            if($dim_flag == 1){
            
                $add_on_pause .= " 
                
                 onPlayerPaused: function(){
                
                jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();";
                
            $yt_video_full .= $add_on_pause;  
                
                
             $close_on_pause = "},";
            
        $yt_video_full .= $close_on_pause;        
                
            }
        
      
     $yt_video_full .= "});"; //closes main video code
             
        $cta_tmp_show_seconds = $cta_timer_value + 1;
    
        $handle_cta_text = "
        
        function hndcta_{$ytid}() {
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$cta_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$cta_tmp_show_seconds} && !forcta_d_{$ytid}) {

            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_text').slideDown(1000);
             forcta_d_{$ytid} = true;
        }
    }

        
        "; 
        
        
        $handle_cta_img = "
        
        function hndcta_{$ytid}() {
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$cta_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$cta_tmp_show_seconds} && !forcta_d_{$ytid}) {

            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_image').slideDown(1000);
             forcta_d_{$ytid} = true;
        }
    }

        
        "; 
        
        if($cta_type_code == 'ctai'){
        
            $yt_video_full .= $handle_cta_img;
            
        }elseif($cta_type_code == 'ctat'){
        
            $yt_video_full .= $handle_cta_text;
        }
        
        
        
        if($cta_type_code == 'ctat'){
            
            
            $cta_text_code = "
            
            jQuery('.open_cta_bt_{$ytid}').click(function(e){
             e.preventDefault();
            jQuery('.open_cta_bt_{$ytid}').hide();
            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_text').slideDown(1000);
             
        });
              jQuery('.close_cta_bt_{$ytid}').click(function(e){
               e.preventDefault();
            jQuery('.close_cta_bt_{$ytid}').hide();
            jQuery('.open_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_text').slideUp(1000);       
        });
             

            
            ";
            
             $yt_video_full .= $cta_text_code;
        
        }else if($cta_type_code == 'ctai'){
            
            
            $cta_img_code = "
            
                  jQuery('.open_cta_bt_{$ytid}').click(function(e){
             e.preventDefault();
            jQuery('.open_cta_bt_{$ytid}').hide();
            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_image').slideDown(1000);
             
        });
              jQuery('.close_cta_bt_{$ytid}').click(function(e){
             e.preventDefault();
            jQuery('.close_cta_bt_{$ytid}').hide();
            jQuery('.open_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_image').slideUp(1000);       
        });  
                         
            ";
            
             $yt_video_full .= $cta_img_code;
        
       
            
}
    
    if($social_share == 1){
        
     $yt_social_script_code = " jQuery( '.share-close-bt-{$ytid}' ).click(function(event) {
                   event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').hide();
             });
             
            jQuery('.yt-{$ytid}').mouseover(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').show();
            });

            jQuery('.yt-{$ytid}').mouseout(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').hide();
            });  
 
             jQuery( '.share-video-bt-{$ytid}' ).click(function(event) {
                 event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').fadeIn();
                 jQuery( '#yt-link-{$ytid}' ).val((jQuery('#{$ytid}').tubeplayer('data').videoURL));
             });
             
              config{$ytid} = {
              
              networks: {
              
                  google_plus: {
                    enabled: true,        
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}' 
                   },
                   
                  twitter: {
                    enabled: true, 
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}'    
                   },
                   
                  facebook: {
                    enabled: true,
                    load_sdk: false,         
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}',
                    app_id: 145148092322424,
                   },
                   
                  pinterest: {
                    enabled: false
                   },
                   
                  email: {
                    enabled: false
                   }
               }
             } 
             
             var share{$ytid} = new Share('.share-button-{$ytid}',config{$ytid});
             
             ";
        
        $yt_video_full .= $yt_social_script_code; 
    }
           
    $yt_video_full .= "</script>";    
            
    
    return $yt_video_full;
    
}



    
function video_optin_plus_cta_shortcode($v_id){
    
     global $wpdb;
    
    $youtube_video_table = $wpdb->prefix . 'prash_videos';
    
    $actions_table = $wpdb->prefix . 'prash_actions';
    
    $optins_table = $wpdb->prefix . 'prash_optins';
        
    $youtube_video_record_sql = "SELECT * from " . $youtube_video_table. " WHERE id = " .$v_id;
    
    $youtube_video_record = $wpdb->get_row($youtube_video_record_sql);
    
    $ytid = $youtube_video_record->vid;
    $yt_id_player = $youtube_video_record->youtube;
    $ytw = $youtube_video_record->width;
    $yth = $youtube_video_record->height;
    
    $autoplay = $youtube_video_record->autoplay;
    
    $dim_flag = $youtube_video_record->dim;
    $scroll_flag = $youtube_video_record->scroll_pause;
    
    $show_controls = $youtube_video_record->controls;
    
    $auto_hide_cb = $youtube_video_record->auto_hide_cb;
    
    $player_theme = $youtube_video_record->theme;
    
    $video_border_color = $youtube_video_record->vbordercolor;
    
     $social_share = $youtube_video_record->social_share;
    
     $logo_brand_code = $youtube_video_record->logo_brand_code;
    
    $logo_brand_pick = $youtube_video_record->logo_pick;
    
    $logo_brand_link = $youtube_video_record->logo_link;
    
    $logo_brand_ps = $youtube_video_record->logo_ps;
        
    if($dim_flag == 1){
    
        $dim_image = plugins_url( 'images/dim.png' , __FILE__ );
    
    $yt_dim = "<div id=\"dim_video_{$ytid}\" style=\"position:fixed;background-image:url({$dim_image});left:0; top:0; width:100%;\"></div>
    
    <script>
     jQuery(document).ready(function(){
               
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();            
        });
   
    </script>
    
    ";
        
    $yt_video_full .= $yt_dim;
    }
        
     $yt_video_full .= "
    
      <style>
     #yt-{$ytid} {
    position:relative;
    padding-bottom:55.50%;
    height:0;
}

#yt-{$ytid} iframe, #yt-{$ytid} object, #yt-{$ytid} embed {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
}
#ytvideowrap-{$ytid}{
    width:100%;
    height:100%;
    max-width: {$ytw}px;
}

@media only screen and (max-width : 480px) {
    .yt-form-{$ytid} button[type=\"submit\"] {
        margin: 0.7em 0 0;
    }

    .yt-form-{$ytid} input:not([type]),
    .yt-form-{$ytid} input[type=\"text\"],
    .yt-form-{$ytid} input[type=\"password\"],
    .yt-form-{$ytid} input[type=\"email\"],
    .yt-form-{$ytid} input[type=\"url\"],
    .yt-form-{$ytid} input[type=\"date\"],
    .yt-form-{$ytid} input[type=\"month\"],
    .yt-form-{$ytid} input[type=\"time\"],
    .yt-form-{$ytid} input[type=\"datetime\"],
    .yt-form-{$ytid} input[type=\"datetime-local\"],
    .yt-form-{$ytid} input[type=\"week\"],
    .yt-form-{$ytid} input[type=\"number\"],
    .yt-form-{$ytid} input[type=\"search\"],
    .yt-form-{$ytid} input[type=\"tel\"],
    .yt-form-{$ytid} input[type=\"color\"],
    .yt-form-{$ytid} label {
        margin-bottom: 0.3em;
        display: block;
    }

    .pure-group input:not([type]),
    .pure-group input[type=\"text\"],
    .pure-group input[type=\"password\"],
    .pure-group input[type=\"email\"],
    .pure-group input[type=\"url\"],
    .pure-group input[type=\"date\"],
    .pure-group input[type=\"month\"],
    .pure-group input[type=\"time\"],
    .pure-group input[type=\"datetime\"],
    .pure-group input[type=\"datetime-local\"],
    .pure-group input[type=\"week\"],
    .pure-group input[type=\"number\"],
    .pure-group input[type=\"search\"],
    .pure-group input[type=\"tel\"],
    .pure-group input[type=\"color\"] {
        margin-bottom: 0;
    }

    .yt-form-{$ytid}-aligned .pure-control-group label {
        margin-bottom: 0.3em;
        text-align: left;
        display: block;
        width: 100%;
    }

    .yt-form-{$ytid}-aligned .pure-controls {
        margin: 1.5em 0 0 0;
    }

    .yt-form-{$ytid} .pure-help-inline,
    .yt-form-{$ytid}-message-inline,
    .yt-form-{$ytid}-message {
        display: block;
        font-size: 0.75em;
        padding: 0.2em 0 0.8em;
    }
}

.yt-form-{$ytid} input[type=\"text\"],
.yt-form-{$ytid} input[type=\"password\"],
.yt-form-{$ytid} input[type=\"email\"],
.yt-form-{$ytid} input[type=\"url\"],
.yt-form-{$ytid} input[type=\"date\"],
.yt-form-{$ytid} input[type=\"month\"],
.yt-form-{$ytid} input[type=\"time\"],
.yt-form-{$ytid} input[type=\"datetime\"],
.yt-form-{$ytid} input[type=\"datetime-local\"],
.yt-form-{$ytid} input[type=\"week\"],
.yt-form-{$ytid} input[type=\"number\"],
.yt-form-{$ytid} input[type=\"search\"],
.yt-form-{$ytid} input[type=\"tel\"],
.yt-form-{$ytid} input[type=\"color\"],
.yt-form-{$ytid} select,
.yt-form-{$ytid} textarea {
    padding: 0.5em 0.6em;
    display: inline-block;
    border: 1px solid #ccc;
    box-shadow: inset 0 1px 3px #ddd;
    border-radius: 4px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}

.yt-form-{$ytid} input:not([type]) {
    padding: 0.5em 0.6em;
    display: inline-block;
    border: 1px solid #ccc;
    box-shadow: inset 0 1px 3px #ddd;
    border-radius: 4px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}


.yt-form-{$ytid} input[type=\"color\"] {
    padding: 0.2em 0.5em;
}


.yt-form-{$ytid} input[type=\"text\"]:focus,
.yt-form-{$ytid} input[type=\"password\"]:focus,
.yt-form-{$ytid} input[type=\"email\"]:focus,
.yt-form-{$ytid} input[type=\"url\"]:focus,
.yt-form-{$ytid} input[type=\"date\"]:focus,
.yt-form-{$ytid} input[type=\"month\"]:focus,
.yt-form-{$ytid} input[type=\"time\"]:focus,
.yt-form-{$ytid} input[type=\"datetime\"]:focus,
.yt-form-{$ytid} input[type=\"datetime-local\"]:focus,
.yt-form-{$ytid} input[type=\"week\"]:focus,
.yt-form-{$ytid} input[type=\"number\"]:focus,
.yt-form-{$ytid} input[type=\"search\"]:focus,
.yt-form-{$ytid} input[type=\"tel\"]:focus,
.yt-form-{$ytid} input[type=\"color\"]:focus,
.yt-form-{$ytid} select:focus,
.yt-form-{$ytid} textarea:focus {
    outline: 0;
    outline: thin dotted \9; /* IE6-9 */
    border-color: #129FEA;
}

.yt-form-{$ytid} input:not([type]):focus {
    outline: 0;
    outline: thin dotted \9; /* IE6-9 */
    border-color: #129FEA;
}

.yt-form-{$ytid} input[type=\"file\"]:focus,
.yt-form-{$ytid} input[type=\"radio\"]:focus,
.yt-form-{$ytid} input[type=\"checkbox\"]:focus {
    outline: thin dotted #333;
    outline: 1px auto #129FEA;
}
.yt-form-{$ytid} .pure-checkbox,
.yt-form-{$ytid} .pure-radio {
    margin: 0.5em 0;
    display: block;
}

.yt-form-{$ytid} input[type=\"text\"][disabled],
.yt-form-{$ytid} input[type=\"password\"][disabled],
.yt-form-{$ytid} input[type=\"email\"][disabled],
.yt-form-{$ytid} input[type=\"url\"][disabled],
.yt-form-{$ytid} input[type=\"date\"][disabled],
.yt-form-{$ytid} input[type=\"month\"][disabled],
.yt-form-{$ytid} input[type=\"time\"][disabled],
.yt-form-{$ytid} input[type=\"datetime\"][disabled],
.yt-form-{$ytid} input[type=\"datetime-local\"][disabled],
.yt-form-{$ytid} input[type=\"week\"][disabled],
.yt-form-{$ytid} input[type=\"number\"][disabled],
.yt-form-{$ytid} input[type=\"search\"][disabled],
.yt-form-{$ytid} input[type=\"tel\"][disabled],
.yt-form-{$ytid} input[type=\"color\"][disabled],
.yt-form-{$ytid} select[disabled],
.yt-form-{$ytid} textarea[disabled] {
    cursor: not-allowed;
    background-color: #eaeded;
    color: #cad2d3;
}

.yt-form-{$ytid} input:not([type])[disabled] {
    cursor: not-allowed;
    background-color: #eaeded;
    color: #cad2d3;
}
.yt-form-{$ytid} input[readonly],
.yt-form-{$ytid} select[readonly],
.yt-form-{$ytid} textarea[readonly] {
    background: #eee; /* menu hover bg color */
    color: #777; /* menu text color */
    border-color: #ccc;
}

.yt-form-{$ytid} input:focus:invalid,
.yt-form-{$ytid} textarea:focus:invalid,
.yt-form-{$ytid} select:focus:invalid {
    color: #b94a48;
    border-color: #ee5f5b;
}
.yt-form-{$ytid} input:focus:invalid:focus,
.yt-form-{$ytid} textarea:focus:invalid:focus,
.yt-form-{$ytid} select:focus:invalid:focus {
    border-color: #e9322d;
}
.yt-form-{$ytid} input[type=\"file\"]:focus:invalid:focus,
.yt-form-{$ytid} input[type=\"radio\"]:focus:invalid:focus,
.yt-form-{$ytid} input[type=\"checkbox\"]:focus:invalid:focus {
    outline-color: #e9322d;
}
.yt-form-{$ytid} select {
    border: 1px solid #ccc;
    background-color: white;
}
.yt-form-{$ytid} select[multiple] {
    height: auto;
}
.yt-form-{$ytid} label {
    margin: 0.5em 0 0.2em;
}
.yt-form-{$ytid} fieldset {
    margin: 0;
    padding: 0.35em 0 0.75em;
    border: 0;
}
.yt-form-{$ytid} legend {
    display: block;
    width: 100%;
    padding: 0.3em 0;
    margin-bottom: 0.3em;
    color: #333;
    border-bottom: 1px solid #e5e5e5;
}

.yt-form-{$ytid}-stacked input[type=\"text\"],
.yt-form-{$ytid}-stacked input[type=\"password\"],
.yt-form-{$ytid}-stacked input[type=\"email\"],
.yt-form-{$ytid}-stacked input[type=\"url\"],
.yt-form-{$ytid}-stacked input[type=\"date\"],
.yt-form-{$ytid}-stacked input[type=\"month\"],
.yt-form-{$ytid}-stacked input[type=\"time\"],
.yt-form-{$ytid}-stacked input[type=\"datetime\"],
.yt-form-{$ytid}-stacked input[type=\"datetime-local\"],
.yt-form-{$ytid}-stacked input[type=\"week\"],
.yt-form-{$ytid}-stacked input[type=\"number\"],
.yt-form-{$ytid}-stacked input[type=\"search\"],
.yt-form-{$ytid}-stacked input[type=\"tel\"],
.yt-form-{$ytid}-stacked input[type=\"color\"],
.yt-form-{$ytid}-stacked select,
.yt-form-{$ytid}-stacked label,
.yt-form-{$ytid}-stacked textarea {
    display: block;
    margin: 0.25em 0;
}

.yt-form-{$ytid}-stacked input:not([type]) {
    display: block;
    margin: 0.25em 0;
}
.yt-form-{$ytid}-aligned input,
.yt-form-{$ytid}-aligned textarea,
.yt-form-{$ytid}-aligned select,
.yt-form-{$ytid}-aligned .pure-help-inline,
.yt-form-{$ytid}-message-inline {
    display: inline-block;
    *display: inline;
    *zoom: 1;
    vertical-align: middle;
}
.yt-form-{$ytid}-aligned textarea {
    vertical-align: top;
}

/* Aligned Forms */
.yt-form-{$ytid}-aligned .pure-control-group {
    margin-bottom: 0.5em;
}
.yt-form-{$ytid}-aligned .pure-control-group label {
    text-align: right;
    display: inline-block;
    vertical-align: middle;
    width: 10em;
    margin: 0 1em 0 0;
}
.yt-form-{$ytid}-aligned .pure-controls {
    margin: 1.5em 0 0 10em;
}

/* Rounded Inputs */
.yt-form-{$ytid} input.pure-input-rounded,
.yt-form-{$ytid} .pure-input-rounded {
    border-radius: 2em;
    padding: 0.5em 1em;
}

/* Grouped Inputs */
.yt-form-{$ytid} .pure-group fieldset {
    margin-bottom: 10px;
}
.yt-form-{$ytid} .pure-group input {
    display: block;
    padding: 10px;
    margin: 0;
    border-radius: 0;
    position: relative;
    top: -1px;
}
.yt-form-{$ytid} .pure-group input:focus {
    z-index: 2;
}
.yt-form-{$ytid} .pure-group input:first-child {
    top: 1px;
    border-radius: 4px 4px 0 0;
}
.yt-form-{$ytid} .pure-group input:last-child {
    top: -2px;
    border-radius: 0 0 4px 4px;
}
.yt-form-{$ytid} .pure-group button {
    margin: 0.35em 0;
}

.yt-form-{$ytid} .pure-input-1 {
    width: 100%;
}
.yt-form-{$ytid} .pure-input-2-3 {
    width: 66%;
}
.yt-form-{$ytid} .pure-input-1-2 {
    width: 50%;
}
.yt-form-{$ytid} .pure-input-1-3 {
    width: 33%;
}
.yt-form-{$ytid} .pure-input-1-4 {
    width: 25%;
}

/* Inline help for forms */
.yt-form-{$ytid} .pure-help-inline,
.yt-form-{$ytid}-message-inline {
    display: inline-block;
    padding-left: 0.3em;
    color: #666;
    vertical-align: middle;
    font-size: 0.875em;
}

/* Block help for forms */
.yt-form-{$ytid}-message {
    display: block;
    color: #666;
    font-size: 0.875em;
}

        </style>
            
         <div id=\"ytvideowrap-{$ytid}\" style=\"z-index:102;\">
         <div id=\"yt-{$ytid}\" class=\"yt-{$ytid}\" style=\"border:4px solid {$video_border_color};\">";
    
     if($logo_brand_code == 1){
     
        if($logo_brand_ps == 0){
            
             $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;left:0;top:0;z-index:101;\"></a>";
            
        }
        else if($logo_brand_ps == 1){
            
            $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;right:0;top:0;z-index:101;\"></a>";
            
        }
        else{
            //Do Nothing
        }
        
       $yt_video_full .=  $yt_logo_brand_code;
        
    }
    
     if($social_share == 1){
         
        $share_image = plugins_url( 'images/share_video.png' , __FILE__ );
        $fb_share_image = plugins_url( 'images/fb.png' , __FILE__ );
        $tw_share_image = plugins_url( 'images/tw.png' , __FILE__ );
        $gplus_share_image = plugins_url( 'images/gplus.png' , __FILE__ );
        $close_share_image = plugins_url( 'images/close.png' , __FILE__ );
        
        $yt_social_share = " <div class=\"share-video-{$ytid}\" style=\"display: none;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;width: 40px;z-index:100;
background: black;height: 30px;\">
        <a href=\"#\" class=\"share-video-bt-{$ytid}\" style=\"cursor: pointer;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;\"><img style=\"border:0;\" src=\"{$share_image}\" alt=\"share-video\" width=\"32\" height=\"32\"></a>
    </div>
    
    <div class=\"share-video-options-{$ytid}\" style=\"width: auto;height: 40px;background: black;position: absolute;top: 0;left: 0;right: 0;z-index:102;
margin: auto;text-align:center;display: none;\">
        
     <div class=\"share-options-{$ytid}\">
        <input type=\"text\" id=\"yt-link-{$ytid}\" style=\"position: absolute;left: 5px;width:45%;font-family:Arial;font-size:1.0em;height:auto;
 background:#008B8B;border:2px solid #000000;color: #FFFFFF;\"> 
         <div class=\"share-bt-main-{$ytid}\" style=\"position: absolute;margin-top: 8px;margin-left: 55%;\">
      <div class='share-button-{$ytid}'></div>
             </div>
    <a href=\"#\" class=\"share-close-bt-{$ytid}\" style=\"position: absolute;right: 5px;margin-top: 5px;cursor: pointer;\"><img style=\"border:0;\" src=\"{$close_share_image}\" alt=\"close_share\" width=\"30\" height=\"30\"></a>   
  </div>
    
    </div>";
    
    $yt_video_full .= $yt_social_share;
        
        
    }
    
    
    $yt_video_code = "
     <div id='{$ytid}'></div> 
      ";

    $yt_video_full .= $yt_video_code;
    
    $optin_form_code = "";
    
    $cta_code = "";
    
    $entry_anim = "";
    
    $exit_anim = "";
    
    $form_bg = "";
    
    $title_color = "";
    $title_text = "";
    
    $content_color = "";
    $content_text = "";
    
    $button_color = "";
    $button_text = "";
    $button_text_color = "";
    
    $emailvalid_text_color = "";
    
    $optin_border_color = "";
    
    $optin_skip_text_color = "";
    
     $buy_now_link = "";
    
     $buy_now_tp  = "";
    
    $buy_now_show  = "";
    
    $show_seconds = 0;
    
    $on_pause_show_optin = 0;
    
    $on_end_show_optin = 0;
    
    $optin_form_submit_url = "";
    
    
    $youtube_actions_record_sql = "SELECT * from " . $actions_table. " WHERE vid_id = " .$v_id;
    
    $actions_records = $wpdb->get_results($youtube_actions_record_sql);

    
        foreach($actions_records as $single_action){
        
            //set all variables to use 
            
//            $entry_anim = $single_action->entry_anim;
            
            $exit_anim = $single_action->exit_anim;
            
            $on_pause = $single_action->on_pause;
            
//            $on_end_show_optin = $single_action->on_end;
            
            $show_seconds = $single_action->show_seconds;
            
//            $on_pause_show_optin = $single_action->on_pause;
            
            $optin_form_submit_url = plugins_url( 'ext.php' , __FILE__ );
         
            if($single_action->act_type == 'optin'){
            
            $optin_form_id = $single_action->form_id;
            
            $entry_anim = $single_action->entry_anim;
                
            $on_pause_show_optin = $single_action->on_pause;    
                
            $on_end_show_optin = $single_action->on_end;
                
            $optin_detail = $wpdb->get_row("SELECT * from " . $optins_table. " WHERE id = " .$single_action->form_id);
                
           
                $form_bg = $optin_detail->bg;
    
                $title_color = $optin_detail->headcolor;
                $title_text =  $optin_detail->headtext;
    
                $content_color = $optin_detail->msgcolor;
                $content_text = $optin_detail->msgtext;
                
                $button_color = $optin_detail->butcolor;
                $button_text = $optin_detail->buttext;
                $button_text_color = $optin_detail->buttextcolor;
                
                $emailvalid_text_color = $optin_detail->emailvalidtxtcolor;
                
                $optin_border_color = $optin_detail->optinbordercolor;
                
                $optin_skip_text_color = $optin_detail->optinskiptxtcolor;

                $optin_skip_text = $optin_detail->skip_bt_text;
                
                $skip_optin_form = $optin_detail->skip_optin_text;
                
                $ar_type_optin_form = $optin_detail->ar;
                
            }
            
             else if($single_action->act_type == 'ctat'){
                  
            $cta_text = $single_action->cta_text;
            $cta_link = $single_action->img_link;
            $cta_bg_color = $single_action->cta_bg_color;
            $cta_text_color = $single_action->cta_text_color;
            $buy_now_link = $single_action->buy_now_link;
            $buy_now_tp = $single_action->buy_now_tp;     
            $buy_now_show = $single_action->buy_now_code; 
            $custom_bt_code = $single_action->ct_bt_code;
            $custom_bt_bgcolor = $single_action->ct_bt_bgcolor;
            $custom_bt_bcolor = $single_action->ct_bt_bcolor;
            $custom_bt_tcolor = $single_action->ct_bt_tcolor;
            $custom_bt_text = $single_action->ct_bt_text;
            $custom_bt_link = $single_action->ct_bt_link;     
             }
            
//            else if($single_action->act_type == 'ctai'){
//                  
//            $cta_image = $single_action->img_url;
//            $cta_link = $single_action->img_link;
//            
//            }
            
        }     
    
$optin_form_fragment1 = "

          <div id=\"{$ytid}-box\" class=\"{$ytid}-box\" style=\"width:100%;height:100%;position: absolute;display: none;bottom: 0;left: 0;right:0;margin: auto;text-align:center;-webkit-border-radius: 0px;-moz-border-radius: 0px;border-radius: 0px;background:rgba(255, 0, 0, 0);z-index:101;\"></div>
                
         <div id=\"{$ytid}-form\" class=\"{$ytid}-form\" required style=\"background:{$form_bg};position: absolute;width:93%;height:145px;top: 0;bottom: 0;left: 0;right: 0;margin: auto;text-align:center;display: none;padding:5px; -webkit-border-radius: 20px;-moz-border-radius: 20px;border-radius: 20px;border:4px solid {$optin_border_color};z-index:101;\">";
          
                 $yt_video_full .= $optin_form_fragment1; 
            
            
                     if($skip_optin_form == 1){
                         
                  $yt_video_full .="       
                     <a href=\"#\" id=\"{$ytid}-close-optin\" style=\"padding: 1px;text-align: center;position: absolute;bottom: 0;
left: 0;right: 0;margin: auto;text-align:center;color:{$optin_skip_text_color};font-weight:bold;text-decoration: none;width: -moz-fit-content;
width: -webkit-fit-content;width: fit-content;\">{$optin_skip_text}</a>";
                         
                }


         $yt_video_full .="
         
   <div class=\"{$ytid}-form-text-title\" id=\"{$ytid}-form-text-title\" style=\"color:{$title_color};font-size: 16px;text-align: center;margin-top: 1%;word-wrap:break-word;\">{$title_text}</div>
     
         <div class=\"{$ytid}-form-text-content\" id=\"{$ytid}-form-text-content\" style=\"color:{$content_color};text-align: center;word-wrap:break-word;font-size:15px;line-height: 100%;\">{$content_text}</div>
     
     <div id=\"{$ytid}-error-em\" class=\"{$ytid}-error-em\" style=\"display: none;text-align: center;font-size: 14px;color:{$emailvalid_text_color};font-style: italic;\">Please enter a valid email id</div>
        
            <form name=\"{$ytid}-cform\" id=\"{$ytid}-cform\" class=\"yt-form-{$ytid}\" action=\"#\" method=\"post\" target=\"_blank\" style=\"margin-top:1%;\">
         <div id=\"em_submit\" style=\"width:100%;padding:2px;display:inline-block;\"> 
         <input id=\"{$ytid}-em\" name=\"email\" type=\"email\" placeholder=\"Email\" style=\"width:60%;margin-right:1%;display:inline-block;\">
          <input type=\"hidden\" name=\"optinformid\" value=\"{$ar_type_optin_form}\">
    <a href=\"#\" id=\"{$ytid}-form_submit_bt\" class=\"{$ytid}-form_submit_bt\" style=\"display:inline-block;font-size:10px;font-family:Arial;font-weight:bold;-moz-border-radius:8px;-webkit-border-radius:8px;border-radius:8px;text-decoration:none;background-color:{$button_color};color:{$button_text_color};padding-left:1%;padding-right:1%;padding-top:7px;padding-bottom:7px;text-align:center;\">{$button_text}</a>
          </div>
</form>
</div>
</div>

<div id=\"{$ytid}_call_to_action\" style=\"position:relative;width:auto;height:auto;\">
              
              <i class=\"open_cta_bt_{$ytid} icon-cta-open\" style=\"position: absolute;right:0;color: #00008B; 
 cursor: pointer;display: none;z-index: 1;\"></i>
              <i class=\"close_cta_bt_{$ytid} icon-cta-close\" style=\"position: absolute;right:0;cursor: pointer;display: none;color: #00008B;z-index: 1;\"></i>
              
            <div id=\"{$ytid}_cta_text\" style=\"width:auto;height:auto;-webkit-border-radius: 0px;-moz-border-radius: 0px;
border-radius: 0px;background-color:{$cta_bg_color};word-wrap:break-word;display: none;padding-bottom:5px;padding-left:2px;padding-right:2px;\">

           <div style=\"padding: 6px;\"></div>
            <div style=\"width:100%;max-width:{$ytw}px;\">";
    
               if($buy_now_show == 1){
                
           $yt_video_full .= "<div><a href=\"{$buy_now_link}\" target=\"_blank\" id=\"buy_now_link_{$ytid}\" style=\"background: url({$buy_now_tp}) no-repeat center;width: 150px;height:50px;display: inline-block;text-align:center;\"></a></div>
           ";
           
           }
                
                if($custom_bt_code == 1){
                
           $yt_video_full .= "<a href=\"{$custom_bt_link}\" target=\"_blank\" id=\"custom_bt_{$ytid}\" class=\"custom_bt_{$ytid}\" style=\"background-color:{$custom_bt_bgcolor};-moz-border-radius:28px;-webkit-border-radius:28px;border-radius:28px;display:inline-block;cursor:pointer;color:{$custom_bt_tcolor};font-family:Courier New;font-size:14px;font-weight:bold;padding:4px 8px;font-style:italic;text-decoration:none;border:2px solid {$custom_bt_bcolor};text-align:center;\">{$custom_bt_text}</a> 
               <br><br>";
           
           }
           
           $yt_video_full .="
           <div>{$cta_text}</div>
              </div>
              </div>
              </div>
              </div>
                ";
                
                
               
                               
       $video_code = "<script type=\"text/javascript\">
      
    var foroptin_d_{$ytid} = false; 
    var foroptin_t_{$ytid};
    
    var forcta_d_{$ytid} = false; 
    var forcta_t_{$ytid};
    
jQuery(\"#{$ytid}\").tubeplayer({
	width: {$ytw}, 
	height: {$yth}, 
	allowFullScreen: \"true\", 
	initialVideo: \"{$yt_id_player}\", 
    start: 0, 
	preferredQuality: \"default\",
    showControls: {$show_controls},
	showRelated: 0, 
	autoPlay: {$autoplay}, 
	autoHide: {$auto_hide_cb}, //for controller 
	theme: \"{$player_theme}\", 
	color: \"red\", 
	showinfo: false, 
	modestbranding: true, 
	wmode: \"transparent\", // note: transparent maintains z-index, but disables GPU acceleration
	swfobjectURL: \"http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js\",
	loadSWFObject: true, 
	iframed: true,     
    ";
    
    $yt_video_full .= $video_code;
    
    $cta_timer_value = 0;
    
    $optin_timer_value = 0;
    
    $cta_type_code = "";
          
        $yt_video_full .= "onPlayerPlaying: function(){";
        
        foreach($actions_records as $single_action_2){
    
        if($single_action_2->act_type == 'optin'){
            
        $optin_timer_value = $single_action_2->show_seconds;    
        $optin_player = "
        
        t_{$ytid} = setInterval(hndoptin_{$ytid},100);
         
    ";
    
        $yt_video_full .= $optin_player;
            
        }
            
    else if($single_action_2->act_type == 'ctai' || $single_action_2->act_type == 'ctat'){
            
            $cta_timer_value = $single_action_2->show_seconds;
            
            $cta_type_code = $single_action_2->act_type;
            
            $cta_player = "
            
            forcta_t_{$ytid} = setInterval(hndcta_{$ytid},100);
            
            ";
            
            $yt_video_full .= $cta_player;
        
        }
        
            
    } //main actions loop
    
     if($dim_flag == 1){
        
            $yt_video_full .= "
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).show();    
            ";
        
        }
        
     $yt_video_full .= "},"; //closes onPlayerPlaying
    
    
        $add_on_pause = "
        onPlayerPaused: function(){
        
        ";
    
     $yt_video_full .= $add_on_pause; 
        
            if($dim_flag == 1){
            
                $add_on_pause_dim .= " jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();";
        
                 $yt_video_full .=  $add_on_pause_dim; 
                
            }    
         
            if($on_pause_show_optin == 1){
            
                
                $showing_optin_pause = " 
                
                if(jQuery.cookie('{$ytid}') == '{$ytid}'){
                
                jQuery('#{$ytid}-form').hide();
             jQuery('#{$ytid}-box').hide();  
             
             }else{
             
               jQuery('#{$ytid}-form').show();
             jQuery('#{$ytid}-box').show();  
             
             }
             
                ";
                
                $yt_video_full .= $showing_optin_pause;
            }
            
        $close_on_pause = "},";
            
        $yt_video_full .= $close_on_pause;    
    
        //for on ended
        
        if($on_end_show_optin == 1){
        
            $start_on_end = "
            
            onPlayerEnded: function(){
            
             if(jQuery.cookie('{$ytid}') == '{$ytid}'){
            
             jQuery('#{$ytid}-form').hide();
             jQuery('#{$ytid}-box').hide();
             
             }else{
             
             jQuery('#{$ytid}-form').show();
             jQuery('#{$ytid}-box').show();
             
             }
             
            },";
            
            $yt_video_full .= $start_on_end;
        
        }
        
        
        //for on ended close
        
        
     $yt_video_full .= "});"; //closes main video code
        
            
        $optin_tmp_show_seconds = $optin_timer_value + 1;
    
        $handle_optin = "
        
        function hndoptin_{$ytid}() {
        
         if(jQuery.cookie('{$ytid}') == '{$ytid}'){
                   jQuery('#{$ytid}-form').hide();
                   jQuery('#{$ytid}-box').hide(); 
                    }
                    
            else {        
        
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$optin_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$optin_tmp_show_seconds} && !foroptin_d_{$ytid}) {
              jQuery(\"#{$ytid}\").tubeplayer(\"pause\");
             jQuery('#{$ytid}-form').show();
             jQuery('#{$ytid}-box').show();  
             foroptin_d_{$ytid} = true;
        }
        
        }
        
    }
           
           jQuery( \"#{$ytid}-cform\" ).submit(function( event ) {
           event.preventDefault();
           
           function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
    return pattern.test(emailAddress);
};
           
           var emailaddress = jQuery(\"#{$ytid}-em\").val();
          
             if( isValidEmailAddress( emailaddress ) )  
          
          {
         
           jQuery('#{$ytid}-form').hide(); jQuery('#{$ytid}').tubeplayer('play');jQuery('#{$ytid}-box').hide(); jQuery.cookie('{$ytid}', '{$ytid}',{ expires: 7, path: '/' }); jQuery('#{$ytid}-error-em').hide();
          
           var email = jQuery(\"#{$ytid}-em\").val();
//                    var dataString = 'email=' + email + '&id=' + {$optin_form_id};
                    
                    jQuery.ajax({
                            type: \"POST\",
                            url: \"{$optin_form_submit_url}\",
                            data: jQuery('#{$ytid}-cform').serialize(),
                            success: function(data){
                            console.log(data);
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                            console.log(xhr.status);
                            console.log(xhr.responseText);
                            console.log(thrownError);
                            }

                            });
                
          
          
          return true;
          
          }
          else{
               
         jQuery('#{$ytid}-error-em').show(); 
         jQuery(\"#{$ytid}-em\").css({'border-color':'#FF0000','color':'#FF0000'});
         
          return false;
          
          }
          
                 
           });
           
            jQuery('#{$ytid}-form_submit_bt').click(function(e)
                {
                    e.preventDefault();
                    jQuery(\"form#{$ytid}-cform\").submit();
 
                });";
            
             $yt_video_full .= $handle_optin;

                          
              if($skip_optin_form == 1){
                  
                  $yt_video_full .="
                  jQuery( \"#{$ytid}-close-optin\" ).click(function(e) {
                e.preventDefault();
        jQuery(\".{$ytid}-form\").hide();      
        jQuery(\"#{$ytid}\").tubeplayer(\"play\");
        jQuery('#{$ytid}-box').hide();    

});

        "; 
                   
              }
                   
    
        $cta_tmp_show_seconds = $cta_timer_value + 1;
    
        $handle_cta_text = "
        
        function hndcta_{$ytid}() {
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$cta_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$cta_tmp_show_seconds} && !forcta_d_{$ytid}) {

            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_text').slideDown(1000);
             forcta_d_{$ytid} = true;
        }
    }

        
        "; 
        
        
        $handle_cta_img = "
        
        function hndcta_{$ytid}() {
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$cta_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$cta_tmp_show_seconds} && !forcta_d_{$ytid}) {

            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_image').slideDown(1000);
             forcta_d_{$ytid} = true;
        }
    }

        
        "; 
        
        if($cta_type_code == 'ctai'){
        
            $yt_video_full .= $handle_cta_img;
            
        }elseif($cta_type_code == 'ctat'){
        
            $yt_video_full .= $handle_cta_text;
        }
        
        
        
        if($cta_type_code == 'ctat'){
            
            
            $cta_text_code = "
            
            jQuery('.open_cta_bt_{$ytid}').click(function(e){
             e.preventDefault();
            jQuery('.open_cta_bt_{$ytid}').hide();
            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_text').slideDown(1000);
             
        });
              jQuery('.close_cta_bt_{$ytid}').click(function(e){
               e.preventDefault();
            jQuery('.close_cta_bt_{$ytid}').hide();
            jQuery('.open_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_text').slideUp(1000);       
        });
             

            
            ";
            
             $yt_video_full .= $cta_text_code;
        
        }else if($cta_type_code == 'ctai'){
            
            
            $cta_img_code = "
            
                  jQuery('.open_cta_bt_{$ytid}').click(function(e){
             e.preventDefault();
            jQuery('.open_cta_bt_{$ytid}').hide();
            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_image').slideDown(1000);
             
        });
              jQuery('.close_cta_bt_{$ytid}').click(function(e){
             e.preventDefault();
            jQuery('.close_cta_bt_{$ytid}').hide();
            jQuery('.open_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_image').slideUp(1000);       
        });  
                         
            ";
            
             $yt_video_full .= $cta_img_code;
        
        }
        
    
    if($social_share == 1){
        
     $yt_social_script_code = " jQuery( '.share-close-bt-{$ytid}' ).click(function(event) {
                   event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').hide();
             });
             
            jQuery('.yt-{$ytid}').mouseover(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').show();
            });

            jQuery('.yt-{$ytid}').mouseout(function(event) {
             event.preventDefault();
            jQuery('.share-video-{$ytid}').hide();
            });  
  
             jQuery( '.share-video-bt-{$ytid}' ).click(function(event) {
                 event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').fadeIn();
                 jQuery( '#yt-link-{$ytid}' ).val((jQuery('#{$ytid}').tubeplayer('data').videoURL));
             });
             
              config{$ytid} = {
              
              networks: {
              
                  google_plus: {
                    enabled: true,        
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}' 
                   },
                   
                  twitter: {
                    enabled: true, 
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}'    
                   },
                   
                  facebook: {
                    enabled: true,
                    load_sdk: false,         
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}',
                    app_id: 145148092322424,
                   },
                   
                  pinterest: {
                    enabled: false
                   },
                   
                  email: {
                    enabled: false
                   }
               }
             } 
             
             var share{$ytid} = new Share('.share-button-{$ytid}',config{$ytid});
             
             ";
        
        $yt_video_full .= $yt_social_script_code; 
    }
    
     $yt_video_full .= "</script>";    
           
    return $yt_video_full;
}




function video_fb_shortcode($v_id){
    
     global $wpdb;
    
    $youtube_video_table = $wpdb->prefix . 'prash_videos';
    
    $actions_table = $wpdb->prefix . 'prash_actions';
    
    $optins_table = $wpdb->prefix . 'prash_optins';

    $youtube_video_record_sql = "SELECT * from " . $youtube_video_table. " WHERE id = " .$v_id;
    
    $youtube_video_record = $wpdb->get_row($youtube_video_record_sql);
    
    $ytid = $youtube_video_record->vid;
    $yt_id_player = $youtube_video_record->youtube;
    $ytw = $youtube_video_record->width;
    $yth = $youtube_video_record->height;
    
     $autoplay = $youtube_video_record->autoplay;
    
    $show_controls = $youtube_video_record->controls;
    
    $auto_hide_cb = $youtube_video_record->auto_hide_cb;
    
    $player_theme = $youtube_video_record->theme;
    
    $video_border_color = $youtube_video_record->vbordercolor;
    
     $dim_flag = $youtube_video_record->dim;
    
     $social_share = $youtube_video_record->social_share;
    
      $logo_brand_code = $youtube_video_record->logo_brand_code;
    
    $logo_brand_pick = $youtube_video_record->logo_pick;
    
    $logo_brand_link = $youtube_video_record->logo_link;
    
    $logo_brand_ps = $youtube_video_record->logo_ps;
    
     if($dim_flag == 1){
    
    $dim_image = plugins_url( 'images/dim.png' , __FILE__ );
    
    $yt_dim_code = "<div id=\"dim_video_{$ytid}\" style=\"position:fixed;background-image:url({$dim_image});left:0; top:0; width:100%;\"></div>
    
    <script>
     jQuery(document).ready(function(){
               
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();            
        });
   
    </script>
    
    ";
    
     $yt_video_full .= $yt_dim_code;
    
     }

        
     $yt_video_full .= "
      <style>
     #yt-{$ytid} {
    position:relative;
    padding-bottom:55.50%;
    height:0;
}

#yt-{$ytid} iframe, #yt-{$ytid} object, #yt-{$ytid} embed {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
}
#ytvideowrap-{$ytid}{
    width:100%;
    height:100%;
    max-width: {$ytw}px;
}

        </style>
            
         <div id=\"ytvideowrap-{$ytid}\" style=\"z-index:102;\">
         <div id=\"yt-{$ytid}\" class=\"yt-{$ytid}\" style=\"border:4px solid {$video_border_color};\">
    ";
    
     if($logo_brand_code == 1){
     
        if($logo_brand_ps == 0){
            
             $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;left:0;top:0;z-index:101;\"></a>";
            
        }
        else if($logo_brand_ps == 1){
            
            $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;right:0;top:0;z-index:101;\"></a>";
            
        }
        else{
            //Do Nothing
        }
        
       $yt_video_full .=  $yt_logo_brand_code;
        
    }
    
    if($social_share == 1){
        
        $share_image = plugins_url( 'images/share_video.png' , __FILE__ );
        $fb_share_image = plugins_url( 'images/fb.png' , __FILE__ );
        $tw_share_image = plugins_url( 'images/tw.png' , __FILE__ );
        $gplus_share_image = plugins_url( 'images/gplus.png' , __FILE__ );
        $close_share_image = plugins_url( 'images/close.png' , __FILE__ );
        
        $yt_social_share = " <div class=\"share-video-{$ytid}\" style=\"display: none;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;width: 40px;z-index:100;
background: black;height: 30px;\">
        <a href=\"#\" class=\"share-video-bt-{$ytid}\" style=\"cursor: pointer;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;\"><img style=\"border:0;\" src=\"{$share_image}\" alt=\"share-video\" width=\"32\" height=\"32\"></a>
    </div>
    
    <div class=\"share-video-options-{$ytid}\" style=\"width: auto;height: 40px;background: black;position: absolute;top: 0;left: 0;right: 0;z-index:102;
margin: auto;text-align:center;display: none;\">
        
     <div class=\"share-options-{$ytid}\">
        <input type=\"text\" id=\"yt-link-{$ytid}\" style=\"position: absolute;left: 5px;width:45%;font-family:Arial;font-size:1.0em;height:auto;
 background:#008B8B;border:2px solid #000000;color: #FFFFFF;\"> 
         <div class=\"share-bt-main-{$ytid}\" style=\"position: absolute;margin-top: 8px;margin-left: 55%;\">
       <div class='share-button-{$ytid}'></div>
             </div>
    <a href=\"#\" class=\"share-close-bt-{$ytid}\" style=\"position: absolute;right: 5px;margin-top: 5px;cursor: pointer;\"><img style=\"border:0;\" src=\"{$close_share_image}\" alt=\"close_share\" width=\"30\" height=\"30\"></a>   
  </div>
    
    </div>";
    
    $yt_video_full .= $yt_social_share;
        
        
    }
    
    
    $yt_video_code = "
         <div id='{$ytid}'></div> 
      ";

    $yt_video_full .= $yt_video_code;
    
   $optin_form_code = "";
    
    $cta_code = "";
    
    $entry_anim = "";
    
    $exit_anim = "";
    
    $form_bg = "";
    
    $skip_fblike = "";
    
    $title_color = "";
    $title_text = "";
    
    $content_color = "";
    $content_text = "";
    
    $fbid = "";
    
    $button_color = "";
    $button_text = "";
    $button_text_color = "";
    
    $emailvalid_text_color = "";
    
    $optin_border_color = "";
    
    $optin_skip_text_color = "";
    
    $show_seconds = 0;
    
    $on_pause_show_optin = 0;
    
    $on_end_show_optin = 0;
    
    $optin_form_submit_url = "";
    
    
    $youtube_actions_record_sql = "SELECT * from " . $actions_table. " WHERE vid_id = " .$v_id;
    
    $actions_records = $wpdb->get_results($youtube_actions_record_sql);
    
        foreach($actions_records as $single_action){
        
            //set all variables to use 
            
            $entry_anim = $single_action->entry_anim;
            
            $exit_anim = $single_action->exit_anim;
            
            $on_pause = $single_action->on_pause;
            
            $on_end_show_optin = $single_action->on_end;
            
            $show_seconds = $single_action->show_seconds;
            
            $on_pause_show_optin = $single_action->on_pause;
            
            $skip_fblike = $single_action->skipfb;
            
            $fbid = $single_action->fbid;
        
            $fblike_show_seconds = $single_action->show_seconds;

            $fblike_skip_text = $single_action->skip_fb_text;

            $fblike_skip_text_color = $single_action->skip_fb_text_color;
            
            $fblike_title = $single_action->fb_title;
            
            $fblike_title_color = $single_action->fb_title_color;
            
            $optin_form_submit_url = plugins_url( 'emailmanager.php' , __FILE__ );
            
            $optin_form_id = $single_action->form_id;
                    
                $optin_detail = $wpdb->get_row("SELECT * from " . $optins_table. " WHERE id = " .$single_action->form_id);
                
                $form_bg = $optin_detail->bg;
    
                $title_color = $optin_detail->headcolor;
                $title_text =  $optin_detail->headtext;
    
                $content_color = $optin_detail->msgcolor;
                $content_text = $optin_detail->msgtext;
                
                $button_color = $optin_detail->butcolor;
                $button_text = $optin_detail->buttext;
                $button_text_color = $optin_detail->buttextcolor;
                
                $emailvalid_text_color = $optin_detail->emailvalidtxtcolor;
            
                $optin_border_color = $optin_detail->optinbordercolor;
            
                $optin_skip_text_color = $optin_detail->optinskiptxtcolor;
            
                $optin_timer_value = $single_action->show_seconds;
                
                $skip_optin_form = $optin_detail->skip_optin_text;
            
                $ar_type_optin_form = $optin_detail->ar;
            
        }
    
    

 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
     
    if($fbid == NULL || $fbid == 0 || $fbid == ''){
                     
                        $fbid = "145148092322424";
                 }
                        
$fb_form_fragment1 = "

          <div id=\"{$ytid}-box\" class=\"{$ytid}-box\" style=\"width:100%;height:100%;position: absolute;display: none;bottom: 0;left: 0;right:0;margin: auto;text-align:center;-webkit-border-radius: 0px;-moz-border-radius: 0px;border-radius: 0px;background:rgba(0, 0, 0, 0.5);z-index:101;\"></div>
          <div id=\"fb-root\"></div>
<script>
        
    (function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = \"//connect.facebook.net/en_US/sdk.js#xfbml=1&appId={$fbid}&version=v2.0\";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

jQuery(window).bind(\"load resize\", function(){    
  var container_width = jQuery('#fbcontainer-{$ytid}').width();    
    jQuery('#fbcontainer-{$ytid}').html('<div id=\"{$ytid}-fb-like\" class=\"fb-like\" data-href=\"{$pageURL}\" data-layout=\"button_count\" data-action=\"like\" data-show-faces=\"false\" data-share=\"false\"></div>');
    FB.XFBML.parse( );    
}); 
   
        window.fbAsyncInit = function() {
        
            FB.Event.subscribe('edge.create', function(response) {
               jQuery('#{$ytid}-fb-div').hide();
               jQuery(\"#{$ytid}\").tubeplayer(\"play\");
               jQuery('#{$ytid}-box').hide(); 
               jQuery.cookie('fblike_{$ytid}', 'fblike_{$ytid}',{ expires: 7, path: '/' });
});
            
        };
    
   
     </script>

    
    <div id=\"{$ytid}-fb-div\" style=\" position:absolute;top:0;bottom:0;left:0;right:0;margin:auto;text-align:center;width:0px;height: 0px;opacity: 0;overflow: hidden !important;word-wrap:break-word;z-index:101;line-height: 100%;\">
        <center><span style=\"color:{$fblike_title_color};\"><b>{$fblike_title}</b></span></center>
        <div id=\"fbcontainer-{$ytid}\" style=\"width:100%;\">
    <div id=\"{$ytid}-fb-like\" class=\"fb-like\" style=\"opacity: 0; overflow: hidden;height: 0px;\" data-href=\"{$pageURL}\" data-layout=\"button_count\" data-action=\"like\" data-show-faces=\"false\" data-share=\"false\"></div>        
        </div>";
            
            $yt_video_full .= $fb_form_fragment1; 
            
            
                     if($skip_fblike == 1){
                         
                  $yt_video_full .="       
           <div style=\"display:inline-block;\">
            
          <a href=\"#\" id=\"{$ytid}-fb-close-optin\" style=\"padding: 2px;text-align: center;color:{$fblike_skip_text_color};font-weight:bold;text-decoration: none;width: -moz-fit-content;
width: -webkit-fit-content;width: fit-content;\">{$fblike_skip_text}</a>
   
     </div>";
                         
                }


         $yt_video_full .="
                         
   </div>
</div>
</div>";
                        
   $yt_video_full .="<script type=\"text/javascript\">
      
    var forfb_d_{$ytid} = false; 
    var forfb_t_{$ytid};
    
jQuery(\"#{$ytid}\").tubeplayer({
	width: {$ytw}, 
	height: {$yth}, 
	allowFullScreen: \"true\", 
	initialVideo: \"{$yt_id_player}\", 
    start: 0, 
	preferredQuality: \"default\",
    showControls: {$show_controls},
	showRelated: 0, 
	autoPlay: {$autoplay}, 
	autoHide: {$auto_hide_cb}, //for controller 
	theme: \"{$player_theme}\", 
	color: \"red\", 
	showinfo: false, 
	modestbranding: true, 
	wmode: \"transparent\", // note: transparent maintains z-index, but disables GPU acceleration
	swfobjectURL: \"http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js\",
	loadSWFObject: true, 
	iframed: true,
    
    onPlayerPlaying: function(){
            
        fbt_{$ytid} = setInterval(hndoptin_fb_{$ytid},100);
         
    ";
   
        if($dim_flag == 1){
        
            $yt_dim = "
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).show();";    
        
         $yt_video_full .= $yt_dim;
            
        }
        
        $yt_video_full .=  "},"; //closes onPlayerPlaying
    
        $add_on_pause = "
        onPlayerPaused: function(){
        
        ";
            
             $yt_video_full .= $add_on_pause;
        
            
            if($dim_flag == 1){
            
                $add_on_pause_dim .= " jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();";
                    
            $yt_video_full .= $add_on_pause_dim;     
            }
            
        $close_on_pause = "},";
            
        $yt_video_full .= $close_on_pause;    
        
     $yt_video_full .= "});"; //closes main video code
          
        $optin_tmp_show_seconds = $optin_timer_value + 1;
    
        $handle_fb = "
        
        function hndoptin_fb_{$ytid}() {
         
         if(jQuery.cookie('fblike_{$ytid}') == 'fblike_{$ytid}'){
                    jQuery(\"#{$ytid}-fb-div\").hide();  
                   jQuery('#{$ytid}-box').hide(); 
                   
                    }

            else {    
            
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$optin_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$optin_tmp_show_seconds} && !forfb_d_{$ytid}) {
              jQuery(\"#{$ytid}\").tubeplayer(\"pause\");
              jQuery(\"#{$ytid}-fb-div\").show();  
              jQuery('#{$ytid}-box').show();
              jQuery('#{$ytid}-fb-like').css({'opacity':'1.0','height':'24px','overflow':'hidden'});
              jQuery('#{$ytid}-fb-div').css({'opacity':'1.0','height':'110px','width':'90%'});
              forfb_d_{$ytid} = true;
        }
        
        }
    }
    
       ";
    
          $yt_video_full .= $handle_fb;

                      
               if($skip_fblike == 1){
                   
                   $yt_video_full .= "
                   jQuery( \"#{$ytid}-fb-close-optin\" ).click(function(e) {
                e.preventDefault();
        jQuery(\"#{$ytid}-fb-div\").hide();       
        jQuery(\"#{$ytid}\").tubeplayer(\"play\");
        jQuery('#{$ytid}-box').hide();
       
});

        "; 
    
                          }
    
    
    if($social_share == 1){
        
     $yt_social_script_code = " jQuery( '.share-close-bt-{$ytid}' ).click(function(event) {
                  event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').hide();
             });
             
            jQuery('.yt-{$ytid}').mouseover(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').show();
            });

            jQuery('.yt-{$ytid}').mouseout(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').hide();
            });  
 
             jQuery( '.share-video-bt-{$ytid}' ).click(function(event) {
                 event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').fadeIn();
                 jQuery( '#yt-link-{$ytid}' ).val((jQuery('#{$ytid}').tubeplayer('data').videoURL));
             });
             
              config{$ytid} = {
              
              networks: {
              
                  google_plus: {
                    enabled: true,        
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}' 
                   },
                   
                  twitter: {
                    enabled: true, 
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}'    
                   },
                   
                  facebook: {
                    enabled: true,
                    load_sdk: false,         
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}',
                    app_id: 145148092322424,
                   },
                   
                  pinterest: {
                    enabled: false
                   },
                   
                  email: {
                    enabled: false
                   }
               }
             } 
             
             var share{$ytid} = new Share('.share-button-{$ytid}',config{$ytid});
             
    
             ";
        
        $yt_video_full .= $yt_social_script_code; 
    }
                
    
    $yt_video_full .= "</script>";    
    
    
     return $yt_video_full;
    
}




function video_fb_plus_cta_shortcode($v_id){
    
    global $wpdb;
    
    $youtube_video_table = $wpdb->prefix . 'prash_videos';
    
    $actions_table = $wpdb->prefix . 'prash_actions';
    
    $optins_table = $wpdb->prefix . 'prash_optins';
        
    $youtube_video_record_sql = "SELECT * from " . $youtube_video_table. " WHERE id = " .$v_id;
    
    $youtube_video_record = $wpdb->get_row($youtube_video_record_sql);
    
    $ytid = $youtube_video_record->vid;
    $yt_id_player = $youtube_video_record->youtube;
    $ytw = $youtube_video_record->width;
    $yth = $youtube_video_record->height;
    
    $autoplay = $youtube_video_record->autoplay;
    
    $dim_flag = $youtube_video_record->dim;
    $scroll_flag = $youtube_video_record->scroll_pause;
    
    $show_controls = $youtube_video_record->controls;
    
    $auto_hide_cb = $youtube_video_record->auto_hide_cb;
    
    $player_theme = $youtube_video_record->theme;
    
    $video_border_color = $youtube_video_record->vbordercolor;
    
     $social_share = $youtube_video_record->social_share;
    
      $logo_brand_code = $youtube_video_record->logo_brand_code;
    
    $logo_brand_pick = $youtube_video_record->logo_pick;
    
    $logo_brand_link = $youtube_video_record->logo_link;
    
    $logo_brand_ps = $youtube_video_record->logo_ps;
 
     if($dim_flag == 1){
    
        $dim_image = plugins_url( 'images/dim.png' , __FILE__ );
    
    $yt_dim = "<div id=\"dim_video_{$ytid}\" style=\"position:fixed;background-image:url({$dim_image});left:0; top:0; width:100%;\"></div>
    
    <script>
     jQuery(document).ready(function(){
               
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();            
        });
   
    </script>
    
    ";
        
    $yt_video_full .= $yt_dim;
    }

      $yt_video_full .= "
    
      <style>
     #yt-{$ytid} {
    position:relative;
    padding-bottom:55.50%;
    height:0;
}

#yt-{$ytid} iframe, #yt-{$ytid} object, #yt-{$ytid} embed {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
}
#ytvideowrap-{$ytid}{
    width:100%;
    height:100%;
    max-width: {$ytw}px;
}

        </style>
            
         <div id=\"ytvideowrap-{$ytid}\" style=\"z-index:102;\">
         <div id=\"yt-{$ytid}\" class=\"yt-{$ytid}\" style=\"border:4px solid {$video_border_color};\">";
    
     if($logo_brand_code == 1){
     
        if($logo_brand_ps == 0){
            
             $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;left:0;top:0;z-index:101;\"></a>";
            
        }
        else if($logo_brand_ps == 1){
            
            $yt_logo_brand_code = "<a href=\"{$logo_brand_link}\" target=\"_blank\" id=\"logo_br_{$ytid}\" style=\"background: url({$logo_brand_pick}) no-repeat center;width: 60px;height:40px;display: inline-block;position:absolute;right:0;top:0;z-index:101;\"></a>";
            
        }
        else{
            //Do Nothing
        }
        
       $yt_video_full .=  $yt_logo_brand_code;
        
    }
    
     if($social_share == 1){
         
        $share_image = plugins_url( 'images/share_video.png' , __FILE__ );
        $fb_share_image = plugins_url( 'images/fb.png' , __FILE__ );
        $tw_share_image = plugins_url( 'images/tw.png' , __FILE__ );
        $gplus_share_image = plugins_url( 'images/gplus.png' , __FILE__ );
        $close_share_image = plugins_url( 'images/close.png' , __FILE__ );
        
        $yt_social_share = "<div class=\"share-video-{$ytid}\" style=\"display: none;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;width: 40px;z-index:100;
background: black;height: 30px;\">
        <a href=\"#\" class=\"share-video-bt-{$ytid}\" style=\"cursor: pointer;position: absolute;top: 0;left: 0;right: 0;margin: auto;text-align:center;\"><img style=\"border:0;\" src=\"{$share_image}\" alt=\"share-video\" width=\"32\" height=\"32\"></a>
    </div>
    
    <div class=\"share-video-options-{$ytid}\" style=\"width: auto;height: 40px;background: black;position: absolute;top: 0;left: 0;right: 0;z-index:102;
margin: auto;text-align:center;display: none;\">
        
     <div class=\"share-options-{$ytid}\">
        <input type=\"text\" id=\"yt-link-{$ytid}\" style=\"position: absolute;left: 5px;width:45%;font-family:Arial;font-size:1.0em;height:auto;
 background:#008B8B;border:2px solid #000000;color: #FFFFFF;\"> 
         <div class=\"share-bt-main-{$ytid}\" style=\"position: absolute;margin-top: 8px;margin-left: 55%;\">
      <div class='share-button-{$ytid}'></div>
             </div>
    <a href=\"#\" class=\"share-close-bt-{$ytid}\" style=\"position: absolute;right: 5px;margin-top: 5px;cursor: pointer;\"><img style=\"border:0;\" src=\"{$close_share_image}\" alt=\"close_share\" width=\"30\" height=\"30\"></a>   
  </div>
    
    </div>";
    
    $yt_video_full .= $yt_social_share;
        
        
    }

      $yt_video_code = "
         <div id='{$ytid}'></div> 
      ";

    $yt_video_full .= $yt_video_code;
    
    $optin_form_code = "";
    
    $cta_code = "";
    
    $entry_anim = "";
    
    $exit_anim = "";
    
    $form_bg = "";
    
    $skip_fblike = "";
    
    $fbid = "";
    
    $title_color = "";
    $title_text = "";
    
    $content_color = "";
    $content_text = "";
    
    $button_color = "";
    $button_text = "";
    $button_text_color = "";
    
    $emailvalid_text_color = "";
    
    $optin_border_color = "";
    
    $optin_skip_text_color = "";
    
    $show_seconds = 0;
    
     $buy_now_link = "";
    
    $buy_now_tp  = "";
    
     $buy_now_show  = "";
    
    $on_pause_show_optin = 0;
    
    $on_end_show_optin = 0;
    
    $optin_form_submit_url = "";
    
       $youtube_actions_record_sql = "SELECT * from " . $actions_table. " WHERE vid_id = " .$v_id;
    
    $actions_records = $wpdb->get_results($youtube_actions_record_sql);

    
        foreach($actions_records as $single_action){
        
            //set all variables to use 
            
//            $entry_anim = $single_action->entry_anim;
            
            $exit_anim = $single_action->exit_anim;
            
            $on_pause = $single_action->on_pause;
            
//            $on_end_show_optin = $single_action->on_end;
            
            $show_seconds = $single_action->show_seconds;
            
//            $on_pause_show_optin = $single_action->on_pause;
            
            $optin_form_submit_url = plugins_url( 'ext.php' , __FILE__ );
         
            if($single_action->act_type == 'fblike'){
            
            $optin_form_id = $single_action->form_id;
            
            $entry_anim = $single_action->entry_anim;
                
            $on_pause_show_optin = $single_action->on_pause;    
                
            $on_end_show_optin = $single_action->on_end;
                
            $skip_fblike = $single_action->skipfb;
            
            $fbid = $single_action->fbid;    
                
            $fblike_skip_text = $single_action->skip_fb_text;

            $fblike_skip_text_color = $single_action->skip_fb_text_color;
                
            $fblike_title = $single_action->fb_title;
            
            $fblike_title_color = $single_action->fb_title_color;    
                
            $optin_detail = $wpdb->get_row("SELECT * from " . $optins_table. " WHERE id = " .$single_action->form_id);
                
           
                $form_bg = $optin_detail->bg;
    
                $title_color = $optin_detail->headcolor;
                $title_text =  $optin_detail->headtext;
    
                $content_color = $optin_detail->msgcolor;
                $content_text = $optin_detail->msgtext;
                
                $button_color = $optin_detail->butcolor;
                $button_text = $optin_detail->buttext;
                $button_text_color = $optin_detail->buttextcolor;
                
                $emailvalid_text_color = $optin_detail->emailvalidtxtcolor;
                
                $optin_border_color = $optin_detail->optinbordercolor;
                
                $optin_skip_text_color = $optin_detail->optinskiptxtcolor;
                
                $skip_optin_form = $optin_detail->skip_optin_text;
                
                $ar_type_optin_form = $optin_detail->ar;
                
            }
            
             else if($single_action->act_type == 'ctat'){
                  
            $cta_text = $single_action->cta_text;
            $cta_link = $single_action->img_link;
            $cta_bg_color = $single_action->cta_bg_color;
            $cta_text_color = $single_action->cta_text_color;
            $buy_now_link = $single_action->buy_now_link;
            $buy_now_tp = $single_action->buy_now_tp;
            $buy_now_show = $single_action->buy_now_code;
            $custom_bt_code = $single_action->ct_bt_code;
            $custom_bt_bgcolor = $single_action->ct_bt_bgcolor;
            $custom_bt_bcolor = $single_action->ct_bt_bcolor;
            $custom_bt_tcolor = $single_action->ct_bt_tcolor;
            $custom_bt_text = $single_action->ct_bt_text;
            $custom_bt_link = $single_action->ct_bt_link;     
             }
            
//            else if($single_action->act_type == 'ctai'){
//                  
//            $cta_image = $single_action->img_url;
//            $cta_link = $single_action->img_link;
//            
//            }
            
        }
    
     $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }

        if($fbid == NULL || $fbid == 0 || $fbid == ''){
                     
                        $fbid = "145148092322424";
                 }

    $fb_form_fragment1 = "

          <div id=\"{$ytid}-box\" class=\"{$ytid}-box\" style=\"max-width:100%;width:100%;height:100%;position: absolute;display: none;bottom: 0;left: 0;right:0;margin: auto;text-align:center;-webkit-border-radius: 0px;-moz-border-radius: 0px;border-radius: 0px;background:rgba(0, 0, 0, 0.5);z-index:101;\"></div>
                
        <div id=\"fb-root\"></div>
<script>
        
    (function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = \"//connect.facebook.net/en_US/sdk.js#xfbml=1&appId={$fbid}&version=v2.0\";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

jQuery(window).bind(\"load resize\", function(){    
  var container_width = jQuery('#fbcontainer-{$ytid}').width();    
    jQuery('#fbcontainer-{$ytid}').html('<div id=\"{$ytid}-fb-like\" class=\"fb-like\" data-href=\"{$pageURL}\" data-layout=\"button_count\" data-action=\"like\" data-show-faces=\"false\" data-share=\"false\"></div>');
    FB.XFBML.parse( );    
}); 
   
        window.fbAsyncInit = function() {
        
            FB.Event.subscribe('edge.create', function(response) {
               jQuery('#{$ytid}-fb-div').hide();
               jQuery(\"#{$ytid}\").tubeplayer(\"play\");
               jQuery('#{$ytid}-box').hide(); 
               jQuery.cookie('fblike_{$ytid}', 'fblike_{$ytid}',{ expires: 7, path: '/' });
});
            
        };
    
   
     </script>

    
    <div id=\"{$ytid}-fb-div\" style=\" position:absolute;top:0;bottom:0;left:0;right:0;margin:auto;text-align:center;width:0px;height: 0px;opacity: 0;overflow: hidden !important;word-wrap:break-word;z-index:101;line-height: 100%;\">
        <center><span style=\"color:{$fblike_title_color};\"><b>{$fblike_title}</b></span></center>
         <div id=\"fbcontainer-{$ytid}\" style=\"width:100%;\">
    <div id=\"{$ytid}-fb-like\" class=\"fb-like\" style=\"opacity: 0; overflow: hidden;height: 0px;\" data-href=\"{$pageURL}\" data-layout=\"button_count\" data-action=\"like\" data-show-faces=\"false\" data-share=\"false\"></div></div>";
         
                 $yt_video_full .= $fb_form_fragment1; 

      if($skip_fblike == 1){
                         
                  $yt_video_full .="       
           <div style=\"display:inline-block;\">
            
          <a href=\"#\" id=\"{$ytid}-fb-close-optin\" style=\"padding: 2px;text-align: center;color:{$fblike_skip_text_color};font-weight:bold;text-decoration: none;width: -moz-fit-content;
width: -webkit-fit-content;width: fit-content;\">{$fblike_skip_text}</a>
   
     </div>";
                         
                }
    
        $yt_video_full .="
         
   </div>
</div>

<div id=\"{$ytid}_call_to_action\" style=\"position:relative;width:auto;height:auto;\">
              
              <i class=\"open_cta_bt_{$ytid} icon-cta-open\" style=\"position: absolute;right:0;color: #00008B; 
 cursor: pointer;display: none;z-index: 1;\"></i>
              <i class=\"close_cta_bt_{$ytid} icon-cta-close\" style=\"position: absolute;right:0;cursor: pointer;display: none;color: #00008B;z-index: 1;\"></i>
              
            <div id=\"{$ytid}_cta_text\" style=\"width:auto;height:auto;-webkit-border-radius: 0px;-moz-border-radius: 0px;
border-radius: 0px;background-color:{$cta_bg_color};word-wrap:break-word;display: none;padding-bottom:5px;padding-left:2px;padding-right:2px;\">

           <div style=\"padding: 6px;\"></div>
            <div style=\"width:100%;max-width:{$ytw}px;\">";
    
              if($buy_now_show == 1){
                
           $yt_video_full .= "<div><a href=\"{$buy_now_link}\" target=\"_blank\" id=\"buy_now_link_{$ytid}\" style=\"background: url({$buy_now_tp}) no-repeat center;width: 150px;height:50px;display: inline-block;text-align:center;\"></a></div>
           ";
           
           }
                
                if($custom_bt_code == 1){
                
           $yt_video_full .= "<a href=\"{$custom_bt_link}\" target=\"_blank\" id=\"custom_bt_{$ytid}\" class=\"custom_bt_{$ytid}\" style=\"background-color:{$custom_bt_bgcolor};-moz-border-radius:28px;-webkit-border-radius:28px;border-radius:28px;display:inline-block;cursor:pointer;color:{$custom_bt_tcolor};font-family:Courier New;font-size:14px;font-weight:bold;padding:4px 8px;font-style:italic;text-decoration:none;border:2px solid {$custom_bt_bcolor};text-align:center;\">{$custom_bt_text}</a> 
               <br><br>";
           
           }
           
           $yt_video_full .="
           <div>{$cta_text}</div>
           </div>
              </div>
              </div>
              </div>

                ";

       $video_code = "<script type=\"text/javascript\">
      
    var forfb_d_{$ytid} = false; 
    var forfb_t_{$ytid};
    
    var forcta_d_{$ytid} = false; 
    var forcta_t_{$ytid};
    
jQuery(\"#{$ytid}\").tubeplayer({
	width: {$ytw}, 
	height: {$yth}, 
	allowFullScreen: \"true\", 
	initialVideo: \"{$yt_id_player}\", 
    start: 0, 
	preferredQuality: \"default\",
    showControls: {$show_controls},
	showRelated: 0, 
	autoPlay: {$autoplay}, 
	autoHide: {$auto_hide_cb}, //for controller 
	theme: \"{$player_theme}\", 
	color: \"red\", 
	showinfo: false, 
	modestbranding: true, 
	wmode: \"transparent\", // note: transparent maintains z-index, but disables GPU acceleration
	swfobjectURL: \"http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js\",
	loadSWFObject: true, 
	iframed: true,     
    ";
    
    $yt_video_full .= $video_code;

    $cta_timer_value = 0;
    
    $optin_timer_value = 0;
    
    $cta_type_code = "";
          
        $yt_video_full .= "onPlayerPlaying: function(){";
        
        foreach($actions_records as $single_action_2){
    
        if($single_action_2->act_type == 'fblike'){
            
        $optin_timer_value = $single_action_2->show_seconds;    
        $optin_player = "
        
        fbt_{$ytid} = setInterval(hndfb_{$ytid},100);
         
    ";
    
        $yt_video_full .= $optin_player;
            
        }
            
    else if($single_action_2->act_type == 'ctai' || $single_action_2->act_type == 'ctat'){
            
            $cta_timer_value = $single_action_2->show_seconds;
            
            $cta_type_code = $single_action_2->act_type;
            
            $cta_player = "
            
            forcta_t_{$ytid} = setInterval(hndcta_{$ytid},100);
            
            ";
            
            $yt_video_full .= $cta_player;
        
        }
        
            
    } //main actions loop
    
     if($dim_flag == 1){
        
            $yt_video_full .= "
            jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).show();    
            ";
        
        }
        
     $yt_video_full .= "},"; //closes onPlayerPlaying
    
      $add_on_pause = "
        onPlayerPaused: function(){
        
        ";
    
     $yt_video_full .= $add_on_pause; 
        
            if($dim_flag == 1){
            
                $add_on_pause_dim .= " jQuery(\"#dim_video_{$ytid}\").css(\"height\", jQuery(document).height()).hide();";
        
                 $yt_video_full .=  $add_on_pause_dim; 
                
            }      
            
        $close_on_pause = "},";
            
        $yt_video_full .= $close_on_pause;    


    $yt_video_full .= "});"; //closes main video code
    
      $optin_tmp_show_seconds = $optin_timer_value + 1;
    
        $handle_fb = "
        
        function hndfb_{$ytid}() {
        
         if(jQuery.cookie('fblike_{$ytid}') == 'fblike_{$ytid}'){
                   jQuery(\"#{$ytid}-fb-div\").hide(); 
                   jQuery('#{$ytid}-box').hide(); 
                    }
                    
            else {        
        
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$optin_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$optin_tmp_show_seconds} && !forfb_d_{$ytid}) {
              jQuery(\"#{$ytid}\").tubeplayer(\"pause\");
              jQuery(\"#{$ytid}-fb-div\").show();  
             jQuery('#{$ytid}-box').show();  
             jQuery('#{$ytid}-fb-like').css({'opacity':'1.0','height':'24px','overflow':'hidden'});
              jQuery('#{$ytid}-fb-div').css({'opacity':'1.0','height':'110px','width':'90%'});
             forfb_d_{$ytid} = true;
        }
        
        }
        
    }
                ";
            
             $yt_video_full .= $handle_fb;
    
      if($skip_fblike == 1){
                  
                  $yt_video_full .="
                  jQuery( \"#{$ytid}-fb-close-optin\" ).click(function(e) {
                e.preventDefault();
         jQuery(\"#{$ytid}-fb-div\").hide();          
        jQuery(\"#{$ytid}\").tubeplayer(\"play\");
        jQuery('#{$ytid}-box').hide();    

});

        "; 
                   
              }

      $cta_tmp_show_seconds = $cta_timer_value + 1;
    
    if($cta_type_code == 'ctat'){
    
        $handle_cta_text = "
        
        function hndcta_{$ytid}() {
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$cta_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$cta_tmp_show_seconds} && !forcta_d_{$ytid}) {

            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_text').slideDown(1000);
             forcta_d_{$ytid} = true;
        }
    }

        
        "; 
        
        $yt_video_full .= $handle_cta_text;

    } else if($cta_type_code == 'ctai'){
      $handle_cta_img = "
        
        function hndcta_{$ytid}() {
        if (jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime >= {$cta_timer_value} && jQuery(\"#{$ytid}\").tubeplayer(\"data\").currentTime <= {$cta_tmp_show_seconds} && !forcta_d_{$ytid}) {

            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_image').slideDown(1000);
             forcta_d_{$ytid} = true;
        }
    }

        
        ";
         
         $yt_video_full .= $handle_cta_img;
         
     }
     

     if($cta_type_code == 'ctat'){
            
            
            $cta_text_code = "
            
            jQuery('.open_cta_bt_{$ytid}').click(function(e){
             e.preventDefault();
            jQuery('.open_cta_bt_{$ytid}').hide();
            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_text').slideDown(1000);
             
        });
              jQuery('.close_cta_bt_{$ytid}').click(function(e){
               e.preventDefault();
            jQuery('.close_cta_bt_{$ytid}').hide();
            jQuery('.open_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_text').slideUp(1000);       
        });
             

            
            ";
            
             $yt_video_full .= $cta_text_code;
        
        }else if($cta_type_code == 'ctai'){
            
            
            $cta_img_code = "
            
                  jQuery('.open_cta_bt_{$ytid}').click(function(e){
             e.preventDefault();
            jQuery('.open_cta_bt_{$ytid}').hide();
            jQuery('.close_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_image').slideDown(1000);
             
        });
              jQuery('.close_cta_bt_{$ytid}').click(function(e){
             e.preventDefault();
            jQuery('.close_cta_bt_{$ytid}').hide();
            jQuery('.open_cta_bt_{$ytid}').show();
            jQuery('#{$ytid}_cta_image').slideUp(1000);       
        });  
                         
            ";
            
             $yt_video_full .= $cta_img_code;
        
        }
    
     if($social_share == 1){
        
     $yt_social_script_code = " jQuery( '.share-close-bt-{$ytid}' ).click(function(event) {
                   event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').hide();
             });
             
            jQuery('.yt-{$ytid}').mouseover(function(event) {
            event.preventDefault();
            jQuery('.share-video-{$ytid}').show();
            });

            jQuery('.yt-{$ytid}').mouseout(function(event) {
             event.preventDefault();
            jQuery('.share-video-{$ytid}').hide();
            });  
  
             jQuery( '.share-video-bt-{$ytid}' ).click(function(event) {
                 event.preventDefault();
                 jQuery('.share-video-options-{$ytid}').fadeIn();
                 jQuery( '#yt-link-{$ytid}' ).val((jQuery('#{$ytid}').tubeplayer('data').videoURL));
             });
             
              config{$ytid} = {
              
              networks: {
              
                  google_plus: {
                    enabled: true,        
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}' 
                   },
                   
                  twitter: {
                    enabled: true, 
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}'    
                   },
                   
                  facebook: {
                    enabled: true,
                    load_sdk: false,         
                    url: 'https://www.youtube.com/watch?v={$yt_id_player}',
                    app_id: 145148092322424,
                   },
                   
                  pinterest: {
                    enabled: false
                   },
                   
                  email: {
                    enabled: false
                   }
               }
             } 
             
             var share{$ytid} = new Share('.share-button-{$ytid}',config{$ytid});
             
             ";
        
        $yt_video_full .= $yt_social_script_code; 
    }
    
     $yt_video_full .= "</script>";    
           
    return $yt_video_full;

 
}
