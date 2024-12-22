const numbers = document.querySelectorAll('.number');

numbers.forEach(number => {
    const endNumber = parseInt(number.dataset.target, 10);
    const duration = 2000; // Animation duration in milliseconds
    const step = Math.ceil(endNumber / (duration / 10)); // Increment step

    let currentNumber = 0; // Initialize the counter

    const startTimer = setInterval(() => {
        if (currentNumber < endNumber) {
            currentNumber += step; // Increment the counter
            if (currentNumber > endNumber) currentNumber = endNumber; // Prevent overshoot
            number.textContent = currentNumber + '+'; // Append '+'
        } else {
            clearInterval(startTimer); // Stop the timer
            number.textContent = endNumber + '+'; // Ensure final value with '+'
        }
    }, 10);
});


