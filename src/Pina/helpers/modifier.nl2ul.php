<?php

function smarty_modifier_nl2ul($data)
{
	if (empty($data)) return '';
	
	if (strpos($data, "<ul") === false)
	{
		$r = "<ul>";
		$a = explode("\n", $data);
		foreach ($a as $v)
		{
			$v = trim($v);
			if (empty($v)) continue;
			$r .= '<li>'.$v.'</li>';
		}
		$r .= "</ul>";
		return $r;
	}
	return $data;
}
