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
     * @param \DateTime $now
     * @param \DateTime $other
     * @param bool      $countUp
     * @return string
     */
    protected function getRelativeDate(\DateTime $now, \DateTime $other, bool $countUp)
    {
        $func = \XF::$versionId >= 2010370 ? 'func' : 'fn';

        return $this->app->templater()->$func('sv_relative_timestamp', [
            $now->getTimestamp(), $other->getTimestamp(), // now and other date time obj
            0, $countUp, // maximum date parts and if allowed counting up
            'time-notice', // class added to the span
            'click', '< .notice-content | .notice-dismiss' // event to trigger and who trigger it on
        ]);
    }
}
