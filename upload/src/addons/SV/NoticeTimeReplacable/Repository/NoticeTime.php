<?php

namespace SV\NoticeTime\Repository;

use XF\Mvc\Entity\Repository;

class NoticeTime extends Repository
{
	public static function getRelativeDate(\DateTime $now, \DateTime $other)
	{
		$interval = $other->diff($now);
		$format = [];
		if ($interval->y)
		{
			if ($interval->y == 1)
			{
				$format[] = '%y year';
			}
			else
			{
				$format[] = '%y years';
			}
		}
		if ($interval->m)
		{
			if ($interval->m == 1)
			{
				$format[] = '%m month';
			}
			else
			{
				$format[] = '%m months';
			}
		}
		if ($interval->d)
		{
			if ($interval->d == 1)
			{
				$format[] = '%d day';
			}
			else
			{
				$format[] = '%d days';
			}
		}
		if ($interval->h)
		{
			if ($interval->h == 1)
			{
				$format[] = '%h hour';
			}
			else
			{
				$format[] = '%h hours';
			}
		}
		if ($interval->i)
		{
			if ($interval->i == 1)
			{
				$format[] = '%i minute';
			}
			else
			{
				$format[] = '%i minutes';
			}
		}
		if ($interval->s == 1)
		{
			$format[] = '%s second';
		}
		else
		{
			$format[] = '%s seconds';
		}

		return $interval->format(join(', ', $format));
	}
}