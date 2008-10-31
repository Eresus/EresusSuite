/**
 * ���������� AJAX
 *
 * ������� ���������� ��������� Eresus� 2
 * � 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @version 0.0.1
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */

/**
 * ������ �� ���������������
 */
var AJAX_NOT_INITIALIZED = 0;
/**
 * ��� �������� �������
 */
var AJAX_SENDING_REQUEST = 1;
/**
 * ������ ���������
 */
var AJAX_REQUEST_SENT = 2;
/**
 * ��� ����� �������
 */
var AJAX_NEGOTIATE = 3;
/**
 * ������ ��������
 */
var AJAX_READY = 4;


/**
 * AJAX-���������
 */
var AJAX = {
 /**
  * @type XMLHttpRequest  ������ XMLHttpRequest
  */
  req: null,
 /**
  * @type array  ������� ��������
  */
  queue: new Array(),
 /**
  * @type string  ������� ������
  */
  current: '',

 /**
  * �������������� ������
  */
  init: function()
  {
		if (window.XMLHttpRequest) {
			// DOM-��������
			try {
				this.req = new XMLHttpRequest();
			} catch (e) {
				this.req = false;
			}
		} else if (window.ActiveXObject) {
			// MSIE
			try {
				this.req = new ActiveXObject('Microsoft.XMLHTTP');
			} catch (e) {
				this.req = false;
			}
		}
		if (this.req) {
			this.req.onreadystatechange = this.handler;
		} else alert('Can not initialize XMLHttpRequest object!'); // TODO: i18n
  },
  //------------------------------------------------------------------------------
 /**
  * ���������� ������ �������
  *
  * @param  string  plugin  ���������� ������
  * @param  string  params  �������������� ��������� � ������� 'param1=value1&param2=value2'
  * @return bool ��������� ����������
  */
  request: function(plugin)
  {
		if (!this.req) return false;

    var url = '$(httpRoot)ajax/'+plugin+'/?__nocache='+Math.random();
    if (arguments.length > 1) url += '&'+arguments[1];
    var result = this.queue.push(url);
    if (result) result = this.process();
		return result;
  },
  //------------------------------------------------------------------------------
 /**
  * ��������� ��������� ������� � ������� ��������
  */
  process: function()
  {
		if (!this.req) return false;
		//TODO: �������� ������ ��� IE
		this.init();
    if (this.queue.length && (this.req.readyState == AJAX_READY || this.req.readyState == AJAX_NOT_INITIALIZED)) {
      this.current = this.queue.shift();
      this.req.open('GET', this.current, true);
      this.req.send(null);
    }
  },
  //------------------------------------------------------------------------------
 /**
  * ��������� JavaScript-������
  */
	processJavaScript: function()
	{
		eval(this.req.responseText);
	},
  //------------------------------------------------------------------------------
 /**
  * ��������� ������ �������
  */
	processResponse: function()
	{
		var type = this.req.getResponseHeader('content-type').replace(/;.*$/, '').toLowerCase();
		switch (type) {
			case 'text/javascript': this.processJavaScript(); break;
		}
	},
  //------------------------------------------------------------------------------
 /**
  * ��������� ��������� �������
  */
  handler: function()
  {
    if (AJAX.req.readyState == AJAX_READY) {
      switch (AJAX.req.status) {
        case 200: AJAX.processResponse(); break;
      }
      AJAX.process();
    }
  }
  //------------------------------------------------------------------------------
}

AJAX.init();