<?php 
defined('_JEXEC') or die;
/**
 * File       cg_like_ajax.php for Joomla 4.x/5.x
 * Author     ConseilGouz
 * Support    https://www.conseilgouz.com
 * Copyright  Copyright (C) 2023 ConseilGouz. All Rights Reserved.
 * License    GNU GPL v3 or later
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

class plgAjaxCGLike extends CMSPlugin
{
	private $min_php_version         = '7.4';
	protected $autoloadLanguage = true;
	
	function onAjaxCglike()	{
		$input	= Factory::getApplication()->input;
		$id  = $input->get('id', '', 'integer');
		$out = "";
		if (!self::cookie($id)) {// cookie exist => exit
		    $out .='{"ret":"9","msg":"'.Text::_("CG_AJAX_ALREADY").'"}';
		    return $out;
		}
		$plugin = PluginHelper::getPlugin('content', 'cglike');
		$params = new Registry($plugin->params);
		self::setcookie($id,$params);
		if (!self::addOne($id)) {
			$out .= '{"ret":"9","msg":"'.Text::_("CG_AJAX_SQL_ERROR").'"}';
			return $out;
		}
		$count = self::countId($id);
		$out .='{"ret":"0","msg":"'.Text::_("CG_AJAX_THANKS").'","cnt":"'.$count.'"}';
		return $out;
	}
	function cookie($id) {
		$jinput = Factory::getApplication()->input;
		$cookieName = 'cg_like_'.$id;
		$value = $jinput->cookie->get($cookieName);
		if ($value)  { // cookie exist
			return false;
		}
		return true;
	}
	function setcookie($id,$params) {
		$duration = $params->get('voteagain','0'); // duree de vie du cookie (0 => pas de cookie, pour debug/demo)
		$name = "cg_like_".$id;
		$value = date("Y-m-d");
		$expire =  time()+3600*24*$duration;
		$path = "/";
		$domain = "";
		$secure = true; // assume https
		if (array_key_exists("HTTPS",$_SERVER)) {
			$secure = $_SERVER["HTTPS"] ? true : false;
		}
		$httponly = false;
		if (PHP_VERSION_ID < 70300) {
			setcookie($name, $value, $expire, "$path; samesite=Lax", $domain, $secure, $httponly);
		}
		else {
			$res= setcookie($name, $value, [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'samesite' => 'Lax',
            'secure' => $secure,
            'httponly' => $httponly,
			]);
		}	
	}
	function addOne($id) {
		$db		= Factory::getDbo();
		$query = $db->getQuery(true);
		$query->insert('#__cg_like');
		$query->set('cid = '.$db->quote($id));
		$query->set('lastdate = NOW()');
		$db->setQuery( (string)$query );	
		if (!$db->execute()) {
			return false;
		}
		return true;
	}
	function countId($id) {
		$db		= Factory::getDbo();
		$query = $db->getQuery(true);
		$query   ->select( 'COUNT(id)') 
				->from($db->quoteName('#__cg_like'))
				->where($db->quoteName('cid'). '='.$db->quote($id));
		$db->setQuery($query);
		$results = $db->loadResult();		
		return $results;
	}

}
