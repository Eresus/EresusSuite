<?php
/**
 * Eresus 2.11
 *
 * Система управления контентом Eresus 2
 *
 * @copyright 2004-2007, ProCreat Systems, http://procreat.ru/
 * @copyright 2007-2008, Eresus Project, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо (по вашему выбору) с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 *
 * $Id$
 */

useLib('accounts');

class TUsers extends Accounts {
	var $accounts;
	var
		$access = ADMIN,
		$itemsPerPage = 30,
		$pagesDesc = false;
 /**
	* Конструктор
	*
	* @return TUsers
	*/
	function TUsers()
	{
		$this->accounts = new Accounts();
	}
	//-----------------------------------------------------------------------------
	function checkMail($mail)
	{
		$host = substr($mail, strpos($mail, '@')+1);
		$ip = gethostbyname($host);
		if ($ip == $host) {
			ErrorMessage(sprintf(errNonexistedDomain, $host));
			return false;
		}
		return true;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function notifyMessage($new, $old=null)
	{
		$result = '';
		if (is_null($old)) {
			$result .= admUsersName.": ".$new['name']."\n";
			$result .= admUsersLogin.": ".$new['login']."\n";
			$result .= admAccessLevel.": ".constant('ACCESSLEVEL'.$new['access'])."\n";
			$result .= admUsersMail.": ".$new['mail']."\n";
		} else {
			$result = "ID ".$new['id']." - <strong>".$old['name']."</strong>\n".admChanges.":\n";
			if ($new['name'] != $old['name']) $result .= admUsersName.": ".$old['name']." &rarr; ".$new['name']."\n";
			if ($new['login'] != $old['login']) $result .= admUsersLogin.": ".$old['login']." &rarr; ".$new['login']."\n";
			if ($new['active'] != $old['active']) $result .= admUsersAccountState.": ".($old['active']?strYes:strNo)." &rarr; ".($new['active']?strYes:strNo)."\n";
			if ($new['loginErrors'] != $old['loginErrors']) $result .= admUsersLoginErrors.": ".$old['loginErrors']." &rarr; ".$new['loginErrors']."\n";
			if ($new['access'] != $old['access']) $result .= admAccessLevel.": ".constant('ACCESSLEVEL'.$old['access'])." &rarr; ".constant('ACCESSLEVEL'.$new['access'])."\n";
			if ($new['mail'] != $old['mail']) $result .= admUsersMail.": ".$old['mail']." &rarr; ".$new['mail']."\n";
		}
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function check_for_root($item)
	{
		return ($item['access'] != ROOT);
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function check_for_edit($item)
	{
		global $Eresus;

		return (($item['access'] != ROOT)||($Eresus->user['id'] == $item['id'])) && UserRights(ADMIN);
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function toggle()
	{
		global $Eresus, $page;

		$item = $this->accounts->get(arg('toggle', 'int'));
		$item['active'] = !$item['active'];
		$this->accounts->update($item);
		SendNotify(($item['active']?admActivated:admDeactivated).': '.$item['name']);
		goto($page->url());
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function update()
	{
		global $Eresus, $page;

		$item = $this->accounts->get(arg('update', 'int'));
		$old = $item;
		foreach ($item as $key => $value) if (isset($Eresus->request['arg'][$key])) $item[$key] = arg($key, 'dbsafe');
		$item['active'] = $Eresus->request['arg']['active'] || ($Eresus->user['id'] == $item['id']);
		if ($this->checkMail($item['mail'])) {
			$this->accounts->update($item);
			SendNotify($this->notifyMessage($item, $old));
		};
		goto(arg('submitURL'));
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
 /**
	* Создание учётной записи
	*/
	function insert()
	{
		global $Eresus, $page;

		# Получение данных
		$item = array(
			'name' => arg('name', 'dbsafe'),
			'login' => arg('login', '/[^a-z0-9_]/'),
			'access' => arg('access', 'int'),
			'hash' => $Eresus->password_hash(arg('pswd1')),
			'mail' => arg('mail', 'dbsafe'),
		);
		# Проверка входных данных
		$error = false;
		if (empty($item['name'])) { ErrorMessage(admUsersNameInvalid); $error = true;}
		if (empty($item['login'])) { ErrorMessage(admUsersLoginInvalid); $error = true;}
		if ($item['access'] <= ROOT) { ErrorMessage('Invalid access level!'); $error = true;}
		if ($item['hash'] != $Eresus->password_hash(arg('pswd2'))) { ErrorMessage(admUsersConfirmInvalid); $error = true;}
		# Проверка данных на уникальность
		$check = $this->accounts->get("`login` = '{$item['login']}'");
		if ($check) { ErrorMessage(admUsersLoginExists); $error = true;}
		if ($error) {
			saveRequest();
			goto($Eresus->request['referer']);
		}
		if ($this->accounts->add($item))
			SendNotify(admUsersAdded.': '.$this->notifyMessage($item), '', false, '', $page->url(array('action'=>'')));
		else ErrorMessage('Error creating user account');
		goto(arg('submitURL'));
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function delete()
	{
		global $Eresus, $page;

		$item = $this->accounts->get(arg('delete', 'int'));
		$this->accounts->delete(arg('delete', 'int'));
		SendNotify(admDeleted.': '.$this->notifyMessage($item));
		goto($page->url());
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function password()
	{
		global $Eresus, $page;

		$item = $this->accounts->get(arg('password', 'int'));
		if (arg('pswd1') == arg('pswd2')) {
			$item['hash'] = $Eresus->password_hash(arg('pswd1'));
			$this->accounts->update($item);
			SendNotify(admUsersPasswordChanged.': '.$item['name']);
		}
		goto(arg('submitURL'));
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function edit()
	{
		global $Eresus, $page;

		$item = $Eresus->db->selectItem('users', "`id`='".arg('id')."'");
		$form = array(
			'name' => 'UserForm',
			'caption' => admUsersChangeUser.' №'.$item['id'],
			'width' => '400px',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$item['id']),
				array('type'=>'edit','name'=>'name','label'=>admUsersName,'maxlength'=>32,'width'=>'100%','value'=>$item['name'], 'pattern'=>'/.+/', 'errormsg'=>admUsersNameInvalid),
				array('type'=>'edit','name'=>'login','label'=>admUsersLogin,'maxlength'=>16,'width'=>'100%','value'=>$item['login'], 'pattern'=>'/^[a-z\d_]+$/', 'errormsg'=>admUsersLoginInvalid, 'access'=>ADMIN),
				array('type'=>'select','name'=>'access','label'=>admAccessLevel, 'values'=>array('2','3','4'),'items'=>array(ACCESSLEVEL2, ACCESSLEVEL3, ACCESSLEVEL4), 'value'=>$item['access'], 'disabled'=>$item['access'] == ROOT, 'access'=>ADMIN),
				array('type'=>'checkbox','name'=>'active','label'=>admUsersAccountState,'value'=>$item['active'], 'access'=>ADMIN),
				array('type'=>'edit','name'=>'loginErrors','label'=>admUsersLoginErrors,'maxlength'=>2,'width'=>'30px','value'=>$item['loginErrors'], 'access'=>ADMIN),
				array('type'=>'edit','name'=>'mail','label'=>admUsersMail,'maxlength'=>32,'width'=>'100%','value'=>$item['mail'], 'pattern'=>'/^[\w]+[\w\d_\.\-]+@[\w\d\-]{2,}\.[a-z]{2,5}$/i', 'errormsg'=>admUsersMailInvalid, 'access'=>ADMIN),
			),
			'buttons' => array(UserRights($this->access)?'ok':'', 'apply', 'cancel'),
		);

		$pswd = array(
			'name' => 'PasswordForm',
			'caption' => admUsersChangePassword,
			'width' => '400px',
			'fields' => array (
				array('type'=>'hidden','name'=>'password', 'value'=>$item['id']),
				array('type'=>'password','name'=>'pswd1','label'=>admUsersPassword,'maxlength'=>32,'width'=>'100%'),
				array('type'=>'password','name'=>'pswd2','label'=>admUsersConfirmation,'maxlength'=>32,'width'=>'100%', 'equal'=>'pswd1', 'errormsg'=>admUsersConfirmInvalid),
			),
			'buttons' => array(UserRights($this->access)?'ok':'apply', 'cancel'),
		);

		$result = $page->renderForm($form)."<br />\n".$page->renderForm($pswd);
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function create()
	{
		global $Eresus, $page;

		restoreRequest();
		$form = array(
			'name'=>'UserForm',
			'caption' => admUsersCreate,
			'width' => '400px',
			'fields' => array (
				array('type'=>'hidden','name'=>'action','value'=>'insert'),
				array('type'=>'edit','name'=>'name','label'=>admUsersName,'maxlength'=>32,'width'=>'100%', 'pattern'=>'/.+/', 'errormsg'=>admUsersNameInvalid),
				array('type'=>'edit','name'=>'login','label'=>admUsersLogin,'maxlength'=>16,'width'=>'100%', 'pattern'=>'/^[a-z0-9_]+$/i', 'errormsg'=>admUsersLoginInvalid),
				array('type'=>'select','name'=>'access','label'=>admAccessLevel, 'width'=>'100%','values'=>array('2','3','4'),'items'=>array(ACCESSLEVEL2,ACCESSLEVEL3,ACCESSLEVEL4), 'default' => USER),
				array('type'=>'checkbox','name'=>'active','label'=>admUsersAccountState, 'default' => true),
				array('type'=>'divider'),
				array('type'=>'password','name'=>'pswd1','label'=>admUsersPassword,'maxlength'=>32,'width'=>'100%'),
				array('type'=>'password','name'=>'pswd2','label'=>admUsersConfirmation,'maxlength'=>32,'width'=>'100%', 'equal'=>'pswd1', 'errormsg'=>admUsersConfirmInvalid),
				array('type'=>'divider'),
				array('type'=>'edit','name'=>'mail','label'=>admUsersMail,'maxlength'=>32,'width'=>'100%'),
			),
			'buttons'=>array('ok', 'cancel')
		);
		$result = $page->renderForm($form, $Eresus->request['arg']);
		return $result;
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
	function adminRender()
	{
		global $Eresus, $page;

		$result = '';
		$granted = false;
		if (UserRights($this->access)) $granted = true; else {
			if (arg('id') == $Eresus->user['id']) {
				if (!arg('password') || (arg('password') == $Eresus->user['id'])) $granted = true;
				if (!arg('update') || (arg('update') == $Eresus->user['id'])) $granted = true;
			}
		}
		if ($granted) {
			if (arg('update')) $this->update();
			elseif (isset($Eresus->request['arg']['password'])  && (!isset($Eresus->request['arg']['action']) || ($Eresus->request['arg']['action'] != 'login'))) $this->password();
			elseif (isset($Eresus->request['arg']['toggle'])) $this->toggle();
			elseif (isset($Eresus->request['arg']['delete'])) $this->delete();
			elseif (isset($Eresus->request['arg']['id'])) $result = $this->edit();
			elseif (isset($Eresus->request['arg']['action'])) switch(arg('action')) {
				case 'create': $result = $this->create(); break;
				case 'insert': $this->insert(); break;
			} else {
				$table = array (
					'name' => 'users',
					'key'=>'id',
					'itemsPerPage' => 20,
					'columns' => array(
						array('name' => 'id', 'caption' => 'ID', 'align' => 'right', 'width' => '40px'),
						array('name' => 'name', 'caption' => admUsersName, 'align' => 'left'),
						array('name' => 'access', 'caption' => admUsersAccessLevelShort, 'align' => 'center', 'width' => '70px', 'replace' => array (
							'1' => '<span style="font-weight: bold; color: red;">ROOT</span>',
							'2' => '<span style="font-weight: bold; color: red;">admin</span>',
							'3' => '<span style="font-weight: bold; color: blue;">editor</span>',
							'4' => 'user'
						)),
						array('name' => 'login', 'caption' => admUsersLogin, 'align' => 'left'),
						array('name' => 'mail', 'caption' => admUsersMail, 'align' => 'center', 'macros'=>true, 'value'=>'<a href="mailto:$(mail)">$(mail)</a>'),
						array('name' => 'lastVisit', 'caption' => admUsersLastVisitShort, 'align' => 'center', 'width' => '140px'),
						array('name' => 'loginErrors', 'caption' => admUsersLoginErrorsShort, 'align' => 'center', 'replace' => array (
							'0' => '',
						)),
					),
					'controls' => array (
						'delete' => 'check_for_root',
						'edit' => 'check_for_edit',
						'toggle' => 'check_for_root',
					),
					'tabs' => array(
						'width'=>'180px',
						'items'=>array(
						 array('caption'=>admUsersCreate, 'name'=>'action', 'value'=>'create')
						)
					)
				);
				$result = $page->renderTable($table);
			}
			return $result;
		}
	}
	#--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}
