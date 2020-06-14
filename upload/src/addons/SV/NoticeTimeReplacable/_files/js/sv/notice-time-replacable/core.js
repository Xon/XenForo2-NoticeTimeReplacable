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
                console.error('Invalid phrase provided.', phrase);
                clearInterval(this.timer);
                return false;
            }

            phrase = 'svNoticeTimeReplacables_' + phrase + (value > 1 ? 's' : '');
            if (!(phrase in XF.phrases))
            {
                console.error('Phrase is not available.', phrase);
                clearInterval(this.timer);
                return false;
            }

            var translatedValue = XF.phrase(phrase, {
                '{count}': value
            }, null);

            if (translatedValue === null)
            {
                console.error('Invalid phrase provided.');
                clearInterval(this.timer);
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
            if (typeof yearPhrase === 'string')
            {
                timeArr.push(yearPhrase);
            }
            else
            {
                return;
            }

            var monthPhrase = this.getDatePart(momentObj.months(), 'month');
            if (typeof monthPhrase === 'string')
            {
                timeArr.push(monthPhrase);
            }

            var daysPhrase = this.getDatePart(momentObj.days(), 'day');
            if (typeof daysPhrase === 'string')
            {
                timeArr.push(daysPhrase);
            }
            else
            {
                return;
            }

            var hoursPhrase = this.getDatePart(momentObj.hours(), 'hour');
            if (typeof hoursPhrase === 'string')
            {
                timeArr.push(hoursPhrase);
            }
            else
            {
                return;
            }

            var minutesPhrase = this.getDatePart(momentObj.minutes(), 'minute');
            if (typeof minutesPhrase === 'string')
            {
                timeArr.push(minutesPhrase);
            }
            else
            {
                return;
            }

            var secondsPhrase = this.getDatePart(momentObj.seconds(), 'second');
            if (typeof secondsPhrase === 'string')
            {
                timeArr.push(secondsPhrase);
            }
            else
            {
                return;
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