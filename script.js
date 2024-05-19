let slideIndex = 1;
showSlides(slideIndex);
let slideInterval = setInterval(() => { plusSlides(1); }, 5000);

function plusSlides(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
    clearInterval(slideInterval);
    slideInterval = setInterval(() => { plusSlides(1); }, 5000);
}

function showSlides(n) {
    let i;
    let slides = document.getElementsByClassName("slides");
    let dots = document.getElementsByClassName("dot");
    if (n > slides.length) {slideIndex = 1}
    if (n < 1) {slideIndex = slides.length}
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex-1].style.display = "block";
    dots[slideIndex-1].className += " active";
}

/*slide show for cards*/
window.addEventListener('scroll', function() {
    const largeCard = document.querySelector('.floating-large-card');
    const offset = window.pageYOffset;
    largeCard.style.top = `${20 + offset}px`;
  });
/*slide show for cards end*/
