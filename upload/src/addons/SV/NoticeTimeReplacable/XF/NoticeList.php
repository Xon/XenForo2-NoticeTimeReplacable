<?php

namespace SV\NoticeTimeReplacable\XF;

class NoticeList extends XFCP_NoticeList
{
    /** @var null|int */
    protected $svNow = null;

    /**
     * This entire block is pre-XF2.1.4 bugfix
     *
     * @return array
     */
    protected function getTokens()
    {
        $tokens = parent::getTokens();

        $tokens['{user_id}'] = $this->user->user_id;

        return $tokens;
    }

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
                    list($absolute, $relative) = $this->getAbsoluteRelativeTimeDiff($criterion, true);
                    $tokens['{time_start:absolute}'] = $absolute;
                    $tokens['{time_start:relative}'] = $relative;
                    break;
                case 'before':
                    list($absolute, $relative) = $this->getAbsoluteRelativeTimeDiff($criterion, false);
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
     * @param bool  $countingUp
     * @return array
     */
    protected function getAbsoluteRelativeTimeDiff(array $criterion, $countingUp)
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

        $absolute = \XF::language()->dateTime($timeStamp->getTimestamp());
        $relative = $this->getRelativeDate($this->svNow, $timeStamp, $countingUp);

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
        $value = (int)$value;
        if ($value === 1)
        {
            $format[] = \XF::phrase('time.' . $phrase, ['count' => $value]);
        }
        else if ($value > 1)
        {
            $format[] = \XF::phrase('time.' . $phrase . 's', ['count' => $value]);
        }
        else if ($value < 0)
        {
            $format[] = [$formatString, \XF::phrase('time.' . $phrase)];
        }
    }

    /**
     * @param \DateTime $now
     * @param \DateTime $other
     * @param bool      $countingUp
     * @return string
     */
    public function getRelativeDate(\DateTime $now, \DateTime $other, $countingUp)
    {
        $language = \XF::language();
        $interval = $other->diff($now);
        if (!$interval)
        {
            return '';
        }
//countUp
        $format = [];
        $this->appendDatePart($format, $interval->y, '%y ', 'year');
        $this->appendDatePart($format, $interval->m, '%m ', 'month');
        $this->appendDatePart($format, $interval->d, '%d ', 'day');
        $this->appendDatePart($format, $interval->h, '%h ', 'hour');
        $this->appendDatePart($format, $interval->i, '%i ', 'minute');
        $this->appendDatePart($format, $interval->s, '%s ', 'second');

        $secondsDiff = intval($now->getTimestamp() - $other->getTimestamp());
        if ($secondsDiff)
        {
            foreach ($format as &$time)
            {
                if (is_array($time))
                {
                    $time = join($time);
                }
            }

            $time = $interval->format(join(', ', $format));
        }
        else
        {
            return '<span class="time-notice" data-seconds-diff="' . \XF::escapeString($secondsDiff) . '">'
                . \XF::escapeString($language->dateTime($other->getTimestamp())) . '</span>';
        }

        $templater = $this->app->templater();
        foreach (['sv/vendor/moment/moment/moment.js', 'sv/notice-time-replacable/core.js'] AS $file)
        {
            $templater->includeJs([
                'src'   => $file,
                'addon' => 'SV/NoticeTimeReplacable',
                'min'   => '1',
            ]);
        }

        return '<span class="time-notice" data-xf-init="sv-notice-time-replacable--relative-timestamp" ' .
            'data-count-up="' . ($countingUp ? '1' : '0') . '" ' .
            'data-timestamp="' . \XF::escapeString($other->getTimestamp()) . '" ' .
            'data-date-format="' . \XF::escapeString($language->date_format) . '" ' .
            'data-time-format="' . \XF::escapeString($language->time_format) . '" ' .
            'data-seconds-diff="' . \XF::escapeString($secondsDiff) . '">' . \XF::escapeString($time) . '</span>';
    }
}
