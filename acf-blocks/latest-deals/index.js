document.addEventListener('DOMContentLoaded', function() {
    const countdownTimers = document.querySelectorAll('.countdown-timer');
    
    countdownTimers.forEach(timer => {
        let endTimeStr = timer.dataset.endTime;
        console.log('Countdown end time:', endTimeStr); // Debug output

        // Parse '23/08/2025 12:00 am' or '23/08/2025T12:00 am' to a Date object
        let endTime;
        const match = endTimeStr.match(/^(\d{2})\/(\d{2})\/(\d{4})[ T]?(\d{1,2}):(\d{2})\s?(am|pm)$/i);
        if (match) {
            let [ , day, month, year, hour, minute, ampm ] = match;
            hour = parseInt(hour, 10);
            if (ampm.toLowerCase() === 'pm' && hour < 12) hour += 12;
            if (ampm.toLowerCase() === 'am' && hour === 12) hour = 0;
            // JS months are 0-based
            endTime = new Date(year, month - 1, day, hour, minute).getTime();
        } else {
            endTime = new Date(endTimeStr).getTime();
        }

        function updateTimer() {
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (!endTimeStr || isNaN(endTime)) {
                timer.innerHTML = '<div class="text-red-500 font-bold">Invalid End Time: ' + endTimeStr + '</div>';
                return;
            }
            if (distance < 0) {
                timer.innerHTML = '<div class="text-red-500 font-bold">Offer Expired</div>';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            timer.querySelector('.days').textContent = String(days).padStart(2, '0');
            timer.querySelector('.hours').textContent = String(hours).padStart(2, '0');
            timer.querySelector('.minutes').textContent = String(minutes).padStart(2, '0');
            timer.querySelector('.seconds').textContent = String(seconds).padStart(2, '0');
        }
        
        updateTimer();
        setInterval(updateTimer, 1000);
    });
});