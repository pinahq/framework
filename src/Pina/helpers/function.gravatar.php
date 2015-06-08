<?php


function smarty_function_gravatar($params)
{
	$url = 'http://www.gravatar.com/avatar/';

	if (empty($params['email'])) $params['email'] = '';
	
	$url .= md5(strtolower(trim($params['email'])));

	$ps = array();
	if (isset($params['size']))
	{
		$ps[] = 's='.$params['size'];
	}

	if (isset($params['rating']))
	{
		$ps[] = 'r='.$params['rating'];
	}

	if (isset($params['default'])) 
	{
		$parsed = parse_url($params['default']);
		if (!empty($parsed["host"]) && $parsed["host"] != "localhost")
		{
			$ps[] = 'd='.urlencode($params['default']);
		}
	}
	
	$ps = join("&", $ps);

	return $url.(!empty($ps)?("?".$ps):"");
}