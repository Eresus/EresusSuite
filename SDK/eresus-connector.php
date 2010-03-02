<?php
/**
 * Eresus {$M{VERSION}}
 *
 * Пример файла-коннектора для подключения к Eresus стороннего расширения
 *
 * @copyright 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Все строки 'ExtensionName' необходимо изменить на имя расширения. Имя расширения
 * должно совпадать с директорией, в которой оно будет расположено.
 *
 * Для каждой расширяемой функции должен быть определён метод с именем вида:
 *   класс_функция()
 * например:
 *   forms_html()
 *
 */

/**
 * Класс-коннектор
 *
 * Класс-коннектор должен иметь имя вида 'ИмяРасширенияConnector' и наследоваться от
 * базового класса EresusExtensionConnector.
 */
class ExntensionNameConntector extends EresusExtensionConnector {

}
