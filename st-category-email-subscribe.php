<?php
/**
	Plugin Name: St Category Email Subscribe
	Plugin URI: http://www.sanskrutitech.in
	Description: Plugin that allows Users to Subscribe for Emails based on Category.They will receive an email when a post is published in the category they have subscribed to.
	Version: 0.6
	Author: Sanskruti Technologies
	Author URI: http://www.sanskrutitech.in
	Author Email: dhara@sanskrutitech.in
	License: GPL

	Copyright 2014 Sanskruti Technologies  (email : info@sanskrutitech.in)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	GNU General Public License: http://www.gnu.org/licenses/gpl.html
	
  TO Do :

  100 	Allow to edit subscriber
  110	Logs of Email Sent. Failures etc...  
  120	Import Subscribers
  130	Send Email when someone subscribes. According to setting
  140	Send Email when someone unsubscribes. According to setting
  150	Send Email of Log of Email sends
  160	How to Import Export Category
  170	Send Email to user for confirmation
  180	Allow to select multiple categories
  190	Allow user to update their subscription / unsubscribe
 */
 

/* If no Wordpress, go home */

if (!defined('ABSPATH')) { exit; }

/* Load Language */
add_action( 'plugins_loaded', 'st_email_load_textdomain' );

function st_email_load_textdomain() {
	load_plugin_textdomain('stemail', false,  dirname( plugin_basename( __FILE__ ) ) . "/language/");
}	

define('WP_ST_CATEGORY_EMAIL_FOLDER', dirname(plugin_basename(__FILE__)));
define('WP_ST_CATEGORY_EMAIL_URL', plugins_url('', __FILE__));

/**
 * 2. Global Parameters
 */
 
global $st_email_table_suffix;
global $st_category_email_db_ver;


$st_category_email_db_ver = "0.6";
$st_email_table_suffix = "st_category_email";

/**
 * 3. Activation / deactivation
 */
 
register_activation_hook(__FILE__, 'st_category_email_install');
register_deactivation_hook(__FILE__, 'st_category_email_uninstall');

function st_category_email_install() {
	global $wpdb;
	global $st_category_email_db_ver;
	global $st_email_table_suffix;
	
	$st_email_table = $wpdb->prefix . $st_email_table_suffix;
	
	$db_ver=get_option('st_category_email_db_ver',"0.5");
	$db_ver=(float) $db_ver;

	$st_category_email_db_ver = (float) $st_category_email_db_ver;
	
	/** If Updating from an older version */
	if($db_ver < $st_category_email_db_ver)
	{
		if($db_ver >= 0.5){
			$sql = "ALTER TABLE $st_email_table CHANGE st_category st_category VARCHAR(100);";
			$wpdb->query($sql);
		}
	}
	/* If new installation*/ 
	else{
		//Create table for subscribers
		$sql = "CREATE TABLE $st_email_table  (
			st_id INT(9) NOT NULL AUTO_INCREMENT,
			st_name VARCHAR(200),
			st_email VARCHAR(200) NOT NULL,
			st_category VARCHAR(100),
			UNIQUE KEY st_id (st_id)
		);";
	
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	//Set DB Version
	update_option("st_category_email_db_ver", $st_category_email_db_ver);
	
    //Set Send Email
	update_option( 'st_category_email_send_email', get_option('admin_email') );
	
	//Set From Name
	update_option( 'st_category_email_from_name', get_option('blogname') );
}

function st_category_email_uninstall() {
	/** Do Nothing **/	
}

/** Short Code to display Subscription Form **/
add_shortcode("st_category_subscribe_form", "st_category_email_subscribe_shortcode");

/** Admin Page **/
if (is_admin()) {
    require_once dirname(__FILE__) . '/st_category_email_subscribe_admin.php';
    add_action('admin_print_scripts', 'st_category_email_subscribe_admin_scripts');
}
function st_category_email_subscribe_admin_scripts() {
	wp_register_style('st-category-email-style.css',WP_ST_CATEGORY_EMAIL_URL.'/css/style.css');
	wp_enqueue_style('st-category-email-style.css');
	
	wp_register_style('st-category-email-multiple-select.css',WP_ST_CATEGORY_EMAIL_URL.'/css/multiple-select.css');
	wp_enqueue_style('st-category-email-multiple-select.css');
	
	wp_enqueue_script('jquery');
	
	wp_enqueue_script( 'st-category-email-jquery.multiple.select.js', WP_ST_CATEGORY_EMAIL_URL . '/scripts/jquery.multiple.select.js', array(), '1.0.0', true );
	//wp_enqueue_script( 'st-category-email-jquery.csv.js', WP_ST_CATEGORY_EMAIL_URL . '/scripts/jquery.csv-0.71.min.js', array(), '1.0.0', true );
	wp_enqueue_script( 'st-category-email-admin_scripts.js', WP_ST_CATEGORY_EMAIL_URL . '/scripts/admin_scripts.js', array(), '1.0.0', true );
}

