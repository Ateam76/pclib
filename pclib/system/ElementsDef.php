<?php 
/**
 * @file
 * ElementsDef class
 *
 * @author -dk- <lenochware@gmail.com>
 * @link http://pclib.brambor.net/
 */

# This library is free software; you can redistribute it and/or
# modify it under the terms of the GNU Lesser General Public
# License as published by the Free Software Foundation; either
# version 2.1 of the License, or (at your option) any later version.

namespace pclib\system;

/**
 * Elements definitions.
 */
class ElementsDef extends BaseObject
{
	static $elem = [
		'base' => [
			'block' => null,
			'default' => null,
			'noprint' => null,
			'onprint' => null,
			'escape' => null,
			'noescape' => null,
			'attr' => null,
			'html' => null,
			'lb' => null,
			'skip' => null,
		],
		'base_form' => [
			'size' => null,
			'hidden' => null,
			'required' => null,
			'hint' => null,
			'ajaxget' => null,
		],		
		'string' => [
			'format' => null,
			'tooltip' => null,
		],
		'number' => [
			'format' => null,
		],
		'head' => [
			'noversion' => null,
			'inline' => null,
		],
		'class_tpl' => [
		],
		'class_grid' => [
			'href' => null,
			'action' => null,
			'route' => null,
			'singlepage' => null,
		],
		'class_form' => [
			'href' => null,
			'action' => null,
			'route' => null,
			'ajaxget' => null,
			'ajax' => null,
			'submitted' => null,
			'noformtag' => null,
			'table' => null,
			'get' => null,
			'jsvalid' => null,
			'default_print' => null,
		],
		'class_gridform' => [
			'href' => null,
			'action' => null,
			'route' => null,
			'singlepage' => null,
			'submitted' => null,
			'noformtag' => null,
			'table' => null,
			'get' => null,
			'jsvalid' => null,
			'default_print' => null,
		],		
		'pager' => [
			'ul' => null,
			'size' => null,
			'nohide' => null,
			'pglen' => null,
		],
		'bind' => [
			'bitfield' => null,
			'format' => null,
			'list' => null,
			'query' => null,
			'lookup' => null,
			'datasource' => null,
			'emptylb' => null,
			'columns' => null,
			'noemptylb' => null,
		],
		'link' => [
			'href' => null,
			'action' => null,
			'route' => null,
			'img' => null,
			'popup' => null,
			'field' => null,
			'confirm' => null,
		],
		'button' => [
			'href' => null,
			'action' => null,
			'route' => null,
			'img' => null,
			'glyph' => null,
			'popup' => null,
			'field' => null,
			'confirm' => null,
			'tag' => null,
			'onclick' => null,
			'submit' => null,
		],
		'input' => [
			'date' => null,
			'file' => null,
			'multiple' => null,
			'maxlength' => null,
			'password' => null,
			'email' => null,
			'number' => null,
			'pattern' => null,
			'range' => null,
		],
		'text' => [
			'maxlength' => null,
		],
		'listinput' => [
			'maxlength' => null,
		],
	];


	static function getElement($id, $type, $templateClass)
	{
		if ($type == 'class') {
			$elem = self::$elem['class_'.$templateClass];
		}
		else {
			$lkpType = in_array($type, ['select', 'radio', 'check'])? 'bind' : $type;
			$elem = isset(self::$elem[$lkpType])? self::$elem[$lkpType] : [];			
		}

		$elem['id'] = $id;
		$elem['type'] = $type;
		
		if ($templateClass == 'form' or $templateClass == 'gridform') {
			$elem = self::$elem['base_form'] + $elem;
		}

		return self::$elem['base'] + $elem;
	}

}