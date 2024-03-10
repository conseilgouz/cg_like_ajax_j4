<?php 
/**
 * File       cg_like_ajax.php for Joomla 4.x/5.x
 * Author     ConseilGouz
 * Support    https://www.conseilgouz.com
 * Copyright  Copyright (C) 2024 ConseilGouz. All Rights Reserved.
 * License    GNU GPL v3 or later
 */
namespace ConseilGouz\Plugin\Ajax\Cglike\Extension;

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Event\SubscriberInterface;
use Joomla\Database\DatabaseAwareTrait;

class Cglike extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

	protected $autoloadLanguage = true;
    
    public static function getSubscribedEvents(): array
    {
        return [
            'onAjaxCglike'   => 'goAjax',
        ];
    }
	function goAjax($event) {
		$input	= Factory::getApplication()->input;
		$id  = $input->get('id', '', 'integer');
		$out = "";
		if (!self::cookie($id)) {// cookie exist => exit
		    $out .='{"ret":"9","msg":"'.Text::_("CG_AJAX_ALREADY").'"}';
		    return  $event->addResult($out);
		}
		$plugin = PluginHelper::getPlugin('content', 'cglike');
		$params = new Registry($plugin->params);
		self::setcookie($id,$params);
		if (!self::addOne($id)) {
			$out .= '{"ret":"9","msg":"'.Text::_("CG_AJAX_SQL_ERROR").'"}';
			return  $event->addResult($out);
		}
		$count = self::countId($id);
		$out .='{"ret":"0","msg":"'.Text::_("CG_AJAX_THANKS").'","cnt":"'.$count.'"}';
		return  $event->addResult($out);
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
		$db    = $this->getDatabase();
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
		$db  = $this->getDatabase();
		$query = $db->getQuery(true);
		$query   ->select( 'COUNT(id)') 
				->from($db->quoteName('#__cg_like'))
				->where($db->quoteName('cid'). '='.$db->quote($id));
		$db->setQuery($query);
		$results = $db->loadResult();		
		return $results;
	}

}
