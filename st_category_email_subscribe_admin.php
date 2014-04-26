<?php

global $st_email_table_suffix;

$st_email_table_suffix = "st_category_email";

function st_category_email_admin_menu() {
    add_menu_page('St Category Email Subscribe', 'St Category Email Subscribe', 'manage_options', 'st_category_email_subscribe', 'st_category_email_subscribe_settings_page', plugins_url('st-category-email-subscribe/images/icon.png'));
	add_submenu_page('st_category_email_subscribe', 'Subscribers', 'Subscribers', 'manage_options', 'st_category_email_subscriber', 'st_category_email_subscribe_subscribers_page');
}

add_action('admin_menu', 'st_category_email_admin_menu');
add_action('init', 'st_category_email_subscribe_export_csv');

function st_category_email_subscribe_export_csv(){
	if(isset($_REQUEST['ExportCSV']))
	{
		global $wpdb;
		global $st_email_table_suffix;	
		
		$getTable = $wpdb->prefix . $st_email_table_suffix;
		echo st_category_email_subscribe_generate($getTable);
		exit;
	}
}

function st_category_email_subscribe_generate($getTable){
	ob_clean();
	
	$field='';
	$getField ='';
	global $wpdb;
	
	
	if($getTable){
		$result = $wpdb->get_results("SELECT * FROM $getTable");
		$requestedTable = mysql_query("SELECT * FROM ".$getTable);
		$fieldsCount = mysql_num_fields($requestedTable);
		
		for($i=0; $i<$fieldsCount; $i++){
			$field = mysql_fetch_field($requestedTable);
			$field = (object) $field;         
			$getField .= $field->name.',';
		}

		$sub = substr_replace($getField, '', -1);
		$fields = $sub; # GET FIELDS NAME
		$each_field = explode(',', $sub);		
		$csv_file_name = $getTable.'_'.date('Ymd_His').'.csv'; # CSV FILE NAME WILL BE table_name_yyyymmdd_hhmmss.csv
		
		# GET FIELDS VALUES WITH LAST COMMA EXCLUDED
		foreach($result as $row){
			for($j = 0; $j < $fieldsCount; $j++){
				if($j == 0) $fields .= "\n"; # FORCE NEW LINE IF LOOP COMPLETE
				$value = str_replace(array("\n", "\n\r", "\r\n", "\r"), "\t", $row->$each_field[$j]); # REPLACE NEW LINE WITH TAB
				$value = str_getcsv ( $value , ",", "\"" , "\\"); # SEQUENCING DATA IN CSV FORMAT, REQUIRED PHP >= 5.3.0
				$fields .= $value[0].','; # SEPARATING FIELDS WITH COMMA
			}			
			$fields = substr_replace($fields, '', -1); # REMOVE EXTRA SPACE AT STRING END
		}
		
		header("Content-type: text/x-csv"); # DECLARING FILE TYPE
		header("Content-Transfer-Encoding: binary");
		header("Content-Disposition: attachment; filename=".$csv_file_name); # EXPORT GENERATED CSV FILE
		header("Pragma: no-cache");
		header("Expires: 0");

		return $fields;
  }
}