add_action( 'wp_enqueue_scripts', 'st_category_email_subscribe_scripts' );

function st_category_email_subscribe_scripts() {
	wp_register_style('st-category-email-multiple-select.css',WP_ST_CATEGORY_EMAIL_URL.'/css/multiple-select.css');
	wp_enqueue_style('st-category-email-multiple-select.css');
	
	wp_enqueue_script('jquery');
	wp_enqueue_script( 'st-category-email-jquery.multiple.select.js', WP_ST_CATEGORY_EMAIL_URL . '/scripts/jquery.multiple.select.js', array(), '1.0.0', true );
	wp_enqueue_script( 'st-category-email-scripts.js', WP_ST_CATEGORY_EMAIL_URL . '/scripts/scripts.js', array(), '1.0.0', true );
}

function st_category_email_subscribe_form($atts){
	extract($atts);
	
	$return = '<form class="st_subscribe_form" method="post"><input class="st_hiddenfield" name="st_subscribe_form" type="hidden" value="1">';
	
	if ($prepend) $return .= '<p class="st_prepend">'.$prepend.'</p>';
	
	if ($_POST['st_subscribe_form'] && $thankyou) { 
		if (!is_email($_POST['st_email']))
		{
			$return .= '<p class="st_error">' . _e('Please Check.Email Address is Invalid','stemail') . '</p>'; 
		}elseif ($thankyou){
			if ($jsthanks) {
				$return .= "<script>window.onload = function() { alert('".$thankyou."'); }</script>";
			} else {
				$return .= '<p class="st_thankyou">'.$thankyou.'</p>'; 
			}
		}	
	}
	
	if ($showname) $return .= '<p class="st_name"><label class="st_namelabel" for="st_name">'.$nametxt.'</label><input class="st_nameinput" placeholder="'.$nameholder.'" name="st_name" type="text" value=""></p>';
	$return .= '<p class="st_email"><label class="st_emaillabel" for="st_email">'.$emailtxt.'</label><input class="st_emailinput" name="st_email" placeholder="'.$emailholder.'" type="text" value=""></p>';
	
	$select_cats = wp_dropdown_categories("name=st_category[]&id=st_category&echo=0&hide_empty=0&hierarchical=1");	
	$select_cats = str_replace( 'id=', 'multiple="multiple" id=', $select_cats );
	if ($showcategory) $return .= '<p class="st_category"><label class="st_categorylabel" for="st_category">'.$categorytxt.'</label><br/>'  . $select_cats . '</p>';
	$return .= '<p class="st_submit"><input name="submit" class="btn st_submitbtn" type="submit" value="'.($submittxt?$submittxt:'Submit').'"></p>';
	
	$return .= '</form>';
	
 	return $return;
}

function st_category_email_subscribe_shortcode($atts=array()){
	$atts = shortcode_atts(array(
		"prepend" => 'Like our posts? Subscribe to our newsletter',  
        "showname" => true,
		"nametxt" => 'Name:',
		"nameholder" => 'Name...',
		"emailtxt" => 'Email:',
		"emailholder" => 'Email Address...',
		"showcategory" => true,
		"categorytxt" => 'Category:',
		"submittxt" =>'Submit',
		"jsthanks" => false,
		"thankyou" => 'Thank you for subscribing to our mailing list'
    ), $atts);
	
	return st_category_email_subscribe_form($atts);
}
// Handle form Post
if ($_POST['st_subscribe_form']) {
	
	global $wpdb;
	global $st_email_table_suffix;
    $subscribers_table = $wpdb->prefix . $st_email_table_suffix;
	
	$name = $_POST['st_name'];
	$email = $_POST['st_email'];
	$category = $_POST['st_category'];
	$category = implode(",",$_POST['st_category']);
	
	if (is_email($email)) {
		$exists = $wpdb->get_results("SELECT * FROM ".$subscribers_table." where st_email like '".esc_sql($email)."' limit 1");
		print_r($exists);
		//if (mysql_num_rows($exists) <1) {
		//	$wpdb->insert($subscribers_table,array('st_name'=>esc_sql($name), 'st_email'=>esc_sql($email),'st_category'=>$category));
		//}
	}
}

