<?php
function st_category_email_subscribe_export_csv(){
	$date = new DateTime();
    $ts = $date->format("Y-m-d-G-i-s");
	
	global $wpdb;
	$table_name = $wpdb->prefix . "st_category_email";
	
	// Use the WordPress database object to run the query and get
	// the results as an associative array
	$qry = array();
	$qry[] = "SELECT st_name,st_email,st_category FROM $table_name";
	
	// Check if any records were returned from the database
	$result = $wpdb->get_results(implode(" ", $qry), ARRAY_A);
	
	if ($wpdb->num_rows > 0) 
	{
		// Make a DateTime object and get a time stamp for the filename
		$date = new DateTime();
		$ts = $date->format("Y-m-d-G-i-s");
		
		// A name with a time stamp, to avoid duplicate filenames
		$filename = "subscribers-$ts.csv";
		
		// Tells the browser to expect a CSV file and bring up the
		// save dialog in the browser
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename='.$filename);
		
		// This opens up the output buffer as a "file"
		$fp = fopen('php://output', 'w');
		
		// Get the first record
		$hrow = $result[0];

		// Extracts the keys of the first record and writes them
		// to the output buffer in CSV format
		fputcsv($fp, array_keys($hrow));
		
		// Then, write every record to the output buffer in CSV format            
		foreach ($result as $data) {
			fputcsv($fp, $data);
		}
		
		// Close the output buffer (Like you would a file)
		fclose($fp);
	}
	else
	{
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}
	
}

// This function removes all content from the output buffer
ob_end_clean();
// Execute the function
st_category_email_subscribe_export_csv();
        
?>