function st_category_email_subscribe_settings_page() {
	$send_email = get_option( 'st_category_email_send_email' );
	$from_name = get_option( 'st_category_email_from_name' );
	
	if (isset($_POST['save_send_email'])) {
		$send_email = $_POST['send_email'];
		update_option( 'st_category_email_send_email', $send_email );
		$from_name = $_POST['from_name'];
		update_option( 'st_category_email_from_name', $from_name );
	}
	
	
	?>
		<div class="wrap">  
			<h2><?php _e('Category Email Subscribe Plugin', 'stemail')?></h2>
			<div class="postbox-container" style="width:70%;padding-right:25px;">
				<div class="metabox-holder">
					<div class="meta-box-sortables">
						<div id="toc" class="postbox">
							<div class="handlediv" title="Click to toggle"><br /></div>
							<h3 class="hndle"><span><?php _e('Settings', 'stemail')?></span></h3>
							<div class="inside">
								<form id="st_settings" method="POST">
									<div>
										<label for="send_email"><?php _e('Send Email From (Email): ', 'stemail')?></label>
										<input class="regular-text" type="text" name="send_email" value="<?php echo $send_email; ?>"/>
									</div>
									<div>
										<label for="from_name"><?php _e('Send Email From (Name): ', 'stemail')?></label>
										<input class="regular-text" type="text" name="from_name" value="<?php echo $from_name; ?>"/>
									</div>	
									<input class="button-primary" type="submit" name="save_send_email" value="Save" />
								</form>
							</div>
						</div>
						<div id="toc" class="postbox">
							<div class="handlediv" title="Click to toggle"><br /></div>
							<h3 class="hndle"><span><?php _e('How to Use', 'stemail')?></span></h3>
							<div class="inside">
							<strong><?php _e('1. Enter the Send Email from Email and Name', 'stemail')?></strong><br/>
							<?php _e('All emails will be sent from this name and email', 'stemail')?><br/>
							<strong><?php _e('2. Add Subscribers', 'stemail')?></strong><br/>
							<?php _e('Go to St Category Emai Subscribe > Subscribers to Manage Subscribers', 'stemail')?><br/>
							<?php _e('You can manually add a subscriber', 'stemail')?><br/>
							<?php _e('Or Import an entire list', 'stemail')?><br/>
							<?php _e('The emails will be sent as soon as a ', 'stemail')?><strong><?php _e('Post is Published', 'stemail')?></strong><br/>
							<?php _e('The email will be sent only to the subscribers registered for the category of Post', 'stemail')?><br/>
							<strong><?php _e('3. Add Subscribe Form', 'stemail')?></strong><br/>
							<?php _e('Place the subscribe form on your website using ', 'stemail')?><strong><?php _e('Widget : Category Email Subscribe Form', 'stemail')?></strong>
							<?php _e('Or Short code [st_category_subscribe_form]', 'stemail')?>
							</div>
						</div>
					</div>
				</div>
            </div>		
			<div class="postbox-container side" style="width:20%;">
				<div class="metabox-holder">
					<div class="meta-box-sortables">
						
						<div id="toc" class="postbox">
							<div class="handlediv" title="Click to toggle"><br /></div>
							<h3 class="hndle"><span><?php _e('Show your Support', 'stemail')?></span></h3>
							<div class="inside">
								<p>
								<strong><?php _e('Want to help make this plugin even better? All donations are used to improve this plugin, so donate $20, $50 or $100 now!', 'stemail')?></strong>
								</p>
								<a href="http://sanskrutitech.in/wordpress-plugins/wordpress-plugins-st-daily-tip/"><?php _e('Donate', 'stemail')?></a>
							</div>
						</div>
						<div id="toc" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
							<h3 class="hndle"><span><?php _e('Connect With Us ', 'stemail')?></span></h3>
							<div class="inside">
							<a class="facebook" href="https://www.facebook.com/sanskrutitech"></a>
							<a class="twitter" href="https://twitter.com/#!/sanskrutitech"></a>
							<a class="googleplus" href="https://plus.google.com/107541175744077337034/posts"></a>
							</div>
						</div>
					</div>
				</div>
			</div>			
		</div>
	<?php
}

