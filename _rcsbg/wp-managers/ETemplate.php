<?php
abstract class ETemplate
{
	// Header
	const	HEADER			= '/templates/header/header.html';
	// Body
	const	_TITLE_			= '/templates/body/title.html';
	const	_ARTICLE_		= '/templates/body/article/body.html';
	const	_ARTICLE_CAT_	= '/templates/body/article/category.html';
	// Page
	const	_PAGE_			= '/templates/body/page/body.html';
	const	_PG_SPACER_		= '/templates/body/page/spacer.html';
	// Comments
	const	COMMENTS		= '/templates/body/comments.html';
	// Gallery
	const	_GALLERY_		= '/templates/body/gallery/body.html';
	// Excerpt
	const	EXCERPT			= '/templates/body/excerpt.html';
	const	MASONRY			= '/assets/js/init_masonry.js';
	const	JQUERY			= '/js/jquery.js';
	const	FREEWALL		= '/js/freewall.js';
	// Footer
	const	FOOTER			= '/templates/footer/footer.html';
	// Navigation
	const	NAV_HEADER		= '/templates/nav/header.html';
	const	NAV_ITEM		= '/templates/nav/body.html';
	const	NAV_FOOTER		= '/templates/nav/footer.html';
	// Archive
	const	_ARCHIVE_		= '/templates/body/archive/body.html';
	const	_ARCHIVE_YR_	= '/templates/body/archive/year.html';
	const	_ARCHIVE_ART_	= '/templates/body/archive/articles.html';
	const	_ARCHIVE_CAT_	= '/templates/body/archive/category.html';
	// Error PAGE
	const	ERR				= '/templates/error.html';
	// Theme CSS
	const	CSS				= '/assets/style.css';
	// Dynamic Page Body
	const	DYNAMIC			= '/liveupdate.html';
	// ThreeJS Pages
	const	THREEDS			= '/three/3ds.html';
	const	FBX				= '/three/fbx.html';
	const	OBJ				= '/three/obj.html';
	const	OBJ_MTL			= '/three/obj_mtl.html';
	// Player CSS/JS
	const	ME_BS			= '/me/me.txt';
	const	ME_CSS			= '/me/me.css';
	const	ME_JS			= '/me/me.js';	//Requires JQUERY
}
?>