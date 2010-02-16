function(html) {
  html = html.replace(/<\/?\s*FONT[^>]*>/gi, "");
  return html;
} 