/**
 * Библиотека AJAX
 *
 * Система управления контентом Eresus™ 2
 * © 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @version 0.0.1
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */

/**
 * Запрос не инициализирован
 */
var AJAX_NOT_INITIALIZED = 0;
/**
 * Идёт отправка запроса
 */
var AJAX_SENDING_REQUEST = 1;
/**
 * Запрос отправлен
 */
var AJAX_REQUEST_SENT = 2;
/**
 * Идёт обмен данными
 */
var AJAX_NEGOTIATE = 3;
/**
 * Обмуен завершён
 */
var AJAX_READY = 4;


/**
 * AJAX-интерфейс
 */
var AJAX = {
 /**
  * @type XMLHttpRequest  Объект XMLHttpRequest
  */
  req: null,
 /**
  * @type array  Очередь запросов
  */
  queue: new Array(),
 /**
  * @type string  Текущий запрос
  */
  current: '',

 /**
  * Инициализирует объект
  */
  init: function()
  {
		if (window.XMLHttpRequest) {
			// DOM-браузеры
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
  * Отправляет запрос серверу
  *
  * @param  string  plugin  Вызываемый плагин
  * @param  string  params  Дополнительные параметры в формате 'param1=value1&param2=value2'
  * @return bool Результат выполнения
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
  * Выполняет следующее задание в очереди запросов
  */
  process: function()
  {
		if (!this.req) return false;
		//TODO: Оставить только для IE
		this.init();
    if (this.queue.length && (this.req.readyState == AJAX_READY || this.req.readyState == AJAX_NOT_INITIALIZED)) {
      this.current = this.queue.shift();
      this.req.open('GET', this.current, true);
      this.req.send(null);
    }
  },
  //------------------------------------------------------------------------------
 /**
  * Обработка JavaScript-ответа
  */
	processJavaScript: function()
	{
		eval(this.req.responseText);
	},
  //------------------------------------------------------------------------------
 /**
  * Обработка ответа сервера
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
  * Обработка состояний запроса
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