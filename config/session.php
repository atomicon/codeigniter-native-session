<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

//The session name (leave empty for cross application sessions) 
$config['sess_name']       = '';

//Time to expire a session AND/OR regenerate the session id
$config['sess_expiration'] = 7200;

//If you want to change the session id every 'sess_expiration' seconds
//turn this to true
$config['sess_regenerate'] = FALSE;

//The flashdata key (this only applies to flashmessages)
$config['sess_flash_key']  = 'flash';