/**
 * ASLY | أصلي TECH STORE
 * Editorial Lookbook Storefront Interactions:
 * - Category Filtering & Collection Jump Cards
 * - Instant Inline & Modal Search
 * - Dual-Photo Quick-View Product Modal (Zero Public Prices!)
 * - Bright Lookbook Lightbox
 */

(() => {
  const DATA = window.ASLY_PUBLIC || { products: [] };
  const products = DATA.products || [];

  // Mobile Menu
  const btnMob = document.getElementById('btnMobileMenu');
  const navLinks = document.getElementById('mainNavLinks');
  btnMob?.addEventListener('click', () => navLinks?.classList.toggle('open'));
  navLinks?.querySelectorAll('a').forEach((a) => {
    a.addEventListener('click', () => navLinks.classList.remove('open'));
  });

  // Category & Search Filtering
  let activeCat = 'all';
  let searchQuery = '';

  const filterPills = document.querySelectorAll('#catalogFilterPills .filter-pill');
  const cards = document.querySelectorAll('#publicProductsGrid .lookbook-product-card');
  const inlineSearch = document.getElementById('catalogInlineSearch');
  const emptyNotice = document.getElementById('emptyCatalogNotice');

  function applyCatalogFilter() {
    let visibleCount = 0;
    const q = searchQuery.trim().toLowerCase();

    cards.forEach((card) => {
      const catMatch = activeCat === 'all' || card.dataset.category === activeCat;
      const textMatch = !q || (card.dataset.search || '').includes(q);
      const show = catMatch && textMatch;
      card.style.display = show ? 'flex' : 'none';
      if (show) visibleCount += 1;
    });

    if (emptyNotice) {
      emptyNotice.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  function selectCategory(cat) {
    activeCat = cat;
    filterPills.forEach((pill) => {
      pill.classList.toggle('active', pill.dataset.cat === cat);
    });
    applyCatalogFilter();
  }

  filterPills.forEach((pill) => {
    pill.addEventListener('click', () => selectCategory(pill.dataset.cat));
  });

  document.querySelectorAll('[data-jump-cat]').forEach((el) => {
    el.addEventListener('click', (e) => {
      const cat = el.dataset.jumpCat;
      if (cat) {
        selectCategory(cat);
        document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  inlineSearch?.addEventListener('input', (e) => {
    searchQuery = e.target.value || '';
    applyCatalogFilter();
  });

  // Quick-View Modal with Dual-Image Gallery
  const qvModal = document.getElementById('quickViewModal');
  const qvMainImg = document.getElementById('qvMainImg');
  const qvThumbs = document.getElementById('qvThumbs');

  function openQuickView(index) {
    const p = products[index];
    if (!p || !qvModal) return;

    document.getElementById('qvCategory').textContent = p.category_label || '';
    document.getElementById('qvCondition').textContent = p.condition_label || '';
    document.getElementById('qvTitle').textContent = p.title || '';
    document.getElementById('qvSpecs').textContent = p.specs || '—';
    document.getElementById('qvDesc').textContent = p.description || '';
    document.getElementById('qvWarranty').textContent = p.warranty ? `🛡️ ${p.warranty}` : '';

    const waUrl = `https://wa.me/${DATA.waNumber}?text=${encodeURIComponent((DATA.waPrefix || '') + p.title)}`;
    document.getElementById('qvWaBtn').href = waUrl;

    const imgs = [p.img_primary, p.img_secondary].filter(Boolean);
    qvMainImg.src = imgs[0] || '';
    qvMainImg.alt = p.title || '';

    qvThumbs.innerHTML = '';
    if (imgs.length > 1) {
      imgs.forEach((src, i) => {
        const im = document.createElement('img');
        im.src = src;
        im.className = i === 0 ? 'active' : '';
        im.addEventListener('click', () => {
          qvMainImg.src = src;
          qvThumbs.querySelectorAll('img').forEach((t) => t.classList.remove('active'));
          im.classList.add('active');
        });
        qvThumbs.appendChild(im);
      });
    }

    qvModal.classList.add('open');
  }

  document.querySelectorAll('.js-open-quickview').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.stopPropagation();
      openQuickView(Number(el.dataset.pindex));
    });
  });

  document.getElementById('btnCloseQuickView')?.addEventListener('click', () => qvModal?.classList.remove('open'));
  qvModal?.addEventListener('click', (e) => {
    if (e.target === qvModal) qvModal.classList.remove('open');
  });

  // Live Search Modal
  const searchModal = document.getElementById('searchModal');
  const modalSearchInput = document.getElementById('modalSearchInput');
  const modalSearchResults = document.getElementById('modalSearchResults');

  function renderModalSearch(q) {
    if (!modalSearchResults) return;
    const term = (q || '').trim().toLowerCase();
    const matched = products.filter((p) => {
      if (!term) return true;
      return `${p.title} ${p.brand} ${p.specs} ${p.category_label}`.toLowerCase().includes(term);
    });

    if (!matched.length) {
      modalSearchResults.innerHTML = `<div style="padding:18px;text-align:center;color:var(--ink-muted);">${DATA.emptyText || ''}</div>`;
      return;
    }

    modalSearchResults.innerHTML = '';
    matched.forEach((p) => {
      const idx = products.indexOf(p);
      const row = document.createElement('div');
      row.style.cssText = 'display:flex;align-items:center;gap:14px;padding:10px 14px;border-radius:16px;border:1px solid var(--line-soft);background:var(--bg-ivory);cursor:pointer;';
      row.innerHTML = `
        <img src="${p.img_primary}" alt="" style="width:58px;height:58px;object-fit:cover;border-radius:12px;background:#fff;">
        <div style="flex:1;min-width:0;">
          <strong style="display:block;color:var(--ink-heading);font-size:0.95rem;"></strong>
          <span style="font-size:0.8rem;color:var(--ink-muted);"></span>
        </div>
      `;
      row.querySelector('strong').textContent = p.title;
      row.querySelector('span').textContent = `${p.category_label} • ${p.condition_label}`;
      row.addEventListener('click', () => {
        searchModal?.classList.remove('open');
        openQuickView(idx);
      });
      modalSearchResults.appendChild(row);
    });
  }

  document.getElementById('btnOpenSearch')?.addEventListener('click', () => {
    searchModal?.classList.add('open');
    renderModalSearch(modalSearchInput?.value || '');
    setTimeout(() => modalSearchInput?.focus(), 60);
  });

  modalSearchInput?.addEventListener('input', (e) => renderModalSearch(e.target.value));
  document.getElementById('btnCloseSearch')?.addEventListener('click', () => searchModal?.classList.remove('open'));
  searchModal?.addEventListener('click', (e) => {
    if (e.target === searchModal) searchModal.classList.remove('open');
  });

  // Lookbook Lightbox
  const lbModal = document.getElementById('lightboxModal');
  const lbImg = document.getElementById('lightboxImg');
  document.querySelectorAll('.js-lightbox-trigger').forEach((fig) => {
    fig.addEventListener('click', () => {
      if (lbImg && lbModal) {
        lbImg.src = fig.dataset.src || '';
        lbModal.classList.add('open');
      }
    });
  });
  document.getElementById('btnCloseLightbox')?.addEventListener('click', () => lbModal?.classList.remove('open'));
  lbModal?.addEventListener('click', (e) => {
    if (e.target === lbModal) lbModal.classList.remove('open');
  });
})();
