// ================= IMAGE SLIDER ================= //
let slideIndex = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');

function showSlides() {
    if (slides.length > 0 && dots.length > 0) {
        slides.forEach((slide, i) => {
            slide.style.display = i === slideIndex ? 'block' : 'none';
            dots[i].classList.toggle('active-dot', i === slideIndex);
        });

        slideIndex++;
        if (slideIndex >= slides.length) slideIndex = 0;

        setTimeout(showSlides, 4000); // Change slide every 4 seconds
    }
}

function currentSlide(index) {
    if (slides.length > 0 && dots.length > 0) {
        slideIndex = index - 1;
        showSlides();
    }
}

// Initialize only if the slider is available
document.addEventListener('DOMContentLoaded', () => {
    if (slides.length > 0) {
        showSlides();
    }
});

// ================= AUTHOR SLIDER ================= //
let authorIndex = 0;

function moveAuthorSlider(direction) {
    const authorList = document.querySelector('.author-list');
    const authorItems = document.querySelectorAll('.author-item');
    
    if (authorList && authorItems.length > 0) {
        const authorWidth = authorItems[0].offsetWidth;
        const visibleAuthors = 5; // Show 5 authors at a time

        // Calculate how far to slide
        authorIndex += direction;

        // Limit sliding within available items
        const maxIndex = authorItems.length - visibleAuthors;
        if (authorIndex < 0) authorIndex = 0;
        if (authorIndex > maxIndex) authorIndex = maxIndex;

        // Move the slider
        const translateX = -authorIndex * authorWidth;
        authorList.style.transform = `translateX(${translateX}px)`;
    }
}
