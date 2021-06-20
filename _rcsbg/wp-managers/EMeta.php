<?php
/*	META ENUM	===========================================================*/
abstract class EMeta
{
	//%METAHEAD%
	const	HTTP_EUIV		= '<meta http-equiv="Content-Type"		content="text/html"charset="UTF-8">' . PHP_EOL;
	const	XUA				= '<meta http-equiv="x-ua-compatible"	content="ie=edge">' . PHP_EOL;
	const	VIEWPORT		= '<meta name="viewport"				content="width=device-width,initial-scale=1.0">' . PHP_EOL;
	const	NO_BOTS			= '<meta name="robots"					content="noindex, nofollow">' . PHP_EOL;
	//%METADESC%
	const	PAGE_AUTHOR			= '<meta name="author"				content="<?php echo $aData[ \'author\' ] ?>">' . PHP_EOL;
	const	PAGE_DESCRIPTION	= '<meta name="description"		content="<?php echo $aData[ \'description\' ] ?>">' . PHP_EOL;
	//%METACARDS%
	const	CARD_TITLE		= '<meta property="og:title"		content="<?php echo $aData[ \'title\' ] ?>" />' . PHP_EOL; // Title does not need to be dynamic
	const	CARD_URL		= '<meta property="og:url"			content="<?php echo $pg_url ?>" />' . PHP_EOL;
	const	CARD_DESC		= '<meta property="og:description"	content="<?php echo $aData[ \'description\' ] ?>" />' . PHP_EOL;
	const	DEFAULT_IMG		= '<meta property="og:image"		content="<?php echo $aData[ \'image\' ] ?>" />' . PHP_EOL;
	
	const	CARD_SITE		= '<meta property="og:site_name"	content="<?php echo SITE_NAME ?>" />' . PHP_EOL;
	const	CARD_TYPE		= '<meta property="og:type"		content="website"/>' . PHP_EOL;
	const	CARD_LOCALE		= '<meta property="og:locale"		content="en_US"/>' . PHP_EOL;
	//%METATWITTER%
	const	CARD_TWITTER	= '<meta name="twitter:card"		content="summary">' . PHP_EOL;
	const	CARD_SITENAME	= '<meta name="twitter:site"		content="<?php echo TWITTER_HANDLE ?>">' . PHP_EOL;
	//%METAFAVICON%
	const	ICON_16		= '<link rel="icon" href="<?php echo FAVICON16 ?>" sizes="16x16" type="image/png">' . PHP_EOL;
	const	ICON_32		= '<link rel="icon" href="<?php echo FAVICON32 ?>" sizes="32x32" type="image/png">' . PHP_EOL;
	const	ICON_48		= '<link rel="icon" href="<?php echo FAVICON48 ?>" sizes="48x48" type="image/png">' . PHP_EOL;
	const	ICON_64		= '<link rel="icon" href="<?php echo FAVICON64 ?>" sizes="64x64" type="image/png">' . PHP_EOL;
	//%METARSS%
	const	ATOM	= '<link href="/feeds/atom.xml" type="application/atom+xml" rel="alternate"title="<?php echo SITE_NAME ?> Atom Feed"/>' . PHP_EOL;
	const	RSS		= '<link href="/feeds/rss.xml" type="application/rss+xml" rel="alternate"title="<?php echo SITE_NAME ?> RSS Feed"/>' . PHP_EOL;
}
?>