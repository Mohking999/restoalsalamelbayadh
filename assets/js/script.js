// script.js — تحكم في السلة والكميات، القائمة المتجاوبة، التنبيهات المخصصة، وتتبع الأقسام أثناء التمرير

document.addEventListener('DOMContentLoaded', () => {
  // --- 1. نظام التنبيهات المخصص (Toast System) ---
  function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    // Icon based on type
    const icon = type === 'success' ? '✓' : '⚠️';
    
    toast.innerHTML = `
      <div style="display:flex; align-items:center; gap:8px;">
        <span>${icon}</span>
        <span>${message}</span>
      </div>
      <button class="toast-close" type="button">&times;</button>
    `;
    
    container.appendChild(toast);
    
    // Close handler
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
      toast.style.animation = 'toast-out 0.3s forwards';
      setTimeout(() => toast.remove(), 300);
    });
    
    // Auto dismiss
    setTimeout(() => {
      if (toast.parentNode) {
        toast.style.animation = 'toast-out 0.3s forwards';
        setTimeout(() => toast.remove(), 300);
      }
    }, 3500);
  }

  // --- 2. القائمة الجانبية المتجاوبة (Mobile Navigation Drawer) ---
  const burgerBtn = document.getElementById('burgerBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const drawerOverlay = document.getElementById('drawerOverlay');
  const closeDrawerBtn = document.getElementById('closeDrawerBtn');

  function openDrawer() {
    burgerBtn.classList.add('open');
    mobileDrawer.classList.add('open');
    drawerOverlay.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
  }

  function closeDrawer() {
    burgerBtn.classList.remove('open');
    mobileDrawer.classList.remove('open');
    drawerOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (burgerBtn && mobileDrawer) {
    burgerBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (mobileDrawer.classList.contains('open')) {
        closeDrawer();
      } else {
        openDrawer();
      }
    });

    if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeDrawer);
    if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeDrawer();
    });
  }

  // --- 3. تحكم في إضافة الأطباق للسلة عبر AJAX ---
  document.querySelectorAll('.ticket[data-id]').forEach(ticket => {
    const itemId = ticket.dataset.id;
    const minus = ticket.querySelector('.minus');
    const plus = ticket.querySelector('.plus');
    const valEl = ticket.querySelector('.qty-val');
    const addBtn = ticket.querySelector('.add-btn');
    if (!minus || !plus) return;

    let qty = 0;

    function render() {
      valEl.textContent = qty;
      addBtn.style.display = qty > 0 ? 'inline-flex' : 'none';
      addBtn.textContent = qty > 0 ? `أضف ${qty} للسلة` : 'أضف للسلة';
    }

    plus.addEventListener('click', () => { qty = Math.min(qty + 1, 20); render(); });
    minus.addEventListener('click', () => { qty = Math.max(qty - 1, 0); render(); });

    addBtn.addEventListener('click', async () => {
      addBtn.textContent = 'جاري الإضافة...';
      addBtn.disabled = true;
      try {
        const res = await fetch('cart_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `item_id=${encodeURIComponent(itemId)}&qty=${encodeURIComponent(qty)}&action=add`
        });
        const data = await res.json();
        if (data.ok) {
          // Bounce animation on cart badges
          document.querySelectorAll('.cart-badge').forEach(b => {
            b.textContent = data.cart_count;
            b.classList.remove('bounce');
            void b.offsetWidth; // Trigger reflow
            b.classList.add('bounce');
          });
          
          showToast('تمت إضافة الطبق إلى السلة بنجاح!', 'success');
          qty = 0;
          render();
        } else {
          showToast(data.error || 'حدث خطأ، حاول مرة أخرى', 'error');
          render();
        }
      } catch (e) {
        showToast('تعذر الاتصال بالخادم', 'error');
        render();
      } finally {
        addBtn.disabled = false;
      }
    });
  });

  // --- 4. مؤشر تمرير الأصناف وتتبع التمرير النشط (Scrollspy & Scroll Indicators) ---
  const pills = document.querySelectorAll('.category-pill');
  const sections = document.querySelectorAll('.menu-section');
  const catBar = document.querySelector('.category-bar');
  const catBarWrapper = document.querySelector('.category-bar-wrapper');

  // Check if category bar is scrollable to show visual hint gradient
  function checkScrollIndicator() {
    if (catBar && catBarWrapper) {
      const isScrollable = catBar.scrollWidth > catBar.clientWidth;
      if (isScrollable) {
        catBarWrapper.classList.add('has-scroll');
      } else {
        catBarWrapper.classList.remove('has-scroll');
      }
    }
  }

  if (catBar) {
    checkScrollIndicator();
    window.addEventListener('resize', checkScrollIndicator);
    catBar.addEventListener('scroll', checkScrollIndicator);
  }

  // Smooth scroll and active pill highlight on click
  pills.forEach(p => {
    p.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = p.getAttribute('href');
      const targetEl = document.querySelector(targetId);
      
      if (targetEl) {
        // Close mobile drawer if click happens inside drawer
        closeDrawer();
        
        const yOffset = -90; // Adjust for sticky header
        const y = targetEl.getBoundingClientRect().top + window.pageYOffset + yOffset;
        window.scrollTo({ top: y, behavior: 'smooth' });

        pills.forEach(x => x.classList.remove('active'));
        p.classList.add('active');
        
        // Center the active pill horizontally in the bar
        if (catBar) {
          const pillOffset = p.offsetLeft - (catBar.clientWidth / 2) + (p.clientWidth / 2);
          catBar.scrollTo({ left: pillOffset, behavior: 'smooth' });
        }
      }
    });
  });

  // Scrollspy: Highlight category pill corresponding to visible section
  if (sections.length && pills.length) {
    const observerOptions = {
      root: null,
      rootMargin: '-100px 0px -60% 0px', // Focus top-mid viewport area
      threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const sectionId = entry.target.id;
          const activePill = document.querySelector(`.category-pill[href="#${sectionId}"]`);
          
          if (activePill) {
            pills.forEach(p => p.classList.remove('active'));
            activePill.classList.add('active');
            
            // Scroll category bar to show the active pill
            if (catBar) {
              const pillOffset = activePill.offsetLeft - (catBar.clientWidth / 2) + (activePill.clientWidth / 2);
              catBar.scrollTo({ left: pillOffset, behavior: 'smooth' });
            }
          }
        }
      });
    }, observerOptions);

    sections.forEach(section => observer.observe(section));
  }
});

