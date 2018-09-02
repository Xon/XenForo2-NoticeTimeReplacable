<?php

namespace SV\NoticeTimeReplacable\XF;

class NoticeList extends XFCP_NoticeList
{
    /** @var null|int */
    protected $svNow = null;

    /**
     * @param string $key
     * @param string $type
     * @param string $message
     * @param array  $override
     */
    public function addNotice($key, $type, $message, array $override = [])
    {
        parent::addNotice($key, $type, $message, $override);

        if (empty($override['page_criteria']))
        {
            return;
        }

        $tokens = [];
        foreach ($override['page_criteria'] AS $criterion)
        {
            switch ($criterion['rule'])
            {
                case 'after':
                    list($absolute, $relative) = $this->getAbsoluteRelativeTimeDiff($criterion);
                    $tokens['{time_start:absolute}'] = $absolute;
                    $tokens['{time_start:relative}'] = $relative;
                    break;
                case 'before':
                    list($absolute, $relative) = $this->getAbsoluteRelativeTimeDiff($criterion);
                    $tokens['{time_end:absolute}'] = $absolute;
                    $tokens['{time_end:relative}'] = $relative;
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

    /**
     * @param array  $format
     * @param int    $value
     * @param string $formatString
     * @param string $phrase
     */
    protected function appendDatePart(&$format, $value, $formatString, $phrase)
    {
        if ($value === 1)
        {
            $format[] = [$formatString, \XF::phrase($phrase)];
        }
        if ($value > 1)
        {
            $format[] = \XF::phrase('time.'. $phrase .'s', ['count' => $value]);
        }
    }

    /**
     * @param \DateTime $now
     * @param \DateTime $other
     * @return string
     */
    public function getRelativeDate(\DateTime $now, \DateTime $other)
    {
        $interval = $other->diff($now);
        if (!$interval)
        {
            return '';
        }

        $format = [];
        $this->appendDatePart($format, $interval->y, '%y ', 'year');
        $this->appendDatePart($format, $interval->m, '%m ', 'month');
        $this->appendDatePart($format, $interval->d, '%d ', 'day');
        $this->appendDatePart($format, $interval->h, '%h ', 'hour');
        $this->appendDatePart($format, $interval->i, '%i ', 'minute');
        $this->appendDatePart($format, $interval->s, '%s ', 'second');

        $f = intval($now->getTimestamp() - $other->getTimestamp());
        if ($f)
        {
            foreach($format as &$s)
            {
                if (is_array($s))
                {
                    $s = join($s);
                }
            }
            $s = $interval->format(join(', ', $format));
        }
        else
        {
            $s = '0 ' . \XF::phrase('seconds');
        }

        return "<span class='time-notice' data-seconds-diff='{$f}'>" .
             $s .
            "</span>";
    }
}
