<?php
/**
 * Eresus 2.10
 *
 * Управление конфигурацией
 *
 * Система управления контентом Eresus™ 2
 * © 2004-2007, ProCreat Systems, http://procreat.ru/
 * © 2007-2008, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 */

class TSettings {
  var $access = ADMIN;
  var $tabs = array(
    'width'=>'120px',
    'items'=>array(
      array('caption'=>admSettingsMain, 'name'=>'section', 'value'=>'main'),
      array('caption'=>admSettingsMail, 'name'=>'section', 'value'=>'mail'),
      array('caption'=>admSettingsFiles, 'name'=>'section', 'value'=>'files'),
      array('caption'=>admSettingsOther, 'name'=>'section', 'value'=>'other'),
    )
  );
  var $notify = '';
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function mkstr($name, $caption, $type='string', $options=array())
  {
    global $Eresus, $locale;

    $result = "  define('".(isset($options['locale'])?($options['locale']?$locale['prefix']:''):'').$name."', ";
    $quot = "'";
    $value = is_null(arg($name)) ? option($name) : arg($name);
    if (isset($options['nobr']) && $options['nobr']) $value = str_replace(array("\n", "\r"), ' ', $value);
    if (isset($options['savebr']) && $options['savebr']) {
      $value = addcslashes($value, "\n\r\"");
      #$value = str_replace('\\','\\\\', $value);
      $quot = '"';
    }
    if ($value != option($name)) $this->notify .= '<strong>'.$caption.':</strong> "'.option($name).'" &rarr; "'.$value."\"\n";
    switch ($type) {
      case 'string': $value = $quot.$value.$quot; break;
      case 'bool': $value = empty($value)?'false':'true'; break;
      case 'int': if (empty($value)) $value = 0; break;
    }
    $result .= $value.");\n";
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function update()
  {

    $settings = "<?php\n";

    $settings .= $this->mkstr('siteName', admConfigSiteName, 'string', array('locale'=>true));
    $settings .= $this->mkstr('siteTitle', admConfigSiteTitle, 'string', array('locale'=>true));
    $settings .= $this->mkstr('siteTitleReverse', admConfigTitleReverse, 'bool');
    $settings .= $this->mkstr('siteTitleDivider', admConfigTitleDivider, 'string');
    $settings .= $this->mkstr('siteKeywords', admConfigSiteKeywords, 'string', array('nobr'=>true));
    $settings .= $this->mkstr('siteDescription', admConfigSiteDescription, 'string', array('locale'=>true, 'nobr'=>true));
    $settings .= $this->mkstr('mailFromAddr', admConfigMailFromAddr, 'string', array('locale'=>true));
    $settings .= $this->mkstr('mailFromName', admConfigMailFromName, 'string', array('locale'=>true));
    $settings .= $this->mkstr('mailFromOrg', admConfigMailFromOrg, 'string', array('locale'=>true));
    $settings .= $this->mkstr('mailReplyTo', admConfigMailReplyTo, 'string', array('locale'=>true));
    $settings .= $this->mkstr('mailCharset', admConfigMailCharset, 'string', array('locale'=>true));
    $settings .= $this->mkstr('mailFromSign', admConfigMailSign, 'string', array('locale'=>true,'savebr'=>true));
    $settings .= $this->mkstr('sendNotifyTo', admConfigSendNotifyTo, 'string');
    $settings .= $this->mkstr('filesOwnerSetOnUpload', admConfigFilesOwnerSetOnUpload, 'bool');
    $settings .= $this->mkstr('filesOwnerDefault', admConfigFilesOwnerDefault, 'string');
    $settings .= $this->mkstr('filesModeSetOnUpload', admConfigFilesModeSetOnUpload, 'bool');
    $settings .= $this->mkstr('filesModeDefault', admConfigFilesModeDefault, 'string');
    $settings .= $this->mkstr('filesTranslitNames', admConfigTranslitNames, 'bool');
    $settings .= $this->mkstr('contentTypeDefault', admConfigDefaultContentType, 'string');
    $settings .= $this->mkstr('pageTemplateDefault', admConfigDefaultPageTamplate, 'string');
    $settings .= $this->mkstr('clientPagesAtOnce', admConfigClientPagesAtOnce.admConfigClientPagesAtOnceComment, 'string');

    $settings .= "?>";
    $fp = fopen(filesRoot.'cfg/settings.inc', 'w');
    fwrite($fp, $settings);
    fclose($fp);
    SendNotify(str_replace(array('<?', '?>'), '', $settings));
    goto(arg('submitURL'));
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function sectionMain()
  {
  global $locale, $page;

    $page->title .= admTDiv.admSettingsMain;
    $form = array(
      'name' => 'settingsForm',
      'caption' => $page->title,
      'width' => '100%',
      'fields' => array (
        array('type'=>'hidden','name'=>'action', 'value'=>'update'),
        array('type'=>'edit','name'=>'siteName','label'=>admConfigSiteName,'width'=>'100%','value'=>option($locale['prefix'].'siteName'), 'hint'=>admConfigSiteNameHint, 'access'=>ADMIN),
        array('type'=>'edit','name'=>'siteTitle','label'=>admConfigSiteTitle,'width'=>'100%','value'=>option($locale['prefix'].'siteTitle'), 'hint'=>admConfigSiteTitleHint),
        array('type'=>'checkbox','name'=>'siteTitleReverse','label'=>admConfigTitleReverse, 'value'=>option('siteTitleReverse')),
        array('type'=>'edit','name'=>'siteTitleDivider','label'=>admConfigTitleDivider, 'value'=>option('siteTitleDivider'), 'width' => '50px', 'hint'=>admConfigTitleDividerHint),
        array('type'=>'memo','name'=>'siteKeywords','label'=>admConfigSiteKeywords, 'value'=>option($locale['prefix'].'siteKeywords'), 'height'=>'3', 'hint'=>admConfigKeywordsHint),
        array('type'=>'memo','name'=>'siteDescription','label'=>admConfigSiteDescription, 'value'=>option($locale['prefix'].'siteDescription'), 'height'=>'3', 'hint'=>admConfigDescriptionHint),
      ),
      'buttons' => array('apply','reset'),
    );
    $result = $page->renderForm($form);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function sectionMail()
  {
  global $locale, $page;

    $page->title .= admTDiv.admSettingsMail;
    $form = array(
      'name' => 'settingsForm',
      'caption' => $page->title,
      'width' => '100%',
      'fields' => array (
        array('type'=>'hidden','name'=>'action', 'value'=>'update'),
        array('type'=>'edit','name'=>'mailFromAddr','label'=>admConfigMailFromAddr, 'width'=>'100%', 'value'=>option('mailFromAddr'), 'hint'=>admConfigMailFromAddrHint, 'access'=>ADMIN),
        array('type'=>'edit','name'=>'mailFromName','label'=>admConfigMailFromName, 'width'=>'100%', 'value'=>option($locale['prefix'].'mailFromName'), 'hint'=>admConfigMailFromNameHint, 'access'=>ADMIN),
        array('type'=>'edit','name'=>'mailFromOrg','label'=>admConfigMailFromOrg, 'width'=>'100%', 'value'=>option($locale['prefix'].'mailFromOrg'), 'hint'=>admConfigMailFromOrgHint, 'access'=>ADMIN),
        array('type'=>'edit','name'=>'mailReplyTo','label'=>admConfigMailReplyTo, 'width'=>'100%', 'value'=>option('mailReplyTo'), 'access'=>ADMIN),
        array('type'=>'edit','name'=>'mailCharset','label'=>admConfigMailCharset, 'width'=>'100%', 'value'=>option($locale['prefix'].'mailCharset'), 'access'=>ADMIN),
        array('type'=>'memo','name'=>'mailFromSign','label'=>admConfigMailSign,'height'=>'5','value'=>option($locale['prefix'].'mailFromSign'), 'access'=>ADMIN),
        array('type'=>'header','value'=>admConfigNotifications, 'access'=>ROOT),
        array('type'=>'edit','name'=>'sendNotifyTo','label'=>admConfigSendNotifyTo, 'width'=>'100%', 'value'=>option('sendNotifyTo'), 'hint'=>admConfigSendNotifyToHint, 'access'=>ROOT),
      ),
      'buttons' => array('apply','reset'),
    );
    $result = $page->renderForm($form);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function sectionFiles()
  {
  global $locale, $page;

    $page->title .= admTDiv.admSettingsFiles;
    $form = array(
      'name' => 'settingsForm',
      'caption' => $page->title,
      'width' => '100%',
      'fields' => array (
        array('type'=>'hidden','name'=>'action', 'value'=>'update'),
        array('type'=>'checkbox','name'=>'filesOwnerSetOnUpload','label'=>admConfigFilesOwnerSetOnUpload, 'value'=>option('filesOwnerSetOnUpload')),
        array('type'=>'edit','name'=>'filesOwnerDefault','label'=>admConfigFilesOwnerDefault, 'width'=>'100px', 'value'=>option('filesOwnerDefault')),
        array('type'=>'checkbox','name'=>'filesModeSetOnUpload','label'=>admConfigFilesModeSetOnUpload, 'value'=>option('filesModeSetOnUpload')),
        array('type'=>'edit','name'=>'filesModeDefault','label'=>admConfigFilesModeDefault, 'width'=>'100px', 'value'=>option('filesModeDefault')),
        array('type'=>'checkbox','name'=>'filesTranslitNames','label'=>admConfigTranslitNames, 'value'=>option('filesTranslitNames')),
      ),
      'buttons' => array('apply','reset'),
    );
    $result = $page->renderForm($form);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function sectionOther()
  {
  global $Eresus, $locale, $page;

    $page->title .= admTDiv.admSettingsOther;

    # Создаем список типов контента
    $content_items = array();
    $content_values = array();
    $content_items[] = admPagesContentDefault; $content_values[] = 'default';
    $content_items[] = admPagesContentList; $content_values[] = 'list';
    $content_items[] = admPagesContentURL; $content_values[] = 'url';
    if(count($Eresus->plugins->list)) foreach($Eresus->plugins->list as $plugin) if (strpos($plugin['type'], 'content') !== false) {
      $content_items[] = $plugin['title'];
      $content_values[] = $plugin['name'];
    }
    # Загружаем список шаблонов
    useLib('templates');
    $templates = new Templates();
    $list = $templates->enum();
    $templates = array();
    $templates[0]= array_values($list);
    $templates[1]= array_keys($list);

    $form = array(
      'name' => 'settingsForm',
      'caption' => $page->title,
      'width' => '100%',
      'fields' => array (
        array('type'=>'hidden','name'=>'action', 'value'=>'update'),
        array('type'=>'select','name'=>'contentTypeDefault','label'=>admConfigDefaultContentType, 'items' => $content_items, 'values' => $content_values, 'value'=>option('contentTypeDefault'), 'access'=>ADMIN),
        array('type'=>'select','name'=>'pageTemplateDefault','label'=>admConfigDefaultPageTamplate, 'items' => $templates[0], 'values' => $templates[1], 'value'=>option('pageTemplateDefault'), 'access'=>ADMIN),
        array('type'=>'edit','name'=>'clientPagesAtOnce','label'=>admConfigClientPagesAtOnce, 'width' => '20px', 'value'=>option('clientPagesAtOnce'), 'access'=>ADMIN, 'comment' => admConfigClientPagesAtOnceComment),
      ),
      'buttons' => array('apply','reset'),
    );
    $result = $page->renderForm($form);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function adminRender()
  {
  global $Eresus, $page;

    $result = '';
    if (UserRights($this->access)) {
      if (arg('action')) {
        switch(arg('action')) {
          case 'update': $this->update(); break;
        }
      } else {
        $result .= $page->renderTabs($this->tabs);
        if (!isset($Eresus->request['arg']['section'])) $Eresus->request['arg']['section'] = 'main';
        switch (arg('section')) {
          case 'other': $result .= $this->sectionOther(); break;
          case 'files': $result .= $this->sectionFiles(); break;
          case 'mail': $result .= $this->sectionMail(); break;
          case 'main': default: $result .= $this->sectionMain(); break;
        }
      }
    }
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}
?>