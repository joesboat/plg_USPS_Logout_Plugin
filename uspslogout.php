<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Plugin class for logout redirect handling.
 *
 * @package		Joomla.Plugin
 * @subpackage	System.logout
 */
jimport('usps.includes.routines');
class plgSystemUSPSLogout extends JPlugin
{
	/**
	 * Object Constructor.
	 *
	 * @access	public
	 * @param	object	The object to observe -- event dispatcher.
	 * @param	object	The configuration object for the plugin.
	 * @return	void
	 * @since	1.5
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();

		$hash = JApplication::getHash('plgSystemLogout');
		if (JFactory::getApplication()->isSite() and JRequest::getString($hash, null , 'cookie'))
		{
			// Destroy the USPS cookie
			//$conf = JFactory::getConfig();
			//$cookie_domain 	= $conf->get('config.cookie_domain', '');
			//$cookie_path 	= $conf->get('config.cookie_path', '/');
			//setcookie($hash, false, time() - 86400, $cookie_path, $cookie_domain);
			//setrawcookie("uspscert",false,time() - 86400,'/');
				
			// Set the error handler for E_ALL to be the class handleError method.
			//JError::setErrorHandling(E_ALL, 'callback', array('plgSystemLogout', 'handleError'));
		}
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data.
	 * @param	array	$options	Array holding options (client, ...).
	 *
	 * @return	object	True on success
	 * @since	1.5
	 */
	public function onUserLogout($user, $options = array())
	{
		$params = $this->params;
		$logging = $params->get("debug");
		if ($logging) log_it(__FUNCTION__,__LINE__);
		if (JFactory::getApplication()->isSite())
		{
			$session = JFactory::getSession();
			$distno = $session->get('distno');
			if ($logging) log_it("distno = $distno - We are clearing");
			$session->clear('distno');
			$session->clear('dist_no');
			$session->clear('lat');
			$session->clear('dist_name');
			$session->clear('dist_url');
			$session->clear('lon');
			$session->clear('zoom');
			$session->clear('squad_no');
			$host = explode('.', $_SERVER['HTTP_HOST']);
			while ($host) {
			    $domain = '.' . implode('.', $host);
			    foreach ($_COOKIE as $name => $value) {
			        setcookie($name, '', 1, '/', $domain);
    			}
				array_shift($host);
			}
			if (isset($_SERVER['HTTP_COOKIE'])) {
   				$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    			foreach($cookies as $cookie) {
        			$parts = explode('=', $cookie);
        			$name = trim($parts[0]);
        			setcookie($name, '', time()-1000);
        			setcookie($name, '', time()-1000, '/');
    			}
			}
			setrawcookie("uspscert",false,time() - 86400,'/');
			setrawcookie("uspsmember",false,time() - 86400,'/');
			setrawcookie("uspsd5_Session",false,time() - 86400,'/');
		}
		return true;
	}

	static function handleError(&$error)
	{
		// Get the application object.
		$app = JFactory::getApplication();

		// Make sure the error is a 403 and we are in the frontend.
		if ($error->getCode() == 403 and $app->isSite()) {
			// Redirect to the home page
			$app->redirect('index.php', JText::_('PLG_SYSTEM_LOGOUT_REDIRECT'), null, true, false);
		}
		else {
			// Render the error page.
			JError::customErrorPage($error);
		}
	}
}
