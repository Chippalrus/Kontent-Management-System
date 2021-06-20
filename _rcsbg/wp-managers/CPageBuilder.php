<?php
if( !defined( '__Permitted__' ) ){ header( 'Location: ' . filter_var( 'https://error.chippalrus.ca/404.html', FILTER_SANITIZE_URL ) ); exit; }
//	Includes	===========================================================
require_once dirname(__DIR__) . '/wp-configs/rcsbg.config.php';
require_once __DIR__ . '/CTemplates.php';
require_once __DIR__ . '/CTextFormat.php';
require_once __DIR__ . '/EMeta.php';
//	Define	===============================================================
define( 'THIS_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'UP_DIR', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'SITE_DIRECTORY', UP_DIR . WEB_DIRECTORY . DIRECTORY_SEPARATOR );
//	Assert	===============================================================
assert_options( ASSERT_WARNING, 0 );
/*	CPageBuilder	=======================================================
=========================================================================== */
class	CPageBuilder	extends	CTextFormat
{
//	Members	===============================================================
	private		$m_CTemplates;
//	private		$m_CTextFormat;
//	Constructor	===========================================================
	public	function __construct()
	{
		$this->m_CTemplates		= new CTemplates();
//		$this->m_CTextFormat	= new CTextFormat();
	}
//	Helper	===================================================================
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
			$sURL	= $this->GetFinalURL( 'https://drive.google.com/uc?id=' . $sFileID );	
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
	public	function GetSheetData( $sFileID, $gID, $sFormat = 'tsv' )
	{	// CSS #main{float:left;width:auto} is causing Tables to not expand fully for calanders
		if( $sFileID != '' && $gID != '' )
		{
			$sURL	= 'https://docs.google.com/spreadsheets/d/'
					. $sFileID
					. '/export?gid='
					. $gID
					. '&format='
					. $sFormat;
			$sData = file_get_contents( $sURL );
		}
		else { $sData = 'ID or gID is empty'; }
		return $sData;
	}
	protected	function GetTemplate( $eTemplate )
	{
		return $this->m_CTemplates->GetTemplate( SITE_THEME, $eTemplate );
	}
	protected	function GetDependency( $eTemplate )
	{
		return $this->m_CTemplates->GetDependency( $eTemplate );
	}
//	Methods	===============================================================
	public	function ConstructSheet( $atts, $sFormat = 'tsv' )
	{
		$sData = $this->GetSheetData( $atts[ 'id' ], $atts[ 'gid' ], $sFormat );
		$sHTML = '<table ang="en-US">';
		$aRows = explode( "\n", $sData );
		for( $i = 0; $i < count( $aRows ); ++$i )
		{
			if( $i == 0 ){ $sHTML .= '<thead><tr>'; } 
			else if( $i == 1 ){ $sHTML .= '<tbody><tr>'; }
			else { $sHTML .= '<tr>'; }
			switch( $sFormat )
			{
				case 'csv': $sCol = explode( ",", $aRows[ $i ] );	break;
				default: 	$sCol = explode( "\t", $aRows[ $i ] );	break;
			}
			foreach( $sCol as $sTxt )
			{
				if( $i == 0 ){ $sHTML .= '<th><div>' . $sTxt . '</div></th>'; }
				else { $sHTML .= '<td>' . $sTxt . '</td>'; }
			}
			if( $i == 0 ){ $sHTML .= '</tr></thead>'; } else { $sHTML .= '</tr>'; }
		}
		$sHTML .= '</tbody></table>';
		return $sHTML;
	}
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
		//	$this->m_CTextFormat->MarkdownCleanup( $sContent );
			$this->MarkdownCleanup( $sContent );
		}
		else if( substr_count( $sContent, '%' . DISABLE_MARKDOWN ) > 0 || !$bMarkdown )
		{
			if( $bMarkdown )
			{
				$sContent = preg_replace( '/%' . DISABLE_MARKDOWN . '/', '', $sContent );
				$bMarkdown = false;
			}
		//	$this->m_CTextFormat->RichTextCleanUp( $sContent );
			$this->RichTextCleanUp( $sContent );
		}
		// Construct Aesthetic Heading Blocks and Google Sheets
	//	return $this->m_CTextFormat->ConstructHAML( $sContent, $bMarkdown, $bGallery, $eType );
		return $this->ConstructHAML( $sContent, $bMarkdown, $bGallery, $eType );
	}
	public	function DisplaySheet( $sFileID, $sGID )
	{
		$atts = [ 'id' => $sFileID, 'gid' => $sGID ];
		echo $this->ConstructSheet( $atts );
	}
	public	function ConstructMeta( &$sHTML, $bNoBots = false )
	{
		if( $bNoBots )
		{ $sHTML	= str_replace( '%METAHEAD%',		EMeta::HTTP_EUIV	.	EMeta::XUA			.	EMeta::VIEWPORT,						$sHTML ); }
		else
		{ $sHTML	= str_replace( '%METAHEAD%',		EMeta::HTTP_EUIV	.	EMeta::XUA			.	EMeta::VIEWPORT	.	EMeta::NO_BOTS,		$sHTML ); }
		
		$sHTML	= str_replace( '%METADESC%',		EMeta::PAGE_AUTHOR	.	EMeta::PAGE_DESCRIPTION,											$sHTML );
		$sHTML	= str_replace( '%METACARDS%',		EMeta::CARD_URL		.	EMeta::CARD_TITLE	.	EMeta::CARD_DESC	.	EMeta::CARD_SITE
												.	EMeta::DEFAULT_IMG	.	EMeta::CARD_TYPE	.	EMeta::CARD_LOCALE,							$sHTML );
		$sHTML	= str_replace( '%METATWITTER%',		EMeta::CARD_TWITTER	.	EMeta::CARD_SITENAME,												$sHTML );
		$sHTML	= str_replace( '%METAFAVICON%',		EMeta::ICON_16		.	EMeta::ICON_32		.	EMeta::ICON_48		.	EMeta::ICON_64,		$sHTML );
		$sHTML	= str_replace( '%METARSS%',			EMeta::ATOM			.	EMeta::RSS,															$sHTML );
	}
	public	function BuildHeaderData( &$sExport, $atts, $bTitle = true, $bSmart, $eType, $sCSS = '', $sNavMenu = '' )
	{
		if( $sExport[ 'title' ] == 'index' ){ $sExport[ 'title' ] = 'Home'; }
		
		$sBreadDir = SITE_DIRECTORY . SMART_PAGES_DIR;
		
		switch( SMART_PAGES )
		{
			case 0:
				$sHTML	= $this->GetTemplate( ETemplate::HEADER );
				$this->ConstructMeta( $sHTML, true );
			break;
			
			case 1:
				switch( $eType )
				{
					case EPageType::ARTICLE:
						$sHTML	= $atts[ 'header' ] . PHP_EOL . $this->GetTemplate( ETemplate::HEADER ) . PHP_EOL . $this->GetTemplate( ETemplate::_ARTICLE_ ) . PHP_EOL . $this->GetTemplate( ETemplate::FOOTER );
					break;
					
					case EPageType::PAGE:
						$sHTML	= $atts[ 'header' ] . PHP_EOL . $this->GetTemplate( ETemplate::HEADER ) . PHP_EOL . $this->GetTemplate( ETemplate::_PAGE_ ) . PHP_EOL . $this->GetTemplate( ETemplate::FOOTER );
					break;
				}
				$this->ConstructMeta( $sHTML, true );
			break;
			
			case 2:
				switch( $eType )
				{
					case EPageType::ARTICLE:
						$sHTML	= $atts[ 'header' ]
								. PHP_EOL
								. '<?php $pg_url = \'' . $atts[ 'page_url' ] . '\'; file_exists( \''
								. $sBreadDir . '/header.php\' ) ? include( \''
								. $sBreadDir . '/header.php\' ) : false; ?>'
								. PHP_EOL
								. $this->GetTemplate( ETemplate::_ARTICLE_ )
								. PHP_EOL
								. '<?php file_exists( \''
								. $sBreadDir . '/footer.php\' ) ? include( \''
								. $sBreadDir . '/footer.php\' ) : false; ?>';
					break;
					case EPageType::PAGE:
						$sHTML	= $atts[ 'header' ]
								. PHP_EOL
								. '<?php $pg_url = \'' . $atts[ 'page_url' ] . '\'; file_exists( \''
								. $sBreadDir . '/header.php\' ) ? include( \''
								. $sBreadDir . '/header.php\' ) : false; ?>'
								. PHP_EOL
								. $this->GetTemplate( ETemplate::_PAGE_ )
								. PHP_EOL
								. '<?php file_exists( \''
								. $sBreadDir . '/footer.php\' ) ? include( \''
								. $sBreadDir . '/footer.php\' ) : false; ?>';
					break;
				}
			break;
		}
		
		if( SMART_PAGES < 2 )
		{
			if( SMART_PAGES < 1 )
			{
				$sHTML	= str_replace( '%NAV%',										$sNavMenu,					$sHTML );
				$sHTML	= str_replace( '%CSS%',										$sCSS,						$sHTML );
				$sHTML	= str_replace( '<?php echo SITE_URL ?>',					SITE_URL,					$sHTML );
				$sHTML	= str_replace( '<?php echo SITE_NAME ?>',					SITE_NAME,					$sHTML );
				$sHTML	= str_replace( '<?php echo SITE_HEADLINE ?>',				SITE_HEADLINE,				$sHTML );
			}
			else
			{
				$sHTML	= str_replace( '%NAV%',				'<?php include \'' . $sBreadDir . '/nav.php\' ?>',			$sHTML );
				$sHTML	= str_replace( '%CSS%',				'<?php include \'' . $sBreadDir . '/assets/style.css\' ?>',	$sHTML );
			}
			
			if( !$bSmart )
			{
				$sHTML	= str_replace( '<?php echo $aData[ \'author\' ] ?>',		$atts[ 'author' ],			$sHTML );
				$sHTML	= str_replace( '<?php echo $aData[ \'description\' ] ?>',	$atts[ 'description' ],		$sHTML );
				$sHTML	= str_replace( '<?php echo $aData[ \'image\' ] ?>',			$atts[ 'image' ],			$sHTML );
			}

			$sHTML	= str_replace( '<?php echo $aData[ \'title\' ] ?>',	$sExport[ 'title' ],		$sHTML );
			$sHTML	= str_replace( '<?php echo $pg_url ?>',				$atts[ 'page_url' ],		$sHTML );
			$sHTML	= str_replace( '<?php echo TWITTER_HANDLE ?>',		TWITTER_HANDLE,				$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON16 ?>',			FAVICON16,					$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON32 ?>',			FAVICON32,					$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON48 ?>',			FAVICON48,					$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON64 ?>',			FAVICON64,					$sHTML );
		}
		
		switch( $eType )
		{
			case EPageType::ARTICLE:
				if( SMART_PAGES < 1 )					/// need to make this dynamic and not replace it
				{
					if( !$bTitle )
					{
						$sHTML	= str_replace( '<?php echo $aData[ \'name\' ] ?>',	'',		$sHTML );
					}
				}
				$sPostTime = new DateTime( $sExport[ 'createdTime' ] );
				$sHTML	= str_replace( '%POSTTIME%',		$sPostTime->format( 'Y-m-d H:i:s' ),$sHTML );
				$sHTML	= str_replace( '%POSTTIME_READ%',	$sPostTime->format( 'M d, Y' ),		$sHTML );
				
				if( !$bSmart )
				{
					if( $sExport[ 'mimeType' ] == '' ) // Unless it is MEDIA file there wouldn't be mimeType in this struct
					{
						$sHTML = str_replace( '<?php echo $aData[ \'data\' ] ?>',	$sExport[ 'data' ],	$sHTML );
					}
				}
			break;
			
			case EPageType::PAGE:
				if( SMART_PAGES < 1 )
				{
					if( $bTitle )
					{
						$sHTML	= str_replace( '<?php echo $aData[ \'name\' ] ?>',	'', $sHTML );
					}
				}
				
				if( !$bSmart )
				{
					if( $sExport[ 'mimeType' ] == '' ) // Unless it is MEDIA file there wouldn't be mimeType in this struct
					{
						$sHTML = str_replace( '<?php echo $aData[ \'data\' ] ?>',	$sExport[ 'data' ],	$sHTML );
					}
				}
			break;
		}
		return $sHTML;
	}
	public	function BuildMetaData( &$sExport )
	{
		// Check for %meta{"author":"","description":"","image":""} Store it for %DESCRIPTION% and clear it from page building
		// Check for %Tags{"","",""}
		$sShort = '/(%meta({|\Z)(.*?)(}|\Z))/';
		$atts = [];
		if( preg_match_all( $sShort, $sExport, $aMatched ) > 0 )
		{
			$aHAML = [];
			// Get Params and Fix/Decode JSON
			preg_match( '/{(.*)}/s', $aMatched[ 0 ][ 0 ], $aHAML[ 'param' ], PREG_OFFSET_CAPTURE );
		//	$aHAML[ 'param' ]	= $this->m_CTextFormat->Decode_JSON( $aHAML[ 'param' ][ 0 ][ 0 ] );
			$aHAML[ 'param' ]	= $this->Decode_JSON( $aHAML[ 'param' ][ 0 ][ 0 ] );
			
			$objArray = [ 'author' => '', 'description' => '', 'image' => '' ];
			
			if( property_exists( $aHAML[ 'param' ], 'author' ) )		{	$objArray[ 'author' ] = $aHAML[ 'param' ]->{ 'author' };			}
			if( property_exists( $aHAML[ 'param' ],'description' ) )	{	$objArray[ 'description' ] = $aHAML[ 'param' ]->{ 'description' };	}
			if( property_exists( $aHAML[ 'param' ], 'image' ) )			{	$objArray[ 'image' ] = $aHAML[ 'param' ]->{ 'image' };				}
			
			$atts	= [ 'author'		=> $objArray[ 'author' ]
					  , 'description'	=> $objArray[ 'description' ]
					  , 'image'			=> $objArray[ 'image' ]
					];
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
			//	$aHAML[ 'param' ] = $this->m_CTextFormat->Decode_JSON( $aHAML[ 'param' ][ 0 ][ 0 ] );
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
	public	function BuildMediaExcerpt( $sName, $sID, $eType, $sPageURL, $iTumbSize = 320 )
	{
		if( CURL_OFF ){ $sURL	= 'https://drive.google.com/uc?id=' . $sID; }
		else { $sURL	= $this->GetFinalURL( 'https://drive.google.com/uc?id=' . $sID ); }
		switch( $eType )
		{
			case 'image/png':
				$sHTML =	'<div class="image-i">
								<a href="#image_' . $sName . '">
									<img src="https://drive.google.com/thumbnail?&sz=w' . $iTumbSize . '&id=' . $sID . '">
								</a>'
								.
						//		. $sCaption .
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
				$sHTML	=	'<a href="' . $sPageURL . '"><img src="https://drive.google.com/thumbnail?&sz=w' . $iTumbSize . '&id=' . $sID . '"></a>';
			/*	$sHTML	=	'<div class="image-i">
									<a href="#image_' . $sName . '">
										<img src="https://drive.google.com/thumbnail?&sz=w' . $iTumbSize . '&id=' . $sID . '">
									</a>
									</div>
								<a href="#close">
									<div id="image_' . $sName .  '" class="image-w">
										<video controls preload="none"><source src="' . $sURL . '"></video>
									</div>
								</a>';*/
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
									<h1><span class="playTitle">' . $sName . '</span></h1>
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
	public	function BuildMediaData( $sName, $sID, $eType, $iTumbSize = 320 )
	{
		if( CURL_OFF ){ $sURL	= 'https://drive.google.com/uc?id=' . $sID; }
		else { $sURL	= $this->GetFinalURL( 'https://drive.google.com/uc?id=' . $sID ); }
		switch( $eType )
		{
			case 'image/png':
				$sHTML =	'<div class="image-i">
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
			$sTitle		=	'';//$this->GetMediaTitle( $sFileID );
			$sExport	=	[ 'title'	=>	$sTitle
							, 'name'	=>	$sTitle
							, 'data'	=>	$this->BuildMediaData( $sTitle, $sFileID, $eType, THUMB_SIZE  ) ];
		}
		return $sExport;
	}
	public	function ConstructNavigation( &$sNavMenu, &$aNavList )
	{
		$sNavMenu = $this->GetTemplate( ETemplate::NAV_HEADER );
		$sHTML = $this->GetTemplate( ETemplate::NAV_ITEM );
		foreach( $aNavList as $file )
		{
			$sNavMenu .= str_replace( '%PAGETITLE%',	$file[ 'rename' ],$sHTML );
			$sNavMenu = str_replace( '%PAGEURL%',	$file[ 'url' ],	$sNavMenu );
		}
		$sNavMenu .= $this->GetTemplate( ETemplate::NAV_FOOTER );
	}
	public	function ConstructArticle( $sExport, $sNavMenu, $sPath, $bSmart = false )
	{
		if( isset( $sExport ) )
		{
			if( SMART_PAGES > 0 )
			{
				$bSmart = true;
				if( substr_count( $sExport[ 'data' ], '%' . STATIC_DOC ) > 0 )
				{
					$bSmart = false;
					$sExport[ 'data' ] = str_replace( '%' . STATIC_DOC, '', $sExport[ 'data' ] );
				}
			}
			else
			{
				if( substr_count( $sExport[ 'data' ], '%' . DYNAMIC_DOC ) > 0 )
				{
					$bSmart = true;
					$sExport[ 'data' ] = str_replace( '%' . DYNAMIC_DOC, '', $sExport[ 'data' ] );
				}
			}
			
			$bTitle		= substr_count( $sExport[ 'data' ], '%' . NO_TITLE ) ? true : false;
			$bGallery	= substr_count( $sExport[ 'data' ], '%' . GALLERY_SECTION ) ? true : false;
			$atts		= $this->BuildMetaData( $sExport[ 'data' ] );
			$sCSS		= $this->GetTemplate( ETemplate::CSS );
			
			$sExport[ 'title' ] = str_replace(' ', '-', $sExport[ 'title' ] );
			$sDirPath = SITE_DIRECTORY . $sPath . strtolower( $sExport[ 'title' ] );
			$atts = array_merge( $atts, [ 'page_url' => SITE_URL . $sPath . strtolower( $sExport[ 'title' ] ) ] );
			
			if( SMART_PAGES > 0 )
			{
				$atts = array_merge( $atts, [ 'header' => '<?php define( "__Permitted__", 1 ); define( "__Sammich__", 1 ); define( "IS_PAGE", 0 );' . $this->m_CTemplates->GetLiveTemplate() . '?>' ] );
				$atts[ 'header' ] = str_replace( '%FILEID%',		"'" . $sExport[ 'id' ] . "'," . EPageType::ARTICLE,	$atts[ 'header' ]  );
				$atts[ 'header' ] = str_replace( '%DIRPATH%',		UP_DIR,												$atts[ 'header' ] );
			}
			
			if( !$bSmart )
			{
				if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 31 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sDirPath, EShort::Code[ 31 ], true ); }
				if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 32 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sDirPath, EShort::Code[ 32 ], true ); }
				if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 33 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sDirPath, EShort::Code[ 33 ], true ); }
				if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 34 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sDirPath, EShort::Code[ 34 ], true ); }
				$sExport = $sExport + [ 'gallery' => $this->ConstructFormatting( $sExport[ 'data' ], $bGallery, EPageType::ARTICLE ) ];
			}
			
			$sHTML	= $this->BuildHeaderData( $sExport, $atts, $bTitle, $bSmart, EPageType::ARTICLE, $sCSS, $sNavMenu );

			// Create Categories
			$sBodyCat	= $this->GetTemplate( ETemplate::_ARTICLE_CAT_ );
			$catList = explode( '/', $sPath );
			$catPath = '';
			$sCatTemp = '';
			for( $i = 0; $i < sizeof( $catList ) - 1; ++$i )
			{
				$sCatTemp .= str_replace( '%DIRNAME%',	$catList[ $i ],			$sBodyCat );
				$catPath .= $catList[ $i ] . '/';
				$sCatTemp = str_replace( '%DIRURL%',	SITE_URL . $catPath,	$sCatTemp );
			}
		
			$sHTML = str_replace( '%CATEGORIES%',	$sCatTemp,	$sHTML );

			if( !$bSmart )
			{
				$sHTML	= str_replace( '<?php echo $aData[ \'data\' ] ?>', $sExport[ 'data' ], $sHTML );
				$sHTML	= str_replace( '<?php if( array_key_exists( \'gallery\', $aData ) ){ echo $aData[ \'gallery\' ]; } ?>', $sExport[ 'gallery' ], $sHTML );
			}
			
			$sHTML	= str_replace( '%COMMENTS%', $this->GetTemplate( ETemplate::COMMENTS ),	$sHTML );
			$sHTML	= str_replace( '%COMMENTJSDIR%', SITE_URL . SMART_PAGES_DIR,	$sHTML );
			
			if( !file_exists( $sDirPath ) ){ mkdir( $sDirPath, 0755, true ); }
			if( file_exists( $sDirPath ) )
			{
				if( $bSmart || SMART_PAGES > 0 )
				{
					if( file_exists( $sDirPath . '/index.html' ) ){ unlink( $sDirPath . '/index.html' ); }
					$htmlFile = fopen( $sDirPath . '/index.php', 'w' );
				}
				else
				{
					if( file_exists( $sDirPath . '/index.php' ) ){ unlink( $sDirPath . '/index.php' ); }
					$htmlFile = fopen( $sDirPath . '/index.html', 'w' );
				}
				fwrite( $htmlFile, $sHTML );
				fclose( $htmlFile );
			} else { echo 'Directory does not exist. <br/>'; }
		} else { echo 'Param not set. <br/>'; }
	}
	public	function ConstructPage( $sExport, $sNavMenu, $sPath, $bSmart = false )
	{
		if( isset( $sExport ) )
		{
			if( SMART_PAGES > 0 ) // Website is DYNAMIC
			{
				$bSmart = true;
				// Check if Page is set to STATIC under dynamic website
				if( substr_count( $sExport[ 'data' ], '%' . STATIC_DOC ) > 0 )
				{
					$bSmart = false;
					$sExport[ 'data' ] = str_replace( '%' . STATIC_DOC, '', $sExport[ 'data' ] );
				}
			}
			else // Website is STATIC
			{
				// Check if Page is set to DYNAMIC under static website
				if( substr_count( $sExport[ 'data' ], '%' . DYNAMIC_DOC ) > 0 )
				{
					$bSmart = true;
					$sExport[ 'data' ] = str_replace( '%' . DYNAMIC_DOC, '', $sExport[ 'data' ] );
				}
			}

			if( strtolower( $sExport[ 'title' ] ) == 'index' ){	$sExport[ 'title' ] = '';	}
			$bTitle		= substr_count( $sExport[ 'data' ], '%' . NO_TITLE ) ? true : false;
			$atts		= $this->BuildMetaData( $sExport[ 'data' ] );
			$sCSS		= $this->GetTemplate( ETemplate::CSS );

			$sExport[ 'title' ] = str_replace(' ', '-', $sExport[ 'title' ] );
			$sDirPath	= SITE_DIRECTORY . $sPath . strtolower( $sExport[ 'title' ] );
			$atts = array_merge( $atts, [ 'page_url' => SITE_URL . $sPath . strtolower( $sExport[ 'title' ] ) ] );
	
			if( SMART_PAGES > 0 )
			{
				if( strtolower( $sExport[ 'title' ] ) == 'index' ){	$dirPath = dirname( dirname( __DIR__ ) ) . '/';	}
				else{	$dirPath	= UP_DIR;	}
				$atts = array_merge( $atts, [ 'header' => '<?php define( "__Permitted__", 1 ); define( "__Sammich__", 1 ); define( "IS_PAGE", 1 );' . $this->m_CTemplates->GetLiveTemplate() . ' ?>' ] );
				$atts[ 'header' ]	= str_replace( '%DIRPATH%',		$dirPath,											$atts[ 'header' ] );
				$atts[ 'header' ]	= str_replace( '%FILEID%',		"'" . $sExport[ 'id' ] . "'," . EPageType::PAGE,	$atts[ 'header' ] );
			}
			
			if( !$bSmart )
			{
				if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 31 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sDirPath, EShort::Code[ 31 ], true ); }
				if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 32 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sDirPath, EShort::Code[ 32 ], true ); }
				if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 33 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sDirPath, EShort::Code[ 33 ], true ); }
				if( substr_count( $sExport[ 'data' ], '%' . EShort::Code[ 34 ] ) > 0 ){ $this->Build3DPages( $sExport[ 'data' ], $sDirPath, EShort::Code[ 34 ], true ); }
				$bGallery	= substr_count( $sExport[ 'data' ], '%' . GALLERY_SECTION ) ? true : false;
				$sExport = $sExport + [ 'gallery' => $this->ConstructFormatting( $sExport[ 'data' ], $bGallery, EPageType::PAGE ) ];
			}
			
			$sHTML	= $this->BuildHeaderData( $sExport, $atts, $bTitle, $bSmart, EPageType::PAGE, $sCSS, $sNavMenu );

			if( !file_exists( SITE_DIRECTORY ) ){ mkdir( SITE_DIRECTORY, 0755, true ); }
			if( !file_exists( $sDirPath ) && strtolower( $sExport[ 'title' ] ) != 'index' ){ mkdir( $sDirPath, 0755, true ); }
			if( $bSmart || SMART_PAGES > 0 )
			{
				if( file_exists( $sDirPath . '/index.html' ) ){ unlink( $sDirPath . '/index.html' ); }
				$htmlFile = fopen( $sDirPath . '/index.php', 'w' );
			}
			else
			{
				if( file_exists( $sDirPath . '/index.php' ) ){ unlink( $sDirPath . '/index.php' ); }
				$htmlFile = fopen( $sDirPath . '/index.html', 'w' );
			}
			fwrite( $htmlFile, $sHTML );
			fclose( $htmlFile );
		} else { echo 'Param1 or 2 not set. <br/>'; }
	}
	public	function ConstructMedia( $sExport, $sNavMenu, $sPath, $bSmart = false )
	{
		if( isset( $sExport ) )
		{
			if( SMART_PAGES > 0 ){ $bSmart = true; }
			else{ $bSmart = true; }
			
			$atts	=	[ 'author'		=> SITE_AUTHOR
						, 'description'	=> SITE_DESCRIPTION
						, 'image'		=> META_DEFAULT_IMG ];
			$sCSS	= $this->GetTemplate( ETemplate::CSS );
			
		//	$sExport[ 'title' ] = str_replace(' ', '-', $sExport[ 'title' ] );		
		//	$sURLName = $this->m_CTextFormat->FormatForURL( $sExport[ 'title' ] );
			$sURLName = $this->FormatForURL( $sExport[ 'title' ] );
			
			$sDirPath = SITE_DIRECTORY . $sPath . $sURLName;
			$atts = array_merge( $atts, [ 'page_url' => SITE_URL . $sPath . $sURLName ] );
			
			if( SMART_PAGES > 0 )
			{
				$atts = array_merge( $atts, [ 'header' => '<?php define( "__Permitted__", 1 ); define( "__Sammich__", 1 );' . $this->m_CTemplates->GetLiveTemplate() . '?>' ] );
				$atts[ 'header' ]	= str_replace( '%FILEID%',	"'" . $sExport[ 'id' ] . "','" . $sExport[ 'mimeType' ] . "'",	$atts[ 'header' ]	);
				$atts[ 'header' ]	= str_replace( '%DIRPATH%',	UP_DIR,															$atts[ 'header' ]	);
				$sFallBackTitle		= '<?php if( $aData[ \'title\' ] == \'\' ){ $aData[ \'title\' ] = %FALLBACK%; } ?>';
				$atts[ 'header' ]	.= str_replace( '%FALLBACK%',	"'" . $sExport[ 'title' ] . "'",	$sFallBackTitle	);
			}

			$sHTML	= '<br>' . $this->BuildHeaderData( $sExport, $atts, true, $bSmart, EPageType::ARTICLE, $sCSS, $sNavMenu );

			// Create Categories
			$sBodyCat	= $this->GetTemplate( ETemplate::_ARTICLE_CAT_ );
			$catList = explode( '/', $sPath );
			$catPath = '';
			$sCatTemp = '';
			for( $i = 0; $i < sizeof( $catList ) - 1; ++$i )
			{
				$sCatTemp .= str_replace( '%DIRNAME%',	$catList[ $i ],			$sBodyCat );
				$catPath .= $catList[ $i ] . '/';
				$sCatTemp = str_replace( '%DIRURL%',	SITE_URL . $catPath,	$sCatTemp );
			}
		
			$sHTML = str_replace( '%CATEGORIES%',	$sCatTemp,	$sHTML );

			if( !$bSmart )
			{
				$sHTML	= str_replace( '<?php echo $aData[ \'data\' ] ?>', $this->DisplayContent( $sExport[ 'id' ], $sExport[ 'mimeType' ], $sPath ), $sHTML );
				$sHTML	= str_replace( '<?php if( array_key_exists( \'gallery\', $aData ) ){ echo $aData[ \'gallery\' ]; } ?>', '', $sHTML );
			}
			
			$sHTML	= str_replace( '%COMMENTS%', $this->GetTemplate( ETemplate::COMMENTS ),	$sHTML );
			$sHTML	= str_replace( '%COMMENTJSDIR%', SITE_URL . SMART_PAGES_DIR,	$sHTML );
			
			if( !file_exists( $sDirPath ) ){ mkdir( $sDirPath, 0755, true ); }
			if( file_exists( $sDirPath ) )
			{
				if( $bSmart || SMART_PAGES > 0 )
				{
					if( file_exists( $sDirPath . '/index.html' ) ){ unlink( $sDirPath . '/index.html' ); }
					$htmlFile = fopen( $sDirPath . '/index.php', 'w' );
				}
				else
				{
					if( file_exists( $sDirPath . '/index.php' ) ){ unlink( $sDirPath . '/index.php' ); }
					$htmlFile = fopen( $sDirPath . '/index.html', 'w' );
				}
				fwrite( $htmlFile, $sHTML );
				fclose( $htmlFile );
			} else { echo 'Directory does not exist. <br/>'; }
		} else { echo 'Param not set. <br/>'; }
	}
	protected	function CopyTo( $sFrom, $sTo, $bForce = false )
	{
		$sDir = opendir( $sFrom );
		if( !file_exists( $sTo ) ){ mkdir( $sTo, 0755, true ); }
		while( false !== ( $sFile = readdir( $sDir ) ) )
		{
			if( ( $sFile != '.' ) && ( $sFile != '..' ) )
			{
				$sFromFile	= $sFrom . '/' . $sFile;
				$sToFile	= $sTo . '/' . $sFile;
				if( is_dir( $sFromFile ) )
				{
					$this->CopyTo( $sFromFile, $sToFile );
				}
				else
				{
					if( !file_exists( $sToFile ) || $bForce )
					{
						copy( $sFromFile, $sToFile );
					}
				}
			}
		}
		closedir( $sDir );
	}
	public	function GetNavigation()
	{
		$sNavMenu = $this->GetTemplate( ETemplate::NAV_HEADER );
		$sHTML = $this->GetTemplate( ETemplate::NAV_ITEM );
		foreach( NAVIGATION_KEY as $file )
		{
			$sNavMenu .= str_replace( '%PAGETITLE%',	$file[ 'rename' ],$sHTML );
			$sNavMenu = str_replace( '%PAGEURL%',		$file[ 'url' ],	$sNavMenu );
		}
		$sNavMenu .= $this->GetTemplate( ETemplate::NAV_FOOTER );
		return $sNavMenu;
	}
	public	function ConstructBread( $sBreadDir, $sNavMenu )
	{
		if( !file_exists( $sBreadDir ) ){	mkdir( $sBreadDir, 0755, true );	}
		if( ENABLE_THREEJS ){	$this->CopyTo( $this->m_CTemplates->GetThreeJSDependencies(), $sBreadDir . '/3js' );	}
		$this->CopyTo( $this->m_CTemplates->GetAssetsDir( SITE_THEME ), $sBreadDir . '/assets', true );
		
		// Navigation List
		$sHTML = '<?php if( !defined( "__Sammich__" ) ){ header( "Location: " . filter_var( "' . SITE_URL . '", FILTER_SANITIZE_URL ) ); exit; } ?>';
		$sHTML .= $sNavMenu;
		$htmlFile = fopen( $sBreadDir . '/nav.php', 'w' );
		fwrite( $htmlFile, $sHTML );
		fclose( $htmlFile );
		
		// Header with Navigation ( Sesame Seeds )
		$sHTML	= '<?php if( !defined( "__Sammich__" ) ){ header( "Location: " . filter_var( "' . SITE_URL . '", FILTER_SANITIZE_URL ) ); exit; } ?>' . $this->GetTemplate( ETemplate::HEADER );
		$this->ConstructMeta( $sHTML, true );
		switch( SMART_PAGES )
		{
			case 0:
				$sHTML	= str_replace( '%NAV%',				'<?php include \'' . $sBreadDir . '/nav.php\' ?>',		$sHTML );
				$sHTML	= str_replace( '%CSS%',				$this->GetTemplate( ETemplate::CSS ),					$sHTML );
			break;
			case 1:
				$sHTML	= str_replace( '%NAV%',				'<?php include \'' . $sBreadDir . '/nav.php\' ?>',			$sHTML );
				$sHTML	= str_replace( '%CSS%',				'<?php include \'' . $sBreadDir . '/assets/style.css\' ?>',	$sHTML );
			break;
			case 2:
				$sHTML	= str_replace( '%NAV%',				'<?php echo $cPB->GetNavigation(); ?>',							$sHTML );
				$sHTML	= str_replace( '%CSS%',				'<?php include \'' . $sBreadDir . '/assets/style.css\' ?>',	$sHTML );
			break;
		}
		if( SMART_PAGES < 2 )
		{
			$sHTML	= str_replace( '<?php echo TWITTER_HANDLE ?>',		TWITTER_HANDLE,				$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON16 ?>',			FAVICON16,					$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON32 ?>',			FAVICON32,					$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON48 ?>',			FAVICON48,					$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON64 ?>',			FAVICON64,					$sHTML );
		}
		$htmlFile = fopen( $sBreadDir . '/header.php', 'w' );
		fwrite( $htmlFile, $sHTML );
		fclose( $htmlFile );

		// Header with no Navigation ( Smooth Bread )
		$sHTML	= '<?php if( !defined( "__Sammich__" ) ){ header( "Location: " . filter_var( "' . SITE_URL . '", FILTER_SANITIZE_URL ) ); exit; } ?>' . $this->GetTemplate( ETemplate::HEADER );
		$this->ConstructMeta( $sHTML, true );
		if( SMART_PAGES < 1 )
		{
			$sHTML	= str_replace( '%CSS%',				$this->GetTemplate( ETemplate::CSS ),						$sHTML );
		}
		else {	$sHTML	= str_replace( '%CSS%',				'<?php include \'' . $sBreadDir . '/assets/style.css\' ?>',	$sHTML ); }
		if( SMART_PAGES < 2 )
		{
			$sHTML	= str_replace( '<?php echo TWITTER_HANDLE ?>',		TWITTER_HANDLE,				$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON16 ?>',			FAVICON16,					$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON32 ?>',			FAVICON32,					$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON48 ?>',			FAVICON48,					$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON64 ?>',			FAVICON64,					$sHTML );
		}
		$htmlFile = fopen( $sBreadDir . '/header-nonav.php', 'w' );
		fwrite( $htmlFile, $sHTML );
		fclose( $htmlFile );
		
		// Footer
		$sHTML	= '<?php if( !defined( "__Sammich__" ) ){ header( "Location: " . filter_var( "' . SITE_URL . '", FILTER_SANITIZE_URL ) ); exit; } ?>' . $this->GetTemplate( ETemplate::FOOTER );
		$htmlFile = fopen( $sBreadDir . '/footer.php', 'w' );
		fwrite( $htmlFile, $sHTML );
		fclose( $htmlFile );
		
		// Generate Not Found page
		if( SMART_PAGES > 0 )
		{
			$sHTML	= '<?php define( "__Permitted__", 1 ); define( "__Sammich__", 1 ); require_once \'' . dirname( dirname( __DIR__ ) ) . '/_rcsbg/wp-managers/CPageBuilder.php\'; ?>' . $this->GetTemplate( ETemplate::HEADER );
			$sHTML	= str_replace( '%NAV%',				'<?php include \'' . $sBreadDir . '/nav.php\' ?>',				$sHTML );
			$sHTML	= str_replace( '%CSS%',				'<?php include \'' . $sBreadDir . '/assets/style.css\' ?>',		$sHTML );
		}
		else
		{ 
			$sHTML	= $this->GetTemplate( ETemplate::HEADER );
			$sHTML	= str_replace( '%NAV%',		$sNavMenu,								$sHTML );
			$sHTML	= str_replace( '%CSS%',		$this->GetTemplate( ETemplate::CSS ),	$sHTML );
		}
		$sHTML	.= $this->GetTemplate( ETemplate::ERR );
		$this->ConstructMeta( $sHTML, true );
		if( SMART_PAGES < 2 )
		{
			$sHTML	= str_replace( '<?php echo TWITTER_HANDLE ?>',			TWITTER_HANDLE,			$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON16 ?>',				FAVICON16,				$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON32 ?>',				FAVICON32,				$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON48 ?>',				FAVICON48,				$sHTML );
			$sHTML	= str_replace( '<?php echo FAVICON64 ?>',				FAVICON64,				$sHTML );
		}
		$sHTML	= str_replace( '<?php echo $aData[ \'title\' ] ?>',			'ERROR',				$sHTML );
		$sHTML	= str_replace( '<?php echo $aData[ \'description\' ] ?>',	'404 not found.',		$sHTML );
		$sHTML	= str_replace( '<?php echo $aData[ \'author\' ] ?>',		SITE_AUTHOR,			$sHTML );
		$sHTML	= str_replace( '<?php echo $aData[ \'image\' ] ?>',			META_DEFAULT_IMG,		$sHTML );
		$sHTML	.= $this->GetTemplate( ETemplate::FOOTER );
		
		if( SMART_PAGES > 0 ){	$htmlFile	= fopen( $sBreadDir . '/index.php', 'w' );	}
		else
		{
			$sHTML	= str_replace( '<?php echo SITE_URL ?>',				SITE_URL,				$sHTML );
			$sHTML	= str_replace( '<?php echo SITE_NAME ?>',				SITE_NAME,				$sHTML );
			$sHTML	= str_replace( '<?php echo SITE_HEADLINE ?>',			SITE_HEADLINE,			$sHTML );
			$htmlFile	= fopen( $sBreadDir . '/index.html', 'w' );
		}
		fwrite( $htmlFile, $sHTML );
		fclose( $htmlFile );
	}
}
?>