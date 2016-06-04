<?php
/**
 * OMC HTML Helper functions
 */
 
/*
 *	Convert simple table table-tr-td to table array
 */
function simplehtml2tablearr( $table = null, $offset_title_row = 0 ) {	

	if( empty( $table ) )
		return false;
	
	$tablearr = array();
	$start = false;
	
	foreach( $table->find( 'tr' ) as $key => $tr ){
		if( $key == $offset_title_row ) {
			$first_row = true;
			$start = true;
		}
		if( $start ){
			$arr = array();
			foreach( $tr->find( 'th, td' ) as $td ){
				$arr[] = trim( $td->plaintext );
			}
			
			if( $first_row ){
				$tablearr['thead'][] = $arr;
				$first_row = false;
			} else {
				$tablearr['tbody'][] = $arr;
			}
		}
	}
	
	if( empty( $tablearr['tbody'] ) )
		return false;
	
	return $tablearr;
}


/*
 *	Convert table array to simple array
 */
function tablearr2arr( $tablearr = array(), $is_single = true ){

	if( empty( $tablearr ) || !isset( $tablearr['thead'][0], $tablearr['tbody'] ) )
		return false;
		
	$arr = array();
	$key = array();
	$i = 0;
	foreach( $tablearr['thead'][0] as & $field ){
		$temp = sanitize_title( $field );
		$key[] = !empty( $temp ) ? $temp : $i++;
	}
		
	foreach( $tablearr['tbody'] as $r => $row ){
		foreach( $row as $c => $value ){
			$arr[$r][$key[$c]] = $value;
		}
	}
	
	if( count( $arr ) == 1 && $is_single )
		$arr = $arr[0];
		
	return $arr;
	
}


/*
 *	Convert simple array to table array
 */
function arr2tablearr( $arr = array(), $is_header_only = false ){
	
	if( empty( $arr ) )
		return false;
		
	$head = array();
	$body = array();
	foreach( $arr as $r => $row ){
		foreach( $row as $key => $value ){
			if( !isset( $head[$key] ) )
				$head[$key] = count( $head );
			$body[$r][$head[$key]] = $value;
		}
	}
	
	$tablearr = array(
		'thead' => array( array_keys( $head ) ),
		'tbody'	=> $is_header_only ? array() : $body,
	);
	
	return $tablearr;
}



/*
 *	Convert table array html
 */
function tablearr2html( $tablearr = array(), $attr = '', $class = '', $extra_html = '' ){
	
	if( empty( $tablearr ) || !isset( $tablearr['thead'], $tablearr['tbody'] ) )
		return false;
	
	$class .= ' table table-striped table-bordered table-hover';
	
	$html = "<table class='{$class}' {$attr}>";
	
	$col_class = array();
	$html .= "<thead>";
	foreach( $tablearr['thead'] as $r => $row ){
		$html .= "<tr id='tr{$r}'>";
		foreach( $row as $key => $value ){
			$col_class[$key] = sanitize_title( $value );
			$html .= "<th class='{$col_class[$key]}'>{$value}</th>";
		}
		$html .= "</tr>";
	}
	$html .= "</thead>";
	
	$html .= "<tbody>";
	if( !empty( $tablearr['tbody'] ) ){
		foreach( $tablearr['tbody'] as $r => $row ){
			$html .= "<tr id='tr{$r}'>";
			foreach( $row as $key => $value ){
				$html .= "<td class='{$col_class[$key]}'>{$value}</td>";
			}
			$html .= "</tr>";
		}
	}
	$html .= "</tbody>";
	
	
	$html .= $extra_html;
	
	if( !empty( $tablearr['tfoot'] ) ){
		$html .= "<tfoot>";
		foreach( $tablearr['tfoot'] as $r => $row ){
			$html .= "<tr class='{$r}'>";
			foreach( $row as $key => $value ){
				$html .= "<td class='{$col_class[$key]}'>{$value}</td>";
			}
			$html .= "</tr>";
		}
		$html .= "</tfoot>";
	}
	
	$html .= "</table>";
	
	return $html;
	
}

/**
* Outputs the html readonly attribute.
*/
function readonly( $test1, $test2 = true, $echo = true ) {
	$test1 = $test2 === true ? !empty( $test1 ) : $test1;
	return __checked_selected_helper( $test1, $test2, $echo, 'readonly' );
}

/**
* Outputs the html autofocus attribute.
*/
function autofocus( $test1, $test2 = true, $echo = true ) {
	$test1 = $test2 === true ? !empty( $test1 ) : $test1;
	return __checked_selected_helper( $test1, $test2, $echo, 'autofocus' );
}

/*
 * Convert data structure(associative array) to hidden input fields
 */
function omc_array_to_input( $array, $prefix='' ) {
  if( (bool)count(array_filter(array_keys($array), 'is_string')) ) {
    foreach($array as $key => $value) {
      if( empty($prefix) ) {
        $name = $key;
      } else {
        $name = $prefix.'['.$key.']';
      }
      if( is_array($value) ) {
        omc_array_to_input($value, $name);
      } else { ?>
        <input type="hidden" value="<?php echo $value ?>" name="<?php echo $name?>">
      <?php }
    }
  } else {
    foreach($array as $key => $item) {
      if( is_array($item) ) {
        omc_array_to_input($item, $prefix.'['.$key.']');
      } else { ?>
        <input type="hidden" name="<?php echo $prefix ?>[]" value="<?php echo $item ?>">
      <?php }
    }
  }
}

/*
 * Convert array to select options
 */
function omc_array_to_options( $array, $is_key_value = true, $is_selected = true ) { 
	foreach( $array as $key => $value ){?>
  <option 
		<?php selected( $is_selected ) ?>
		<?php $is_key_value && _e( "value='{$key}'" ) ?>
	><?php echo $value ?></option>
<?php }}

/*
 * Convert array to select
 */
function omc_array_to_select( $data, $html_name = "", $is_hidden = true, $is_key_value = true, $is_selected = true, $class = "" ){ ?>
	<select class="<?php echo $class ?>" name="<?php echo $html_name ?>" <?php $is_hidden && _e( 'style="display:none"' ) ?> multiple readonly>
		<?php omc_array_to_options( $data, $is_key_value, $is_selected ) ?>
	</select>
<?php }