<?php
useClass('backward/TPlugin');
/**
 *	�������	�����	���	��������,	���������������	���	��������
 *
 *
 */
class	TContentPlugin	extends	TPlugin	{
/**
 *	�����������
 *
 *	�������������	������	�	��������	�������	��������	�	������	���������	���������
 */
function	TContentPlugin()
{
	global	$page;

	parent::TPlugin();
	if	(isset($page))	{
		$page->plugin	=	$this->name;
		if	(count($page->options))	foreach	($page->options	as	$key=>$value)	$this->settings[$key]	=	$value;
	}
}
//------------------------------------------------------------------------------
/**
 *	���������	�������	��������	�	��
 *
 *	@param	string	$content	�������
 */
function	updateContent($content)
{
	global	$Eresus,	$page;

	$item	=	$Eresus->db->selectItem('pages',	"`id`='".$page->id."'");
	$item['content']	=	$content;
	$Eresus->db->updateItem('pages',	$item,	"`id`='".$page->id."'");
}
//------------------------------------------------------------------------------
/**
 *	���������	�������	��������
 */
function	update()
{
	$this->updateContent(arg('content',	'dbsafe'));
	goto(arg('submitURL'));
}
//------------------------------------------------------------------------------
/**
 *	���������	����������	�����
 *
 *	@return	string	�������
 */
function	clientRenderContent()
{
	global	$page;

	return	$page->content;
}
//------------------------------------------------------------------------------
/**
 *	���������	����������������	�����
 *
 *	@return	string	�������
 */
function	adminRenderContent()
{
	global	$page,	$Eresus;

	$item	=	$Eresus->db->selectItem('pages',	"`id`='".$page->id."'");
	$form	=	array(
		'name'	=>	'content',
		'caption'	=>	$page->title,
		'width'	=>	'100%',
		'fields'	=>	array	(
			array	('type'=>'hidden','name'=>'update'),
			array	('type'	=>	'memo',	'name'	=>	'content',	'label'	=>	strEdit,	'height'	=>	'30'),
		),
		'buttons'	=>	array('apply',	'reset'),
	);

	$result	=	$page->renderForm($form,	$item);
	return	$result;
}
//------------------------------------------------------------------------------
}
?>