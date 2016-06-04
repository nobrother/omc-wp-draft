<?php
/*
	
	== PHP FILE TREE ==
	
		Let's call it...oh, say...version 1?
	
	== AUTHOR ==
	
		Cory S.N. LaViska
		http://abeautifulsite.net/
		
	== DOCUMENTATION ==
	
		For documentation and updates, visit http://abeautifulsite.net/notebook.php?article=21
		
*/


function php_file_tree( $directory, $return_link, $extensions = array(), &$current_file = '' ) {
	
	// Remove trailing slash
	if( substr($directory, -1) == "/" ) 
		$directory = substr( $directory, 0, strlen( $directory ) - 1 );
	
	$code = php_file_tree_dir( $directory, $return_link, $extensions, $current_file );
	
	return $code;
}

function php_file_tree_dir( $directory, $return_link, $extensions = array(), &$current_file, $first_call = true ) {	


	// Get and sort directories/files
	if( function_exists( "scandir" ) ) 
		$file = scandir( $directory ); 
	else 
		$file = php4_scandir( $directory );
	//natcasesort($file);
	
	// Make directories first
	$files = $dirs = array();
	
	foreach( $file as $this_file ) {
		if( is_dir( "$directory/$this_file" ) ) 
			$dirs[] = $this_file; 
		else {
			
			$files[] = $this_file;
			
			// Filter unwanted extensions
			if( !empty( $extensions ) ) {
				$ext = substr( $this_file, strrpos( $this_file, "." ) + 1 );
				if( !in_array( $ext, $extensions ) )
					continue;
			}
			// Set current file if it is empty => set as first file
			if( empty( $current_file ) )
				$current_file = "$directory/$this_file";
		}
	}
	
	$file = array_merge( $dirs, $files );
	$php_file_tree = '';
	if( count( $file ) > 2 ) { // Use 2 instead of 0 to account for . and .. "directories"
	
		$php_file_tree .= "<ul";
		if( $first_call ) { 
			$php_file_tree .= " class=\"php-file-tree\""; 
			$first_call = false; 
		} else {
			$php_file_tree .= ' style="display: none;"'; 
		}
		$php_file_tree .= ">";
		
		foreach( $file as $this_file ) {
			if( $this_file != "." && $this_file != ".." ) {
				if( is_dir("$directory/$this_file") ) {
					// Directory
					$php_file_tree .= "<li class=\"pft-directory\"><a href=\"#\">" . htmlspecialchars( $this_file ) . "</a>";
					$php_file_tree .= php_file_tree_dir( "$directory/$this_file", $return_link ,$extensions, $current_file, false );
					$php_file_tree .= "</li>";
				} else {
					// File
					// Get extension (prepend 'ext-' to prevent invalid classes from extensions that begin with numbers)
					$ext = "ext-" . substr($this_file, strrpos( $this_file, "." ) + 1); 
					$link = str_replace( "[link]", urlencode( "$directory/".$this_file ), $return_link );
					$php_file_tree .= "<li class=\"pft-file " .( $current_file == "$directory/$this_file" ? "active " : "" ). strtolower($ext) 
						."\"><a href=\"$link\">" . htmlspecialchars( $this_file ) 
						."</a>" //.'<br>'
						//."$directory/$this_file".'<br>'
						//.$current_file
						."</li>";
				}
			}
		}
		$php_file_tree .= "</ul>";
	}
	return $php_file_tree;
}

// For PHP4 compatibility
function php4_scandir($dir) {
	$dh  = opendir($dir);
	while( false !== ($filename = readdir($dh)) ) {
	    $files[] = $filename;
	}
	sort($files);
	return($files);
}
