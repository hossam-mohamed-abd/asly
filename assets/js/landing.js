/**
 * ASLY | أصلي TECH STORE
 * Public Landing Page Interactions (Filters, Search, Quick-View Modal, WhatsApp Inquiry)
 * Strictly ZERO prices anywhere on the public storefront!
 */

document.addEventListener('DOMContentLoaded', () => {
  const categoryTabs = document.querySelectorAll('[data-filter-cat]');
  const conditionSelect = document.getElementById('conditionFilter');
  const searchInput = document.getElementById('catalogSearch');
  const cards = Array.from(document.querySelectorAll('.product-card'));
  const emptyState = document.getElementById('catalogEmptyState');

  let activeCategory = 'all';

  function filterCatalog() {
    const cond = conditionSelect ? conditionSelect.value : 'all';
    const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
    let visibleCount = 0;

    cards.forEach((card) => {
      const cardCat = card.dataset.category || '';
      const cardCond = card.dataset.condition || '';
      const cardText = (card.dataset.search || '').toLowerCase();

      const catMatch = activeCategory === 'all' || cardCat === activeCategory;
      const condMatch = cond === 'all' || cardCond === cond;
      const qMatch = query === '' || cardText.includes(query);

      if (catMatch && condMatch && qMatch) {
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  categoryTabs.forEach((btn) => {
    btn.addEventListener('click', () => {
      activeCategory = btn.dataset.filterCat || 'all';
      document.querySelectorAll('.filter-tab[data-filter-cat]').forEach((t) => {
        t.classList.toggle('active', t.dataset.filterCat === activeCategory);
      });
      const catalogSection = document.getElementById('catalog');
      if (btn.classList.contains('hero-cat-pill') && catalogSection) {
        catalogSection.scrollIntoView({ behavior: 'smooth' });
      }
      filterCatalog();
    });
  });

  if (conditionSelect) conditionSelect.addEventListener('change', filterCatalog);
  if (searchInput) searchInput.addEventListener('input', filterCatalog);

  // Product Quick-View Modal
  const modal = document.getElementById('productModal');
  const modalClose = document.getElementById('closeProductModal');

  document.querySelectorAll('.js-open-product').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (!modal) return;
      const data = JSON.parse(btn.dataset.product || '{}');
      document.getElementById('modalProdImg').src = data.image || '';
      document.getElementById('modalProdTitle').textContent = data.name || '';
      document.getElementById('modalProdCategory').textContent = data.category_label || '';
      document.getElementById('modalProdCondition').textContent = data.condition_label || '';
      document.getElementById('modalProdBrand').textContent = data.brand ? `الماركة: ${data.brand}` : '';
      document.getElementById('modalProdCode').textContent = `كود القطعة: ${data.barcode || ''}`;
      document.getElementById('modalProdSpecs').textContent = data.specs || 'متوفر للمعاينة والفحص الكامل داخل المركز.';
      document.getElementById('modalProdDesc').textContent = data.description || '';
      document.getElementById('modalProdWarranty').textContent = data.warranty || 'ضمان الفحص والتجربة المعتمد - أصلي تك ستور';
      document.getElementById('modalWhatsappBtn').href = data.whatsapp_url || '#';
      modal.classList.add('open');
    });
  });

  if (modalClose && modal) {
    modalClose.addEventListener('click', () => modal.classList.remove('open'));
    modal.addEventListener('click', (e) => {
      if (e.target === modal) modal.classList.remove('open');
    });
  }
});
