<?php

function smarty_modifier_format_description($descr)
{
	if (empty($descr)) return '';
	if (strpos($descr, "<") === false)
	{
		return "<p>".nl2br($descr)."</p>";
	}
	return str_replace('<hr class="pinacut" />', '', $descr);
}
