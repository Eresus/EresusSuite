<?php
/**
 * E-Forms
 *
 * Eresus 2
 *
 * ����������� HTML-�����
 *
 * @version 1.00b
 *
 * @copyright   2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� (�� ������ ������) � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 */


class EForms extends Plugin {
	var $version = '1.00b';
	var $kernel = '2.10';
	var $title = 'E-Forms';
	var $description = '����������� HTML-�����';
	var $type = 'client,admin';

	/**
	 * ������ ��������� ����
	 *
	 * @var array
	 *
	 * @access private
	 */
	var $forms = null;
	/**
	 * ��������� ������� Templates
	 *
	 * @var object Templates
	 *
	 * @access private
	 */
	var $templates = null;


	/**
	 * �����������
	 *
	 * @return EForms
	 */
	function EForms()
	{
		parent::Plugin();
		$this->listenEvents('clientOnContentRender', 'clientOnPageRender');
	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� ��� ��������� �������
	 *
	 */
	function install()
	{
		global $Eresus;

		parent::install();

		$umask = umask(0000);
		mkdir($Eresus->froot.'templates/'.$this->name, 0777);
		umask($umask);

		#TODO: �������� �������� ���������� � ���� ��� �������������

	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� ������ Templates
	 *
	 * @return object Templates
	 */
	function getTemplates()
	{
		if (is_null($this->templates)) {
			useLib('templates');
			$this->templates = new Templates();
		}

		return $this->templates;
	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� ������ ��������� ����
	 *
	 * @return array
	 */
	function getForms()
	{
		if (is_null($this->forms)) {
			$templates = $this->getTemplates();
			$this->forms = $templates->enum($this->name);
		}

		return $this->forms;
	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� ��� �����
	 *
	 * @param string $name
	 * @return string
	 */
	function getFormCode($name)
	{

		$templates = $this->getTemplates();
		$form = $templates ? $templates->get($name, $this->name) : false;

		return $form;
	}
	//-----------------------------------------------------------------------------
	/**
	 * ����������� ���� �� ��������
	 *
	 * @param string $text
	 * @return string
	 */
	function clientOnPageRender($text)
	{

		$text = preg_replace_callback('/\$\('.$this->name.':(.*)\)/Usi', array($this, 'buildForm'), $text);
		return $text;
	}
	//-----------------------------------------------------------------------------
	/**
	 * HTML-��� �����
	 *
	 * @param array $macros
	 * @return string
	 */
	function buildForm($macros)
	{
		$result = $macros[0];

		$form = new EForm($this, $macros[1]);

		if ($form->valid()) $result = $form->getHTML();

		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * ��������� ������������ ����
	 *
	 */
	function clientOnContentRender($content)
	{
		global $Eresus, $page;

		if (arg('ext') == $this->name) {

			$form = new EForm($this, arg('form', 'word'));
			$content = $form->processActions();

		}
		return $content;
	}
	//-----------------------------------------------------------------------------
}

/**
 * �����
 *
 */
class EForm {
	/**
	 * E-Forms namespace
	 */
	const NS = 'http://procreat.ru/eresus2/ext/eforms';
	/**
	 * Owner plugin
	 *
	 * @var TPlugin
	 */
	protected $owner;
	/**
	 * Form name
	 *
	 * @var string
	 */
	protected $name;
	/**
	 * Raw form code
	 *
	 * @var string
	 */
	protected $code;
	/**
	 * XML representation
	 *
	 * @var DOMDocument
	 */
	protected $xml;

	/**
	 * URI for redirect
	 *
	 * @var mixed
	 */
	protected $redirect = false;
	/**
	 * Contents of 'html' actions
	 *
	 * @var string
	 */
	protected $html = '';

	/**
	 * Constructor
	 *
	 * @param TPlugin $owner  Plugin
	 * @param string  $name   Form name
	 */
	function __construct($owner, $name)
	{
		global $Eresus;

		$this->owner = $owner; #TODO: Check $owner class
		$this->name = $name;

		$code = $this->owner->getFormCode($name);

		if ($code) {

			$imp = new DOMImplementation;
			$dtd = $imp->createDocumentType('html', '-//W3C//DTD XHTML 1.0 Strict//EN', 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd');
			$this->xml = $imp->createDocument("", "", $dtd);
			if (strtolower(CHARSET) != 'utf-8') $code = iconv(CHARSET, 'utf-8', $code);
			$this->xml->loadXML($code);
			$this->xml->encoding = 'utf-8';
			$this->xml->normalize();
			$this->setActionAttribute();
			$this->setActionTags();
		}
	}
	//-----------------------------------------------------------------------------
	/**
	 * Set form's action attribute
	 *
	 */
	protected function setActionAttribute()
	{
		global $Eresus;

		$form = $this->xml->getElementsByTagName('form')->item(0);
		$form->setAttribute('action', $Eresus->request['path']);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Adds hidden inputs to form
	 *
	 */
	protected function setActionTags()
	{
		$form = $this->xml->getElementsByTagName('form')->item(0);
		$div = $this->xml->createElement('div');

		$input = $this->xml->createElement('input');
		$input->setAttribute('type', 'hidden');
		$input->setAttribute('name', 'ext');
		$input->setAttribute('value', $this->owner->name);
		$div->appendChild($input);

		$input = $this->xml->createElement('input');
		$input->setAttribute('type', 'hidden');
		$input->setAttribute('name', 'form');
		$input->setAttribute('value', $this->name);
		$div->appendChild($input);

		$form->appendChild($div);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Return TRUE if form loaded and it is valid
	 *
	 * @return bool
	 */
	public function valid()
	{
		return is_object($this->xml);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Get HTML form markup
	 *
	 * @return string
	 */
	public function getHTML()
	{
		$xml = clone $this->xml;

		# Clean extended tags
		$tags = $xml->getElementsByTagNameNS(self::NS, '*');
		for($i=0; $i<$tags->length; $i++) {
			$node = $tags->item($i);
			$node->parentNode->removeChild($node);
		}

		# Clean extended attrs
		$tags = $xml->getElementsByTagName('*');
		for($i=0; $i<$tags->length; $i++) {
			$node = $tags->item($i);

			$isElement = $node->nodeType == XML_ELEMENT_NODE;
			$hasAttributes = $isElement && $node->hasAttributes();

			if ($isElement && $hasAttributes) {
				$attrs = $node->attributes;
				for($j=0; $j<$attrs->length; $j++) {
					$node = $attrs->item($j);
					if ($node->namespaceURI == self::NS) $node->ownerElement->removeAttributeNode($node);
				}
			}
		}

		# Prevent em[ty textareas from collapsing
		$tags = $xml->getElementsByTagName('textarea');
		for($i=0; $i<$tags->length; $i++) {
			$node = $tags->item($i);
			$cdata = $xml->createCDATASection('');
			$node->appendChild($cdata);
		}

		$xml->formatOutput = true;
		$html = $xml->saveXML($xml->firstChild); # This exclude xml declaration
		$html = preg_replace('/\s*xmlns:\w+=("|\').*?("|\')/', '', $html); # Remove ns attrs
		$html = str_replace('<![CDATA[]]>', '', $html); # Remove empty <![CDATA[]]> sections
		if (strtolower(CHARSET) != 'utf-8') $html = iconv('utf-8', CHARSET, $html);

		return $html;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Get element's 'label' attribute
	 *
	 * @param DOMElement $element
	 * @return string
	 */
	protected function getLabelAttr($element)
	{
		$label = $element->getAttributeNS(self::NS, 'label');
		if ($label) $label = iconv('utf-8', CHARSET, $label);
		return $label;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Return posted form data
	 *
	 * @return array
	 */
	protected function getFormData()
	{
		$data = array();
		$inputTagNames = array('input', 'textarea', 'select');
		$skipNames = array('ext', 'form');

		$elements = $this->xml->getElementsByTagName('form')->item(0)->getElementsByTagName('*');

		for($i = 0; $i < $elements->length; $i++) {
			$element = $elements->item($i);

			$isElement = $element->nodeType == XML_ELEMENT_NODE;
			$isInputTag = $isElement && in_array($element->nodeName, $inputTagNames);

			if ($isInputTag) {
				$name = $element->getAttribute('name');
				if (in_array($name, $skipNames)) continue;
				if ($name) {
					$data[$name]['data'] = arg($name);
					$data[$name]['label'] = $this->getLabelAttr($element);
					if (!$data[$name]['label']) $data[$name]['label'] = $name;

					switch ($element->nodeName) {
						case 'input':

							switch($element->getAttribute('type')) {
								case 'checkbox':
									$data[$name]['data'] = $data[$name]['data'] ? strYes : strNo;
								break;
							}

						break;
					}

				}
			}
		}

		return $data;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Process form actions
	 *
	 */
	public function processActions()
	{
		global $Eresus;

		$actionsElement = $this->xml->getElementsByTagNameNS(self::NS, 'actions');

		if ($actionsElement) {
			$actions = $actionsElement->item(0)->childNodes;
			for($i = 0; $i < $actions->length; $i++) {
				$action = $actions->item($i);
				if ($action->nodeType == XML_ELEMENT_NODE) $this->processAction($action);
			}
		}

		if ($this->redirect) goto($this->redirect);
		if ($this->html) return $this->html;
		goto($Eresus->request['referer']);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Process action directive
	 *
	 * @param DOMElement $action
	 */
	protected function processAction($action)
	{
		$actionName = substr($action->nodeName, strlen($action->lookupPrefix(self::NS))+1);
		$methodName = 'action'.$actionName;
		if (method_exists($this, $methodName)) $this->$methodName($action);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Process 'mailto' action
	 *
	 * @param DOMElement $action
	 */
	protected function actionMailto($action)
	{
		$to = $action->getAttribute('to');
		$subj = $action->getAttribute('subj');
		#$from = $action->getAttribute('from');
		$data = $this->getFormData();

		if (!$to) return false;
		if (!$subj) $subj = $this->name;
		#$from = $action->getAttribute('from');

		$text = '';
		foreach ($data as $item) {
			if (!isset($item['label'])) continue;
			$text .= $item['label'].': '.$item['data']."\n";
		}
		sendMail($to, $subj, $text);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Process 'redirect' action
	 *
	 * @param DOMElement $action
	 */
	protected function actionRedirect($action)
	{
		global $page;

		if ($this->redirect) return;

		$this->redirect = $action->getAttribute('uri');
		$this->redirect = $page->replaceMacros($this->redirect);

	}
	//-----------------------------------------------------------------------------
	/**
	 * Process 'html' action
	 *
	 * @param DOMElement $action
	 */
	protected function actionHtml($action)
	{
		$elements = $action->childNodes;

		if ($elements->length) {

			$html = '';
			for($i = 0; $i < $elements->length; $i++) $html .= $this->xml->saveXML($elements->item($i));
			if (strtolower(CHARSET) != 'utf-8') $html = iconv('utf-8', CHARSET, $html);
			$this->html .= $html;

		}
	}
	//-----------------------------------------------------------------------------
}
