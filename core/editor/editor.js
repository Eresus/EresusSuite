function xinha_init()
{
	var xinha_plugins = [
		'ContextMenu',
		'DynamicCSS',
		'EnterParagraphs',
		'FullScreen',
		'GetHtml',
		'InsertAnchor',
		'PasteText',
		'TableOperations',
		'SuperClean'
	];
	if(!HTMLArea.loadPlugins(xinha_plugins, xinha_init)) return;
	var xinha_config = new HTMLArea.Config();
	// external stylesheets to load (REFERENCE THESE ABSOLUTELY)
	xinha_config.pageStyleSheets = [
		"$(httpRoot)style/default.css"
	];
	// specify a base href for relative links
	xinha_config.baseHref = "$(httpRoot)";
	xinha_config.baseHref = xinha_config.baseHref.substr(0, xinha_config.baseHref.length-1);
	xinha_config.stripBaseHref = false;
	xinha_config.makeLinkShowsTarget = false;
	xinha_config.showLoading = true;

	xinha_config.toolbar =
	[
		["popupeditor","htmlmode"],
		["separator","formatblock","fontname","fontsize","bold","italic","underline","strikethrough"],
		["separator","forecolor","hilitecolor"],
		["separator","subscript","superscript"],
		["linebreak","separator","justifyleft","justifycenter","justifyright","justifyfull"],
		["separator","insertorderedlist","insertunorderedlist","outdent","indent"],
		["separator","inserthorizontalrule","createlink","insertimage","inserttable"],
		(HTMLArea.is_gecko ? [] : ["separator","cut","copy","paste"]),
		["separator","killword","clearfonts","removeformat","toggleborders","splitblock"],
	];

	xinha_config.fontname =
	{
		"&mdash; font &mdash;": "",
		"Arial":	         'arial,helvetica,sans-serif',
		"Verdana":	       'verdana,arial,helvetica,sans-serif',
		"Tahoma":	         'tahoma,arial,helvetica,sans-serif',
		"Courier New":	   'courier new,courier,monospace',
		"Times New Roman": 'times new roman,times,serif',
		"Georgia":	       'georgia,times new roman,times,serif'
	};

	xinha_config.fontsize =
	{
		"&mdash; size &mdash;": "",
		"1 (8 pt)" : "1",
		"2 (10 pt)": "2",
		"3 (12 pt)": "3",
		"4 (14 pt)": "4",
		"5 (18 pt)": "5",
		"6 (24 pt)": "6",
		"7 (36 pt)": "7"
	};

	xinha_config.formatblock =
	{
		"&mdash; format &mdash;": "",
		"Normal"   : "p",
		"Heading 1": "h1",
		"Heading 2": "h2",
		"Heading 3": "h3",
		"Heading 4": "h4",
		"Heading 5": "h5",
		"Heading 6": "h6",
		"Block"    : "div",
		"Address"  : "address",
		"Formatted": "pre"
	};

	xinha_editors = HTMLArea.makeEditors(xinha_editors, xinha_config, xinha_plugins);
	HTMLArea.startEditors(xinha_editors);
}
window.onload = xinha_init;
