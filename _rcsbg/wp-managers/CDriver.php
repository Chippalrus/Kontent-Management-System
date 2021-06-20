<?php
//if( !defined( '__Permitted__' ) ){ header( 'Location: ' . filter_var( 'https://error.chippalrus.ca/404.html', FILTER_SANITIZE_URL ) ); exit; }
//	Includes	===========================================================
require_once dirname(__DIR__) . '/wp-configs/web.config.php';
require_once dirname(__DIR__) . '/wp-configs/rcsbg.config.php';
require_once __DIR__ . '/CPageBuilder.php';
//	Define	===============================================================
//define( 'THIS_DIR', __DIR__ . DIRECTORY_SEPARATOR );
//define( 'UP_DIR', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
//define( 'SITE_DIRECTORY', UP_DIR . WEB_DIRECTORY . DIRECTORY_SEPARATOR );
//	Assert	===============================================================
/*	CGallery	=======================================================
=========================================================================== */
class	CDriver	extends CPageBuilder
{
//	Members	===============================================================
	private		$m_CPageBuilder;
	private		$m_CTemplates;
	private		$m_CTextFormat;
	private		$m_ExcerptList	= [];
	private		$m_PurgeList	= [];
	private		$m_MasterList	= [];
	private		$m_NavList		= [];
	private		$m_NavMenu		= '';
//	Constructor	===========================================================
	public	function __construct()
	{
		$this->m_CPageBuilder	= new CPageBuilder();
		$this->m_CTemplates		= new CTemplates();
		$this->m_CTextFormat	= new CTextFormat();
	}
//	Helper	===================================================================
	public	function	GetNavMenu		(){ return $this->m_NavMenu; }
	public	function	GetNavList		(){ return $this->m_NavList; }
	public	function	GetExcerptList	(){ return $this->m_ExcerptList; }
	public	function	GetPurgeList	(){ return $this->m_PurgeList; }
	public	function	GetMasterList	(){ return $this->m_MasterList; }
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
	//	echo $cInfo . '<br>';
		while( $cInfo != '200' )
		{
			$cOut		=	curl_exec( $cURL );
			$cInfo		=	curl_getinfo( $cURL, CURLINFO_HTTP_CODE );
	//		echo $cInfo . '<br>';
		}
		curl_close( $cURL );
		return $cOut;
	}
	public	function	GetFileData		( $sFileID )
	{
		$sURL	=	'https://www.googleapis.com/drive/v3/files/'
				.	$sFileID
				.	'?fields=kind,createdTime,modifiedTime,id,name,mimeType'
				.	'&key=' . 	API_KEY;

	//	$jData	=	json_decode( file_get_contents( $sURL ),true );
		$jData	=	json_decode( $this->GetFromCurl( $sURL ),true );
				
		return $jData;
	}
	protected	function GetDependency( $eTemplate )
	{
		return $this->m_CTemplates->GetDependency( $eTemplate );
	}
	public	function	GetExportedContents	( $sFileID, $eDocumentType = 'html' )
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
				$sFileName = explode( '=', $sHeader[ 'content-disposition' ] );
				if( $sFileName[ 1 ] )
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
			
	//		$sFileData = file_get_contents( $sURL );
			$sFileData = $this->GetFromCurl( $sURL );
			$sDisplayTitle = '';
			if( strtolower( $sTitle ) != 'index' )
			{
				if( !substr_count( $sFileData, '%' . NO_TITLE ) )
				{
					$sDisplayTitle = $this->m_CTemplates->GetTemplate( SITE_THEME, ETemplate::_TITLE_ );
					$sDisplayTitle = str_replace( '%TITLE%', urldecode( $sTitle ), $sDisplayTitle  );
				//	if( IS_PAGE ){ $sDisplayTitle .= $this->m_CTemplates->GetTemplate( SITE_THEME, ETemplate::_PG_SPACER_ ); }
				}
			}
			else { $sDisplayTitle = ''; }
			
			$sData = [ 'title' => urldecode( $sTitle ), 'data' => $sFileData, 'name' => $sDisplayTitle ];
		}
		return $sData;
	}
	public	function	ExportFormatedText	( $sFileID )
	{
		$sExport = $this->GetExportedContents( $sFileID, 'txt' );
		
		$bOverwrite = false;
		if( substr_count( $sExport[ 'data' ], '%' . DISABLE_RICHTEXT ) > 0 ){ $bOverwrite = true; }
		else if( substr_count( $sExport[ 'data' ], '%' . DISABLE_MARKDOWN ) > 0 )
		{
			$sExport = $this->GetExportedContents( $sFileID, 'html' );
			$bOverwrite = true;
		}
		
		if( !$bOverwrite )
		{
			if( !MARKDOWN_MODE )
			{
				$sExport = $this->GetExportedContents( $sFileID, 'html' );
			}
		}
		return $sExport;
	}
	public	function	ConstructPageData	( $aData, $sNavMenu, $sPath = '' )
	{
		$sExport = [ 'id' => $aData[ 'id' ], 'title' => $aData[ 'name' ], 'data' => $aData[ 'contents' ][ 'data' ], 'createdTime' => $aData[ 'createdTime' ] ];
		$this->m_CPageBuilder->ConstructPage( $sExport, $sNavMenu, $sPath );
	}
	public	function	ConstructPage		( $sFileID, $sName, $sCreatedTime, $sNavMenu, $sPath = '' )
	{
		$sExport = [ 'id' => $sFileID, 'title' => $sName, 'data' => $this->ExportFormatedText( $sFileID )[ 'data' ], 'createdTime' => $sCreatedTime ];
		$this->m_CPageBuilder->ConstructPage( $sExport, $sNavMenu, $sPath );
	}
	public	function	ConstructArticleData	( $aData, $sNavMenu, $sPath = '' )
	{
		if( $aData[ 'mimeType' ] == 'application/vnd.google-apps.document' )
		{
			$sExport = [ 'id' => $aData[ 'id' ], 'title' => $aData[ 'name' ], 'data' => $aData[ 'contents' ][ 'data' ], 'createdTime' => $aData[ 'createdTime' ] ];
			$this->m_CPageBuilder->ConstructArticle( $sExport, $sNavMenu, $sPath );
		}
		else
		{
			$sExport = [ 'id' => $aData[ 'id' ], 'title' => $aData[ 'name' ], 'mimeType' => $aData[ 'mimeType' ], 'createdTime' => $aData[ 'createdTime' ] ];
			$this->m_CPageBuilder->ConstructMedia( $sExport, $sNavMenu, $sPath );
		}
	}
	public	function	ConstructArticle	( $sFileID, $sName, $sCreatedTime, $sNavMenu, $sPath = '' )
	{
		$sExport = [ 'id' => $sFileID, 'title' => $sName, 'data' => $this->ExportFormatedText( $sFileID )[ 'data' ], 'createdTime' => $sCreatedTime ];
		$this->m_CPageBuilder->ConstructArticle( $sExport, $sNavMenu, $sPath );
	}
	public	function	RecursiveDepthTree	( $sFolderID, &$aContent, $sParents = '', $pageSize = '100', $pageToken = '', $aParams = []	)
	{
		if( $sFolderID != '' )
		{
			$sParams = '';
			for( $i = 0; $i < sizeof( $aParams ); ++$i )
			{
				if( ( $i + 1 ) != sizeof( $aParams ) ){ $sParams .= $aParams[ $i ] . ','; }
				else{ $sParams .= $aParams[ $i ]; }
			}
			
			$sURL	= 'https://www.googleapis.com/drive/v3/files?q=\''
					. $sFolderID
					. '\'+in+parents&pageSize='
					. $pageSize
					. '&orderBy=' . $sParams . '&key='
					. API_KEY
					. '&pageToken='
					. $pageToken;
					
		//	$jList	=	json_decode( file_get_contents( $sURL ),true );
			$jList	=	json_decode( $this->GetFromCurl( $sURL ),true );
			foreach( $jList[ 'files' ] as $jFile )
			{
				// Get actual file data
				$jFile = $this->GetFileData( $jFile[ 'id' ] );
				// Get Name
				$sFileName			=	$this->m_CTextFormat->FormatForURL( $jFile[ 'name' ] );
				$jFile[ 'parents' ] =	$sParents;
				
				// Generate Navigation list based on Root Directory
				if( $sParents == null 
				&&( $jFile[ 'mimeType' ] == 'application/vnd.google-apps.folder'
				 || $jFile[ 'mimeType' ] == 'application/vnd.google-apps.document' ) )
				 {
					 if( $sFileName != 'index' && !CUSTOM_NAV_ONLY )
					 {
						$bPrioritised = true;
						foreach( $this->m_NavList as &$aNav )
						{
							if( $aNav[ 'name' ] == $sFileName )
							{
								$aNav[ 'url' ] = SITE_URL . $sFileName;
								$bPrioritised = true;
								break;
							} else { $bPrioritised = false; }
						}
						if( !$bPrioritised )
						{
							$aMenu = [ 'name' => $jFile[ 'name' ], 'rename' => $jFile[ 'name' ], 'url' => SITE_URL . $sFileName ];
							if( !in_array( $aMenu, $this->m_NavList ) )
							array_push( $this->m_NavList, $aMenu );
						}
					 }
				 }

				switch( $jFile[ 'mimeType' ] )
				{
					case 'application/vnd.google-apps.folder':
						// Build Directory Path
						if( $sParents != null )
						{
							$sFileName 	=	$sParents . DIRECTORY_SEPARATOR . $sFileName;
						}
					//	$sDir	=	preg_replace( '#/+#', '/', SITE_DIRECTORY . $sPar . DIRECTORY_SEPARATOR );
						$sDir	=	SITE_DIRECTORY . $sFileName . DIRECTORY_SEPARATOR;
						if( !file_exists( $sDir ) ){ mkdir( $sDir, 0755, true ); }
						if( !in_array( $sDir, $this->m_MasterList ) ){ array_push( $this->m_MasterList, $sDir ); }
						if( $sParents == null )
						{
							if( !array_key_exists( $sFileName, $aContent ) )
							{
								$aContent = array_merge( $aContent, [ $sFileName => [] ] );
							}
						}
						else
						{
							$aDirList = explode( '/', $sFileName );
							$aTemp	= &$aContent;
							$iVal	= sizeof( $aDirList );
							for( $i = 0; $i < $iVal; ++$i )
							{
								if( array_key_exists( $aDirList[ $i ], $aTemp ) )
								{
									$aTemp	= &$aTemp[ $aDirList[ $i ] ];
								}
							}
							$aTemp = array_merge( $aTemp, [ $aDirList[ $iVal - 1 ] => [] ] );
						}
						// Continue checking for files
						$this->RecursiveDepthTree( $jFile[ 'id' ], $aContent, $sFileName, $pageSize, $pageToken, $aParams );
					break;
					
					case 'application/vnd.google-apps.document':
						$jFile[ 'contents' ] = $this->ExportFormatedText( $jFile[ 'id' ] );
						// Get file parents, if any
						if( $sFileName != 'index' )
						{
							$sDir	=	SITE_DIRECTORY . $jFile[ 'parents' ] . DIRECTORY_SEPARATOR . $sFileName . DIRECTORY_SEPARATOR;
							$sDir	=	str_replace( '//', DIRECTORY_SEPARATOR, $sDir );
							if( !file_exists( $sDir ) ){ mkdir( $sDir, 0755, true ); }
							if( !in_array( $sDir, $this->m_MasterList ) ){ array_push( $this->m_MasterList, $sDir ); }
						}
						if( $jFile[ 'parents' ] != null )
						{
							$aDirList = explode( '/', $sParents );
							$aTemp	= &$aContent;
							$iVal	= sizeof( $aDirList );
							for( $i = 0; $i < $iVal; ++$i )
							{
								if( array_key_exists( $aDirList[ $i ], $aTemp ) )
								{
									$aTemp	= &$aTemp[ $aDirList[ $i ] ];
								}
							}
							array_push( $aTemp, $jFile );
						}
						else
						{
							array_push( $aContent, $jFile );
						}
					break;

					default:
						if( $jFile[ 'parents' ] != null )
						{
							$sDir	=	SITE_DIRECTORY . $jFile[ 'parents' ] . DIRECTORY_SEPARATOR . $sFileName . DIRECTORY_SEPARATOR;
							if( !in_array( $sDir, $this->m_MasterList ) ){ array_push( $this->m_MasterList, $sDir ); }
							
							$aDirList = explode( '/', $sParents );
							$aTemp	= &$aContent;
							$iVal	= sizeof( $aDirList );
							for( $i = 0; $i < $iVal; ++$i )
							{
								if( array_key_exists( $aDirList[ $i ], $aTemp ) )
								{
									$aTemp	= &$aTemp[ $aDirList[ $i ] ];
								}
							}
							array_push( $aTemp, $jFile );
						}
					break;
				}
			}
			
			if( array_key_exists( 'nextPageToken', $jList ) )
			{
				$this->RecursiveDepthTree( $sFolderID, $aContent, $sParents, $pageSize, $pageToken, $aParams );
			}
		}
	}
	public	function	SetupNavigationKey		(){ $this->m_NavList = NAVIGATION_KEY; }
	public	function	AddCustomNavigation	( $sName, $sURL )
	{
		if( isset( $sName ) && isset( $sURL ) )
		{
			array_push( $this->m_NavList, [ 'name' => $sName, 'url' => $sURL ] );
		}
	}
	protected	function	GetTemplate		( $eTemplate )
	{
		return $this->m_CTemplates->GetTemplate( SITE_THEME, $eTemplate );
	}
	private	function	RemoveDir( $sDir )
	{
		if( is_dir( $sDir ) )
		{
			$aObj = preg_grep( '/^([^.])/', scandir( $sDir ) );
			foreach( $aObj as $sObj )
			{
				$sRecDir = str_replace('//', '/', $sDir . '/' . $sObj );
				if( is_dir( $sRecDir ) ){ $this->RemoveDir( $sRecDir ); }
				else
				{
					unlink( $sRecDir );
				}
			}
			rmdir( $sDir );
		}
	}	
	private	function	GeneratePurgeList( $sDir = SITE_DIRECTORY )
	{
		$aTemp = PURGE_WHITELIST;
		array_push( $aTemp, SMART_PAGES_DIR );
		
		$sPurgeWhiteList = SITE_DIRECTORY . '/' . SMART_PAGES_DIR . '/3js/_map.json';
		if( file_exists( $sPurgeWhiteList ) )
		{
			$sList = file_get_contents( $sPurgeWhiteList );
			$aList = json_decode( $sList, true );
			$aTemp = array_merge( $aTemp, $aList );
		}
		
		if( is_dir( $sDir ) )
		{
			$aObj = array_diff( preg_grep( '/^([^.])/', scandir( $sDir ) ), $aTemp  );
			foreach( $aObj as $sObj )
			{
				$sRecDir = $sDir . $sObj . '/';
				if( is_dir( $sRecDir ) )
				{
					$this->GeneratePurgeList( $sRecDir );
				}
				array_push( $this->m_PurgeList, $sRecDir );
			}
		}
	}
	public	function	SetLastUpdated( $sFile )
	{
		$textFile = fopen( $sFile, 'w' );
		$tDate = date( DATE_RFC3339 );
		fwrite( $textFile, $tDate );
		fclose( $textFile );
		return $tDate;
	}
	public	function	GetLastUpdated( $sDir )
	{
		$textFile = $sDir;
		if( file_exists( $textFile ) )
		{
			$sUpdated = file_get_contents( $textFile );
		} else { $sUpdated = '0000-00-00T00:00:00-00:00'; }
		return $sUpdated;
	}
