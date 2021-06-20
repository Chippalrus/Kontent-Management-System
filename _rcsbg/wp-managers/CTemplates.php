<?php
if( !defined( '__Permitted__' ) ){ header( 'Location: ' . filter_var( 'https://error.chippalrus.ca/404.html', FILTER_SANITIZE_URL ) ); exit; }
define( 'THEMES_DIR', dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'wp-themes/' );
require_once __DIR__ . '/ETemplate.php';
/*	CTemplates	===============================================================
=============================================================================== */
class	CTemplates
{
//	Members	===============================================================
//	Constructor	===========================================================
	public	function __construct(){}
//	Helper	===============================================================
	public	function GetRSS_Header()
	{
		return	  '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>'
				. '<atom:link href="' . SITE_URL . 'feeds/rss.xml" rel="self" type="application/rss+xml"/>'
				. '<title>' . SITE_NAME . '</title><link>' . SITE_URL . '</link><description>' . SITE_HEADLINE . '</description>'
				. '<lastBuildDate>' . date( DATE_RFC2822 ) . '</lastBuildDate>';
	}
	
	public	function GetRSS_Item()
	{
		return	  '<item><title>%POSTTITLE%</title><pubDate>%POSTTIME%</pubDate><guid isPermaLink="true">%POSTURL%</guid>'
				. '<description>%CONTENT%</description>'
				. '<dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">' . SITE_AUTHOR . '</dc:creator></item>';
	}
	
	public	function GetAtom_Header()
	{
		return	  '<?xml version="1.0" encoding="UTF-8"?><feed xmlns="http://www.w3.org/2005/Atom">'
				. '<title>' . SITE_NAME . '</title><link href="' . SITE_URL . '" rel="alternate"/>'
				. '<link href="' . SITE_URL . 'feeds/atom.xml" rel="self"/><id>' . SITE_URL . '</id>'
				. '<updated>' . date( DATE_RFC3339 ) . '</updated>';
	}
	
	public	function GetAtom_Item()
	{
		return	  '<entry><title>%POSTTITLE%</title><link href="%POSTURL%" rel="alternate"/>'
				. '<published>%POSTTIME%</published><updated>%LASTUPDATED%</updated>'
				. '<author><name>' . SITE_AUTHOR . '</name></author><id>%POSTURL%</id>'
				. '<summary type="html">%CONTENT%</summary>'
				. '<content type="html">%CONTENT%</content></entry>';
	}
	
	public	function GetCSS( $sName )
	{
		return THEMES_DIR . $sName . ETemplate::CSS;
	}
	
	public	function GetAssetsDir( $sName )
	{
		return THEMES_DIR . $sName . '/assets';
	}
	
	public	function GetTemplate( $sName, $eTemplate )
	{
		return file_get_contents( THEMES_DIR . $sName . $eTemplate );
	}
	
	public	function GetLiveTemplate()
	{
		return file_get_contents( THEMES_DIR . '_includes' . ETemplate::DYNAMIC );
	}
	
	public	function GetThreeTemplate( $eTemplate )
	{
		return file_get_contents( THEMES_DIR . '_includes' . $eTemplate );
	}
	
	public	function GetDependency( $sName )
	{
		return file_get_contents( THEMES_DIR . '_includes' . $sName ); 
	}
	
	public	function GetThreeJSDependencies()
	{
		return THEMES_DIR . '_includes/three/dependencies/';
	}
	
	public	function GetMEDependencies()
	{
		return THEMES_DIR . '_includes/me/';
	}
//	Methods	===============================================================
}
?>