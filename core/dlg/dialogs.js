
var iBrowser = new Array();
iBrowser["UserAgent"] = navigator.userAgent.toLowerCase();
if ((iBrowser["UserAgent"].indexOf("msie") != -1) && (iBrowser["UserAgent"].indexOf("opera") == -1) && (iBrowser["UserAgent"].indexOf("webtv") == -1)) iBrowser["Engine"] = "IE";
if (iBrowser["UserAgent"].indexOf("gecko") != -1) iBrowser["Engine"] = "Gecko";
if (iBrowser["UserAgent"].indexOf("opera") != -1) iBrowser["Engine"] = "Opera";
if (iBrowser["UserAgent"].indexOf("safari") != -1) iBrowser["Engine"] = "Safari";
if (iBrowser["UserAgent"].indexOf("konqueror") != -1) iBrowser["Engine"] = "Konqueror";
