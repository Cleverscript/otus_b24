document.addEventListener('DOMContentLoaded', function () {
    const maxLength = 150;
    const companies = document.querySelectorAll('.companies p');

    companies.forEach(paragraph => {
        const fullText = paragraph.innerText;

        if (fullText.length > maxLength) {
            const visibleText = fullText.substring(0, maxLength) + '... ';
            const hiddenText = fullText.substring(maxLength);

            const moreLink = document.createElement('a');
            moreLink.href = '#';
            moreLink.innerText = 'Показать больше';
            moreLink.style.color = 'blue';
            moreLink.style.cursor = 'pointer';

            const lessLink = document.createElement('a');
            lessLink.href = '#';
            lessLink.innerText = 'Показать меньше';
            lessLink.style.color = 'blue';
            lessLink.style.cursor = 'pointer';
            lessLink.classList.add('collapsed');

            const hiddenSpan = document.createElement('span');
            hiddenSpan.classList.add('collapsed');
            hiddenSpan.innerText = hiddenText;

            paragraph.innerText = visibleText;
            paragraph.appendChild(moreLink);
            paragraph.appendChild(hiddenSpan);
            paragraph.appendChild(lessLink);

            moreLink.addEventListener('click', function (event) {
                event.preventDefault();
                hiddenSpan.classList.remove('collapsed');
                lessLink.classList.remove('collapsed');
                moreLink.classList.add('collapsed');
            });

            lessLink.addEventListener('click', function (event) {
                event.preventDefault();
                hiddenSpan.classList.add('collapsed');
                lessLink.classList.add('collapsed');
                moreLink.classList.remove('collapsed');
            });
        }
    });
});