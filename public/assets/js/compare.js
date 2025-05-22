
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.remove-compare').forEach(btn => {
    btn.addEventListener('click', () => {
     
      const id = btn.dataset.productId;
      let list;
      try {
        list = JSON.parse(localStorage.getItem('productComparison') || '[]');
      } catch (e) {
        console.error('Помилка читання списку порівняння:', e);
        list = [];
      }

      
      const updatedList = list.filter(x => x != id);

      try {
        localStorage.setItem('productComparison', JSON.stringify(updatedList));
      } catch (e) {
        console.error('Помилка запису списку порівняння:', e);
        alert('Не вдалося оновити список порівняння');
        return;
      }

     
      const params = new URLSearchParams(window.location.search);
      const newProducts = params.get('products')
        .split(',')
        .filter(x => x != id)
        .join(',');

      
      const target = newProducts
        ? `/backend/utils/compare.php?products=${newProducts}`
        : '/index.php';
      window.location.href = target;
    });
  });
});
