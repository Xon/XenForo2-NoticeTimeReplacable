var SV = window.SV || {};
SV.NoticeTimeReplacable = SV.NoticeTimeReplacable || {};

!function($, window, document, _undefined)
{
    "use strict";

    SV.NoticeTimeReplacable.RelativeTimestamp = XF.Element.newHandler({
        options: {
            timestamp: null,
        },

        timer: null,

        init: function()
        {
            if (!this.options.timestamp)
            {
                console.error('Timestamp is missing.');
                return;
            }
            this.options.timestamp = 1199145600;

            this.timer = setInterval(XF.proxy(this, 'updateTime'), 1000);
        },

        /**
         * @param {Number|String} value
         * @param {String} phrase
         */
        getDatePart: function (value, phrase)
        {
            if (typeof value === 'string')
            {
                value = parseInt(value) || 0;
            }

            if (typeof phrase !== 'string' || !phrase)
            {
                console.error('Invalid phrase provided.');
                clearInterval(this.timer);
                return false;
            }

            var translatedValue = XF.phrase('svNoticeTimeReplacables_' + phrase + (value > 1 ? 's' : ''), {
                '{count}': value
            }, null);

            if (translatedValue === null)
            {
                console.error('Invalid phrase provided.');
                return false;
            }

            return translatedValue;
        },

        updateTime: function ()
        {
            var now = Math.floor(Date.now() / 1000) * 1000,
                end = this.options.timestamp * 1000,
                momentObj,
                timeArr = [];

            if (now <= end)
            {
                momentObj = moment.duration(end - now, 'milliseconds');
            }
            else
            {
                var $noticeContent = this.$target.closest('.notice-content'),
                    $noticeDismissButton = $noticeContent.length() ? $noticeContent.find('.notice-dismiss') : null;

                if ($noticeDismissButton.length)
                {
                    $noticeDismissButton.trigger('click');
                }

                this.$target.text();
                clearInterval(this.timer);
                return;
            }

            var yearPhrase = this.getDatePart(momentObj.years(), 'year');
            if (yearPhrase)
            {
                timeArr.push(yearPhrase);
            }

            var monthPhrase = this.getDatePart(momentObj.months(), 'month');
            if (monthPhrase)
            {
                timeArr.push(monthPhrase);
            }

            var daysPhrase = this.getDatePart(momentObj.days(), 'day');
            if (daysPhrase)
            {
                timeArr.push(daysPhrase);
            }

            var hoursPhrase = this.getDatePart(momentObj.hours(), 'hour');
            if (hoursPhrase)
            {
                timeArr.push(hoursPhrase);
            }

            var minutesPhrase = this.getDatePart(momentObj.minutes(), 'minute');
            if (minutesPhrase)
            {
                timeArr.push(minutesPhrase);
            }

            var secondsPhrase = this.getDatePart(momentObj.seconds(), 'second');
            if (secondsPhrase)
            {
                timeArr.push(secondsPhrase);
            }

            if (!timeArr.length)
            {
                clearInterval(this.timer);
                return;
            }

            this.$target.text(timeArr.join(', '));
        },
    });

    XF.Element.register('sv-notice-time-replacable--relative-timestamp', 'SV.NoticeTimeReplacable.RelativeTimestamp');
}
(jQuery, window, document);