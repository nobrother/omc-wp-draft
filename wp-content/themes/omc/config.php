<?php
global $theme_config;

$theme_config = array(
	'loader' => array(
		'general' => array(
			'inc/constants.php',
			'inc/functions/general.php',
			'inc/functions/formatting.php',
			'inc/functions/html-helper.php',
			'inc/optimization.php',
			'inc/functions/general-template.php',
			'inc/class/class-wp-exception.php',
			'inc/class/class-omc-cpt.php',
			'inc/class/class-omc-taxonomy.php',
			'inc/load-scripts/load-scripts.php',
			'inc/load-scripts/load-styles.php',
			'inc/router.php',
			
			// User
			'inc/user/main.php',
			'inc/user/object.php',
			
			// Post Type
			'inc/post-type/abstract-object.php',
			'inc/post-type/abstract-main.php',
			'inc/post-type/page/object.php',
			'inc/post-type/page/main.php',
			'inc/post-type/post/object.php',
			'inc/post-type/post/main.php',			
			'inc/post-type/attachment/object.php',
			'inc/post-type/attachment/main.php',
			'inc/post-type/portfolio/object.php',
			'inc/post-type/portfolio/main.php',
			
			// Taxonomy
			'inc/taxonomy/abstract-object.php',
			'inc/taxonomy/abstract-main.php',
			'inc/taxonomy/portfolio_tag/object.php',
			'inc/taxonomy/portfolio_tag/main.php',
			'inc/taxonomy/post_tag/object.php',
			'inc/taxonomy/post_tag/main.php',
			'inc/taxonomy/attachment_category/object.php',
			'inc/taxonomy/attachment_category/main.php',
			
			// 'inc/mainframe/mainframe.php',			
		),
		'frontend' => array(
			'templates/common/widgets/tags-cloud/tags-cloud.php',
			'templates/common/comments/class-walker-comments.php',
		),
		'backend' => array(
			'inc/admin/functions.php',
			'inc/admin/add-admin-notice.php',
			'inc/admin/add-admin-menu.php',
			'inc/admin/class/abstract-omc-admin.php',
			'inc/admin/class/abstract-omc-admin-file-editor.php',
			
			// Post Type
			'inc/post-type/abstract-admin.php',
			'inc/post-type/page/admin/main.php',
			'inc/post-type/post/admin/main.php',
			'inc/post-type/attachment/admin/main.php',
			'inc/post-type/portfolio/admin/main.php',
			
			// User
			'inc/user/admin/main.php',
			
			// Taxonomy
			'inc/taxonomy/abstract-admin.php',
			'inc/taxonomy/post_tag/admin/main.php',
			'inc/taxonomy/portfolio_tag/admin/main.php',
			'inc/taxonomy/attachment_category/admin/main.php',
		),
		'ajax' => array(
			'inc/class/abstract-ajax.php',
			'inc/post-type/abstract-ajax.php',
			'inc/post-type/page/ajax.php',
			'inc/post-type/post/ajax.php',
			'inc/post-type/portfolio/ajax.php',
			
			// 'inc/post-type/post-ajax.php',			
			// 'inc/post-type/admin/portfolio-ajax.php',
			
			// User
			'inc/user/ajax.php',
		),
	),
);