function st_email_get_abs_path_from_src_file($src_file)
{
	if(preg_match("/http/",$src_file))
	{
		$path = parse_url($src_file, PHP_URL_PATH);
		$abs_path = $_SERVER['DOCUMENT_ROOT'].$path;
		$abs_path = realpath($abs_path);
		if(empty($abs_path)){
			$wpurl = get_bloginfo('wpurl');
			$abs_path = str_replace($wpurl,ABSPATH,$src_file);
			$abs_path = realpath($abs_path);			
		}
	}
	else
	{
		$relative_path = $src_file;
		$abs_path = realpath($relative_path);
	}
	return $abs_path;
}
function st_email_read_dump($src_file,$table_name,$column_string="",$start_row=2)
{
	ini_set('auto_detect_line_endings', true);
	global $wpdb;
	$errorMsg = "";
	if(empty($src_file))
	{
            $errorMsg .= "<br />" . _e('Input file is not specified', 'stemail');
            return $errorMsg;
    }
	
	$file_path = st_email_get_abs_path_from_src_file($src_file);	
	
	$file_handle = fopen($file_path, "r");
	if ($file_handle === FALSE) {
		// File could not be opened...
		$errorMsg .= _e('Source file could not be opened!', 'stemail') . '<br />';
		$errorMsg .= _e('Error on fopen', 'stemail') .  "('$file_path')";	// Catch any fopen() problems.
		return $errorMsg;
	}
	
	$row = 1;
	while (!feof($file_handle) ) 
	{
		$line_of_text = fgetcsv($file_handle, 1024);
		if ($row < $start_row)
		{
			// Skip until we hit the row that we want to read from.
			$row++;
			continue;
		}
		$columns = count($line_of_text);
		
		if ($columns>1)
		{
	        	$query_vals = "'".esc_sql($line_of_text[0])."'";
	        	for($c=1;$c<$columns;$c++)
	        	{
					$line_of_text[$c] = utf8_encode($line_of_text[$c]);
					$line_of_text[$c] = addslashes($line_of_text[$c]);
	                $query_vals .= ",'".esc_sql($line_of_text[$c])."'";
					
	        	}
	        	        $query = "INSERT INTO $table_name ($column_string) VALUES ($query_vals)";
						
                        $results = $wpdb->query($query);
                        if(empty($results))
                        {
                            $errorMsg .= "<br />" . _e('Insert into the Database failed for the following Query: ', 'stemail') . " <br />";
                            $errorMsg .= $query;
                        }
	    }
		$row++;
	}
	fclose($file_handle);
	
	return $errorMsg;
}

