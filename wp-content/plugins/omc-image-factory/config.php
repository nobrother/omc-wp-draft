<?php

// Path and URL
define( 'OMC_IMAGE_FACTORY_DIR', __DIR__ );
define( 'OMC_IMAGE_FACTORY_CACHE_DIR', OMC_IMAGE_FACTORY_DIR.'/cache/' );
define( 'OMC_IMAGE_FACTORY_PHPTHUMB_DIR', OMC_IMAGE_FACTORY_DIR.'/apps/phpThumb/' );

// Security
define( 'OMC_IMAGE_SALT', '12asdnkn19vkn13nvs' );

// Filter
global $_omc_image_factory_filter;
$_omc_image_factory_filter = array(
	
	// Cool
	'cool' => array(
		'clr|20|00ff99',
	),
	
	// Frame
	'frame' => array(
		'fram|40|0|001100|000',
	),
	
	// Grayscale
	'grayscale' => array(
		'gray',
	),
	
	// Oldtime
	'oldtime' => array(
		'sep|100|472400',
		'blur|1',
	),
	
	// Moody
	'moody' => array(
		'clr|25|006666',
	),
	
	// Early Bird
	'earlybird' => array(
		'clr|10|eeff99'
	),
);


// Crop position
global $_omc_image_factory_crop_position;
$_omc_image_factory_crop_position = array(
	'center' => 'C',
	'top-center' => 'T',
	'bottom-center' => 'B',
	'left-center' => 'L',
	'right-center' => 'R',
	'top-left' => 'TL',
	'top-right' => 'TR',
	'bottom-left' => 'BL',
	'bottom-right' => 'BR',
);

// Security functions: Gen hash
function omc_gen_image_hash( $url ){
	if( empty( $url ) )
		return false;
		
	return md5( OMC_IMAGE_SALT.$url );
}

// Security functions: Check hash
function omc_check_image_hash( $url, $hash ){
	if( empty( $url ) || empty( $hash ) )
		return false;
		
	return omc_gen_image_hash( $url ) == $hash;
}