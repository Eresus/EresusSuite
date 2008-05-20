function(html) {
    // Remove HTML comments
	html = html.replace(/<!--[\w\s\d@{}:.;,'"%!#_=&|?~()[*+\/\-\]]*-->/gi, "" );
	html = html.replace(/<!--[^\0]*-->/gi, '');
  // Remove all HTML tags
	html = html.replace(/<\/?\s*HTML[^>]*>/gi, "" );
  // Remove all BODY tags
  html = html.replace(/<\/?\s*BODY[^>]*>/gi, "" );
  // Remove all META tags
	html = html.replace(/<\/?\s*META[^>]*>/gi, "" );
  // Remove all IFRAME tags.
  html = html.replace(/<\/?\s*IFRAME[^>]*>/gi, "");
  // Remove all STYLE tags & content
	html = html.replace(/<\/?\s*STYLE[^>]*>(.|[\n\r\t])*<\/\s*STYLE\s*>/gi, "" );
  // Remove all TITLE tags & content
  html = html.replace(/<\s*TITLE[^>]*>(.|[\n\r\t])*<\/\s*TITLE\s*>/gi, "" );
	// Remove javascript
  html = html.replace(/<\s*SCRIPT[^>]*>[^\0]*<\/\s*SCRIPT\s*>/gi, "");
  // Remove all HEAD tags & content
	html = html.replace(/<\s*HEAD[^>]*>(.|[\n\r\t])*<\/\s*HEAD\s*>/gi, "" );
	// Remove XML elements and declarations
	html = html.replace(/<\\?\?xml[^>]*>/gi, "") ;
	// Remove Tags with XML namespace declarations: <o:p></o:p>
	html = html.replace(/<\/?\w+:[^>]*>/gi, "") ;
	// Remove unwanted elements
	html = html.replace(/<\/?\s*span[^>]*>/gi, "" );
  
	html = html.replace(/<\/?\s*applet[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*basefont[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*center[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*dir[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*frame[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*frameset[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*iframe[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*menu[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*noframes[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*s[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*strike[^>]*>/gi, "" );
	html = html.replace(/<\/?\s*u[^>]*>/gi, "" );
	// Remove unwanted attributes
	html = html.replace(/<\s*(\w[^>]*) align=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) background=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) bgcolor=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) dir=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) height=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) hspace=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) noshade=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) nowrap=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) start=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) valign=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) vspace=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace(/<\s*(\w[^>]*) width=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	// Replace the &nbsp;
	html = html.replace(/&nbsp;/, " " );

	// Transform <p><br /></p> to <br>
	//html = html.replace(/<\s*p[^>]*>\s*<\s*br\s*\/>\s*<\/\s*p[^>]*>/gi, "<br>");
	html = html.replace(/<\s*p[^>]*><\s*br\s*\/?>\s*<\/\s*p[^>]*>/gi, "<br>");
	
	// Remove any <br> at the end
	html = html.replace(/(\s*<br>\s*)*$/, "");
	
	html = html.trim();
	return html;
} 