function st_category_email_subscribe_subscribers_page() {
	global $wpdb;
	global $st_email_table_suffix;	
	
	$table_name = $wpdb->prefix . $st_email_table_suffix;
	$column_string = "st_name,st_email,st_category";
	

	if(isset($_REQUEST['UploadFile']))
	{
		
		$upload_dir = wp_upload_dir();
		$target_path =  $upload_dir['path'];
		 
		$tmp_name = $_FILES["csvfile"]["tmp_name"];
		$name = $_FILES["csvfile"]["name"];
				
		if(move_uploaded_file($tmp_name,"$target_path/$name"))
		{
			$file_name = $target_path . "/" . $name;
			
			$errorMsg = st_email_read_dump($file_name,$table_name,$column_string);
	
		
			if(empty($errorMsg))
			{
				echo '<div id="message" class="updated fade"><p><strong>';
				echo _e('File content has been successfully imported into the database!', 'stemail');
				echo '</strong></p></div>';
			}
			else
			{
				echo '<div id="message" class="error"><p><strong>';
				echo _e('Error occured while trying to import!', 'stemail') . "<br />";
				echo $errorMsg;
				echo '</strong></p></div>';
			}
		} 
		else
		{
			echo '<div id="message" class="error"><p><strong>';
			echo _e('There was an error uploading the file, please try again!', 'stemail') ;
			echo '</strong></p></div>';
		}		
	}
	global $wpdb;
	global $st_email_table_suffix;

	$table_name = $wpdb->prefix . $st_email_table_suffix;
	
	//Store the Data input if data is submitted
	if (isset($_REQUEST['Subscribe'])) { 
		
		$sub_name = check_input($_REQUEST["sub_name"]);
		$sub_email = check_input($_REQUEST["sub_email"]); 
		$st_category = check_input($_REQUEST["st_category"]); 
		//Insert
		$rows_affected = $wpdb->insert( $table_name, array( 'st_name' => $sub_name, 'st_email' => $sub_email,'st_category' => $st_category));
		echo "<div id=\"message\" class=\"updated fade\"><p><strong>Subscriber Added Successfully!</strong></p></div>";
	}
	if (isset($_REQUEST['Unsubscribe'])) {
		if(isset($_REQUEST['checkbox']))
		{
			$i=0;
			foreach($_REQUEST['checkbox']  as $chkid)
			{
				$wpdb->query("DELETE FROM $table_name WHERE st_id = " .$chkid."");
				$i++;
			}
			echo "<div id=\"message\" class=\"updated fade\"><p><strong>$i Emails(s) Unsubscribed Successfully!</strong></p></div>";
		}
	}
	?>
		<div class="wrap">  
			<h2><?php _e('Category Email Subscribe Plugin','stemail')?></h2>
			<div class="postbox-container" style="width:70%;padding-right:25px;">
				<div class="metabox-holder">
					<div class="meta-box-sortables">
						<div id="toc" class="postbox">
							<div class="handlediv" title="Click to toggle"><br /></div>
							<h3 class="hndle"><span><?php _e('Subscribers','stemail')?></span></h3>
							<div class="inside">
								<?php
									
									$table_result = $wpdb->get_results("SELECT * FROM $table_name ");
									echo "<form id=\"st_subscriber\" action=\"" .$_SERVER["PHP_SELF"] . "?page=st_category_email_subscriber\" method=\"post\">";
									echo "<div class=\"dataTables_wrapper\" role=\"grid\">";
									echo "<table class=\"display sortable\" id=\"display_data\" style=\"width:100%;\" >";
									echo "<thead><th class=\"unsortable\"><span><input type='checkbox' name='checkall' onclick='checkedAll();'/> Select All<span/> </th><th>Id</th><th>Name</th><th>Email</th><th>Category</th></tr></thead>";	
									echo "<tbody>";
									echo "<input type=\"submit\" name=\"Unsubscribe\" value=\"Unsubscribe\" id=\"btnUnsubscribe\" class=\"button\" />";
									echo "<input type=\"submit\" name=\"ExportCSV\" value=\"Export to CSV\" id=\"btnExport\" class=\"button\" />";
									foreach ( $table_result as $table_row ) 
									{
										echo "<tr>";
										echo "<td><input type=\"checkbox\" name=\"checkbox[]\" value=\"" . $table_row->st_id . "\"></input></td>";
										echo "<td>" . $table_row->st_id . "</td>";
										echo "<td>" . $table_row->st_name . "</td>";
										echo "<td>" . $table_row->st_email . "</td>";
										echo "<td>" . get_cat_name($table_row->st_category) . "</td>";
										echo "</tr>";
									}
						
									echo "</tbody>";
									echo "</table></form>";
									?>
							</div>
						</div>
					</div>
						<div class="meta-box-sortables">
							<div id="toc" class="postbox">
								<div class="handlediv" title="Click to toggle"><br /></div>
								<h3 class="hndle"><span><?php _e('Import Subscribers','stemail')?></span></h3>
								<div class="inside">
									<form id="upload" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']."?page=st_category_email_subscriber"; ?>" method="POST">
										<input type="hidden" name="file_upload" id="file_upload" value="true" />
										<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
										<strong><?php _e('Choose a CSV file to upload: ','stemail')?></strong><input name="csvfile" id="csvfile" type="file" size="25" />
										<input type="submit" class="button-primary" name="UploadFile" value="Upload File" />
									</form>
								</div>
							</div>
						</div>
						<div class="meta-box-sortables">
							<div id="toc" class="postbox">
								<div class="handlediv" title="Click to toggle"><br /></div>
								<h3 class="hndle"><span><?php _e('Add a Subscriber','stemail')?></span></h3>
								<div class="inside">
									<form id="add_subscriber" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']."?page=st_category_email_subscriber"; ?>" method="post">
										<div>
											<label><?php _e('Name','stemail')?></label>
											<input name="sub_name" class="regular-text code" value=""/>
										</div>
										<div>
											<label><?php _e('Email','stemail')?></label>
											<input name="sub_email" class="regular-text code" value=""/>
										</div>
										<div>
											<label for="st_category"><?php _e('Category','stemail')?></label>
											<?php echo wp_dropdown_categories("name=st_category&id=st_category&show_option_all=All Categories&echo=0&hide_empty=0&hierarchical=1")?>
										<input type="submit" class="button-primary" name="Subscribe"  value="Subscribe" />
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
            </div>				
		</div>
	<?php
}
?>
