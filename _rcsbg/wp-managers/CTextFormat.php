<?php
if( !defined( '__Permitted__' ) ){ header( 'Location: ' . filter_var( 'https://error.chippalrus.ca/404.html', FILTER_SANITIZE_URL ) ); exit; }
require_once dirname(__DIR__) . '/wp-includes/Parsedown.php';
require_once dirname(__DIR__) . '/wp-includes/ParsedownExtra.php';
require_once dirname(__DIR__) . '/wp-includes/ParsedownCheckbox.php';
require_once dirname(__DIR__) . '/wp-includes/gw2api/includes/CRender.php';
require_once __DIR__ . '/CTemplates.php';
require_once __DIR__ . '/EShort.php';
require_once __DIR__ . '/EPageTye.php';
define( 'DISABLE_MARKDOWN',		'mrk_off'		);
define( 'DISABLE_RICHTEXT',		'rtxt_off'		);
define( 'DYNAMIC_DOC',			'live_doc'		);
define( 'STATIC_DOC',			'static_doc'	);
define( 'NO_TITLE',				'no_title'		);
define( 'GALLERY_SECTION',		'gallery_sec'	);
/*	CTextFormat	===============================================================
=============================================================================== */
class CTextFormat extends ParsedownExtra
{
//	Members	===============================================================
	private		$m_CGW2Render;
	private		$m_CTemplates;
//	Constructor	===========================================================
	public	function __construct(){ parent::__construct(); $this->m_CGW2Render = new CRender(); $this->m_CTemplates		= new CTemplates(); }
//	Helper	===================================================================
	protected	function GetTemplate( $eTemplate )
	{
		return $this->m_CTemplates->GetTemplate( SITE_THEME, $eTemplate );
	}
	public	function StripTags( $sContent, $sExclude = '' ){ return strip_tags( $sContent, $sExclude ); }
	public	function GetStringBetween( $sStart, $sContent, $sEnd )
	{
		$sContent = ' ' . $sContent;
		$iPos = strpos( $sContent, $sStart );
		if( $iPos == 0 ){ $sContent = ''; }
		$iPos += strlen( $sStart );
		$iLength = strpos( $sContent, $sEnd, $iPos ) - $iPos;
		$sContent = substr( $sContent, $iPos, $iLength );
		return $sContent;
	}
	public function GetSheetData( $sFileID, $gID, $sFormat = 'tsv' )
	{	// CSS #main{float:left;width:auto} is causing Tables to not expand fully for calanders
		if( $sFileID != '' && $gID != '' )
		{
			$gID = str_replace( '/',	'', $gID );
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
//	Methods	===============================================================
	public	function UrlFixerUper( $sURL )
	{
		$sURL = str_replace( '/', '\/', $sURL );
		return $sURL;
	}
	public	function TruncateTextOnly( $sHTML, $iLength = 1024, $bUTF8 = true )
	{
		$printedLength  = 0;			$iPosition = 0;			$aTags = [];
		$sResult		= '';
		// For UTF-8, we need to count multibyte sequences as one character.
		$re = $bUTF8	? '{<style[^>]*>(.*)<\/style>|</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}' : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';
		while( $printedLength < $iLength && preg_match( $re, $sHTML, $match, PREG_OFFSET_CAPTURE, $iPosition ) )
		{
			list( $tag, $tagPosition ) = $match[ 0 ];
			// Text leading up to the tag.
			$str = substr( $sHTML, $iPosition, $tagPosition - $iPosition );
			if( $printedLength + strlen( $str ) > $iLength )
			{
				$sResult .= substr( $str, 0, $iLength - $printedLength );
				$printedLength = $iLength;
				break;
			}
			$sResult .= $str;
			$printedLength += strlen( $str );
			if( $printedLength >= $iLength ){ break; }
			if( $tag[ 0 ] == '&' || ord( $tag ) >= 0x80 )
			{
				// Pass the entity or UTF-8 multibyte sequence through unchanged.
				$sResult .= $tag;
				$printedLength++;
			}
			else
			{
				// Handle the tag.
				$tagName = $match[ 1 ][ 0 ];
				if( $tag[ 1 ] == '/' )
				{
					// This is a closing tag.
					$openingTag = array_pop( $aTags );
					assert( $openingTag == $tagName ); // check that tags are properly nested.
					$sResult .= $tag;
				}
				else if( $tag[ strlen( $tag ) - 2 ] == '/' )
				{
					// Self-closing tag.
					$sResult .= $tag;
				}
				else
				{
					// Opening tag.
					$sResult .= $tag;
					$aTags[] = $tagName;
				}
			}

			// Continue after the tag.
			$iPosition = $tagPosition + strlen( $tag );
		}

		// Any remaining text.
		if( $printedLength < $iLength && $iPosition < strlen( $sHTML ) ){	$sResult .= substr( $sHTML, $iPosition, $iLength - $printedLength );	}
		// Close any open tags.
		while( !empty( $aTags ) )
		{
			$sResult .= '</' . array_pop( $aTags ) . '>';
		}
		return $sResult;
	}
	public	function Decode_JSON( $sJSON )
	{
		$sJSON = $this->StripTags( $sJSON );
		// Unicode quotes to ASCII
		$sJSON = preg_replace( '/“|”|"|&quot|&rdquo|&ldquo/', '"', $sJSON );
		// Preg adds ; to end of replacement no clue why, remove them.
		$sJSON = preg_replace( '/;/', '', $sJSON );
		// Fixes unquoted keys
		$sJSON = preg_replace( '/(\w+):"/i', '"\1":"', $sJSON );
		// Fixes missing commas
		$sJSON = preg_replace( '/(\w+)"\s/i', '\1", ', $sJSON );
		return json_decode( $sJSON );
	}
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
				$this->ConstructHAML( $sTxt, true, false, EPageType::PAGE );
				if( $i == 0 ){ $sHTML .= '<th><div>' . $sTxt . '</div></th>'; }
				else { $sHTML .= '<td>' . $sTxt . '</td>'; }
			}
			if( $i == 0 ){ $sHTML .= '</tr></thead>'; } else { $sHTML .= '</tr>'; }
		}
		$sHTML .= '</tbody></table>';
		return $sHTML;
	}
	protected	function	GetOEmbedContet( $sURL, $sParam = 'html' )
	{
		$sHeader = get_headers( $sURL )[ 0 ];
		if( substr_count( $sHeader, '404' ) < 1 && substr_count( $sHeader, '502' ) < 1 )
		{
			$jOembed	= json_decode( file_get_contents( $sURL ) );
			$sHTML		= $jOembed->{ $sParam };
		} else
		{
			$sHTML	= $sHeader;
		}
		return $sHTML;
	}
	protected	function	GetOEmbedContets( $sURL )
	{
		$sHeader = get_headers( $sURL )[ 0 ];
		if( substr_count( $sHeader, '404' ) < 1 && substr_count( $sHeader, '502' ) < 1 )
		{
			$jOembed	= json_decode( file_get_contents( $sURL ) );

		}
		else
		{
			$jOembed	= 'Error';
		}
		return $jOembed;
	}
	public	function ConstructHAML( &$sContent, $mrkMode = false, $bGallery, $eType )
	{
		// Construct Blockquotes
		if( substr_count( $sContent, '%blqt' ) > 0 && substr_count( $sContent, '%blqt' ) == substr_count( $sContent, 'blqt%' ) )
		{
			$sContent = str_replace( '%blqt', '<blockquote>', $sContent );
			$sContent = str_replace( 'blqt%', '</blockquote>', $sContent );
		}
		
		// Grouped columns
		$iGrps = substr_count( $sContent, '%grp' );
		if( $iGrps > 0 && $iGrps == substr_count( $sContent, 'grp%' ) )
		{
			for( $i = 0; $i < $iGrps; ++$i )
			{
				$iCols = substr_count( $sContent, '%col' );
				if( $iCols > 0 )
				{
					for( $j = 0; $j < $iCols; ++$j )
					{
						$sContent = preg_replace( '/%col/', '<div class="col'. strval( $j + 1 ) .'">', $sContent, 1 );
						$sContent = preg_replace( '/col%/', '</div>', $sContent, 1 );
					}
					
					$sContent = preg_replace( '/%grp/', '<div class="fp'. $iCols .'">', $sContent, 1 );
					$sContent = preg_replace( '/grp%/', '</div>', $sContent, 1 );
				}
			}
		}
		$sTemp = '';
		$sHTML = '';
		for( $i = 0; $i < sizeof( EShort::Code ); ++$i )
		{
			$sShort = '/\%' . EShort::Code[ $i ] . '+[^}]*}/';
			$iLength = preg_match_all( $sShort, $sContent, $aMatched );
			if( $iLength > 0 )
			{
				for( $j = 0; $j < $iLength; ++$j )
				{
					$aHAML = [];
					// Get Params and Fix/Decode JSON
					preg_match( '/{(.*)}/s', $aMatched[ 0 ][ $j ], $aHAML[ 'param' ], PREG_OFFSET_CAPTURE );
					$aHAML[ 'param' ] = $this->Decode_JSON( $aHAML[ 'param' ][ 0 ][ 0 ] );
					
					switch( EShort::Code[ $i ] )
					{
						case 'image':
							if( preg_match_all( '/(%' . GALLERY_SECTION . ')[^|]*((' . $this->UrlFixerUper( $aHAML[ 'param' ]->{ 'src' } ) . '))/', $sContent ) > 0 )
							{
								$sTemp	.= str_replace( '%IMAGE%'
										,'<div class="image-i"><a href="#image_' . strval( $j ) . '"><img src="' . $aHAML[ 'param' ]->{ 'src' } . '"></a></div>
										  <a href="#close"><div id="image_' . strval( $j ) .  '" class="image-w"><img src="' . $aHAML[ 'param' ]->{ 'src' } . '"></div></a>'
										,$this->GetTemplate( ETemplate::_GALLERY_ ) );
								$sHTML = '';
							}
							else
							{
								if( !isset( $aHAML[ 'param' ]->{ 'idx' } ) )	
								{
									$sHTML = '<div class="image-i"><a href="#image_' . strval( $j ) . '"><img src="' . $aHAML[ 'param' ]->{ 'src' } . '"></a></div>
											  <a href="#close"><div id="image_' . strval( $j ) .  '" class="image-w">
											  <img src="' . $aHAML[ 'param' ]->{ 'src' } . '"></div></a>';
								}
								else
								{
									$sHTML = '<div class="image-i"><a href="#image_' . $aHAML[ 'param' ]->{ 'idx' } . '"><img src="' . $aHAML[ 'param' ]->{ 'src' } . '"></a></div>
										  <a href="#close"><div id="image_' . $aHAML[ 'param' ]->{ 'idx' } .  '" class="image-w">
										  <img src="' . $aHAML[ 'param' ]->{ 'src' } . '"></div></a>';
								}
							}
						break;
						
						case 'list':
							if( !isset( $aHAML[ 'param' ]->{ 'link' } ) )	
							{
								$aHAML[ 'param' ]->{ 'link' } = 'enabled';
							}
							switch( $aHAML[ 'param' ]->{ 'link' } )
							{
								case 'disabled':
									$sHTML = '<div id="port-list">
											  <time class="post-time" style="padding:0 63px 0 13px;">' . $aHAML[ 'param' ]->{ 'date' } . '</time>
											  <span class="port-link">' . $aHAML[ 'param' ]->{ 'title' } . '</span>
											  <span id="port-desc">' . $aHAML[ 'param' ]->{ 'desc' } . '</span></div>';
								break;
								case 'blog':
									$sHTML = '<div class="blog-list">
										      <span id="blog-point">' . $aHAML[ 'param' ]->{ 'date' } . '</span>
											  <a class="blog-link" href="' . $aHAML[ 'param' ]->{ 'url' } . '">' . $aHAML[ 'param' ]->{ 'title' } . '</a>
											  <span id="blog-desc">' . $aHAML[ 'param' ]->{ 'desc' } . '</span></div>';
								break;
								default:
									$sHTML = '<div id="port-list">
											  <time class="post-time" style="padding:0 63px 0 13px;">' . $aHAML[ 'param' ]->{ 'date' } . '</time>
											  <a class="port-link" href="' . $aHAML[ 'param' ]->{ 'url' } . '">' . $aHAML[ 'param' ]->{ 'title' } . '</a>
											  <span id="port-desc">' . $aHAML[ 'param' ]->{ 'desc' } . '</span></div>';
								break;
							}
						break;
						
						case 'sheet':
							$atts = [ 'id' => $aHAML[ 'param' ]->{ 'id' }, 'gid' => $aHAML[ 'param' ]->{ 'gid' } ];
							$sHTML = $this->ConstructSheet( $atts );
						break;
						
						case 'audio':
							$sHTML = '<audio controls><source src="' . $aHAML[ 'param' ]->{ 'src' } . '"></audio>';
						break;
						
						case 'video':
							$sHTML = '<video controls><source src="' . $aHAML[ 'param' ]->{ 'src' } . '"></video>';
						break;
						
						case 'vimeo':
							$sHTML		= $this->GetOEmbedContet( 'https://vimeo.com/api/oembed.json?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'youtube':	//http://img.youtube.com/vi/<YouTube_Video_ID_HERE>/maxresdefault.jpg
							$sHTML		= '<div class="oembed"><div class="oembed-yt">'
										. $this->GetOEmbedContet( 'https://www.youtube.com/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } )
										. '</div></div>';
						break;
						
						case 'twitch':
							$sHTML		= 'Endpoint deprecated';//$this->GetOEmbedContet( 'https://api.twitch.tv/v5/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'twitter':
							$sHTML		= $this->GetOEmbedContet( 'https://publish.twitter.com/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } . '&align=center&chrome=nofooter' );
						break;
						
						case 'instagram':
							/*'<div class="image-i">
									<a href="#image_' . strval( $j ) . '">
										<img src="' . $aHAML[ 'param' ]->{ 'src' } . '">
									</a>
							</div>
								<a href="#close">
									<div id="image_' . strval( $j ) .  '" class="image-w">
										  <img src="' . $aHAML[ 'param' ]->{ 'src' } . '">
									</div>
							</a>';*/
						
							$jObject	= $this->GetOEmbedContets( 'https://api.instagram.com/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } );
							$sHTML		= '<div class="oembed"><div class="image-i">'
										. '<a href="#image_'	. $jObject->{ 'media_id' }	. '">'
										. '<img style="width:auto;height:auto" src="'	. $jObject->{ 'thumbnail_url' }	. '"></a></div>'
										. 'Instagram: <a href="'	. $jObject->{ 'author_url' }	. '">@'. $jObject->{ 'author_name' }	. '</a></div>'
										. '<a href="#close" class="image-c"><div id="image_'	. $jObject->{ 'media_id' }	.  '" class="image-inst"><div class="image-cl"> Close Image </div>'
										. $jObject->{ 'html' }
										. '</div></a>';
						break;
						
						case 'facebook':
							$sHTML		= $this->GetOEmbedContet( 'https://www.facebook.com/plugins/post.php?href=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'reddit':
							$sHTML		= $this->GetOEmbedContet( 'https://www.reddit.com/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'soundcloud':
							$sHTML		= '<div class="oembed"><div class="oembed-sc">'
										. $this->GetOEmbedContet( 'https://soundcloud.com/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } . '&format=json&maxwidth=100%&show_artwork=false&show_comments=false&visual=false&hide_related=true&buying=false&sharing=false&download=false' )
										. '</div></div>';
						break;
						
						case 'spotify':
							$sHTML		= $this->GetOEmbedContet( 'https://embed.spotify.com/oembed/?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'flickr':
							$sHTML		= '<div class="oembed">'
										. $this->GetOEmbedContet( 'https://www.flickr.com/services/oembed/?format=json&url=' . $aHAML[ 'param' ]->{ 'src' } )
										. '</div>';
						break;
						
						case 'deviantart':
							$sHTML		= $this->GetOEmbedContet( 'http://backend.deviantart.com/oembed?format=json&url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'wordpress':
							$sHTML		= $this->GetOEmbedContet( 'http://public-api.wordpress.com/oembed/1.0/?format=json&url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'meetup':
							$sHTML		= $this->GetOEmbedContet( 'https://api.meetup.com/oembed?format=json&url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'kickstarter':
							$sHTML		= $this->GetOEmbedContet( 'http://www.kickstarter.com/services/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'vlive':
							$sHTML		= $this->GetOEmbedContet( 'https://www.vlive.tv/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'streamable':
							$sHTML		= $this->GetOEmbedContet( 'https://api.streamable.com/oembed.json?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'codepen':
							$sHTML		= $this->GetOEmbedContet( 'http://codepen.io/api/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'ted':
							$sHTML		= $this->GetOEmbedContet( 'https://www.ted.com/services/v1/oembed.json?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'gfycat':
							$sHTML		= $this->GetOEmbedContet( 'https://api.gfycat.com/v1/oembed?url=' . $aHAML[ 'param' ]->{ 'src' } );
						break;
						
						case 'obj':
							$sHTML		= '<div class="oembed-threejs"><iframe class="obj" src="./_3js/obj_' . strval( $j ) . '.html"></iframe></div>';
						break;
						
						case 'obj_mtl':
							$sHTML		= '<div class="oembed-threejs"><iframe class="obj_mtl" src="./_3js/obj_mtl_' . strval( $j ) . '.html"></iframe></div>';
						break;
						
						case 'fbx':
							$sHTML		= '<div class="oembed-threejs"><iframe class="fbx" src="./_3js/fbx_' . strval( $j ) . '.html"></iframe></div>';
						break;
						
						case '3ds':
							$sHTML		= '<div class="oembed-threejs"><iframe class="3ds" src="./_3js/3ds_' . strval( $j ) . '.html"></iframe></div>';
						break;
						
						case '360x360':
							$sHTML		= '<div class="oembed-360x360"><iframe class="360x360" src="' . $aHAML[ 'param' ]->{ 'url' } . '"></iframe></div>';
						break;
						
						case 'GW2':
							$atts = [ 'apikey' => $aHAML[ 'param' ]->{ 'apikey' }, 'character' => $aHAML[ 'param' ]->{ 'character' }, 'mode' => $aHAML[ 'param' ]->{ 'mode' }, 'charimg' => $aHAML[ 'param' ]->{ 'charimg' } ];
							if( $this->m_CGW2Render != null ){ $sHTML = $this->m_CGW2Render->ConstructGW2Data( $atts ); } else { echo '$this->m_CGW2Render is null.'; }
							$sHTML .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
							$sHTML .= '<script>' . file_get_contents( dirname(__DIR__) . '/wp-includes/gw2api/includes/riflewarrior.js' ) . '</script>';
							$sHTML  = str_replace( '%ATTRIURL%',	SITE_URL . '도/img/chara/gw2/',		$sHTML );
						break;
						
						default:
							preg_match( '/#[a-z|A-Z|0-9|!@#$&()\\-\_`\+,\"]+(?![^{]*})/', $aMatched[ 0 ][ $j ], $aHAML[ 'id' ], PREG_OFFSET_CAPTURE );
							preg_match( '/\.[a-z|A-Z|0-9|!@#$&()\\-\_`\.+,\"]+(?![^{]*})/', $aMatched[ 0 ][ $j ], $aHAML[ 'class' ], PREG_OFFSET_CAPTURE );
							// Remove the notations
							
							if( !empty( $aHAML[ 'id' ][ 0 ][ 0 ] ) ) 
							{
								$aHAML[ 'id' ] = preg_replace( '/#/', '', $aHAML[ 'id' ][ 0 ][ 0 ] );
							}
								$aHAML[ 'class' ] = preg_replace( '/\./', '', $aHAML[ 'class' ][ 0 ][ 0 ] );
								if( EShort::Code[ $i ] == 'btn' )
								{
									if( !isset( $aHAML[ 'class' ] ) ){ $aHAML[ 'class' ] = 'btn'; }
									$sHTML = '<a class="' . $aHAML[ 'class' ] . '"';
								}
								else
								{
									if( !isset( $aHAML[ 'class' ] ) ){ $sHTML = '<' . EShort::Code[ $i ]; }
									else { $sHTML = '<' . EShort::Code[ $i ] . ' class="' . $aHAML[ 'class' ] . '"'; }
								}	
								if( !empty( $aHAML[ 'id' ] ) ){ $sHTML .= ' id="' . $aHAML[ 'id' ] . '"'; }			
								if( isset( $aHAML[ 'param' ]->{ 'url' } ) && ( EShort::Code[ $i ] == 'a' || EShort::Code[ $i ] == 'btn' ) )
								{ $sHTML .= ' href="' . $aHAML[ 'param' ]->{ 'url' } . '"'; }
								$sHTML .= '>';
								if( isset( $aHAML[ 'param' ]->{ 'img' } ) ){ $sHTML .= '<img src="' . $aHAML[ 'param' ]->{ 'img' } . '">'; }	
								$sHTML .= '<div><span>' . $aHAML[ 'param' ]->{ 'title' } . '</span>';
								if( isset( $aHAML[ 'param' ]->{ 'desc' } ) ){ $sHTML .= '<br/>' . $aHAML[ 'param' ]->{ 'desc' }; }
								$sHTML .= '</div>';

								if( EShort::Code[ $i ] == 'btn' ){ $sHTML .= '</a>'; }
								else { $sHTML .= '</' . EShort::Code[ $i ] . '>'; }
						break;
					}

					if( !$mrkMode )
					{
						if( $bGallery && EShort::Code[ $i ] == 'image' ){ $sContent = preg_replace( '/<span[^>]*>(\%'. EShort::Code[ $i ] .'(|\Z)(.*?)(|\Z))<\/span>/', $sHTML, $sContent, 1 ); }
						else{ $sContent = preg_replace( '/<p[^>]*><span[^>]*>(\%'. EShort::Code[ $i ] .'(|\Z)(.*?)(|\Z))<\/span><\/p>/', $sHTML, $sContent, 1 ); }
					}
					else{	$sContent = preg_replace( $sShort, $sHTML, $sContent, 1 );	}
				}
			}
		}
		// Gallery Section
		$sGallery = '';
		if( $bGallery && $eType == EPageType::PAGE )
		{
			$sGallery = '<div id="Masonry" class="Gallery">' . $sTemp . '</div>';
			$this->CleanUp( '/(%' . GALLERY_SECTION . '(|\Z)(.*?)(' . GALLERY_SECTION . '%|\Z))/', $sContent );
		}
		else if( $bGallery && $eType == EPageType::ARTICLE )
		{
			$sGallery = '<div id="Masonry" class="Article">' . $sTemp . '</div>';
			$this->CleanUp( '/(%' . GALLERY_SECTION . '(|\Z)(.*?)(' . GALLERY_SECTION . '%|\Z))/', $sContent );
		}
		$sContent = str_replace( '%%:', '%', $sContent );
		return $sGallery;
	}
	public	function CleanUp( $sMatch, &$sContent )
	{
		if( preg_match_all( $sMatch, $sContent ) > 0 )
		{
			$sContent = preg_replace( $sMatch, '', $sContent );
		}
	}
	public	function Replace( $sMatch, &$sContent, $sReplaceW )
	{
		if( preg_match_all( $sMatch, $sContent ) > 0 )
		{
			$sContent = preg_replace( $sMatch, $sReplaceW, $sContent );
		}
	}
	public	function FormatForURL( $sName )
	{
		$sName	=	strtolower( pathinfo( $sName, PATHINFO_FILENAME ) );
		$sName	=	str_replace('.', '-', $sName );
		$sName	=	str_replace(' ', '-', $sName );
		$sName	=	str_replace('\'', '_', $sName );
		$sName	=	str_replace('---', '-', $sName );
		$sName	=	str_replace('--', '-', $sName );
		return $sName;
	}
	public	function FixQuotaions( &$sContent )
	{
		$sContent = preg_replace( '/“/', '"', $sContent );
		$sContent = preg_replace( '/”/', '"', $sContent );
	}
	public	function ConvertDocumentImageTags( &$sContent )
	{
		if( substr_count( $sContent, '<img alt=' ) ? true : false )
		{
			$sContent = preg_replace( '/<img alt="" src="/', '%image{"src":"', $sContent );
			$sContent = preg_replace( '/\s\stitle="">/', '}', $sContent );
		}
	}
	
	public	function MarkdownCleanup( &$sContent )
	{
		// Remove Exported Style Sheet
		$sContent = preg_replace( '/<style[^>]*>(.*)<\/style>/', '', $sContent );
		// Remove Inline Styles
		$this->CleanUp( '/(style=("|\Z)(.*?)("|\Z))/', $sContent );
		// Check for Google Doc Images
		if( substr_count( $sContent, '<img alt=' ) ? true : false )
		{
			$sContent = preg_replace( '/<img alt="" src="/', '%image{"src":"', $sContent );
			$sContent = preg_replace( '/\s\stitle="">/', '}', $sContent );
		}
		// Fix Quotations
		$this->FixQuotaions( $sContent );
		$sContent = preg_replace( '/%' . STATIC_DOC . '/', '', $sContent );
		$sContent = preg_replace( '/%' . DYNAMIC_DOC . '/', '', $sContent );
		$sContent = preg_replace( '/%' . NO_TITLE . '/', '', $sContent );
		// mb_detect_encoding( $sContent, 'ASCII,UTF-8,ISO-8859-15', true );
		// ???? Detects as UTF-8 but requires utf8_encode to fix &#65279 ( ï»¿ )
		$sContent = preg_replace( '/ï»¿/', '', utf8_encode( $sContent ) );
		$sContent = ParsedownExtra::instance()->setBreaksEnabled( true )->setUrlsLinked( false )->text( $sContent );
	}
	
	public	function RichTextCleanUp( &$sContent )
	{
		//Clean up Exported Tags
		$sContent = strip_tags( $sContent, KEEP_TAGS );
		// Remove exported Style sheet ( but keep Document Styles )
		$this->CleanUp( '/.title\{[^}]*\}|.subtitle\{[^}]*\}|li\{[^}]*\}|h1\{[^}]*\}h2\{[^}]*\}h3\{[^}]*\}h4\{[^}]*\}h5\{[^}]*\}h6\{[^}]*\}/', $sContent );
		// Remove Inline Styles
		$this->CleanUp( '/(style=("|\Z)(.*?)("|\Z))/', $sContent );
		// Check for Google Doc Images
		$this->ConvertDocumentImageTags( $sContent );
		str_replace( '%' . DYNAMIC_DOC, '', $sContent );
		str_replace( '%' . STATIC_DOC, '', $sContent );
		$sContent = preg_replace( '/%' . STATIC_DOC . '/', '', $sContent );
		$sContent = preg_replace( '/%' . DYNAMIC_DOC . '/', '', $sContent );
		$sContent = preg_replace( '/%' . NO_TITLE . '/', '', $sContent );
	}
}
?>