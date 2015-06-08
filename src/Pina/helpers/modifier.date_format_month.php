<?php

	function smarty_modifier_date_format_month($date)
	{
		$date = trim($date);
		if(empty($date) || preg_match('#^\d{4}-(\d{2})-(\d{2})\s+\d{2}:\d{2}:\d{2}$#', $date, $matches) == 0)
		{
			return $date;
		}

		$monthNumber = (int)$matches[1];
		$dayNumber = (int)$matches[2];

		$monthes = array(
			1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
		);

		if(!isset($monthes[$monthNumber]))
		{
			return $date;
		}

		return $dayNumber .' '. $monthes[$monthNumber];
	}