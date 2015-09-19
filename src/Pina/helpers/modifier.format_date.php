<?php

use Pina\Config;
use Pina\Date;

function smarty_modifier_format_date($date)
{
	return Date::format($date);
}