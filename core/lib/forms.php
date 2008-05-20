<?php
/**
* Eresus� 2
*
* ���������� ��� ������ � HTML-�������
*
* @maintainer Mikhail Krasilnikov <mk@procreat.ru>
* @author Mikhail Krasilnikov <mk@procreat.ru>
* @author ����� <bersz@procreat.ru>
* @version 0.0.3
*/


/**
* HTML-�����
*/
class Form {
  var $form;
  var $values;
  var $hidden = '';
  var $onsubmit = '';
  var $validator = '';
  var $file = false;    # ������� ������� ����� ���� file
  var $html = false;    # ������� ������� WYSIWYG ����������
  var $syntax = false;  # �������� ������� ����� � ���������� ����������
  /**
  * �����������
  *
  * @param  array  $form    �������� �����
  * @param  array  $values  �������� ����� �� ��������� (�������������)
  */
  function Form($form, $values=array())
  {
    $this->form = $form;
    $this->values = $values;
  }
  //------------------------------------------------------------------------------
  /**
  * ��������������� ���� ����� ��� ���������� ���������
  *
  * @access  private
  *
  * @param  &array  $item  �������� ����
  */
  function field_prep(&$item)
  {
    $item['type'] = strtolower($item['type']);
    # �����
    if (!isset($item['label'])) $item['label'] = '';
    # ���������
    if (isset($item['hint'])) $item['label'] = '<span class="hint" title="'.$item['hint'].'">'.$item['label'].'</span>';
    # ����� ��������
    if (isset($item['pattern']) && isset($item['name']))
      $this->validator .= "
        if (!form.".$item['name'].".value.match(".$item['pattern'].")) {
          alert('".(isset($item['errormsg'])?$item['errormsg']:sprintf(errFormPatternError, $item['name'], $item['pattern']))."');
          result = false;
          form.".$item['name'].".select();
        } else ";
    # ��������
    $item['value'] = isset($item['value']) ? $item['value']
      : (isset($item['name']) && isset($this->values[$item['name']]) ? $this->values[$item['name']]
      : (isset($item['default']) ? $item['default']
      : '' )
    );
    # ID
    if (!isset($item['id'])) $item['id'] = '';
    # ������� ��������
    if (!isset($item['disabled'])) $item['disabled'] = '';
    # �����������
    $item['comment'] = isset($item['comment']) ? ' '.$item['comment'] : '';
    # �����
    $item['style'] = isset($item['style']) ? explode(';', $item['style']) : array();
    # ������
    $item['class'] = isset($item['class']) ? explode(' ', $item['class']) : array();
    # �������������
    if (!isset($item['extra'])) $item['extra'] = '';
  }
  //------------------------------------------------------------------------------
  /**
  * ������������ �������� ��������
  *
  * @access  private
  *
  * @param  array  $item  �������
  *
  * @return  string  ������������ ��������
  */
  function attrs($item)
  {
    $result = '';
    if ($item['id']) $result .= ' id="'.$item['id'].'"';
    if ($item['disabled']) $result .= ' disabled="disabled"';
    if (count($item['class'])) $result .= ' class="'.implode(' ', $item['class']).'"';
    # ������
    if (isset($item['width'])) $item['style'][] = 'width: '.$item['width'];
    # �����
    if (count($item['style'])) $result .= ' style="'.implode(';', $item['style']).'"';
    $result .= ' '.$item['extra'];
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * ����������
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_divider($item)
  {
    $result = "\t\t<tr><td colspan=\"2\"><hr class=\"formDivider\" /></td></tr>\n";
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * �����
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_text($item)
  {
    $result = "\t\t".'<tr><td colspan="2" class="formText"'.$this->attrs($item).'>'.$item['value']."</td></tr>\n";
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * ������������
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_header($item)
  {
    $result = "\t\t".'<tr><th colspan="2" class="formHeader"'.$this->attrs($item).'>'.$item['value']."</th></tr>\n";
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * <input type="hidden" />
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_hidden($item)
  {
    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    $this->hidden .= '<input type="hidden" name="'.$item['name'].'" value="'.$item['value'].'" />'."\n";
    return '';
  }
  //------------------------------------------------------------------------------
  /**
  * <input type="text" />
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_edit($item)
  {
    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    $result = "\t\t".'<tr><td class="formLabel">'.$item['label'].'</td><td><input type="text" name="'.$item['name'].'" value="'.EncodeHTML($item['value']).'"'.(empty($item['maxlength'])?'':' maxlength="'.$item['maxlength'].'"').$this->attrs($item).' />'.$item['comment']."</td></tr>\n";
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * <input type="password" />
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_password($item)
  {
    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    $result = "\t\t".'<tr><td class="formLabel">'.$item['label'].'</td><td><input type="password" name="'.$item['name'].'" value="'.EncodeHTML($item['value']).'"'.(empty($item['maxlength'])?'':' maxlength="'.$item['maxlength'].'"').$this->attrs($item).' />'.$item['comment']."</td></tr>\n";
    if (isset($item['equal'])) $this->validator .= "if (form.".$item['name'].".value != form.".$item['equal'].".value) {\nalert('".errFormBadConfirm."');\nresult = false;\nform.".$item['name'].".value = '';\nform.".$item['equal'].".value = ''\nform.".$item['equal'].".select();\n} else ";
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * <input type="checkbox" />
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_checkbox($item)
  {
    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    $result = "\t\t".'<tr><td><input type="hidden" name="'.$item['name'].'" value="" /></td><td><input type="checkbox" name="'.$item['name'].'" value="'.($item['value'] ? $item['value'] : true).'" '.($item['value'] ? 'checked' : '').$this->attrs($item).' style="background-color: transparent; border-style: none; margin:0px;" /><span style="vertical-align: baseline"> '.$item['label']."</span></td></tr>\n";
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * <select>
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_select($item)
  {
    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    $result = "\t\t".'<tr><td class="formLabel">'.$item['label'].'</td><td><select name="'.$item['name'].'"'.$this->attrs($item).'>'."\n";
    if (!isset($item['items']) && isset($item['values'])) $item['items'] = $item['values'];
    for($i = 0; $i< count($item['items']); $i++) {
      if (isset($item['values'])) $value = $item['values'][$i]; else $value = $i;
      $result .= '<option value="'.$value.'" '.($value == (isset($this->values[$item['name']]) ? $this->values[$item['name']] : (isset($item['value'])?$item['value']:'')) ? 'selected = "selected"' : '').">".$item['items'][$i]."</option>\n";
    }
    $result .= '</select>'.$item['comment']."</td></tr>\n";
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * <select multiple>
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_listbox($item)
  {
    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    $result = "\t\t".'<tr><td class="formLabel">'.$item['label'].'</td><td><select multiple name="'.$item['name'].'[]"'.(isset($item['height'])?' size="'.$item['height'].'"':'').$this->attrs($item).">\n";
    if (!isset($item['items']) && isset($item['values'])) $item['items'] = $item['values'];
    for($i = 0; $i< count($item['items']); $i++) {
      if (isset($item['values'])) $value = $item['values'][$i]; else $value = $i;
      $result .= '<option value="'.$value.'" '.(count($this->values) && in_array($value, $this->values[$item['name']]) ? 'selected = "selected"' : '').">".$item['items'][$i]."</option>\n";
    }
    $result .= '</select>'.$item['comment']."</td></tr>\n";
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * <textarea></textarea>
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_memo($item)
  {
    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    if (empty($item['width'])) $item['width'] = '100%';
    if (strpos($item['width'], '%') === false) {
      $cols = $item['width'];
      $item['width'] = '';
    } else $cols = '50';
    if (isset($item['syntax'])) {
      if (!$item['id']) $item['id'] = $this->form['name'].'_'.$item['name'];
      $item['class'][] = 'codepress';
      $item['class'] = array_merge($item['class'], explode(' ', $item['syntax']));
      $this->onsubmit .=
        "\n    form.".$item['name'].".value = ".$item['id'].".getCode();\n".
        "    form.".$item['name'].".disabled = false;\n";
      $this->syntax = true;
    }
    $result = "\t\t".'<tr><td colspan="2">'.(empty($item['label'])?'':'<span class="formLabel">'.$item['label'].'</span><br />').'<textarea name="'.$item['name'].'" cols="'.$cols.'" rows="'.(empty($item['height'])?'3':$item['height']).'" '.$this->attrs($item).'>'.EncodeHTML($item['value'])."</textarea></td></tr>\n";
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * <textarea html>
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_html($item)
  {
    global $page;
    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    $value = isset($values[$item['name']]) ? $values[$item['name']] : (isset($item['value'])?$item['value']:'');
    $result = "\t\t".'<tr><td colspan="2">'.$item['label'].'<br /><textarea name="wyswyg_'.$item['name'].'" id="wyswyg_'.$item['name'].'" style="width: 100%; height: '.$item['height'].';">'.str_replace('$(httpRoot)', httpRoot, EncodeHTML($value)).'</textarea></td></tr>'."\n";
    $page->htmlEditors[] = 'wyswyg_'.$item['name'];
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * <input type="file" />
  *
  * @access  protected
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_file($item)
  {
    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    $result = "\t\t".'<tr><td class="formLabel">'.$item['label']."</td><td><input type=\"file\" name=\"".$item['name']."\"".(isset($item['width']) ? ' size="'.$item['width'].'"':'').$this->attrs($item)." />".$item['comment']."</td></tr>\n";
    $this->file = true;
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * <input type="image" />
  *
  * @access  private
  *
  * @param  array  $item  �������� ����
  *
  * @return  string  ������������ ����
  */
  function render_image($item)
  {

    if ($item['name'] === '') ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $this->form['name']));
    $result = "\t\t".'<tr><td class="formImage">'."</td><td><input type=\"image\" name=\"".$item['name']."\" src=\"".$item['src']."\" ".$this->attrs($item)." alt='".$item['label']."' />".$item['comment']."</td></tr>\n";
    $this->file = true;
    return $result;
  }
  //------------------------------------------------------------------------------
  /**
  * �������� HTML-����
  *
  * @access  public
  *
  * @return  string  HTML-��� �����
  */
  function render()
  {
    global $page;

    $result = '';     # �������� ���
    $hidden = '';     # ������� ����???
    $body = '';       # ���� �������-�����

    if (empty($this->form['name'])) $result .= ErrorBox(errFormHasNoName);
    if (count($this->form['fields'])) foreach($this->form['fields'] as $item) {
      # ��������� ����� ������� � ��������
      if ((!isset($item['access'])) || (UserRights($item['access']))) {
        $this->field_prep($item);
        $control = 'render_'.$item['type'];
        #if (method_exists($this, $control)) $result .= call_user_func(array($this, $control), $item);
        if (method_exists($this, $control)) {
          $result .= $this->$control($item);
        }
        else ErrorMessage(sprintf(errFormUnknownType, $item['type'], $this->form['name']));
      }
    }
    $this->onsubmit .= $this->validator;
    if (!empty($this->onsubmit)) $page->scripts .= "
      function ".$this->form['name']."Submit()
      {
        var result = true;
        var form = document.forms.namedItem('".$this->form['name']."');
        ".$this->onsubmit.";
        return result;
      }
    ";
    if ($this->syntax) $page->linkScripts(httpRoot.'core/codepress/codepress.js');
    # FIXME: sub_id - ���������� �������
    $referer = arg('sub_id')?$page->url(array('sub_id'=>'')):$page->url(array('id'=>''));
    $this->hidden .= "\t\t".'<input type="hidden" name="submitURL" value="'.$referer.'" />';
    $this->hidden = "\t<div class=\"hidden\">\n\t\t{$this->hidden}\n\t</div>";
    $result =
      "<form ".(empty($this->form['name'])?'':'name="'.$this->form['name'].'" id="'.$this->form['name'].'" ')."action=\"".$page->url()."\" method=\"post\"".(empty($this->onsubmit)?'':' onsubmit="return '.$this->form['name'].'Submit();"').($this->file?' enctype="multipart/form-data"':'').">\n".
      $this->hidden.
      "\n\t<table width=\"100%\">\n".
      "\t\t<tr><td style=\"height: 0px; font-size: 0px; padding: 0px;\">".img('style/dot.gif')."</td><td style=\"width: 100%; height: 0px; font-size: 0px; padding: 0px;\">".img('style/dot.gif')."</td>\n\t\t</tr>\n".
      $result.
      "\t\t<tr><td colspan=\"2\" align=\"center\"><br />".
      ((isset($this->form['buttons']) && isset($this->form['buttons']['ok']))?'<input name="form_ok" type="submit" class="button" value="'.$this->form['buttons']['ok'].'" /> ':'').
      (!isset($this->form['buttons']) || in_array('ok', $this->form['buttons'])?"<input name=\"form_ok\" type=\"submit\" class=\"button\" value=\"".strOk."\" /> ":''). # onClick=\"formOKClick('".$form['name']."')\"> ":'').

      ((isset($this->form['buttons']) && isset($this->form['buttons']['apply']))?'<input name="form_apply" type="submit" class="button" value="'.$this->form['buttons']['apply']."\" onclick=\"formApplyClick('".$this->form['name']."')\" /> ":'').
      (!isset($this->form['buttons']) || in_array('apply', $this->form['buttons'])?"<input name=\"form_apply\" type=\"submit\" class=\"button\" value=\"".strApply."\" onclick=\"formApplyClick('".$this->form['name']."')\" /> ":'').

      ((isset($this->form['buttons']) && isset($this->form['buttons']['reset']))?'<input name="form_reset" type="reset" class="button" value="'.$this->form['buttons']['reset'].'" /> ':'').
      (isset($this->form['buttons']) && in_array('reset', $this->form['buttons'])?"<input name=\"form_reset\" type=\"reset\" class=\"button\" value=\"".strReset."\" /> ":'').

      ((isset($this->form['buttons']) && isset($this->form['buttons']['cancel']) && (!is_array($this->form['buttons']['cancel'])))?'<input name="form_cancel" type="button" class="button" value="'.$this->form['buttons']['cancel']."\" onclick=\"javascript:history.back();\" /> ":'').
      ((!isset($this->form['buttons']) || (in_array('cancel', $this->form['buttons'])))?"<input name=\"form_cancel\" type=\"button\" class=\"button\" value=\"".strCancel."\" onclick=\"javascript:history.back();\" />":'').
      ((isset($this->form['buttons']['cancel']) && (is_array($this->form['buttons']['cancel'])))?"<input name=\"form_cancel\" type=\"button\" class=\"button\" value=\"".$this->form['buttons']['cancel']['label']."\" onclick=\"window.location.href='".$this->form['buttons']['cancel']['url']."'\" />":'').

      "</td>\n\t\t</tr>\n".
      "\t</table>\n</form>\n";

    return $result;
  }
  //------------------------------------------------------------------------------
}

/**
* ������������ ����� �� ������ �������
*
* @access  public
*
* @param  array  $form    �������� �����
* @param  array  $values  �������� ����� �� ��������� (�������������)
*
* @return  string  HTML-��� �����
*/
function form($form, $values=array())
{
  $Form = new Form($form, $values);
  $result = $Form->render();
  return $result;
}
//------------------------------------------------------------------------------
?>