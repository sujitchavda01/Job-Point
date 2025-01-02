const numbers = document.querySelectorAll('.number');

numbers.forEach(number => {
    const endNumber = parseInt(number.dataset.target, 10);
    const duration = 2000; 
    const step = Math.ceil(endNumber / (duration / 10)); 

    let currentNumber = 0; 

    const startTimer = setInterval(() => {
        if (currentNumber < endNumber) {
            currentNumber += step; 
            if (currentNumber > endNumber) currentNumber = endNumber; 
            number.textContent = currentNumber + '+'; 
        } else {
            clearInterval(startTimer); 
            number.textContent = endNumber + '+'; 
        }
    }, 10);
});


