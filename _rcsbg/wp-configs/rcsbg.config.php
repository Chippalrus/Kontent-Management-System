<?php
if( !defined( '__Permitted__' ) ){ header( 'Location: ' . filter_var( 'https://error.chippalrus.ca/404.html', FILTER_SANITIZE_URL ) ); exit; }
require_once __DIR__ . '/web.config.php';
/*
	Really Crude Static-Blog Generator
	by Chippalrus	http://chippalrus.ca/
	GitHub:			https://github.com/Chippalrus
	
	Description:	RCSBG allows blog posts/pages to be made with Google Docs. Can generate
					Static/Dynamic pages or a combination of both.

	Notes:			My goal was to move away form packed CMS and build something that uses
					Google Drive as the database/pages for a website. My programming
					skills/discipline are not comparable to professionals. This has always
					been an interest/self-taught process.
*/
/*===================================================================================================
	SETTINGS
===================================================================================================*/
/*	0: ( HTML ) Full Static Website ( Ignores Dynamic/Static document settings )
	1: ( PHP )	Hybrid ( Some elements become dynamic but is still maintained in single file )
	2: ( PHP )	Full Dynamic Website ( Splits Header/Body/Footer, Ignores Dynamic/Static document settings )
	Excerts are always Static
*/
define( 'SMART_PAGES',			2						);
define( 'SMART_NAV',			true					); //	Only works with SMART_PAGES + CUSTOM_NAV_ONLY calls GetNavigation(); per-page
define( 'SMART_CSS',			true					); //	Only works with SMART_PAGES uses include( .css ); per-page
/*===================================================================================================
	GENERAL CONFIG
===================================================================================================*/
define( 'ROOT_DIRECTORY',		'' ); //	GOOGLE DRIVE DIRECTORY
/*===================================================================================================
	EXCERPT / ARCHIVE SETTINGS
===================================================================================================*/
define( 'EXCERPT_LENGTH',		512 					); //	EXCERPT - number of characters for summary
define( 'EXCERPT_LIMIT',		10						); //	EXCERPT - number of excerpt posts
define( 'MASONRY_EXCERPTS',		true					); //	EXCERPT - displays /blog/ using javascript Masonry over CSS Grid
define( 'GENERATE_ARCHIVES',	true					); //	ARCHIVES
/*===================================================================================================
	Other SETTINGS
===================================================================================================*/
define( 'ENABLE_THREEJS',		true					); //	Writes three.js libraries to SMART_PAGES_DIR /3js/build/ and /3js/includes/
define( 'CUSTOM_NAV_ONLY',		true					); //	Only custom navigations; Removes generated navigation pages
define( 'CURL_OFF',				true					);
/*===================================================================================================
	SETTING LISTS
===================================================================================================*/
define( 'PAGE_LIST',
			array
			( 'markup-documentation'
			) );
define(	'GALLERY_LIST',
			array
			( 'mixed-media'
			) );
define( 'PURGE_WHITELIST',
			array
			( 'index.php'
			, 'archive'
			, 'index.html'
			, 'feeds'
			) ); //	White list safe from purging
?>