function st_apply_template($post_detail,$template){
	include( $template );
	//Blog Name
	$st_category_email_template = str_replace('%blog_name%',$post_detail['blog_name'],$st_category_email_template);
	//Post Title
	$st_category_email_template = str_replace('%post_title%',$post_detail['post_title'],$st_category_email_template);
	//Post Link
	$st_category_email_template = str_replace('%post_link%',$post_detail['post_link'],$st_category_email_template);
	//Author Link
	$st_category_email_template = str_replace('%author_link%',$post_detail['author_link'],$st_category_email_template);
	//Author Name
	$st_category_email_template = str_replace('%author_name%',$post_detail['author_name'],$st_category_email_template);
	//Post Content
	$st_category_email_template = str_replace('%post_content%',$post_detail['post_content'],$st_category_email_template);
	//Post Date
	$st_category_email_template = str_replace('%post_date%',date("M d,Y",strtotime($post_detail['post_date'])),$st_category_email_template);
	
	
	//March 7, 2014 at 5:08 pm
	return $st_category_email_template;
}
//Send Email on Publish Post
add_action('publish_post','st_send_email');

function st_set_html_content_type() {
	return 'text/html';
}

//send notification e-mail on story publish
function st_send_email($post_ID){
	global $wpdb;
	global $st_email_table_suffix;

	$table_name = $wpdb->prefix . $st_email_table_suffix;
	
	$send_email = get_option( 'st_category_email_send_email' );
	$from_name = get_option( 'st_category_email_from_name' );
	
	//From Name <Email>
	$headers[] = 'From: '.$from_name.' <'.$send_email.'>';
	
	
	$post = get_post($post_ID); 
	// Post Title
	$subject = $post->post_title;
	$post_detail['post_title'] = $post->post_title;
	$post_detail['post_date'] = $post->post_date;
	//Post Link
	$post_detail['post_link'] = get_permalink( $post_ID );
	//Author
	$post_detail['author_name'] = get_the_author_meta( 'display_name', $post->post_author );
	$post_detail['author_link'] = get_the_author_meta( 'display_name', $post->post_author );
	
	//Blog Name
	$post_detail['blog_name']  = get_bloginfo('name');
	
	//Template
	
	// Post Content
	$post_detail['post_content']=$post->post_content;
	$body = st_apply_template($post_detail,'templates/template1.php');
	// Get the Categories of the Post
	$categories = get_the_category($post_ID);
	//Get all the email address who have subscribed to this categories	
	if($categories){
		foreach($categories as $category) {
			$table_result = $wpdb->get_results("SELECT * FROM ".$table_name." where st_category = ".$category->term_id ." OR st_category = 0");
			foreach ( $table_result as $table_row ) 
			{
				$headers[] = 'Bcc: '.$table_row->st_name.' <'.$table_row->st_email.'>';
			}
		}
	}

	//get e-mail address from post meta field
	$email_address = get_option( 'st_category_email_send_email' );
	add_filter('wp_mail_content_type', 'st_set_html_content_type');
	
	if(wp_mail($email_address, $subject, $body, $headers)){
		//mail sent!
		
	} else {
		//failure
	}
}


