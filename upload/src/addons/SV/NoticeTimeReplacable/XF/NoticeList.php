<?php

namespace SV\NoticeTime\XF;

class NoticeList extends XFCP_NoticeList
{
    /** @var null|int */
    protected $svNow = null;

    public function addNotice($key, $type, $message, array $override = [])
    {
        parent::addNotice($key, $type, $message, $override);


        $tokens = [];
        foreach ($override['page_criteria'] AS $criterion)
        {
            switch ($criterion['rule'])
            {
                case 'after':
                    list($absolute, $relative) = $this->getAbsoluteRelativeTimeDiff($criterion);
                    $tokens['{time_end:absolute}'] = $absolute;
                    $tokens['{time_end:relative}'] = $relative;
                    break;
                case 'before':
                    list($absolute, $relative) = $this->getAbsoluteRelativeTimeDiff($criterion);
                    $tokens['{time_start:absolute}'] = $absolute;
                    $tokens['{time_start:relative}'] = $relative;
                    break;
            }
        }

        if ($tokens)
        {
            $message = $this->notices[$type][$key]['message'];

            $message = strtr($message, $tokens);

            $this->notices[$type][$key]['message'] = $message;
        }
    }

    /**
     * @param array $criterion
     * @return array
     */
    protected function getAbsoluteRelativeTimeDiff(array $criterion)
    {
        $ymd = $criterion['data']['ymd'];
        $timeHour = $criterion['data']['hh'];
        $timeMinute = $criterion['data']['mm'];

        if ($criterion['data']['user_tz'])
        {
            $timezone = new \DateTimeZone(\XF::visitor()->timezone);
        }
        else
        {
            $timezone = new \DateTimeZone($criterion['data']['timezone']);
        }

        $timeStamp = new \DateTime("{$ymd}T$timeHour:$timeMinute", $timezone);
        if ($this->svNow === null)
        {
            $this->svNow = new \DateTime();
        }

        $absolute = \XF::language()->dateTime($timeStamp);
        $relative = $this->getRelativeDate($this->svNow, $timeStamp);

        return [$absolute, $relative];
    }

    public function getRelativeDate(\DateTime $now, \DateTime $other)
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
