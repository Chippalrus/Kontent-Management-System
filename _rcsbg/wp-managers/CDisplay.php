<?php
if( !defined( '__Permitted__' ) ){ header( 'Location: ' . filter_var( 'https://error.chippalrus.ca/404.html', FILTER_SANITIZE_URL ) ); exit; }
//	Includes	===========================================================
require_once dirname(__DIR__) . '/wp-configs/web.config.php';
require_once __DIR__ . '/CTemplates.php';
require_once __DIR__ . '/CTextFormat.php';
//	Define	===============================================================
define( 'THIS_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'UP_DIR', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'SITE_DIRECTORY', UP_DIR . WEB_DIRECTORY . DIRECTORY_SEPARATOR );
//	Assert	===============================================================
assert_options( ASSERT_WARNING, 0 );
/*	CDisplay	=======================================================
=========================================================================== */
class	CDisplay	extends	CTextFormat
{
//	Members	===============================================================
	private		$m_CTemplates;
//	private		$m_CTextFormat;
//	Constructor	===========================================================
	public	function __construct()
	{
		parent::__construct();
		$this->m_CTemplates		= new CTemplates();
//		$this->m_CTextFormat	= new CTextFormat();
	}
//	Helper	===================================================================
	protected	function GetFromCurl( $sURL )
	{
		$cURL		=	curl_init();
		curl_setopt( $cURL, CURLOPT_URL, $sURL );
		curl_setopt( $cURL, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $cURL, CURLOPT_FOLLOWLOCATION, true );
	//	curl_setopt( $cURL, CURLOPT_TIMEOUT, 10 );
	//	curl_setopt( $cURL, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows: U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );
		curl_setopt( $cURL, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt( $cURL, CURLOPT_SSL_VERIFYPEER, 2 );
		$cOut		=	curl_exec( $cURL );
		$cInfo		=	curl_getinfo( $cURL, CURLINFO_HTTP_CODE );
		while( $cInfo != '200' )
		{
			$cOut		=	curl_exec( $cURL );
			$cInfo		=	curl_getinfo( $cURL, CURLINFO_HTTP_CODE );
		}
		curl_close( $cURL );
		return $cOut;
	}
	public	function GetExportedContents( $sFileID, $eDocumentType = 'html' )
	{
		if( $sFileID != '' )
		{
			// No direct access to full Drive, just the document.
			$sURL	= 'https://docs.google.com/document/d/'
					. $sFileID
					. '/export?format='
					. $eDocumentType;
					
			$sHeader	= get_headers( $sURL, 1 );
			$sHeader	= array_change_key_case( $sHeader, CASE_LOWER );
			if( $sHeader[ 'content-disposition' ] )
			{
				$sFileName = explode( ';', $sHeader[ 'content-disposition' ] );
				if( $sFileName[ 2 ] )
				{
					$sTitle = str_replace( 'filename*=UTF-8\'\'', '', $sFileName[ 2 ] );
					$sTitle = preg_replace( '/.html|.txt/', '', $sTitle );
				}
				else
				{
					$sTitle = preg_replace( '/\\?.*/', '', $sURL );
					$sTitle = basename( $sTitle );
				}
			}
			
			$sFileData = file_get_contents( $sURL );
			if( strtolower( $sTitle ) != 'index' )
			{
				$sDisplayTitle = '';
				if( !substr_count( $sFileData, '%' . NO_TITLE ) )
				{
					$sDisplayTitle = $this->m_CTemplates->GetTemplate( SITE_THEME, ETemplate::_TITLE_ );
					$sDisplayTitle = str_replace( '%TITLE%', urldecode( $sTitle ), $sDisplayTitle  );
					if( IS_PAGE ){ $sDisplayTitle .= $this->m_CTemplates->GetTemplate( SITE_THEME, ETemplate::_PG_SPACER_ ); }
				}
			}
			else { $sDisplayTitle = ''; }
			$sData = [ 'title' => urldecode( $sTitle ), 'data' => $sFileData, 'name' => $sDisplayTitle ];
		}
		return $sData;
	}
	public	function GetFinalURL( $sURL )
	{
		$cURL		=	curl_init();
		curl_setopt( $cURL, CURLOPT_URL, $sURL ); 
		curl_setopt( $cURL, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $cURL, CURLOPT_FOLLOWLOCATION, true );
		$cOut		=	curl_exec( $cURL );
		$sFinalURL	=	curl_getinfo( $cURL, CURLINFO_EFFECTIVE_URL );
		curl_close( $cURL );
		return $sFinalURL;
	}
	public	function GetMediaTitle( $sFileID )
	{
		if( $sFileID != '' )
		{
			// No direct access to full Drive, just the document.
			$sURL		= $this->GetFinalURL( 'https://drive.google.com/uc?id=' . $sFileID );	
			$sHeader	= get_headers( $sURL, 1 );
			$sHeader	= array_change_key_case( $sHeader, CASE_LOWER );
			if( $sHeader[ 'content-disposition' ] )
			{
				$sFileName = explode( ';', $sHeader[ 'content-disposition' ] );
				if( $sFileName[ 2 ] )
				{
					$sTitle = str_replace( 'filename*=UTF-8\'\'', '', $sFileName[ 2 ] );
					$sTitle = pathinfo( $sTitle, PATHINFO_FILENAME );
				}
				else
				{
					$sTitle = preg_replace( '/\\?.*/', '', $sURL );
					$sTitle = basename( $sTitle );
				}
			}
			$sData = urldecode( $sTitle );
		}
		return $sData;
	}
	public	function ExportFormatedText( $sFileID )
	{
		if( MARKDOWN_MODE ){ $sExport = $this->GetExportedContents( $sFileID, 'txt' ); }
		else { $sExport = $this->GetExportedContents( $sFileID, 'html' ); }
		
		if( !MARKDOWN_MODE && substr_count( $sExport[ 'data' ], '%' . DISABLE_RICHTEXT ) > 0 )
		{
			$sExport = $this->GetExportedContents( $sFileID, 'txt' );
		} 
		else if( MARKDOWN_MODE && substr_count( $sExport[ 'data' ], '%' . DISABLE_MARKDOWN ) > 0 )
		{
			$sExport = $this->GetExportedContents( $sFileID, 'html' );
		}
		
		return $sExport;
	}
//	Methods	===============================================================
	public	function ConstructFormatting( &$sContent, $bGallery = false, $eType = EPageType::PAGE )
	{
		$bMarkdown = MARKDOWN_MODE;
		if( substr_count( $sContent, '%' . DISABLE_RICHTEXT ) > 0 || $bMarkdown )
		{
			if( !$bMarkdown )
			{
				$sContent = preg_replace( '/%' . DISABLE_RICHTEXT . '/', '', $sContent );
				$bMarkdown = true;
			}
			$this->MarkdownCleanup( $sContent );
		}
		else if( substr_count( $sContent, '%' . DISABLE_MARKDOWN ) > 0 || !$bMarkdown )
		{
			if( $bMarkdown )
			{
				$sContent = preg_replace( '/%' . DISABLE_MARKDOWN . '/', '', $sContent );
				$bMarkdown = false;
			}
			$this->RichTextCleanUp( $sContent );
		}
		// Construct Aesthetic Heading Blocks and Google Sheets
		return $this->ConstructHAML( $sContent, $bMarkdown, $bGallery, $eType );
	}
	public	function BuildMetaData( &$sExport )
	{
		// Check for %meta{"author":"","description":"","image":""} Store it for %DESCRIPTION% and clear it from page building
		// Check for %Tags{"","",""}
		$sShort = '/(%meta({|\Z)(.*?)(}|\Z))/';
		$atts	= [ 'author'		=> ''
				, 'description'		=> ''
				, 'image'			=> ''
				];
		if( preg_match_all( $sShort, $sExport, $aMatched ) > 0 )
		{
			$aHAML = [];
			// Get Params and Fix/Decode JSON
			preg_match( '/{(.*)}/s', $aMatched[ 0 ][ 0 ], $aHAML[ 'param' ], PREG_OFFSET_CAPTURE );
			$aHAML[ 'param' ] = $this->Decode_JSON( $aHAML[ 'param' ][ 0 ][ 0 ] );
			$objArray = [ 'author' => '', 'description' => '', 'image' => '' ];
			if( property_exists( $aHAML[ 'param' ], 'author' ) )		{	$objArray[ 'author' ] = $aHAML[ 'param' ]->{ 'author' };			}
			if( property_exists( $aHAML[ 'param' ],'description' ) )	{	$objArray[ 'description' ] = $aHAML[ 'param' ]->{ 'description' };	}
			if( property_exists( $aHAML[ 'param' ], 'image' ) )			{	$objArray[ 'image' ] = $aHAML[ 'param' ]->{ 'image' };				}
			
			$atts[ 'author' ]		= $objArray[ 'author' ];
			$atts[ 'description' ]	= $objArray[ 'description' ];
			$atts[ 'image' ]		= $objArray[ 'image' ];
			$sExport = preg_replace( $sShort, '', $sExport );
		}
		if( empty( $atts[ 'author' ] ) )		{ $atts[ 'author' ]			= SITE_AUTHOR;		}
		if( empty( $atts[ 'description' ] ) )	{ $atts[ 'description' ]	= SITE_DESCRIPTION; }
		if( empty( $atts[ 'image' ] ) )			{ $atts[ 'image' ]			= META_DEFAULT_IMG; }
		return $atts;
	}
	public	function Build3DPages( &$sExport, $sPath , $sType, $bEnableWL = true )
	{
		$sPurgeWhiteList	= SITE_DIRECTORY . SMART_PAGES_DIR . '/3js/_map.json';
		$sThreeJSDir		= $sPath . '/_3js/';
		$sShort				= '/\%' . $sType . '+[^}]*}/';
		$iLength			= preg_match_all( $sShort, $sExport, $aMatched );
		if( $iLength > 0 )
		{
			for( $j = 0; $j < $iLength; ++$j )
			{
				$aHAML = [];
				// Get Params and Fix/Decode JSON
				preg_match( '/{(.*)}/s', $aMatched[ 0 ][ $j ], $aHAML[ 'param' ], PREG_OFFSET_CAPTURE );
				$aHAML[ 'param' ] = $this->Decode_JSON( $aHAML[ 'param' ][ 0 ][ 0 ] );
				
				switch( $sType )
				{
					case 'obj':
						$sHTML = $this->m_CTemplates->GetThreeTemplate( ETemplate::OBJ );
						$sHTML = str_replace( '%UVTEXTURE%',	$aHAML[ 'param' ]->{ 'texture' },	$sHTML );
						$sHTML = str_replace( '%OBJFILE%',		$aHAML[ 'param' ]->{ 'file' },		$sHTML );
					break;
					
					case 'obj_mtl':
						$sHTML = $this->m_CTemplates->GetThreeTemplate( ETemplate::OBJ_MTL );
						$sHTML = str_replace( '%DDS_MTL%',		$aHAML[ 'param' ]->{ 'dds' },		$sHTML );
						$sHTML = str_replace( '%OBJFILE%',		$aHAML[ 'param' ]->{ 'file' },		$sHTML );
					break;
					
					case '3ds':
						$sHTML = $this->m_CTemplates->GetThreeTemplate( ETemplate::THREEDS );
						$sHTML = str_replace( '%3DSNORMAL%',	$aHAML[ 'param' ]->{ 'normal' },	$sHTML );
						$sHTML = str_replace( '%RESOURCEPATH%',	$aHAML[ 'param' ]->{ 'resource' },	$sHTML );
						$sHTML = str_replace( '%3DSFILE%',		$aHAML[ 'param' ]->{ 'file' },		$sHTML );
					break;
					
					case 'fbx':
						$sHTML = $this->m_CTemplates->GetThreeTemplate( ETemplate::FBX );
						$sHTML = str_replace( '%FBXFILE%',		$aHAML[ 'param' ]->{ 'file' },		$sHTML );
					break;
				}
				$sHTML = str_replace( '%3JSDIR%',		'/' . SMART_PAGES_DIR . '/3js',	$sHTML );
				
				if( !file_exists( $sThreeJSDir ) ){ mkdir( $sThreeJSDir, 0755, true ); }
				$sFile = $sThreeJSDir . $sType . '_' . strval( $j ) . '.html';
				$htmlFile = fopen( $sFile, 'w' );
				fwrite( $htmlFile, $sHTML );
				fclose( $htmlFile );
				
				if( $bEnableWL )
				{
					// Add to Purge Whitelist
					if( !file_exists( $sPurgeWhiteList ) )
					{
						$aMap = [ $sThreeJSDir, $sFile ];
						$htmlFile = fopen( $sPurgeWhiteList, 'w' );
					}
					else
					{
						$sMap = file_get_contents( $sPurgeWhiteList );
						$aMap = json_decode( $sMap, true );
						array_push( $aMap, $sFile );
						$htmlFile = fopen( $sPurgeWhiteList, 'w' );
					}
					fwrite( $htmlFile, json_encode( $aMap ) );
					fclose( $htmlFile );
				}
			}
		}
	}
	public	function BuildMediaData( $sName, $sID, $eType, $iTumbSize = 320 )
	{
		$sHTML	= '<br>';
		$sURL	= 'https://drive.google.com/uc?id=' . $sID;
		switch( $eType )
		{
			case 'image/png':
				$sHTML .=	'<div class="image-i">
								<a href="#image_' . $sName . '">
									<img src="https://drive.google.com/thumbnail?&sz=w' . $iTumbSize . '&id=' . $sID . '">
								</a>'
								. $sCaption .
							'</div>
							<a href="#close">
								<div id="image_' . $sName .  '" class="image-w">
									<img src="' . $sURL . '">
								</div>
							</a>';
			break;
			
			case 'video/avi':
			case 'video/x-matroska':
			case 'video/mp4':
			case 'video/webm':
				$sHTML	=	'<video controls preload="none"><source src="' . $sURL . '"></video>';
			break;
			
			case 'audio/ogg':
			case 'audio/wav':
			case 'audio/flac':
			case 'audio/mpeg':
				$sHTML	=	'<div class="audioPlayer">
						<div class="play-cont">
							<div class="music-player">'
						//	.	'<div class="cover"><img src="' . $sImage . '" alt=""></div>'
							.	'<div class="titre">
									<h3>.</h3>
									<h1><span class="playTitle">' . $sFileName . '</span></h1>
								</div>
								<div class="lecteur">
									<audio style="width:100%;" class="fc-media">
										<source src="' . $sURL . '">
									</audio>
								</div>
							</div>
						</div>
					</div>';
			break;
			
			default:
				$sHTML = '';
			break;
		}
		return $sHTML;
	}
	public	function DisplayContent( $sFileID, $eType, $sPath )
	{
		if( !is_string( $eType ) )
		{
			$sExport	= $this->ExportFormatedText( $sFileID );
			$atts		= $this->BuildMetaData( $sExport[ 'data' ] );
			$sExport	= array_merge( $sExport, $atts );
			$bGALLERY	= substr_count( $sExport[ 'data' ], '%' . GALLERY_SECTION ) ? true : false;
			
			if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 31 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sPath, EShort::Code[ 31 ], false ); }
			if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 32 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sPath, EShort::Code[ 32 ], false ); }
			if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 33 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sPath, EShort::Code[ 33 ], false ); }
			if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 34 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sPath, EShort::Code[ 34 ], false ); }
			
			$sExport = $sExport + [ 'gallery' => $this->ConstructFormatting( $sExport[ 'data' ], $bGALLERY, $eType ) ];
			if( strtolower( $sExport[ 'title' ] ) == 'index' ){ $sExport[ 'title' ] = ''; }
		}
		else
		{
			// Running into Google Drive limits dynamically loading non-document file names via export.
			$sTitle		=	'';//$this->GetMediaTitle( $sFileID );
			$sExport	=	[ 'title'	=>	$sTitle
							, 'name'	=>	$sTitle
							, 'data'	=>	$this->BuildMediaData( $sTitle, $sFileID, $eType, THUMB_SIZE  ) ];
		}
		return $sExport;
	}
	public	function GetNavigation()
	{
		$sNavMenu = $this->m_CTemplates->GetTemplate( SITE_THEME, ETemplate::NAV_HEADER );
		$sHTML = $this->m_CTemplates->GetTemplate( SITE_THEME, ETemplate::NAV_ITEM );
		foreach( NAVIGATION_KEY as $file )
		{
			$sNavMenu .= str_replace( '%PAGETITLE%',	$file[ 'rename' ],$sHTML );
			$sNavMenu = str_replace( '%PAGEURL%',		$file[ 'url' ],	$sNavMenu );
		}
		$sNavMenu .= $this->m_CTemplates->GetTemplate( SITE_THEME, ETemplate::NAV_FOOTER );
		return $sNavMenu;
	}
}
?>