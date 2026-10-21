(function () {
  const root = document.documentElement;

 
  document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-bs-theme', next);
      try { localStorage.setItem('theme', next); } catch (e) {}
    });
  });


  const toTop = document.getElementById('toTop');
  if (toTop) {
    const onScroll = function () {
      const max = root.scrollHeight - root.clientHeight;
      const p = max > 0 ? (root.scrollTop / max) * 100 : 0;
      toTop.style.setProperty('--p', p.toFixed(1));
      toTop.classList.toggle('show', root.scrollTop > 400);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  
  const targets = new Set();
  ['.hero-feature', '.section-title', '.page-head', '.widget', '.article',
   '.author-box', '.share-bar', '.welcome', '.empty-state'].forEach(function (sel) {
    document.querySelectorAll(sel).forEach(function (el) { targets.add(el); });
  });

  ['.post-card', '.stat-card', '.nav-post'].forEach(function (sel) {
    document.querySelectorAll(sel).forEach(function (el) {
      if (el.parentElement) targets.add(el.parentElement);
    });
  });

  if (!('IntersectionObserver' in window)) {
    targets.forEach(function (el) { el.classList.add('in'); });
  } else {
    const io = new IntersectionObserver(function (entries) {
      let n = 0;
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        const el = e.target;
        el.style.transitionDelay = (n++ * 80) + 'ms';
        el.classList.add('in');
        io.unobserve(el);
        setTimeout(function () { el.style.transitionDelay = ''; }, 1100);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    targets.forEach(function (el) {
      el.setAttribute('data-reveal', '');
      io.observe(el);
    });
  }

  
  document.querySelectorAll('.post-thumb img, .rank-thumb img').forEach(function (img) {
    const done = function () { img.classList.add('loaded'); };
    if (img.complete) done();
    else {
      img.addEventListener('load', done, { once: true });
      img.addEventListener('error', done, { once: true });
    }
  });

 
  const loader = document.getElementById('topLoader');
  if (loader) {
    window.addEventListener('beforeunload', function () { loader.classList.add('on'); });
    window.addEventListener('pageshow', function () { loader.classList.remove('on'); });
  }
})();


(function () {
  const loader = document.getElementById('pageLoader');
  if (!loader) return;

  const MIN_SHOW = 500;  
  const MAX_WAIT = 4000;  
  const NAV_DELAY = 150;  

  let firstHidden = false;

  function hideFirst() {
    if (firstHidden) return;
    firstHidden = true;


    const wait = Math.max(0, MIN_SHOW - performance.now());

    setTimeout(function () {
      loader.style.animation = 'none';                       
      loader.classList.add('hide');
      document.documentElement.classList.add('loaded');     
    }, wait);
  }


  if (document.readyState === 'complete') hideFirst();
  else window.addEventListener('load', hideFirst, { once: true });
  setTimeout(hideFirst, MAX_WAIT);

  window.addEventListener('pageshow', function (e) {
    if (e.persisted) {
      loader.style.animation = 'none';
      loader.classList.add('hide');
      document.documentElement.classList.add('loaded');
    }
  });

  
  if (loader.dataset.nav !== 'true') return;

  let hideTimer;

  function show() {
    setTimeout(function () {
      loader.classList.remove('hide');
      clearTimeout(hideTimer);
      hideTimer = setTimeout(function () { loader.classList.add('hide'); }, MAX_WAIT + 4000); 
    }, NAV_DELAY);
  }


  document.addEventListener('click', function (e) {
    const a = e.target.closest('a[href]');
    if (!a || e.defaultPrevented) return;

    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;   
    if (a.target && a.target !== '_self') return;                                      
    if (a.hasAttribute('download') || a.hasAttribute('data-no-loader')) return;

    const url = new URL(a.href, location.href);
    if (url.origin !== location.origin) return;                                       
    if (url.pathname === location.pathname && url.search === location.search) return;  

    show();
  });


  document.addEventListener('submit', function (e) {
    const f = e.target;
    if (e.defaultPrevented || f.hasAttribute('data-no-loader') || f.target === '_blank') return;
    show();
  });
})();