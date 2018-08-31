<?php

namespace SV\NoticeTime\XF;

use SV\NoticeTime\Repository\NoticeTime;

class NoticeList extends XFCP_NoticeList
{
    protected $svNow = null;

    public function addNotice($key, $type, $message, array $override = [])
    {
        parent::addNotice($key, $type, $message, $override);

        $message = $this->notices[$type][$key]['message'];

        $startAbsolute = $startRelative = $beforeAbsolute = $beforeRelative = '';

        foreach ($override['page_criteria'] AS $criterion)
        {
            if ($criterion['rule'] == 'after')
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

                $startAbsolute = \XF::language()->dateTime($timeStamp);
                $startRelative = NoticeTime::getRelativeDate($this->getNowDateTime(), $timeStamp);
            }

            if ($criterion['rule'] == 'before')
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

                $beforeAbsolute = \XF::language()->dateTime($timeStamp);
                $beforeRelative = NoticeTime::getRelativeDate($this->getNowDateTime(), $timeStamp);
            }
        }

        $tokens = [
            '{time_start:absolute}' => $startAbsolute,
            '{time_start:relative}' => $startRelative,
            '{time_end:absolute}'   => $beforeAbsolute,
            '{time_end:relative}'   => $beforeRelative
        ];

        $message = strtr($message, $tokens);

        $this->notices[$type][$key]['message'] = $message;
    }

    protected function getNowDateTime()
    {
        if ($this->svNow === null)
        {
            $this->svNow = new \DateTime();
        }

        return $this->svNow;
    }
}