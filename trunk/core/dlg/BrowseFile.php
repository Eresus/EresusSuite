<?
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# Система управления контентом Eresus™
# Версия 2.10.1
# © 2004-2007, ProCreat Systems
# © 2007-2008, Eresus Group
# http://eresus.ru/
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

$icons = array(
  array('ext'=>'php|inc|js','icon'=>'script'),
  array('ext'=>'jpg|jpeg','icon'=>'jpeg'),
  array('ext'=>'gif','icon'=>'gif'),
  array('ext'=>'bmp','icon'=>'bmp'),
  array('ext'=>'swf','icon'=>'flash'),
  array('ext'=>'htm|html|shtml','icon'=>'html'),
  array('ext'=>'wav|mid|mp3','icon'=>'audio'),
  array('ext'=>'avi|mov|mpg|mpeg','icon'=>'video'),
  array('ext'=>'txt','icon'=>'text'),
  array('ext'=>'exe','icon'=>'app'),
  array('ext'=>'rar','icon'=>'rar'),
  array('ext'=>'zip','icon'=>'zip'),
  array('ext'=>'doc','icon'=>'word'),
  array('ext'=>'xls','icon'=>'excel'),
  array('ext'=>'pdf','icon'=>'pdf'),
);

#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
function files_compare($a, $b)
{
  if ($a['filename'] == $b['filename']) return 0;
  return ($a['filename'] < $b['filename']) ? -1 : 1;
}
#--------------------------------------------------------------------------------------------------------------------------------------------------------------#

require_once('../kernel.php');

$uRoot = UserRights(ADMIN)?'':'data/';

if (count($_FILES)) {
  parse_str($Eresus->request['referer'], $Eresus->request['arg']);
  $root = arg('root');
  if (substr($root, 0, 1) == '/') $root = substr($root, 1);
  $folder = filesRoot.$uRoot.$root.(empty($root)?'':'/');
  upload('file', $folder);
  goto($Eresus->request['referer']);
}
if (arg('folder')) {
  $folder = arg('folder');
  parse_str($Eresus->request['referer'], $Eresus->request['arg']);
  $root = arg('root');
  if ($root[0] == '/') $root = substr($root, 1);
  $folder = filesRoot.$uRoot.$root.(empty($root)?'':'/').$folder;
  umask(0000);
  mkdir($folder, 0777);
  goto($Eresus->request['referer']);
}


$content = '';

$root = filesRoot.$uRoot.arg('root');
$hnd=opendir($root);
$i = 0;
while (($name = readdir($hnd))!==false) if ($name != '.') {
  $files[$i]['filename'] = $name;
  switch (filetype($root.'/'.$name)) {
    case 'dir':
      $files[$i]['icon'] = 'folder';
    break;
    case 'file':
      $files[$i]['icon'] = 'file';
      if (count($icons)) foreach($icons as $item) if (preg_match('/\.('.$item['ext'].')$/i', $name)) {
        $files[$i]['icon'] = $item['icon'];
        break;
      }
    break;
  }
  $i++;
}
closedir($hnd);
if (count($files)) {
  usort ($files, "files_compare");
  if (count($files) > 1) {
    for ($i=1; $i<count($files); $i++) {
      if ($files[$i]['icon'] == 'folder') {
        $k = $i;
        while (($k>0)&&(($files[$k-1]['icon'] != 'folder')||(($files[$k-1]['icon'] == 'folder')&&($files[$k-1]['filename'] > $files[$k]['filename'])))) {
          $tmp = $files[$k];
          $files[$k] = $files[$k-1];
          $files[$k-1] = $tmp;
          $k--;
        }
      }
    }
  }
}

foreach($files as $file) {
 $content .= '<div>'.img('core/img/icon_'.$file['icon'].'.gif').$file['filename'].'</div>';
}

$template = file_get_contents(filesRoot.'core/dlg/BrowseFile.tmpl');

$template = str_replace(
  array(
    '$(httpRoot)',
    '$(uRoot)',
    '$(pageTitle)',
    '$(content)',
  ),array(
    httpRoot,
    httpRoot.$uRoot,
    'FileOpenDialog',
    $content,
  ),
  $template
);

echo $template;
?>