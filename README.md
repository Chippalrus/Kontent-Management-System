# For my own reference and backup
A personal project / proof of concept to create blogs by using Google Docs/Drive, with a focus of not relying on MySQL (and backups from webserver). This is not really for anyone to use, but feel free to do so if you want to dig into to the horrible code base. This has no support or help, and is mainly here for my own backup/reference should I decide to pick this back up.
> I am not a "profestional" web-developer, nor do I have any coding discipline. I simply build things out of interest tailored to my own interests and niche needs. This is just for my own use. Do not expect any type of support if you choose to use this.

# Usage
Requires Google Drive API Key and defined as API_KEY in web.config.php
- define( 'API_KEY', '' );
- Call CDriver()->ConstructWebsite( rootDirectoryID, pageSize, thumbnailSize ) to build a website.

# Kontent Management System
KMS is a web builder / CMS that constructs web pages based on the hierarchy of a given Google Drive directory.
- https://kms.chippalrus.ca/

# Requirements
- Web-Server
- PHP 7.0+
- Google API Key
- Parsedown ( https://github.com/erusev/parsedown )
- ParsedownExtra ( https://github.com/erusev/parsedown-extra )
- GoogleAuthenticator ( https://github.com/PHPGangsta/GoogleAuthenticator ) for future login GUI feature
- Three.js ( https://github.com/mrdoob/three.js ) for displaying 3D Models in blog posts

## Features
- Builds Dynamic OR Static pages
- Custom Themes
- Standard Markdown
- Preserves Rich Text from Google Docs
- Constructs Per-page meta information
- Additional markup language for aesthetics ( see below )
- Custom navigation links
- Builds RSS and Atom feeds
- Purges deleted files and directories

# Markup
## Document Modifications

### %live_doc
Converts generation of static page or article to dynamic, allowing for documents to be frequently updated without re-generating the page or article again. This is on a page by page basis, the rest of the website will still be static if this is used.

### %no_title
Prevents generating page or article titles from document name. This is on a page by page basis, every other page or post will continue to display titles. This is useful if you wish to keep URL short and create a heading of your own inside the document.

### %meta{"author":"name","description":"150 character description","image":"url"}
Add meta for the page, if this is not present website default will be used in its place.

## Google Sheets
Work in progress, basic function is working.
### %sheet{"id":"google_sheet_id", "gid":"grid_id"}
Displays a given Google Sheet Grid in a HTML table. This allows the user to display spreadsheets inside their article.

## Formatting

### %blqt some quote of text blqt%
This forces the use of *block quotes* for Rich Text when converted to HTML.

### %gallery_sec (drag drop images) gallery_sec%
Turns Page or Article into a gallery. Display images in carded format. (Only works at bottom of page/article)

### %image{"src":"url"}
Manually add an image via URL. Inserted images are automatically converted to this format. This adds shadow box / lightbox functionality.

### %list{"date":"2020-01-01","title":"name","url":"link","desc":"description","link":"disabled"}
Adds a 3 column 1 row block element for stacking URLs. Similar to how archive lists blog posts. "Link" disables the URL feature.

## Embed/oEmbed

### %audio{"src":"file.ogg"}
Embeds an audio file to the page or article.

### %video{"src":"file.mp4"}
Embeds a video to the page or article.

### %platform{"src":"url"}
Replace “platform” with:  youtube, soundcloud, twitter, instagram, facebook, twitch, flickr, deviantart, meetup, kickstarter, codepen, reddit, spotify
Embeds content using oEmbed. 


## 3D Renders ( Three.js )
Work in progress, using compiled example implimetations
### %3ds{"normal":"normalmap.jpg","resource":"resource_path","file":"file.3ds"}
Embeds a 3DS file.

### %fbx{"file":"file.fbx"}
Embeds a FBX file.

### %obj{"texture":"texture.jpg","file":"file.obj"}
Embeds an OBJ file.

### %obj_mtl{"dds":"dds.mtl","file":"file.3ds"}
Embeds an OBJ file with DDS material


## HTML Block Elements

### %h1#id.class{"title":"heading_name","desc":"optional_description","img":"none"}
Creates a block graphic for heading uses. Yes this is worse than BB code, but it works, whatever.

### %btn#id.class{"title":"button_name","desc":"optional_description","url":"#","img":"none"}
Creates a block graphic that links to given URL. Yes this is worse than BB code, but it works, whatever.

## Guild Wars 2 API ( deprecated )

### %GW2{"apikey":"API_KEY","character":"CHAR_NAME" "mode":"GAME MODE","charimg":"none"}
Displays a character sheet based on given game mode.
!!! Since I no longer play, I am going to stop development on this function.
