<?php 

	require_once('wp-load.php');

	function endsWith($haystack, $needle) {
	    // search forward starting from end minus needle length characters
	    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}

	global $wpdb;
	//switch_to_blog(1);
	// Get WP uploads dir
	$wp_upload_dir = wp_upload_dir();
	echo "<h1>Iterating through uploads folder</h1>";

	// Init recursive Obj
	$di = new RecursiveDirectoryIterator($wp_upload_dir['basedir']);
		echo "<ol>";
		foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
		    // get filetype for attachment meta
			if(strpos($file->getPathname(),'uploads/20') >0)
			{
			    if(endsWith($file->getPathname(),'.'))
			    {
			    	echo "<li>Skipping: " . $file->getPathname() . "</li>";
			    	// Ignore files which look like . or ..
			    	continue;
			    }
			    if(!endsWith(strtoupper($file->getPathname()),'PDF'))
			    {
			    	echo "<li>Skipping: " . $file->getPathname() . "</li>";
			    	continue;
			    }

			    $filetype = wp_check_filetype( $file->getPathname(), null );
			    // attachment meta
			    $guid = $wp_upload_dir['url'] . '/' . _wp_relative_upload_path( $filename );
			    
			    $query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid='$guid'";
				$count = $wpdb->get_var($query);
				if($count == 0)
				{
				    $attachment = array(
				        'guid'           => $guid, 
				        'post_mime_type' => $filetype['type'],
				        'post_title'     => preg_replace( '/\.[^.]+$/', '', $file->getFilename() ),
				        'post_content'   => '',
				        'post_status'    => 'inherit'
				    );

				    // Insert the attachment.
				    $attach_id = wp_insert_attachment( $attachment, $filename );
				    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
					require_once( ABSPATH . 'wp-admin/includes/image.php' );

					// Generate the metadata for the attachment, and update the database record.
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id, $attach_data );
				    echo "<li>Inserting: " . $filename . "</li>";
				}
			}
		}
		echo "</ol>";

 ?>