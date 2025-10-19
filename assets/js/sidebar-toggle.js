
(function(){
  const body = document.body;
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');

  if (!sidebar) return;

  function openSidebar(){
    body.classList.add('sidebar-open');
    overlay && overlay.classList.remove('hidden');
    sidebar.classList.remove('-translate-x-full');
  }
  function closeSidebar(){
    body.classList.remove('sidebar-open');
    overlay && overlay.classList.add('hidden');
    sidebar.classList.add('-translate-x-full');
  }
  function toggleSidebar(){
    if (sidebar.classList.contains('-translate-x-full')) openSidebar();
    else closeSidebar();
  }

  // ✅ Support multiple toggle button selectors
  const toggleSelectors = ['#sidebarToggle', '.sidebar-toggle', '#toggleSidebar', '.btn-toggle-sidebar'];
  function bindToggles(){
    toggleSelectors.forEach(sel => {
      document.querySelectorAll(sel).forEach(btn => {
        btn.removeEventListener('click', toggleSidebar);
        btn.addEventListener('click', toggleSidebar);
      });
    });
  }

  // Initial bind
  bindToggles();
  // Rebind on DOM changes (for dynamically loaded content)
  const observer = new MutationObserver(bindToggles);
  observer.observe(document.body, { childList: true, subtree: true });

  // Overlay click closes sidebar
  overlay && overlay.addEventListener('click', closeSidebar);

  // Responsive behavior
  function init(){
    if (window.matchMedia('(max-width: 991px)').matches) {
      sidebar.classList.add('-translate-x-full');
      overlay && overlay.classList.add('hidden');
    } else {
      sidebar.classList.remove('-translate-x-full');
      overlay && overlay.classList.add('hidden');
    }
  }
  init();
  window.addEventListener('resize', init);
})();
