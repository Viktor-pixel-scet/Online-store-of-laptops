function changeMainImage(thumbnail) {
  document.querySelector('.main-product-image').src = thumbnail.src;
}


function changeQuantity(delta) {
  const input = document.getElementById('quantity');
  let val = parseInt(input.value, 10) || 1;
  val += delta;
  if (val >= parseInt(input.min, 10) && val <= parseInt(input.max, 10)) {
    input.value = val;
  }
}


document.addEventListener('DOMContentLoaded', () => {
  const zoom = document.querySelector('.image-zoom-container');
  const mainImage = document.querySelector('.main-product-image');


  mainImage.addEventListener('mouseenter', () => {
    zoom.style.backgroundImage = `url(${mainImage.src})`;
    zoom.style.opacity = '1';
  });
  mainImage.addEventListener('mouseleave', () => {
    zoom.style.opacity = '0';
  });


  mainImage.addEventListener('mousemove', e => {
    const { left, top, width, height } = mainImage.getBoundingClientRect();
    const x = ((e.clientX - left) / width) * 100;
    const y = ((e.clientY - top) / height) * 100;
    zoom.style.backgroundPosition = `${x}% ${y}%`;
  });
});