//	Methods	===============================================================	
	public	function	RecursiveConstruct	( &$aContent, $bUpdate = false, $sDir = __DIR__ . 'lastUpdated.txt' )
	{
		foreach( $aContent as $sKey => $aVal )
		{
	//		echo $sKey . '::' . json_encode( $aVal ) . '<br><br>';
	//		echo $sKey . '::' . json_encode( $aVal[0] ) . '<br><br>';
	//		if( array_key_exists( 'kind', $aVal ) ){ echo $sKey . ':: Parent :: ' . $aVal[ 'parents' ] . ' :: ' . $aVal[ 'name' ] . '<br><br>'; }
	
			// Check for document data
			switch( array_key_exists( 'kind', $aVal ) )
			{
				case 0:
					$this->RecursiveConstruct( $aVal, $bUpdate );
				break;
				
				case 1:
					// Check if Content is in root dir
					if( $aVal[ 'parents' ] == null )
					{
						if( $bUpdate )
						{
							if( $this->GetLastUpdated( $sDir ) < $aVal[ 'createdTime' ] )
							{
								$this->ConstructPageData( $aVal, $this->m_NavMenu );
							}
						} else {	$this->ConstructPageData( $aVal, $this->m_NavMenu );	}
					}
					else
					{
						// Check if Directory is set as PAGE/SUBPAGES else set as Blog/Excerpt mode
						$bIsPage	=	false;
						$aParents	=	explode( '/', $aVal[ 'parents' ] );
						for( $i = 0; $i < sizeof( $aParents ); ++$i )
						{
							foreach( PAGE_LIST as $sDirName )
							{
								if( $aParents[ $i ] == strtolower( $sDirName ) )
								{
									$bIsPage = true;
									break;
								}
							}
						}
						
						// Correct directory slashes
						$aVal[ 'parents' ] .= '/';
						// Build Pages based on type
						if( !$bIsPage )
						{
							if( $bUpdate )
							{
								if( $this->GetLastUpdated( $sDir ) < $aVal[ 'createdTime' ] )
								{
									// Build Article
									$this->ConstructArticleData( $aVal, $this->m_NavMenu, $aVal[ 'parents' ] );
								}
							} else {	$this->ConstructArticleData( $aVal, $this->m_NavMenu, $aVal[ 'parents' ] );	}
							
							// Add to ExcerptList to generate Index and Archive
							$sRealPath = '';
							for( $i = 0; $i < sizeof( $aParents ); ++$i )
							{
								$sRealPath .= $aParents[ $i ] . '/';
							//	if( is_null( $this->m_ExcerptList[ $aParents[ $i ] ] ) )
								if( !array_key_exists( $aParents[ $i ], $this->m_ExcerptList ) )
								{
									$this->m_ExcerptList[ $aParents[ $i ] ] = [ 'parents' => $sRealPath ];
								}
								array_push
								(
									 $this->m_ExcerptList[ $aParents[ $i ] ]
									,['parents' => $aVal[ 'parents' ]
									, 'data' => $aVal ]
								);
							//	echo 'Excerpt[ ' . $aParents[ $i ] . ' ] = ' . json_encode( $this->m_ExcerptList[ $aParents[ $i ] ] ) . '<br><br>';
							}
						}
						else if( $bIsPage )
						{
							if( $bUpdate )
							{
								if( $this->GetLastUpdated( $sDir ) < $aVal[ 'createdTime' ] )
								{
									// Build Page
									$this->ConstructPageData( $aVal, $this->m_NavMenu, $aVal[ 'parents' ] );
								}
							} else {	$this->ConstructPageData( $aVal, $this->m_NavMenu, $aVal[ 'parents' ] );	}
						}
						else
						{
							// Error cannot be both PAGE & GALLERY
						}
					}
				break;
				default:break;
			}
		}
	}
	public	function	ConstructExcerptPage( &$aSection, $iWordLimit, $bArchive, $iDisplayLimit = EXCERPT_LIMIT, $bSmart = false )
	{
		if( $bArchive )
		{
			$archHTML = '';
			$sYear = '0000';
		}
		$sHTML = '';
		$sExport = '';
		//	Checks parent paths where 'blog/' is root:
		//	'blog/'				= 2	(root)		["blog",""]
		//	'blog/test/'		= 3 (subdir)	["blog","test",""]
		//	'blog/test/ohno/'	= 4 (subdir)	["blog","test","ohno",""]
		$aPList		= explode( '/', $aSection[ 'parents' ] );
		$isRoot		= sizeof( $aPList ) < 3 ? false : true;
		if( $isRoot )
		{
			$sRSS		= '';
			$sRSSItem	= $this->m_CTemplates->GetRSS_Item();
			$sAtom		= '';
			$sAtomItem	= $this->m_CTemplates->GetAtom_Item();
		}
		
		$iLimiter = 0;
		$aArchiveList = [];
		foreach( $aSection as $sKey => $aPost )
		{
			if( is_numeric( $sKey ) )
			{
			//	echo $sKey . ' :: ' .json_encode( $aPost ) . '<br><br>';
			
			/*	Structure set given
				"0":
				{
					"parents":"blog\/test2\/testi3\/"
					,"data":
					{
						"kind":"drive#file"
						,"id":"1PGXs641WJD31rqHwieVdLk3J7qSbJBSapx1-SAvniMI"
						,"name":"Untitled documentHN OOOOOOOOOOO"
						,"mimeType":"application\/vnd.google-apps.document"
						,"createdTime":"2020-11-04T09:16:37.178Z"
						,"modifiedTime":"2020-11-06T12:42:49.711Z"
						,"parents":"blog\/test2\/testi3\/"
						,"contents":
						{
							"name":"Untitled documentHN OOOOOOOOOOO"
							"data": --DOWNLOADED GOOGLE DOC IN HTML OR TXT--
						}
					}
				}
			*/
				// Set data formats
				$sLowcase	= 	$this->m_CTextFormat->FormatForURL( $aPost[ 'data' ][ 'name' ] );
				$aFormat	=	[ 'title'		=>	$aPost[ 'data' ][ 'name' ]
								, 'urlTitle'	=>	$sLowcase
								, 'dirPath'		=>	$aPost[ 'data' ][ 'parents' ] . $sLowcase
								, 'postTime'	=>	new DateTime( $aPost[ 'data' ][ 'createdTime' ] )
								, 'editTime'	=>	new DateTime( $aPost[ 'data' ][ 'modifiedTime' ] )
								];
				if( $isRoot )
				{
					$sRSS		.= str_replace( '%POSTTITLE%',	$aFormat[ 'title' ],							$sRSSItem );
					$sRSS		 = str_replace( '%POSTURL%',	SITE_URL . $aFormat[ 'dirPath' ],				$sRSS );
					$sRSS		 = str_replace( '%POSTTIME%',	$aFormat[ 'postTime' ]->format( DATE_RFC2822 ),	$sRSS );

					$sAtom		.= str_replace( '%POSTTITLE%',	$aFormat[ 'title' ],							$sAtomItem );
					$sAtom		 = str_replace( '%POSTURL%',	SITE_URL . $aFormat[ 'dirPath' ],				$sAtom );
					$sAtom		 = str_replace( '%POSTTIME%',	$aFormat[ 'postTime' ]->format( DATE_RFC3339 ),	$sAtom );
					$sAtom		 = str_replace( '%LASTUPDATED%',$aFormat[ 'editTime' ]->format( DATE_RFC3339 ),	$sAtom );
				}
				
			//	if( $aPost[ 'data' ][ 'mimeType' ] == 'application/vnd.google-apps.document' )
			//	{
					if( array_key_exists( 'contents', $aPost[ 'data' ] ) )
					{
						$sExport	= $aPost[ 'data' ][ 'contents' ][ 'data' ];
					//	$sExport	= preg_replace( '/(%twitter({|\Z)(.*?)(}|\Z))/', '[ Twitter Quote Avaliable ]', $sExport );
					//	$sExport	= preg_replace( '/(%flickr({|\Z)(.*?)(}|\Z))/', '[ Flickr Image Avaliable ]', $sExport );
					//	$sExport	= preg_replace( '/(%soundcloud({|\Z)(.*?)(}|\Z))/', '[ Soundcloud Avaliable ]', $sExport );
					
						$sExport	= preg_replace( '/(%' . EShort::Code[ 31 ] . '({|\Z)(.*?)(}|\Z))/', '[ OBJ Model Avaliable ]',	$sExport );
						$sExport	= preg_replace( '/(%' . EShort::Code[ 32 ] . '({|\Z)(.*?)(}|\Z))/', '[ OBJ Model Avaliable ]',	$sExport );
						$sExport	= preg_replace( '/(%' . EShort::Code[ 33 ] . '({|\Z)(.*?)(}|\Z))/', '[ FBX Model Avaliable ]',	$sExport );
						$sExport	= preg_replace( '/(%' . EShort::Code[ 34 ] . '({|\Z)(.*?)(}|\Z))/', '[ 3DS Model Avaliable ]',	$sExport );
						$sExport	= preg_replace( '/(%gallery_sec(|\Z)(.*?)(gallery_sec%|\Z))/',		'[ Gallery Avaliable ]',	$sExport );
						$sExport	= preg_replace( '/(%' . EShort::Code[ 35 ] . '({|\Z)(.*?)(}|\Z))/', '',	$sExport );
						$this->m_CPageBuilder->ConstructFormatting( $sExport, false, EPageType::EXCERPT );
					
						$sExport	= preg_replace( '/<table[^>]*>(.*)<\/table>/', '',	$sExport );
						$sExport	= preg_replace( '/<style[^>]*>(.*)<\/style>/', '',	$sExport );
						$sExport	= preg_replace( '/(%meta({|\Z)(.*?)(}|\Z))/', '',	$sExport );
						$sExport	= $this->m_CTextFormat->TruncateTextOnly( $sExport, $iWordLimit );
					}
				//	$this->m_CPageBuilder->ConstructFormatting( $sExport, false, EPageType::EXCERPT );
			//	}
			/*	else
				{
					$sExport	=	$this->m_CPageBuilder->BuildMediaExcerpt
								(	$aFormat[ 'title' ]
								,	$aPost[ 'data' ][ 'id' ]
								,	$aPost[ 'data' ][ 'mimeType' ]
								,	SITE_URL . $aFormat[ 'dirPath' ]
								, 	THUMB_SIZE );
				}
			*/
				if( $iLimiter < $iDisplayLimit )
				{
					$sBody		= str_replace( '%POSTTITLE%',		$aFormat[ 'title' ],							$this->GetTemplate( ETemplate::EXCERPT ) );
					$sBody		= str_replace( '%POSTURL%',			SITE_URL . $aFormat[ 'dirPath' ],				$sBody );
					$sBody		= str_replace( '%POSTTIME%',		$aFormat[ 'postTime' ]->format( 'Y-m-d H:i:s' ),$sBody );
					$sBody		= str_replace( '%POSTTIME_READ%',	$aFormat[ 'postTime' ]->format( 'M d, Y' ),		$sBody );
					$sBody		= str_replace( '%CONTENT%',			$sExport,										$sBody );
					$sHTML		.= $sBody;
					++$iLimiter;
				}
				
				if( $isRoot )
				{
				//	if( $aPost[ 'data' ][ 'mimeType' ] == 'application/vnd.google-apps.document' )
				//	{
						$sExport	= preg_replace( '/<span[^>]*><div[^>]*><span[^>]*>.+<\/span><br\/>.+<\/div><\/span>/', '', $sExport );
						$sExport	= preg_replace( '/<a[^>]*><div[^>]*><span[^>]*>.+<\/span><br\/>.+<\/div><\/a>/', '', $sExport );
						$sExport	= preg_replace( '/ style="[^>]*"/', '', $sExport );
						$sExport	= preg_replace( '/<table[^>]*>.+<\/table>/', '', $sExport );
						$sExport	= htmlspecialchars( html_entity_decode( $sExport, ENT_QUOTES, 'UTF-8' ) );
				//	}
						$sRSS		= str_replace( '%CONTENT%',		$sExport, $sRSS );
						$sAtom		= str_replace( '%CONTENT%',		$sExport, $sAtom );
				}
				
				if( $bArchive )
				{
					if( $sYear != $aFormat[ 'postTime' ]->format( 'Y' ) )
					{
						$sYear			=	$aFormat[ 'postTime' ]->format( 'Y' );
						$aYear			=	[ $sYear => [] ];
						$aArchiveList	=	$aArchiveList + $aYear;
						
						$tempHTML	=	str_replace( '%POSTTIME%',		$aFormat[ 'postTime' ]->format( 'Y-m-d H:i:s' ),$this->GetTemplate( ETemplate::_ARCHIVE_ART_ ) );
						$tempHTML	=	str_replace( '%POSTTIME_READ%',	$aFormat[ 'postTime' ]->format( 'M d' ),		$tempHTML );
						$tempHTML	=	str_replace( '%POSTTITLE%',		$aFormat[ 'title' ],							$tempHTML );
						$tempHTML	=	str_replace( '%POSTURL%',		SITE_URL . $aFormat[ 'dirPath' ],				$tempHTML );
					}
					else
					{
						$tempHTML	=	str_replace( '%POSTTIME%',		$aFormat[ 'postTime' ]->format( 'Y-m-d H:i:s' ),$this->GetTemplate( ETemplate::_ARCHIVE_ART_ ) );
						$tempHTML	=	str_replace( '%POSTTIME_READ%',	$aFormat[ 'postTime' ]->format( 'M d' ),		$tempHTML );
						$tempHTML	=	str_replace( '%POSTTITLE%',		$aFormat[ 'title' ],							$tempHTML );
						$tempHTML	=	str_replace( '%POSTURL%',		SITE_URL . $aFormat[ 'dirPath' ],				$tempHTML );
					}
					$sTemp		= '';
					$tempCat	= '';
					$aCats		= explode( '/', $aPost[ 'data' ][ 'parents' ] );
					for( $i = 1; $i < count( $aCats ) - 1; ++$i )
					{
						if( $aCats[ $i ] != '' )
						{
							if( $aCats[ $i ] != null ){ $sTemp .= $aCats[ $i ] . '/'; }
							$tempCat	.= str_replace( '%DIRNAME%', $aCats[ $i ], $this->GetTemplate( ETemplate::_ARCHIVE_CAT_ ) );
							$tempCat	= str_replace( '%DIRURL%', SITE_URL . $aCats[ 0 ] . '/'  . $sTemp, $tempCat );
						}
					}
					$tempHTML	=	str_replace( '%CATEGORIES%',		$tempCat,	$tempHTML );
					array_push( $aArchiveList[ $sYear ], $tempHTML );
				}
			}
		}
		
		$arcTemp = '';
		foreach( $aArchiveList as $sKey => $aVal )
		{
			$archHTML	.=	str_replace( '%POSTYEAR%', 		$sKey, 		$this->GetTemplate( ETemplate::_ARCHIVE_YR_ ) );
			foreach( $aVal as $sVal )
			{
				$arcTemp .= $sVal;
			}
			$archHTML	=	str_replace( '%ARTICLE_LIST%', 	$arcTemp, 	$archHTML );
		}
		//	'blog/'				= 2	(root)		["blog",""]
		//	'blog/test/'		= 3 (subdir)	["blog","test",""]
		//	'blog/test/ohno/'	= 4 (subdir)	["blog","test","ohno",""]
	//	echo ucfirst( $aPList[ sizeof( $aPList ) - 2 ] ) . '<br><Br>';
		$sPageTitle		= ucfirst( $aPList[ sizeof( $aPList ) - 2 ] );
		if( !$bSmart )
		{
			$sHeader		= $this->GetTemplate( ETemplate::HEADER );
			
			$this->m_CPageBuilder->ConstructMeta( $sHeader, true );
			
			$sHeader	= str_replace( '%NAV%',										$this->m_NavMenu,					$sHeader );
			
			$sHeader	= str_replace( '<?php echo SITE_NAME ?>',					SITE_NAME,							$sHeader );
			$sHeader	= str_replace( '<?php echo SITE_HEADLINE ?>',				SITE_HEADLINE,						$sHeader );
			
			$sHeader	= str_replace( '<?php echo $aData[ \'author\' ] ?>',		SITE_AUTHOR,						$sHeader );
			$sHeader	= str_replace( '<?php echo $aData[ \'description\' ] ?>',	$sPageTitle . ' posts, I think?',	$sHeader );
			$sHeader	= str_replace( '<?php echo SITE_URL ?>',					SITE_URL,							$sHeader );
			$sHeader	= str_replace( '<?php echo $pg_url ?>',						SITE_URL . $aSection[ 'parents' ],	$sHeader );
			$sHeader	= str_replace( '<?php echo $aData[ \'title\' ] ?>',			$sPageTitle,						$sHeader );
			$sHeader	= str_replace( '<?php echo $aData[ \'image\' ] ?>',			META_DEFAULT_IMG,					$sHeader );
			$sHeader	= str_replace( '<?php echo TWITTER_HANDLE ?>',				TWITTER_HANDLE,						$sHeader );
			$sHeader	= str_replace( '<?php echo FAVICON16 ?>',					FAVICON16,							$sHeader );
			$sHeader	= str_replace( '<?php echo FAVICON32 ?>',					FAVICON32,							$sHeader );
			$sHeader	= str_replace( '<?php echo FAVICON48 ?>',					FAVICON48,							$sHeader );
			$sHeader	= str_replace( '<?php echo FAVICON64 ?>',					FAVICON64,							$sHeader );
			$sHeader	= str_replace( '%CSS%',										$this->GetTemplate( ETemplate::CSS ),$sHeader );
			
			if( MASONRY_EXCERPTS ){ $sMasonry = '<script>' . $this->GetDependency( ETemplate::JQUERY ) . $this->GetDependency( ETemplate::FREEWALL ) . $this->GetTemplate( ETemplate::MASONRY ) . '</script>'; }
			
			//	if( $aPost[ 'data' ][ 'mimeType' ] == 'application/vnd.google-apps.document' )
			//	{
					$sHTML		= $sHeader . '<div id="Masonry" class="Excerpt">' . $sHTML . '</div>' . $sMasonry . $this->GetTemplate( ETemplate::FOOTER );
			/*	} 
				else
				{
					$sHTML		= $sHeader . '<div id="Masonry" class="Gallery">' . $sHTML . '</div>' . $sMasonry . $this->GetTemplate( ETemplate::FOOTER );
				} */
		}
		else
		{
			if( MASONRY_EXCERPTS ){ $sMasonry = '<script>' . $this->GetDependency( ETemplate::JQUERY ) . $this->GetDependency( ETemplate::FREEWALL ) . $this->GetTemplate( ETemplate::MASONRY ) . '</script>'; }
			$sHTML = '<?php $pg_url = \'' . SITE_URL . $aSection[ 'parents' ] . '\';  $sHeader = str_replace( \'<?php echo $aData[ \'title\' ] ?>\', '
						. $sPageTitle .
						 ', file_get_contents( \''. SITE_DIRECTORY . SMART_PAGES_DIR . '/header.html\' ) ); echo $sHeader; ?>'
						. $sHTML . $sMasonry .
						 '<?php include \''. SITE_DIRECTORY . SMART_PAGES_DIR . '/footer.html\'; ?>';
		}
		
		$sDirectory = SITE_DIRECTORY . $aSection[ 'parents' ];
		if( !file_exists( $sDirectory ) ){ mkdir( $sDirectory, 0755, true ); }
		if( $bSmart ){ $htmlFile = fopen( $sDirectory . '/index.php', 'w' ); }
		else { $htmlFile = fopen( $sDirectory . '/index.html', 'w' ); }
		fwrite( $htmlFile, $sHTML );
		fclose( $htmlFile );

		if( $bArchive )
		{
		//	$sPageTitle		= ucfirst( $aPList[ sizeof( $aPList ) - 2 ] );
			if( !$bSmart )
			{
				$sHeader	= $this->GetTemplate( ETemplate::HEADER ) . $this->GetTemplate( ETemplate::_ARCHIVE_ ) . $this->GetTemplate( ETemplate::FOOTER );
				$this->m_CPageBuilder->ConstructMeta( $sHeader, true );
				
				$sHeader	= str_replace( '%NAV%',										$this->m_NavMenu,										$sHeader );
				
				$sHeader	= str_replace( '<?php echo SITE_NAME ?>',					SITE_NAME,												$sHeader );
				$sHeader	= str_replace( '<?php echo SITE_HEADLINE ?>',				SITE_HEADLINE,											$sHeader );
				
				$sHeader	= str_replace( '<?php echo $aData[ \'author\' ] ?>',		SITE_AUTHOR,											$sHeader );
				$sHeader	= str_replace( '<?php echo $aData[ \'description\' ] ?>',	$sPageTitle . ' - ' . ARCHIVE_PAGE_NAME,				$sHeader );
				$sHeader	= str_replace( '<?php echo SITE_URL ?>',					SITE_URL,			$sHeader );
				$sHeader	= str_replace( '<?php echo $pg_url ?>',						SITE_URL . $aSection[ 'parents' ] . ARCHIVE_PAGE_NAME,	$sHeader );
				$sHeader	= str_replace( '<?php echo $aData[ \'title\' ] ?>',			$sPageTitle . ' - ' . ARCHIVE_PAGE_NAME,				$sHeader );
				$sHeader	= str_replace( '<?php echo $aData[ \'image\' ] ?>',			META_DEFAULT_IMG,										$sHeader );
				$sHeader	= str_replace( '<?php echo TWITTER_HANDLE?>',				TWITTER_HANDLE,											$sHeader );
				$sHeader	= str_replace( '<?php echo FAVICON16 ?>',					FAVICON16,												$sHeader );
				$sHeader	= str_replace( '<?php echo FAVICON32 ?>',					FAVICON32,												$sHeader );
				$sHeader	= str_replace( '<?php echo FAVICON48 ?>',					FAVICON48,												$sHeader );
				$sHeader	= str_replace( '<?php echo FAVICON64 ?>',					FAVICON64,												$sHeader );
				$sHeader	= str_replace( '%CSS%',										$this->GetTemplate( ETemplate::CSS ),					$sHeader );
				$archHTML	= str_replace( '%ARCHIVES%',								$archHTML,												$sHeader );
			}
			else
			{
				$archHTML	= '<?php $pg_url = \'' . SITE_URL . $aSection[ 'parents' ] . ARCHIVE_PAGE_NAME . '\';$sHeader = str_replace( \'<?php echo $aData[ \'title\' ] ?>\', '
							. $sPageTitle .
							 ', file_get_contents( \''. SITE_DIRECTORY . SMART_PAGES_DIR . '/header.html\' ) ); echo $sHeader; ?>'
							. $archHTML .
							 '<?php include \''. SITE_DIRECTORY . '/' . SMART_PAGES_DIR . '/footer.html\'; ?>';
			}
			
			$sDirectory .= strtolower( ARCHIVE_PAGE_NAME );
			if( !file_exists( $sDirectory ) ){ mkdir( $sDirectory, 0755, true ); }
			if( $bSmart )
			{
				if( file_exists( $sDirectory . '/index.html' ) ){ unlink( $sDirectory . '/index.html' ); }
				$htmlFile	= fopen( $sDirectory . '/index.php', 'w' );
			}
			else
			{
				if( file_exists( $sDirectory . '/index.php' ) ){ unlink( $sDirectory . '/index.php' ); }
				$htmlFile	= fopen( $sDirectory . '/index.html', 'w' );
			}
			fwrite( $htmlFile, $archHTML );
			fclose( $htmlFile );
		}
		
		if( $isRoot )
		{
			$sDirectory = SITE_DIRECTORY . 'feeds';	
			if( !file_exists( $sDirectory ) ){ mkdir( $sDirectory, 0755, true ); }
			
			$htmlFile = fopen( $sDirectory . '/rss.xml', 'w' );
			fwrite( $htmlFile, $this->m_CTemplates->GetRSS_Header() . $sRSS . '</channel></rss>' );
			fclose( $htmlFile );
				
			$htmlFile = fopen( $sDirectory . '/atom.xml', 'w' );
			fwrite( $htmlFile, $this->m_CTemplates->GetAtom_Header() . $sAtom . '</feed>' );
			fclose( $htmlFile );
		}
	}
	public	function	ConstructGalleryPage( &$aSection, $iWordLimit, $bArchive, $iDisplayLimit = EXCERPT_LIMIT, $bSmart = false )
	{
		if( $bArchive )
		{
			$archHTML = '';
			$sYear = '0000';
		}

		$aPList		= explode( '/', $aSection[ 'parents' ] );
		$isRoot		= sizeof( $aPList ) < 3 ? false : true;
		if( $isRoot )
		{
			$sRSS		= '';
			$sRSSItem	= $this->m_CTemplates->GetRSS_Item();
			$sAtom		= '';
			$sAtomItem	= $this->m_CTemplates->GetAtom_Item();
		}
		
		$iLimiter = 0;
		$aArchiveList = [];
		$sHTML = '';
		foreach( $aSection as $sKey => $aPost )
		{
			if( is_numeric( $sKey ) )
			{
				// Set data formats
				$sLowcase	=	$this->m_CTextFormat->FormatForURL( $aPost[ 'data' ][ 'name' ] );
				$aFormat	=	[ 'title'		=>	$aPost[ 'data' ][ 'name' ]
								, 'urlTitle'	=>	$sLowcase
								, 'dirPath'		=>	$aPost[ 'data' ][ 'parents' ] . $sLowcase
								, 'postTime'	=>	new DateTime( $aPost[ 'data' ][ 'createdTime' ] )
								, 'editTime'	=>	new DateTime( $aPost[ 'data' ][ 'modifiedTime' ] )
								];
				if( $isRoot )
				{
					$sRSS		.= str_replace( '%POSTTITLE%',	$aFormat[ 'title' ],							$sRSSItem );
					$sRSS		 = str_replace( '%POSTURL%',	SITE_URL . $aFormat[ 'dirPath' ],				$sRSS );
					$sRSS		 = str_replace( '%POSTTIME%',	$aFormat[ 'postTime' ]->format( DATE_RFC2822 ),	$sRSS );

					$sAtom		.= str_replace( '%POSTTITLE%',	$aFormat[ 'title' ],							$sAtomItem );
					$sAtom		 = str_replace( '%POSTURL%',	SITE_URL . $aFormat[ 'dirPath' ],				$sAtom );
					$sAtom		 = str_replace( '%POSTTIME%',	$aFormat[ 'postTime' ]->format( DATE_RFC3339 ),	$sAtom );
					$sAtom		 = str_replace( '%LASTUPDATED%',$aFormat[ 'editTime' ]->format( DATE_RFC3339 ),	$sAtom );
				}
				
				$sExport	=	$this->m_CPageBuilder->BuildMediaExcerpt
							(	$aFormat[ 'title' ]
							,	$aPost[ 'data' ][ 'id' ]
							,	$aPost[ 'data' ][ 'mimeType' ]
							,	SITE_URL . $aFormat[ 'dirPath' ]
							, 	THUMB_SIZE );
			
				if( $iLimiter < $iDisplayLimit )
				{
					$sBody		= str_replace( '%IMAGE%',			$sExport,										$this->GetTemplate( ETemplate::_GALLERY_ ) );
					$sHTML		.= $sBody;
					++$iLimiter;
				}
				
				if( $isRoot )
				{
						$sRSS		= str_replace( '%CONTENT%',		$sExport, $sRSS );
						$sAtom		= str_replace( '%CONTENT%',		$sExport, $sAtom );
				}
				
				if( $bArchive )
				{
					if( $sYear != $aFormat[ 'postTime' ]->format( 'Y' ) )
					{
						$sYear			=	$aFormat[ 'postTime' ]->format( 'Y' );
						$aYear			=	[ $sYear => [] ];
						$aArchiveList	=	$aArchiveList + $aYear;
						
						$tempHTML	=	str_replace( '%POSTTIME%',		$aFormat[ 'postTime' ]->format( 'Y-m-d H:i:s' ),$this->GetTemplate( ETemplate::_ARCHIVE_ART_ ) );
						$tempHTML	=	str_replace( '%POSTTIME_READ%',	$aFormat[ 'postTime' ]->format( 'M d' ),		$tempHTML );
						$tempHTML	=	str_replace( '%POSTTITLE%',		$aFormat[ 'title' ],							$tempHTML );
						$tempHTML	=	str_replace( '%POSTURL%',		SITE_URL . $aFormat[ 'dirPath' ],				$tempHTML );
					}
					else
					{
						$tempHTML	=	str_replace( '%POSTTIME%',		$aFormat[ 'postTime' ]->format( 'Y-m-d H:i:s' ),$this->GetTemplate( ETemplate::_ARCHIVE_ART_ ) );
						$tempHTML	=	str_replace( '%POSTTIME_READ%',	$aFormat[ 'postTime' ]->format( 'M d' ),		$tempHTML );
						$tempHTML	=	str_replace( '%POSTTITLE%',		$aFormat[ 'title' ],							$tempHTML );
						$tempHTML	=	str_replace( '%POSTURL%',		SITE_URL . $aFormat[ 'dirPath' ],				$tempHTML );
					}
					$sTemp		= '';
					$tempCat	= '';
					$aCats		= explode( '/', $aPost[ 'data' ][ 'parents' ] );
					for( $i = 1; $i < count( $aCats ) - 1; ++$i )
					{
						if( $aCats[ $i ] != '' )
						{
							if( $aCats[ $i ] != null ){ $sTemp .= $aCats[ $i ] . '/'; }
							$tempCat	.= str_replace( '%DIRNAME%', $aCats[ $i ], $this->GetTemplate( ETemplate::_ARCHIVE_CAT_ ) );
							$tempCat	= str_replace( '%DIRURL%', SITE_URL . $aCats[ 0 ] . '/'  . $sTemp, $tempCat );
						}
					}
					$tempHTML	=	str_replace( '%CATEGORIES%',		$tempCat,	$tempHTML );
					array_push( $aArchiveList[ $sYear ], $tempHTML );
				}
			}
		}
		
		$arcTemp = '';
		foreach( $aArchiveList as $sKey => $aVal )
		{
			$archHTML	.=	str_replace( '%POSTYEAR%', 		$sKey, 		$this->GetTemplate( ETemplate::_ARCHIVE_YR_ ) );
			foreach( $aVal as $sVal )
			{
				$arcTemp .= $sVal;
			}
			$archHTML	=	str_replace( '%ARTICLE_LIST%', 	$arcTemp, 	$archHTML );
		}
		$sPageTitle		= ucfirst( $aPList[ sizeof( $aPList ) - 2 ] );
		if( !$bSmart )
		{
			$sHeader		= $this->GetTemplate( ETemplate::HEADER );
			
			$this->m_CPageBuilder->ConstructMeta( $sHeader, true );
			
			$sHeader	= str_replace( '%NAV%',										$this->m_NavMenu,					$sHeader );
			
			$sHeader	= str_replace( '<?php echo SITE_NAME ?>',					SITE_NAME,							$sHeader );
			$sHeader	= str_replace( '<?php echo SITE_HEADLINE ?>',				SITE_HEADLINE,						$sHeader );
			
			$sHeader	= str_replace( '<?php echo $aData[ \'author\' ] ?>',		SITE_AUTHOR,						$sHeader );
			$sHeader	= str_replace( '<?php echo $aData[ \'description\' ] ?>',	$sPageTitle . ' posts, I think?',	$sHeader );
			$sHeader	= str_replace( '<?php echo SITE_URL ?>',					SITE_URL,							$sHeader );
			$sHeader	= str_replace( '<?php echo $pg_url ?>',						SITE_URL . $aSection[ 'parents' ],	$sHeader );
			$sHeader	= str_replace( '<?php echo $aData[ \'title\' ] ?>',			$sPageTitle,						$sHeader );
			$sHeader	= str_replace( '<?php echo $aData[ \'image\' ] ?>',			META_DEFAULT_IMG,					$sHeader );
			$sHeader	= str_replace( '<?php echo TWITTER_HANDLE ?>',				TWITTER_HANDLE,						$sHeader );
			$sHeader	= str_replace( '<?php echo FAVICON16 ?>',					FAVICON16,							$sHeader );
			$sHeader	= str_replace( '<?php echo FAVICON32 ?>',					FAVICON32,							$sHeader );
			$sHeader	= str_replace( '<?php echo FAVICON48 ?>',					FAVICON48,							$sHeader );
			$sHeader	= str_replace( '<?php echo FAVICON64 ?>',					FAVICON64,							$sHeader );
			$sHeader	= str_replace( '%CSS%',										$this->GetTemplate( ETemplate::CSS ),$sHeader );
			
			if( MASONRY_EXCERPTS ){ $sMasonry = '<script>' . $this->GetDependency( ETemplate::JQUERY ) . $this->GetDependency( ETemplate::FREEWALL ) . $this->GetTemplate( ETemplate::MASONRY ) . '</script>' . '<script>' . $this->GetDependency( ETemplate::ME_JS ) . '</script>'; }

			$sHTML		= $sHeader
						. $this->GetDependency( ETemplate::ME_BS )
						. '<style>'. $this->GetDependency( ETemplate::ME_CSS ) . '</style>'
						. '<div id="Masonry" class="Gallery">' . $sHTML . '</div>' . $sMasonry . $this->GetTemplate( ETemplate::FOOTER );
		}
		else
		{
			if( MASONRY_EXCERPTS ){ $sMasonry = '<script>' . $this->GetDependency( ETemplate::JQUERY ) . $this->GetDependency( ETemplate::FREEWALL ) . $this->GetTemplate( ETemplate::MASONRY ) . '</script>'; }
			$sHTML = '<?php $pg_url = \'' . SITE_URL . $aSection[ 'parents' ] . '\';  $sHeader = str_replace( \'<?php echo $aData[ \'title\' ] ?>\', '
						. $sPageTitle .
						 ', file_get_contents( \''. SITE_DIRECTORY . SMART_PAGES_DIR . '/header.html\' ) ); echo $sHeader; ?>'
						. $sHTML . $sMasonry .
						 '<?php include \''. SITE_DIRECTORY . SMART_PAGES_DIR . '/footer.html\'; ?>';
		}
		
		$sDirectory = SITE_DIRECTORY . $aSection[ 'parents' ];
		if( !file_exists( $sDirectory ) ){ mkdir( $sDirectory, 0755, true ); }
		if( $bSmart ){ $htmlFile = fopen( $sDirectory . '/index.php', 'w' ); }
		else { $htmlFile = fopen( $sDirectory . '/index.html', 'w' ); }
		fwrite( $htmlFile, $sHTML );
		fclose( $htmlFile );

		if( $bArchive )
		{
		//	$sPageTitle		= ucfirst( $aPList[ sizeof( $aPList ) - 2 ] );
			if( !$bSmart )
			{
				$sHeader	= $this->GetTemplate( ETemplate::HEADER ) . $this->GetTemplate( ETemplate::_ARCHIVE_ ) . $this->GetTemplate( ETemplate::FOOTER );
				$this->m_CPageBuilder->ConstructMeta( $sHeader, true );
				
				$sHeader	= str_replace( '%NAV%',										$this->m_NavMenu,										$sHeader );
				
				$sHeader	= str_replace( '<?php echo SITE_NAME ?>',					SITE_NAME,												$sHeader );
				$sHeader	= str_replace( '<?php echo SITE_HEADLINE ?>',				SITE_HEADLINE,											$sHeader );
				
				$sHeader	= str_replace( '<?php echo $aData[ \'author\' ] ?>',		SITE_AUTHOR,											$sHeader );
				$sHeader	= str_replace( '<?php echo $aData[ \'description\' ] ?>',	$sPageTitle . ' - ' . ARCHIVE_PAGE_NAME,				$sHeader );
				$sHeader	= str_replace( '<?php echo SITE_URL ?>',					SITE_URL,			$sHeader );
				$sHeader	= str_replace( '<?php echo $pg_url ?>',						SITE_URL . $aSection[ 'parents' ] . ARCHIVE_PAGE_NAME,	$sHeader );
				$sHeader	= str_replace( '<?php echo $aData[ \'title\' ] ?>',			$sPageTitle . ' - ' . ARCHIVE_PAGE_NAME,				$sHeader );
				$sHeader	= str_replace( '<?php echo $aData[ \'image\' ] ?>',			META_DEFAULT_IMG,										$sHeader );
				$sHeader	= str_replace( '<?php echo TWITTER_HANDLE?>',				TWITTER_HANDLE,											$sHeader );
				$sHeader	= str_replace( '<?php echo FAVICON16 ?>',					FAVICON16,												$sHeader );
				$sHeader	= str_replace( '<?php echo FAVICON32 ?>',					FAVICON32,												$sHeader );
				$sHeader	= str_replace( '<?php echo FAVICON48 ?>',					FAVICON48,												$sHeader );
				$sHeader	= str_replace( '<?php echo FAVICON64 ?>',					FAVICON64,												$sHeader );
				$sHeader	= str_replace( '%CSS%',										$this->GetTemplate( ETemplate::CSS ),					$sHeader );
				$archHTML	= str_replace( '%ARCHIVES%',								$archHTML,												$sHeader );
			}
			else
			{
				$archHTML	= '<?php $pg_url = \'' . SITE_URL . $aSection[ 'parents' ] . ARCHIVE_PAGE_NAME . '\';$sHeader = str_replace( \'<?php echo $aData[ \'title\' ] ?>\', '
							. $sPageTitle .
							 ', file_get_contents( \''. SITE_DIRECTORY . SMART_PAGES_DIR . '/header.html\' ) ); echo $sHeader; ?>'
							. $archHTML .
							 '<?php include \''. SITE_DIRECTORY . '/' . SMART_PAGES_DIR . '/footer.html\'; ?>';
			}
			
			$sDirectory .= strtolower( ARCHIVE_PAGE_NAME );
			if( !file_exists( $sDirectory ) ){ mkdir( $sDirectory, 0755, true ); }
			if( $bSmart )
			{
				if( file_exists( $sDirectory . '/index.html' ) ){ unlink( $sDirectory . '/index.html' ); }
				$htmlFile	= fopen( $sDirectory . '/index.php', 'w' );
			}
			else
			{
				if( file_exists( $sDirectory . '/index.php' ) ){ unlink( $sDirectory . '/index.php' ); }
				$htmlFile	= fopen( $sDirectory . '/index.html', 'w' );
			}
			fwrite( $htmlFile, $archHTML );
			fclose( $htmlFile );
		}
		
		if( $isRoot )
		{
			$sDirectory = SITE_DIRECTORY . 'feeds';	
			if( !file_exists( $sDirectory ) ){ mkdir( $sDirectory, 0755, true ); }
			
			$htmlFile = fopen( $sDirectory . '/g_rss.xml', 'w' );
			fwrite( $htmlFile, $this->m_CTemplates->GetRSS_Header() . $sRSS . '</channel></rss>' );
			fclose( $htmlFile );
				
			$htmlFile = fopen( $sDirectory . '/g_atom.xml', 'w' );
			fwrite( $htmlFile, $this->m_CTemplates->GetAtom_Header() . $sAtom . '</feed>' );
			fclose( $htmlFile );
		}
	}
	public	function	ConstructExcerpts()
	{
		// Loop through Excerpt list for each section and categories
		foreach( $this->m_ExcerptList as $sKey => $aSection )
		{
			$bIsGallery	= false;
			foreach( GALLERY_LIST as $sDirName )
			{
				if( strtolower( $sKey ) == strtolower( $sDirName ) )
				{
					$bIsGallery = true;
					break;
				}
			}
			
			if( !$bIsGallery )
			{
				// Construct each section's excerpts and archives
				$this->ConstructExcerptPage( $aSection, EXCERPT_LENGTH, GENERATE_ARCHIVES, EXCERPT_LIMIT );
			}
			else
			{
				$this->ConstructGalleryPage( $aSection, EXCERPT_LENGTH, GENERATE_ARCHIVES, EXCERPT_LIMIT );
			}
		}
	}
	public	function	PurgeDeletedPosts()
	{
		$this->GeneratePurgeList();
		foreach( $this->m_PurgeList as $sFile )
		{
			$bMatched = false;
			foreach( $this->m_MasterList as $gFile )
			{
				if( $sFile == $gFile )
				{
					$bMatched = true;
					break;
				}
			}
			if( !$bMatched )
			{
				$this->RemoveDir( $sFile );
			}
		}
	}
	public	function	ConstructWebsite	( $sFileID, $iPageSize = '1000', $iThumbSize )
	{
		$aContent		=	[];
		$aParam			=	[ 'folder','createdTime+desc','modifiedTime+desc' ];
		$this->SetupNavigationKey();
		$this->RecursiveDepthTree( $sFileID, $aContent, '', $iPageSize, '', $aParam );
	//	echo json_encode( $aContent );
		$this->m_CPageBuilder->ConstructNavigation( $this->m_NavMenu, $this->m_NavList );
	//	echo '<br>';
	//	echo $this->m_NavMenu;
		$this->m_CPageBuilder->ConstructBread( SITE_DIRECTORY . SMART_PAGES_DIR . '/', $this->m_NavMenu );
		$this->RecursiveConstruct( $aContent );
	//	echo json_encode( $this->m_ExcerptList ) . '<br><br>';
		$this->ConstructExcerpts();
		$this->PurgeDeletedPosts();
	}
}
?>