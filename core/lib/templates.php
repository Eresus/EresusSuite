<?php
/**
* Eresus� 2
*
* ���������� ��� ������ � ���������
*
* @author Mikhail Krasilnikov <mk@procreat.ru>
* @version 0.0.3
*/

class Templates {
	var $pattern = '/^<!--\s*(.+?)\s*-->.*$/s';
	/**
	 * ���������� ������ ��������
	 *
	 * @param string $type ��� �������� (������������� ������������� � /templates)
	 * @return array
	 */
	function enum($type = '')
	{
		$result = array();
		$dir = filesRoot.'templates/';
		if ($type) $dir .= "$type/";
		$list = glob("$dir*.html");
		if ($list) foreach($list as $filename) {
			$file = file_get_contents($filename);
			$title = trim(substr($file, 0, strpos($file, "\n")));
			if (preg_match('/^<!-- (.+) -->/', $title, $title)) {
				$file = trim(substr($file, strpos($file, "\n")));
				$title = $title[1];
			} else $title = admNoTitle;
			$result[basename($filename, '.html')] = $title;
		}
		return $result;
	}
  //------------------------------------------------------------------------------
  /**
   * ���������� ������
   *
   * @param string $name  ��� �������
   * @param string $type  ��� ������� (������������� ������������� � /templates)
   * @param bool   $array ������� ������ � ���� �������
   * @return mixed ������
   */
  function get($name = '', $type = '', $array = false)
  {
  	$result = false;
  	if (empty($name)) $name = 'default';
  	$filename = filesRoot.'templates/';
		if ($type) $filename .= "$type/";
		$filename .= "$name.html";
		$result = fileread($filename);
		if ($result) {
			if ($array) {
				$desc = preg_match($this->pattern, $result);
				$result = array(
					'name' => $name,
					'desc' => $desc ? preg_replace($this->pattern, '$1', $result) : admNA,
					'code' => $desc ? trim(substr($result, strpos($result, "\n"))) : $result,
				);
			} else {
				if (preg_match($this->pattern, $result)) $result = trim(substr($result, strpos($result, "\n")));
			}
		} else {
			if (empty($type) && $name != 'default') $result = $this->get('default', $type);
			#if (!$result) FatalError(sprintf(errTemplateNotFound, $name));
			if (!$result) $result = '';
		}
  	return $result;
  }
  //------------------------------------------------------------------------------
  /**
   * ����� ������
   *
   * @param string $name ��� �������
   * @param string $type ��� ������� (������������� ������������� � /templates)
   * @param string $code ���������� �������
   * @param string $desc �������� ������� (�������������)
   * @return bool ��������� ����������
   */
  function add($name, $type, $code, $desc = '')
  {
  	$result = false;
  	$filename = filesRoot.'templates/';
		if ($type) $filename .= "$type/";
		$filename .= "$name.html";
		$content = "<!-- $desc -->\n\n$code";
		$result = filewrite($filename, $content);
		if ($result) {
			$message = admAdded.': '.$name;
			InfoMessage($message);
			SendNotify($message);
		} else {
			ErrorMessage(sprintf(errFileWrite, $filename));
		}
  	return $result;
  }
  //------------------------------------------------------------------------------
  /**
   * �������� ������
   *
   * @param string $name ��� �������
   * @param string $type ��� ������� (������������� ������������� � /templates)
   * @param string $code ���������� �������
   * @param string $desc �������� ������� (�������������)
   * @return bool ��������� ����������
   */
  function update($name, $type, $code, $desc = null)
  {
  	$result = false;
  	$filename = filesRoot.'templates/';
		if ($type) $filename .= "$type/";
		$filename .= "$name.html";
		$item = $this->get($name, $type, true);
		$item['code'] = $code;
		if (!is_null($desc)) $item['desc'] = $desc;
		$content = "<!-- {$item['desc']} -->\n\n{$item['code']}";
		$result = filewrite($filename, $content);
		if ($result) {
			$message = admUpdated.': '.$name;
			#InfoMessage($message);
			SendNotify($message);
		} else {
			ErrorMessage(sprintf(errFileWrite, $filename));
		}
  	return $result;
  }
  //------------------------------------------------------------------------------
  /**
   * ������� ������
   *
   * @param string $name ��� �������
   * @param string $type ��� ������� (������������� ������������� � /templates)
   * @return bool ��������� ����������
   */
  function delete($name, $type = '')
  {
  	$result = false;
  	$filename = filesRoot.'templates/';
		if ($type) $filename .= "$type/";
		$filename .= "$name.html";
		$result = filedelete($filename);
		if ($result) {
			$message = admDeleted.': '.$name;
			InfoMessage($message);
			SendNotify($message);
		} else {
			ErrorMessage(sprintf(errFileDelete, $filename));
		}
  	return $result;
  }
  //------------------------------------------------------------------------------
}

?>