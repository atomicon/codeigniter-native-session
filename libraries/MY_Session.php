<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
* Session class using native PHP session.
*
* @package     CodeIgniter
* @subpackage  Libraries
* @category    Sessions
* @author      Yvo van Dillen
* @link        http://www.atomicon.nl
*/

/*
Example config file: session.php

<?php

//The session name (leave empty for cross application sessions)
$config['sess_name']       = '';

//Time to expire a session AND/OR regenerate the session id
$config['sess_expiration'] = 7200;

//If you want to change the session id every 'sess_expiration' seconds
//turn this to true
$config['sess_regenerate'] = FALSE;

//The flashdata key (this only applies to flashmessages)
$config['sess_flash_key']  = 'flash';


*/


class MY_Session
{
	function __construct()
	{
		log_message('debug', "MY_Session Class Initialized");
        get_instance()->load->config('session', FALSE, TRUE);
		$this->_sess_run();
	}

    function data()
    {
        return $_SESSION;
    }

	/**
    * Regenerates session id
    */
	function regenerate_id()
	{
		// copy old session data, including its id
		$old_session_id = session_id();
		$old_session_data = isset($_SESSION) ? $_SESSION : array();

		// regenerate session id and store it
		session_regenerate_id();
		$new_session_id = session_id();

		// switch to the old session and destroy its storage
		if (session_id($old_session_id))
        {
            session_destroy();
        }

		// switch back to the new session id and send the cookie

        if ($new_session_id)
        {
            session_id($new_session_id);
            session_start();

            // restore the old session data into the new session
            $_SESSION = $old_session_data;
        }

		// end the current session and store session data.
		session_write_close();
	}

	/**
    * Destroys the session and erases session storage
    */
	function destroy()
	{
		$_SESSION = array();
		if ( isset( $_COOKIE[session_name()] ) )
		{
			setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
	}

	function sess_create()
	{
		$this->_sess_run();
	}

	function sess_destroy()
	{
		$this->destroy();
	}

	/**
    * Reads given session attribute value
    */
	function userdata($item)
	{
		if($item == 'session_id'){ //added for backward-compatibility
			return session_id();
		}else{
			return ( ! isset($_SESSION[$item])) ? false : $_SESSION[$item];
		}
	}

	public function all_userdata()
	{
		return (array)$_SESSION;
	}

	/**
    * Sets session attributes to the given values
    */
	function set_userdata($newdata = array(), $newval = '')
	{
		if (is_string($newdata))
		{
			$newdata = array($newdata => $newval);
		}

		if (count($newdata) > 0)
		{
			foreach ($newdata as $key => $val)
			{
				$_SESSION[$key] = $val;
			}
		}
	}

	/**
    * Erases given session attributes
    */
	function unset_userdata($newdata = array())
	{
		if (is_string($newdata))
		{
			$newdata = array($newdata => '');
		}

		if (count($newdata) > 0)
		{
			foreach ($newdata as $key => $val)
			{
				unset($_SESSION[$key]);
			}
		}
	}

	/**
    * Starts up the session system for current request
    */
	function _sess_run()
	{
		if (config_item('sess_name'))
		{
			session_name(config_item('sess_name'));
		}

		if (session_id()=='')
		{
			session_start();
		}

		// check if session id needs regeneration
		if ( $this->_session_id_expired() )
		{
			// regenerate session id (session data stays the
			// same, but old session storage is destroyed)
			if (config_item('sess_regenerate'))
			{
				$this->regenerate_id();
				return;
			}
		}

		// delete old flashdata (from last request)
		$this->_flashdata_sweep();

		// mark all new flashdata as old (data will be deleted before next request)
		$this->_flashdata_mark();
	}

	/**
    * Checks if session has expired
    */
	function _session_id_expired()
	{
        $sess_expiration = config_item('sess_expiration');
        if (is_numeric($sess_expiration) && $sess_expiration > 0)
		{
            if (config_item('sess_regenerate'))
            {
                if ( !isset($_SESSION['_sess:last-generated']) )
                {
                    $_SESSION['_sess:last-generated'] = time();
                    return false;
                }
                else
                {
                    $expiry_time = $_SESSION['_sess:last-generated'] + $sess_expiration;
                    if (time() >= $expiry_time)
                    {
                        return true;
                    }
                }
            }
            else
            {
            	if (isset($_SESSION['_sess:last-activation']))
	            {
	                $expiry_time = $_SESSION['_sess:last-activation'] + $sess_expiration;
	                if (time() >= $expiry_time)
	                {
	                    $this->destroy();
	                    return true;
	                }
	            }
	            $_SESSION['_sess:last-activation'] = time();
            }
        }
		return false;
	}

	/**
    * Sets "flash" data which will be available only in next request (then it will
    * be deleted from session). You can use it to implement "Save succeeded" messages
    * after redirect.
    */
	function set_flashdata($key, $value)
	{
		$flash_key = config_item('sess_flash_key').':new:'.$key;
		$this->set_userdata($flash_key, $value);
	}

	/**
    * Keeps existing "flash" data available to next request.
    */
	function keep_flashdata($key)
	{
		$old_flash_key = config_item('sess_flash_key').':old:'.$key;
		$value = $this->userdata($old_flash_key);

		$new_flash_key = config_item('sess_flash_key').':new:'.$key;
		$this->set_userdata($new_flash_key, $value);
	}

	/**
    * Returns "flash" data for the given key.
    */
	function flashdata($key)
	{
		$flash_key = config_item('sess_flash_key').':old:'.$key;
		return $this->userdata($flash_key);
	}

	/**
    * PRIVATE: Internal method - marks "flash" session attributes as 'old'
    */
	function _flashdata_mark()
	{
		foreach ($_SESSION as $name => $value)
		{
			$parts = explode(':new:', $name);
			if (is_array($parts) && count($parts) == 2)
			{
				$new_name = config_item('sess_flash_key').':old:'.$parts[1];
				$this->set_userdata($new_name, $value);
				$this->unset_userdata($name);
			}
		}
	}

	/**
    * PRIVATE: Internal method - removes "flash" session marked as 'old'
    */
	function _flashdata_sweep()
	{
		foreach ($_SESSION as $name => $value)
		{
			$parts = explode(':old:', $name);
			if (is_array($parts) && count($parts) == 2 && $parts[0] == config_item('sess_flash_key'))
			{
				$this->unset_userdata($name);
			}
		}
	}
}
?>