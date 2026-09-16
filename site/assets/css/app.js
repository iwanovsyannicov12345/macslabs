/* mac:lab — interactions
   smooth scroll · split text · reveal · counters · header · menu · tilt
   spotlight · parallax · filter · configurator · cart · faq · lead form   */
(function () {
  'use strict';

  var html = document.documentElement;
  var body = document.body;
  var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var touch = matchMedia('(hover:none)').matches || innerWidth < 900;
  var fmt = function (n) { return n.toLocaleString('ru-RU').replace(/,/g, ' ') + ' ₽'; };

  /* ---------- 1. SMOOTH SCROLL (Lenis) ---------------------------------- */
  var lenis = null;
  if (window.Lenis && !reduced) {
    lenis = new Lenis({ duration: 1.15, lerp: .09, wheelMultiplier: .95, smoothWheel: true });
    (function raf(t) { lenis.raf(t); requestAnimationFrame(raf); })();
  }
  function scrollTo(target) {
    if (lenis) lenis.scrollTo(target, { offset: -80, duration: 1.4 });
    else target.scrollIntoView({ behavior: 'smooth' });
  }
  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href^="#"]');
    if (!a) return;
    var id = a.getAttribute('href');
    if (id.length < 2) return;
    var el = document.querySelector(id);
    if (!el) return;
    e.preventDefault();
    closeMenu();
    scrollTo(el);
  });

  /* ---------- 2. SPLIT TEXT --------------------------------------------- */
  function split(el) {
    if (el.dataset.done) return;
    el.dataset.done = '1';
    var out = '';
    el.innerHTML.split(/(<br\s*\/?>)/i).forEach(function (chunk) {
      if (/^<br/i.test(chunk)) { out += chunk; return; }
      chunk.split(/\s+/).filter(Boolean).forEach(function (w, i) {
        out += '<span class="word"><i style="transition-delay:' + (i * .045).toFixed(3) + 's">' + w + '</i></span> ';
      });
    });
    el.innerHTML = out;
  }
  [].forEach.call(document.querySelectorAll('.split'), split);

  /* ---------- 3. REVEAL + COUNTERS -------------------------------------- */
  function animateCount(el) {
    var to = parseFloat(el.dataset.count), suf = el.dataset.suffix || '', t0 = null, dur = 1500;
    function step(t) {
      if (!t0) t0 = t;
      var p = Math.min((t - t0) / dur, 1);
      var e = 1 - Math.pow(1 - p, 4);
      el.textContent = Math.round(to * e).toLocaleString('ru-RU').replace(/,/g, ' ') + suf;
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (!en.isIntersecting) return;
      en.target.classList.add('in');
      [].forEach.call(en.target.querySelectorAll('[data-count]'), function (c) {
        if (!c.dataset.ran) { c.dataset.ran = '1'; animateCount(c); }
      });
      if (en.target.dataset.count && !en.target.dataset.ran) {
        en.target.dataset.ran = '1'; animateCount(en.target);
      }
      io.unobserve(en.target);
    });
  }, { threshold: .18, rootMargin: '0px 0px -8%' });
  [].forEach.call(document.querySelectorAll('[data-rv],.split,[data-count]'), function (el) { io.observe(el); });

  /* ---------- 4. HEADER -------------------------------------------------- */
  var hdr = document.getElementById('hdr'), prog = document.getElementById('prog'), last = 0;
  function onScroll() {
    var y = scrollY;
    hdr.classList.toggle('stuck', y > 40);
    hdr.classList.toggle('hide', y > 420 && y > last && !body.classList.contains('menu-open'));
    last = y;
    var max = document.documentElement.scrollHeight - innerHeight;
    prog.style.transform = 'scaleX(' + (max > 0 ? y / max : 0) + ')';
    parallax(y);
    queueMorph();
  }
  addEventListener('scroll', onScroll, { passive: true });
  addEventListener('resize', function () { measureMorph(); morphFrame(); });

  /* ---------- 5. PARALLAX ------------------------------------------------ */
  var pxItems = [].map.call(document.querySelectorAll('[data-parallax]'), function (el) {
    return { el: el, img: el.querySelector('.bgimg img') };
  }).filter(function (o) { return o.img; });
  function parallax(y) {
    if (reduced) return;
    pxItems.forEach(function (o) {
      var r = o.el.getBoundingClientRect();
      if (r.bottom < -200 || r.top > innerHeight + 200) return;
      var p = (r.top + r.height / 2 - innerHeight / 2) / innerHeight;
      o.img.style.setProperty('--py', (p * -8).toFixed(2) + '%');
    });
  }
  parallax(scrollY);
  measureMorph();
  morphFrame();

  /* ---------- 5c. WIREFRAME-РЕЛЬЕФ (canvas) -------------------------------
     Карта высот считается один раз (fBm из value-noise, зациклена по глубине),
     в кадре — только проекция точек и ОДИН stroke на слой.                   */
  function initMesh(id, opt) {
    var cv = document.getElementById(id);
    if (!cv || reduced) return;
    opt = opt || {};

    var COLS = opt.cols || 74, ROWS = opt.rows || 40, MAP = 128;
    var AMP = opt.amp || 0.62, SPEED = opt.speed || 0.9, ALPHA = opt.alpha || 1;
    var ctx = cv.getContext('2d', { alpha: true });
    var w = 0, h = 0, dpr = 1, offset = 0, last = 0, visible = true, still = false;
    var px = new Float32Array(COLS * ROWS), py = new Float32Array(COLS * ROWS);
    var mx = 0, tx = 0;

    /* --- периодический value-noise --- */
    function lattice(cells) {
      var g = new Float32Array(cells * cells);
      for (var i = 0; i < g.length; i++) g[i] = Math.random();
      return function (u, v) {
        var x = u * cells, y = v * cells;
        var x0 = Math.floor(x), y0 = Math.floor(y);
        var fx = x - x0, fy = y - y0;
        fx = fx * fx * (3 - 2 * fx); fy = fy * fy * (3 - 2 * fy);
        var xa = ((x0 % cells) + cells) % cells, xb = (xa + 1) % cells;
        var ya = ((y0 % cells) + cells) % cells, yb = (ya + 1) % cells;
        var v00 = g[ya * cells + xa], v10 = g[ya * cells + xb];
        var v01 = g[yb * cells + xa], v11 = g[yb * cells + xb];
        return (v00 * (1 - fx) + v10 * fx) * (1 - fy) + (v01 * (1 - fx) + v11 * fx) * fy;
      };
    }
    var n1 = lattice(4), n2 = lattice(8), n3 = lattice(16);

    /* --- карта высот COLS × MAP, считается один раз --- */
    var H = new Float32Array(COLS * MAP);
    (function buildMap() {
      for (var j = 0; j < MAP; j++) {
        var v = j / MAP;
        for (var i = 0; i < COLS; i++) {
          var u = i / COLS;
          var val = n1(u, v) * 0.6 + n2(u, v) * 0.3 + n3(u, v) * 0.14;
          /* к краям рельеф затухает — как в референсе */
          var edge = Math.sin(Math.PI * Math.min(Math.max(i / (COLS - 1), 0), 1));
          H[j * COLS + i] = (val - 0.5) * edge * edge;
        }
      }
    })();

    function resize() {
      var r = cv.getBoundingClientRect();
      w = r.width; h = r.height;
      dpr = Math.min(devicePixelRatio || 1, w > 1100 ? 1.25 : 1.5);
      cv.width = Math.round(w * dpr); cv.height = Math.round(h * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function compute() {
      var cx = w / 2, cy = h * 0.5;
      var spanX = w * 0.62, spanY = h * 0.42, amp = h * AMP;
      var base = offset, j, i, k, row0, row1, f, hh, z, sx, sy, scale;
      for (j = 0; j < ROWS; j++) {
        z = j / (ROWS - 1);                       /* 0 — дальний край */
        scale = 0.52 + z * 0.72;                  /* перспектива */
        sy = cy + (z - 0.46) * spanY * 1.5;
        var mapPos = base + z * (MAP * 0.42);
        row0 = Math.floor(mapPos) % MAP; if (row0 < 0) row0 += MAP;
        row1 = (row0 + 1) % MAP;
        f = mapPos - Math.floor(mapPos);
        for (i = 0; i < COLS; i++) {
          k = j * COLS + i;
          hh = H[row0 * COLS + i] * (1 - f) + H[row1 * COLS + i] * f;
          sx = cx + ((i / (COLS - 1)) - 0.5) * spanX * 2 * scale + tx * (0.4 + z * 0.6);
          px[k] = sx;
          py[k] = sy - hh * amp * (0.45 + z * 0.75);
        }
      }
    }

    function stroke(fromRow, toRow, alpha) {
      var i, j, k;
      ctx.beginPath();
      for (j = fromRow; j < toRow; j++) {
        for (i = 0; i < COLS; i++) {
          k = j * COLS + i;
          i ? ctx.lineTo(px[k], py[k]) : ctx.moveTo(px[k], py[k]);
        }
      }
      for (i = 0; i < COLS; i++) {
        for (j = fromRow; j < toRow; j++) {
          k = j * COLS + i;
          j === fromRow ? ctx.moveTo(px[k], py[k]) : ctx.lineTo(px[k], py[k]);
        }
      }
      ctx.strokeStyle = 'rgba(226,226,232,' + (alpha * ALPHA).toFixed(3) + ')';
      ctx.stroke();
    }

    function render() {
      compute();
      ctx.clearRect(0, 0, w, h);
      ctx.lineWidth = 1;
      var mid = Math.round(ROWS * 0.55);
      stroke(0, mid + 1, 0.1);        /* дальние ряды бледнее */
      stroke(mid, ROWS, 0.2);
    }

    function frame(now) {
      requestAnimationFrame(frame);
      if (!visible || still) return;
      if (now - last < (innerWidth < 768 ? 50 : 40)) return;   /* 20–24 fps */
      last = now;
      offset += SPEED * 0.045;
      tx += (mx - tx) * 0.05;
      render();
    }

    addEventListener('resize', function () { resize(); render(); }, { passive: true });
    if (matchMedia('(hover:hover)').matches) {
      addEventListener('mousemove', function (e) {
        mx = (e.clientX / innerWidth - 0.5) * 34;
      }, { passive: true });
    }
    new IntersectionObserver(function (en) { visible = en[0].isIntersecting; },
      { rootMargin: '100px' }).observe(cv);

    resize();
    /* статичный кадр — только на совсем слабых устройствах */
    still = (navigator.hardwareConcurrency || 8) <= 2;
    render();
    if (!still) requestAnimationFrame(frame);
    requestAnimationFrame(function () { cv.classList.add('on'); });
  }

  var narrow = innerWidth < 768;
  initMesh('mesh', { cols: narrow ? 30 : 48, rows: narrow ? 18 : 26, amp: 0.5, alpha: 0.85, speed: 0.7 });
  initMesh('mesh2', { cols: narrow ? 38 : 64, rows: narrow ? 22 : 34, amp: 0.66, alpha: 1.3, speed: 0.9 });

  /* ---------- 6. MORPH: карточки стягиваются в кнопку ---------------------- */
  var morph = document.getElementById('morph');
  var mCards = morph ? [].slice.call(morph.querySelectorAll('.mcard')) : [];
  var mTop = morph && morph.querySelector('.morph-top');
  var mBot = morph && morph.querySelector('.morph-bottom');
  var mBtn = morph && morph.querySelector('.morph-btn');
  var mSize = [], btnSize = { w: 240, h: 58 };

  function measureMorph() {
    if (!morph) return;
    mSize = mCards.map(function (c) {
      c.style.width = ''; c.style.height = '';
      return { w: c.offsetWidth, h: c.offsetHeight };
    });
    if (mBtn) btnSize = { w: mBtn.offsetWidth, h: mBtn.offsetHeight };
  }

  var mTick = false;
  function queueMorph() {
    if (mTick) return;
    mTick = true;
    requestAnimationFrame(function () { mTick = false; morphFrame(); });
  }

  function morphFrame() {
    if (!morph) return;
    var r = morph.getBoundingClientRect();
    var total = morph.offsetHeight - innerHeight;
    var p = total > 0 ? Math.min(Math.max(-r.top / total, 0), 1) : 0;

    var gather = Math.min(p / 0.66, 1);                       /* стягивание к центру */
    var ease = gather * gather * (3 - 2 * gather);   /* smoothstep */
    var fade = Math.min(Math.max((p - 0.14) / 0.2, 0), 1);    /* гаснет текст плашек */
    var fill = Math.min(Math.max((p - 0.3) / 0.26, 0), 1);    /* плашки краснеют */
    var solid = Math.min(Math.max((p - 0.16) / 0.26, 0), 1);  /* уходят фон и рамка */
    var merge = Math.min(Math.max((ease - 0.72) / 0.28, 0), 1); /* лишние слои гаснут */
    var out = Math.min(Math.max((p - 0.66) / 0.07, 0), 1);    /* плашки исчезают */
    var show = Math.min(Math.max((p - 0.67) / 0.13, 0), 1);   /* появляется кнопка */

    mCards.forEach(function (c, i) {
      var st = mSize[i] || { w: c.offsetWidth, h: c.offsetHeight };
      c.style.setProperty('--k', (1 - ease).toFixed(4));
      c.style.setProperty('--t', (1 - fade).toFixed(3));
      c.style.setProperty('--fill', fill.toFixed(3));
      c.style.setProperty('--bo', (1 - solid).toFixed(3));
      c.style.setProperty('--bgo', (1 - solid).toFixed(3));
      /* при слиянии оставляем один слой — иначе видны стыки и рамки */
      c.style.setProperty('--op', ((1 - out) * (i ? 1 - merge : 1)).toFixed(3));
      c.style.width = (st.w + (btnSize.w - st.w) * ease).toFixed(1) + 'px';
      c.style.height = (st.h + (btnSize.h - st.h) * ease).toFixed(1) + 'px';
    });
    if (mTop) {
      mTop.style.setProperty('--top-op', (1 - Math.min(Math.max((p - 0.3) / 0.25, 0), 1)).toFixed(3));
      mTop.style.setProperty('--top-shift', Math.min(p / 0.6, 1).toFixed(3));
    }
    if (mBot) {
      mBot.style.setProperty('--bot-op', show.toFixed(3));
      mBot.style.setProperty('--bot-pe', show > 0.9 ? 'auto' : 'none');
      mBot.style.setProperty('--btn-sc', (0.9 + show * 0.1).toFixed(3));
    }
  }

  /* ---------- 7. SPOTLIGHT + TILT ---------------------------------------- */
  [].forEach.call(document.querySelectorAll('.card'), function (c) {
    c.addEventListener('mousemove', function (e) {
      var r = c.getBoundingClientRect();
      c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      c.style.setProperty('--my', (e.clientY - r.top) + 'px');
      if (c.hasAttribute('data-tilt') && !touch) {
        var px = (e.clientX - r.left) / r.width - .5, py = (e.clientY - r.top) / r.height - .5;
        c.style.transform = 'perspective(900px) rotateX(' + (-py * 5).toFixed(2) + 'deg) rotateY(' +
          (px * 6).toFixed(2) + 'deg) translateY(-6px)';
      }
    });
    c.addEventListener('mouseleave', function () { c.style.transform = ''; });
  });

  /* ---------- 8. FULLSCREEN MENU ----------------------------------------- */
  var menu = document.getElementById('menu'), burger = document.getElementById('burger');
  var mLinks = document.getElementById('mLinks'), mImgs = document.querySelectorAll('#mMedia img');
  [].forEach.call(mLinks.children, function (a, i) {
    a.style.setProperty('--d', (.18 + i * .06).toFixed(2) + 's');
    a.addEventListener('mouseenter', function () {
      [].forEach.call(mImgs, function (im, j) { im.classList.toggle('on', i === j); });
    });
  });
  function openMenu() {
    menu.classList.add('open'); body.classList.add('menu-open', 'is-locked');
    if (lenis) lenis.stop();
  }
  function closeMenu() {
    if (!menu.classList.contains('open')) return;
    menu.classList.remove('open'); body.classList.remove('menu-open', 'is-locked');
    if (lenis) lenis.start();
  }
  burger.addEventListener('click', function () {
    menu.classList.contains('open') ? closeMenu() : openMenu();
  });
  addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeMenu(); closeCart(); } });

  /* ---------- 9. MARQUEE (duplicate for seamless loop) -------------------- */
  var mq = document.getElementById('mqTrack');
  mq.innerHTML += mq.innerHTML;

  /* ---------- 10. CATALOG FILTER ----------------------------------------- */
  var tabs = document.querySelectorAll('.tab'), prods = document.querySelectorAll('.prod');
  [].forEach.call(tabs, function (t) {
    t.addEventListener('click', function () {
      [].forEach.call(tabs, function (x) { x.classList.remove('on'); });
      t.classList.add('on');
      var f = t.dataset.filter;
      STEP = 6;
      [].forEach.call(prods, function (p, i) {
        var show = f === 'all' || p.dataset.cat === f;
        p.classList.toggle('hidden', !show);
        if (show) {
          p.style.animation = 'none'; void p.offsetWidth;
          p.style.opacity = 0; p.style.transform = 'translateY(22px)';
          setTimeout(function () {
            p.style.transition = 'opacity .6s var(--ease),transform .7s var(--ease)';
            p.style.opacity = 1; p.style.transform = '';
          }, 40 + i * 55);
        }
      });
      applyFold();
    });
  });

  /* ---------- 10b. ПОКАЗАТЬ ЕЩЁ ------------------------------------------- */
  var STEP = 6, moreBtn = document.getElementById('moreBtn');
  function applyFold() {
    var shown = 0, hidden = 0;
    [].forEach.call(prods, function (p) {
      if (p.classList.contains('hidden')) { p.classList.remove('folded'); return; }
      shown++;
      var fold = shown > STEP;
      p.classList.toggle('folded', fold);
      if (fold) hidden++;
    });
    if (moreBtn) {
      moreBtn.parentElement.classList.toggle('done', hidden === 0);
      moreBtn.querySelector('span').textContent = 'Показать ещё ' + hidden;
    }
  }
  if (moreBtn) {
    moreBtn.addEventListener('click', function () {
      STEP += 6; applyFold();
      [].forEach.call(document.querySelectorAll('.prod:not(.folded):not(.hidden)'), function (p, i) {
        if (!p.classList.contains('in')) {
          setTimeout(function () { p.classList.add('in'); }, i * 40);
        }
      });
    });
  }
  applyFold();

  /* ---------- 11. FAVOURITES --------------------------------------------- */
  document.addEventListener('click', function (e) {
    var f = e.target.closest('.prod-fav');
    if (f) { e.preventDefault(); f.classList.toggle('on'); }
  });

  /* ---------- 12. CART ---------------------------------------------------- */
  var cart = [];
  try { cart = JSON.parse(localStorage.getItem('maclab-cart') || '[]'); } catch (err) { cart = []; }
  var cartEl = document.getElementById('cart'), scrim = document.getElementById('scrim');
  var cartBody = document.getElementById('cartBody'), cartTotal = document.getElementById('cartTotal');
  var cartDot = document.getElementById('cartDot');

  function saveCart() { try { localStorage.setItem('maclab-cart', JSON.stringify(cart)); } catch (err) {} }
  function renderCart() {
    if (!cart.length) {
      cartBody.innerHTML = '<p class="cart-empty">Пока пусто — добавьте технику из каталога</p>';
    } else {
      cartBody.innerHTML = cart.map(function (it, i) {
        return '<div class="ci"><img src="' + it.img + '" alt=""><div><b>' + it.name +
          '</b><s>' + fmt(it.price) + '</s></div><button class="rm" data-i="' + i + '" aria-label="Удалить">&times;</button></div>';
      }).join('');
    }
    var total = cart.reduce(function (s, i) { return s + i.price; }, 0);
    cartTotal.textContent = fmt(total);
    cartDot.textContent = cart.length;
    cartDot.classList.toggle('on', cart.length > 0);
    saveCart();
  }
  function openCart() { cartEl.classList.add('on'); scrim.classList.add('on'); if (lenis) lenis.stop(); }
  function closeCart() { cartEl.classList.remove('on'); scrim.classList.remove('on'); if (lenis) lenis.start(); }
  function addToCart(item, btn) {
    cart.push(item); renderCart();
    if (btn) {
      btn.classList.add('done');
      setTimeout(function () { btn.classList.remove('done'); }, 900);
    }
    cartDot.animate(
      [{ transform: 'scale(1)' }, { transform: 'scale(1.45)' }, { transform: 'scale(1)' }],
      { duration: 420, easing: 'cubic-bezier(.22,1,.36,1)' }
    );
  }
  document.getElementById('cartBtn').addEventListener('click', openCart);
  document.getElementById('cartClose').addEventListener('click', closeCart);
  scrim.addEventListener('click', closeCart);
  cartBody.addEventListener('click', function (e) {
    var rm = e.target.closest('.rm');
    if (!rm) return;
    var row = rm.closest('.ci'), i = +rm.dataset.i;
    row.classList.add('out');
    rm.disabled = true;
    setTimeout(function () { cart.splice(i, 1); renderCart(); }, 380);
  });
  [].forEach.call(document.querySelectorAll('.prod .add'), function (b) {
    b.addEventListener('click', function () {
      var p = b.closest('.prod');
      addToCart({ name: p.dataset.name, price: +p.dataset.price, img: p.dataset.img }, b);
    });
  });
  renderCart();

  /* ---------- 12b. ОФОРМЛЕНИЕ ЗАКАЗА -------------------------------------- */
  var orderForm = document.getElementById('orderForm');
  if (orderForm) {
    var oName = orderForm.querySelector('#oName');
    var oPhone = orderForm.querySelector('#oPhone');
    var oMsg = document.getElementById('orderMsg');
    phoneMask(oPhone);
    oName.addEventListener('input', function () { this.classList.remove('err'); });

    orderForm.addEventListener('submit', function (e) {
      e.preventDefault();
      oMsg.classList.remove('err');
      if (!cart.length) { oMsg.textContent = 'Сначала добавьте технику в корзину'; oMsg.classList.add('err'); return; }
      var ok = true;
      if (oName.value.trim().length < 2) { oName.classList.add('err'); ok = false; }
      if (oPhone.value.replace(/\D/g, '').length < 11) { oPhone.classList.add('err'); ok = false; }
      if (!ok) { orderForm.querySelector('.err').focus(); return; }

      var btn = orderForm.querySelector('button[type="submit"]');
      busy(btn, true);
      oMsg.textContent = '';

      send('order', {
        name: oName.value.trim(),
        phone: oPhone.value,
        items: cart,
        hp: orderForm.querySelector('[name="hp"]').value
      }).then(function () {
        cart = [];
        renderCart();
        orderForm.reset();
        oMsg.textContent = 'Заказ принят — перезвоним в ближайшие 15 минут';
      }).catch(function (err) {
        oMsg.textContent = err.message;
        oMsg.classList.add('err');
      }).then(function () { busy(btn, false); });
    });
  }

  /* ---------- 13. CONFIGURATOR -------------------------------------------- */
  var cfgImg = document.getElementById('cfgImg'), cfgPrice = document.getElementById('cfgPrice');
  var cfgMonth = document.getElementById('cfgMonth'), cfgNameEl = document.getElementById('cfgName');
  var cfgTags = document.getElementById('cfgTags'), cfgSum = document.getElementById('cfgSum');

  function pick(group) {
    var g = document.querySelector('[data-group="' + group + '"]');
    return g.querySelector('.opt.on') || g.querySelector('.opt');
  }
  function label(group) { return pick(group).dataset.label || pick(group).textContent.trim(); }
  function cfgTotal() {
    return ['model', 'ram', 'ssd', 'color'].reduce(function (s, g) { return s + (+pick(g).dataset.price || 0); }, 0);
  }
  function cfgName() {
    return label('model') + ' · ' + label('ram') + ' / ' + label('ssd') + ' · ' + label('color');
  }
  function updateCfg(newImg) {
    var total = cfgTotal();
    cfgPrice.childNodes[0].nodeValue = fmt(total);
    cfgMonth.textContent = fmt(Math.round(total / 12)) + ' × 12 месяцев';
    cfgNameEl.textContent = label('model');
    cfgTags.innerHTML = [pick('model').dataset.chip, label('ram') + ' RAM', label('ssd') + ' SSD', label('color')]
      .map(function (t) { return '<span>' + t + '</span>'; }).join('');
    cfgSum.innerHTML = [
      ['Базовая модель', +pick('model').dataset.price],
      ['Память ' + label('ram'), +pick('ram').dataset.price],
      ['Накопитель ' + label('ssd'), +pick('ssd').dataset.price],
      ['Цвет ' + label('color'), +pick('color').dataset.price]
    ].map(function (r) {
      return '<div><b>' + r[0] + '</b><span>' + (r[1] ? '+ ' + fmt(r[1]) : 'включено') + '</span></div>';
    }).join('');
    if (newImg) {
      cfgImg.style.opacity = 0; cfgImg.style.transform = 'scale(1.05)';
      setTimeout(function () {
        cfgImg.src = newImg;
        cfgImg.style.opacity = 1; cfgImg.style.transform = '';
      }, 240);
    }
  }
  [].forEach.call(document.querySelectorAll('.opts'), function (grp) {
    grp.addEventListener('click', function (e) {
      var o = e.target.closest('.opt');
      if (!o) return;
      [].forEach.call(grp.children, function (x) { x.classList.remove('on'); });
      o.classList.add('on');
      updateCfg(o.dataset.img || null);
    });
  });
  updateCfg(null);
  document.getElementById('cfgAdd').addEventListener('click', function () {
    addToCart({ name: cfgName(), price: cfgTotal(), img: pick('model').dataset.img });
    openCart();
  });

  /* ---------- 14. FAQ ------------------------------------------------------ */
  [].forEach.call(document.querySelectorAll('.faq-q'), function (q) {
    q.addEventListener('click', function () {
      var item = q.parentElement, a = item.querySelector('.faq-a');
      var open = item.classList.contains('open');
      [].forEach.call(document.querySelectorAll('.faq-i.open'), function (i) {
        i.classList.remove('open'); i.querySelector('.faq-a').style.maxHeight = 0;
      });
      if (!open) { item.classList.add('open'); a.style.maxHeight = a.scrollHeight + 'px'; }
    });
  });

  /* ---------- 14b. ОТПРАВКА НА СЕРВЕР (WordPress REST) --------------------- */
  var T0 = Date.now();
  var API = window.MACLAB || null;   /* на статической версии его нет */

  function phoneMask(input) {
    input.addEventListener('input', function () {
      var d = input.value.replace(/\D/g, '');
      if (d[0] === '8') d = '7' + d.slice(1);
      if (d[0] !== '7') d = '7' + d;
      d = d.slice(0, 11);
      var out = '+7';
      if (d.length > 1) out += ' (' + d.slice(1, 4);
      if (d.length >= 5) out += ') ' + d.slice(4, 7);
      if (d.length >= 8) out += '-' + d.slice(7, 9);
      if (d.length >= 10) out += '-' + d.slice(9, 11);
      input.value = out;
      input.classList.remove('err');
    });
  }

  function send(route, data) {
    data.elapsed = Date.now() - T0;
    data.page = location.href;
    if (!API) {                       /* демо-режим без WordPress */
      return new Promise(function (resolve) { setTimeout(function () { resolve({ ok: true }); }, 400); });
    }
    return fetch(API.rest + route, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': API.nonce },
      body: JSON.stringify(data)
    }).then(function (r) {
      return r.json().then(function (body) {
        if (!r.ok) throw new Error(body && body.message ? body.message : 'Ошибка отправки');
        return body;
      });
    });
  }

  function busy(btn, on) {
    btn.classList.toggle('loading', on);
    btn.disabled = on;
  }

  /* ---------- 15. ФОРМА ЗАЯВКИ -------------------------------------------- */
  var form = document.getElementById('leadForm');
  if (form) {
    var phone = form.querySelector('#lPhone');
    var name = form.querySelector('#lName');
    var msg = document.getElementById('leadMsg');
    var ways = document.getElementById('lWays');

    phoneMask(phone);
    name.addEventListener('input', function () { this.classList.remove('err'); });
    ways.addEventListener('click', function (e) {
      var w = e.target.closest('.way');
      if (!w) return;
      [].forEach.call(ways.children, function (x) { x.classList.remove('on'); });
      w.classList.add('on');
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var ok = true;
      if (name.value.trim().length < 2) { name.classList.add('err'); ok = false; }
      if (phone.value.replace(/\D/g, '').length < 11) { phone.classList.add('err'); ok = false; }
      if (!ok) { form.querySelector('.err').focus(); return; }

      var btn = form.querySelector('button[type="submit"]');
      var way = ways.querySelector('.way.on');
      busy(btn, true);
      if (msg) { msg.textContent = ''; msg.classList.remove('err'); }

      send('lead', {
        name: name.value.trim(),
        phone: phone.value,
        what: form.querySelector('#lWhat').value,
        way: way ? way.dataset.way : 'Звонок',
        hp: form.querySelector('[name="hp"]') ? form.querySelector('[name="hp"]').value : ''
      }).then(function () {
        document.getElementById('leadDone').hidden = false;
      }).catch(function (err) {
        if (msg) { msg.textContent = err.message; msg.classList.add('err'); }
      }).then(function () { busy(btn, false); });
    });
  }

  /* ---------- 15b. LINKS THAT OPEN A CATALOG TAB --------------------------- */
  [].forEach.call(document.querySelectorAll('[data-tab]'), function (a) {
    a.addEventListener('click', function () {
      var t = document.querySelector('.tab[data-filter="' + a.dataset.tab + '"]');
      if (t) setTimeout(function () { t.click(); }, 260);
    });
  });

  /* ---------- 16. INTRO ---------------------------------------------------- */
  if (window.gsap && !reduced) {
    gsap.fromTo('.hero-main', { scale: 1.03, opacity: 0 }, { scale: 1, opacity: 1, duration: 1.2, ease: 'expo.out' });
    gsap.fromTo('.hdr', { y: -30, opacity: 0 }, { y: 0, opacity: 1, duration: .9, ease: 'expo.out', delay: .1 });
  }

  onScroll();
})();
