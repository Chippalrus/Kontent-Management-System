<?php
if( !defined( '__Permitted__' ) ){ header( 'Location: ' . filter_var( 'https://error.chippalrus.ca/404.html', FILTER_SANITIZE_URL ) ); exit; }
/*===================================================================================================
	SETTINGS
===================================================================================================*/
define( 'SMART_PAGES_DIR',		'_cache_'				); //	Location of Header/Fooder files for Smart pages/articles
/*===================================================================================================
	SITE CONFIGURATION
===================================================================================================*/
//	WEB-SITE INFORMATION
define( 'SITE_AUTHOR',			'Chippalrus'														);
define( 'SITE_URL',				'https://kms.chippalrus.ca/'											);
define( 'SITE_NAME',			'Kontent Management System'														);
define( 'SITE_HEADLINE',		'Not a CMS, just a databaseless web-builder.' );
define( 'SITE_DESCRIPTION',		'Not a CMS, just a databaseless web-builder.' );
define( 'TWITTER_HANDLE',		'@Chippalrus'														);
define( 'META_DEFAULT_IMG',		'https://img.chippalrus.ca/profile/IMG_1004.JPG'					);
define( 'FAVICON16',			'/도/img/icons/16/favicon.png'										);
define( 'FAVICON32',			'/도/img/icons/32/favicon.png'										);
define( 'FAVICON48',			'/도/img/icons/48/favicon.png'										);
define( 'FAVICON64',			'/도/img/icons/64/favicon.png'										);
// ERROR PAGE MESSAGES
define( 'ERROR400',				'400'																);
define( 'ERROR401',				'401'																);
define( 'ERROR403',				'403'																);
define( 'ERROR404',				'Not found.'														);
define( 'ERROR500',				'500'																);
// ERROR DIRECTORY
define( 'ERROR_DIR',			'error'																);
/*===================================================================================================
	GENERAL CONFIG
===================================================================================================*/
define( 'WEB_DIRECTORY', 		'kms'							);	// Directroy where website root is located
define( 'SITE_THEME',			'default'							);
define( 'THUMB_SIZE',			'320'								);	// Thumbnail size of Google Drive Media
/*===================================================================================================
	EXCERPT / ARCHIVE SETTINGS
===================================================================================================*/
define( 'ARCHIVE_PAGE_NAME',	'archive'				); //	ARCHIVES - uri name
/*===================================================================================================
	SETTING LISTS
===================================================================================================*/
define( 'NAVIGATION_KEY',
			array
			( [ 'name' => 'blog',							'rename' => 'Developer Blog',				'url' => 'https://kms.chippalrus.ca/blog/'					]
			, [ 'name' => 'markup-documentation', 			'rename' => 'Markup Documentation',			'url' => 'https://kms.chippalrus.ca/markup-documentation/'					]
			, [ 'name' => 'mixed-media', 					'rename' => 'Mixed Media Demo',				'url' => 'https://kms.chippalrus.ca/mixed-media/'				]
			) ); //	Order list/rename for Navigation bar
/*===================================================================================================
	TEXT EDITOR OPTIONS
===================================================================================================*/
define( 'MARKDOWN_MODE',		false ); // Sets project to only use Markdown mode ( you can still individually disable per-document )
define( 'KEEP_TAGS',			'<p><span><a><img><span><br><table><thead><tbody><tr><td><th><li><ul><blockquote><style>' ); //	RICH TEXT - Ignore this if Markdown is enabled
?>