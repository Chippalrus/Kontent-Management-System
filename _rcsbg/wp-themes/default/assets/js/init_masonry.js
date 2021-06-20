var wall = new Freewall(".Excerpt");
$(function()
{
wall.reset({selector:'.X',animate:false,cellW:50,cellH:'auto',onResize:function(){wall.fitWidth();}});
wall.fitWidth();
$(window).on('resize', function(){setTimeout(function(){$(window).trigger("resize");},1500);});
setTimeout(function(){$(window).trigger("resize");},1500);
});