/**
 * Add function to widgets_init that'll load our widget.
 */
 
 add_action('widgets_init','st_category_email_subscribe_load_widget');
 
 
 
 class st_category_email_subscribe_widget extends WP_Widget
 {
 
	/**
	 * Widget setup.
	 */
	 function __construct() {
		parent::__construct(
		// Base ID of your widget
		'st_category_email_subscribe_widget', 

		// Widget name will appear in UI
		__('Category Email Subscribe Form', 'stemail'), 

		// Widget description
		array( 'description' => __( 'An Widget that display Subscriber Form', 'stemail' ), ) 
		);
	}

	
	/**
	 * How to display the widget on the screen.
	 */
	 
	function widget($args,$instance)
	{
		extract($args);
		
		$title=apply_filters('widget_title',$instance['title']);
		echo $args['before_widget'];

		if ( $title )
		{
			echo $before_title . $title . $after_title;
		}
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		
		echo st_category_email_subscribe_form($instance);
		echo $args['after_widget'];

	}
	 
	 function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['prepend'] = strip_tags( $new_instance['prepend'] );
		$instance['showname'] = strip_tags( $new_instance['showname'] );
		$instance['nametxt'] = strip_tags($new_instance['nametxt']);
		$instance['nameholder'] = strip_tags($new_instance['nameholder']);
		$instance['emailtxt'] = strip_tags($new_instance['emailtxt']);
		$instance['emailholder'] = strip_tags($new_instance['emailholder']);
		$instance['showcategory'] = strip_tags($new_instance['showcategory']);
		$instance['categorytxt'] = strip_tags($new_instance['categorytxt']);
		$instance['submittxt'] = strip_tags($new_instance['submittxt']);
		$instance['jsthanks'] = strip_tags($new_instance['jsthanks']);
		$instance['thankyou'] = strip_tags($new_instance['thankyou']);
		return $instance;
	}
	
	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	 
	function form( $instance ) 
	{
		/* Set up some default widget settings. */
		$defaults = array( 	'prepend' => 'Subscribe to receive updates in email',
							'showname' => '1',
							'nametxt' => 'Name:',
							'nameholder' => 'Name...',
							'emailtxt' => 'Email:',
							'emailholder' => 'Email Address...',
							'showcategory' => '1',
							'categorytxt' => 'Category:',
							'submittxt' => 'Submit',
							'jsthanks' => '0',
							'thankyou' => 'Thank you for subscribing to our mailing list');
		$instance = wp_parse_args( $instance, $defaults );
		
	?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'prepend' ); ?>"><?php _e('Prepend:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'prepend' ); ?>" name="<?php echo $this->get_field_name( 'prepend' ); ?>" value="<?php echo $instance['prepend']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'showname' ); ?>"><?php _e('Show Name Field:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id('showname'); ?>" name="<?php echo $this->get_field_name('showname'); ?>" type="checkbox" value="1" <?php if ($instance['showname']=="1") {echo "checked='checked'";} ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'nametxt' ); ?>"><?php _e('Name Field Label:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'nametxt' ); ?>" name="<?php echo $this->get_field_name( 'nametxt' ); ?>" value="<?php echo $instance['nametxt']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'nameholder' ); ?>"><?php _e('Name Field Default Value:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'nameholder' ); ?>" name="<?php echo $this->get_field_name( 'nameholder' ); ?>" value="<?php echo $instance['nameholder']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'emailtxt' ); ?>"><?php _e('Email Field Label:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'emailtxt' ); ?>" name="<?php echo $this->get_field_name( 'emailtxt' ); ?>" value="<?php echo $instance['emailtxt']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'emailholder' ); ?>"><?php _e('Email Field Default Value:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'emailholder' ); ?>" name="<?php echo $this->get_field_name( 'emailholder' ); ?>" value="<?php echo $instance['emailholder']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'showcategory' ); ?>"><?php _e('Show Category Field:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id('showcategory'); ?>" name="<?php echo $this->get_field_name('showcategory'); ?>" type="checkbox" value="1" <?php if ($instance['showcategory']=="1") {echo "checked='checked'";} ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'categorytxt' ); ?>"><?php _e('Category Field Label:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'categorytxt' ); ?>" name="<?php echo $this->get_field_name( 'categorytxt' ); ?>" value="<?php echo $instance['categorytxt']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'submittxt' ); ?>"><?php _e('Submit Button Label:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'submittxt' ); ?>" name="<?php echo $this->get_field_name( 'submittxt' ); ?>" value="<?php echo $instance['submittxt']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'jsthanks' ); ?>"><?php _e('Show JavaScript Thanks:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id('jsthanks'); ?>" name="<?php echo $this->get_field_name('jsthanks'); ?>" type="checkbox" value="1" <?php if ($instance['jsthanks ']=="1") {echo "checked='checked'";} ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'thankyou' ); ?>"><?php _e('Thank You Text', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'thankyou' ); ?>" name="<?php echo $this->get_field_name( 'thankyou' ); ?>" value="<?php echo $instance['thankyou']; ?>" style="width:100%;" />
		</p>

	<?php
	}
 }
 
 
 /**
 * Register our widget.
 * 'st_category_email_subscribe_load_widget' is the widget class used below.
 */
 function st_category_email_subscribe_load_widget()
 {
	register_widget('st_category_email_subscribe_widget'); 
 }
?>