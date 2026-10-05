(() => {
    'use strict';

    const clockSelector = '[data-application-live-time]';

    const startClock = (clock) => {
        const serverEpoch = Number(clock.dataset.serverEpoch);
        const timeZone = clock.dataset.timeZone;

        if (!Number.isFinite(serverEpoch) || !timeZone) {
            return;
        }

        const formatter = new Intl.DateTimeFormat('en-US', {
            timeZone,
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
        const startedAt = performance.now();

        const currentDate = () => new Date(serverEpoch + (performance.now() - startedAt));
        const dateElement = clock.querySelector('.application-live-time__date');
        const clockElement = clock.querySelector('.application-live-time__clock');

        const render = () => {
            const date = currentDate();
            const parts = {};

            formatter.formatToParts(date).forEach(({ type, value }) => {
                if (type !== 'literal') {
                    parts[type] = value;
                }
            });

            const day = Number(parts.day);
            const formattedDate = `${parts.month} - ${day} - ${parts.year}`;
            const formattedClock = `${parts.hour}:${parts.minute}:${parts.second} ${parts.dayPeriod.toLowerCase()}`;

            if (dateElement && clockElement) {
                dateElement.textContent = formattedDate;
                clockElement.textContent = formattedClock;
            } else {
                clock.textContent = `${formattedDate}, ${formattedClock}`;
            }

            clock.setAttribute('aria-label', `${formattedDate}, ${formattedClock}`);
            clock.dateTime = date.toISOString();
        };

        render();

        const millisecondsToNextSecond = 1000 - (currentDate().getTime() % 1000);
        window.setTimeout(() => {
            render();
            window.setInterval(render, 1000);
        }, millisecondsToNextSecond);
    };

    const initializeClocks = () => {
        document.querySelectorAll(clockSelector).forEach(startClock);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeClocks, { once: true });
    } else {
        initializeClocks();
    }
})();
