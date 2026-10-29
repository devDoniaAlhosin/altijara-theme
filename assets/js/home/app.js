/**
 * Altijara Magazine - Home page live content controller
 * Uses shared helpers from wp-api.js (window.AltijaraWP)
 */

const WP = window.AltijaraWP || {};
const API_BASE = WP.API_BASE
  || ((window.AltijaraTheme && window.AltijaraTheme.urls && window.AltijaraTheme.urls.rest)
    ? String(window.AltijaraTheme.urls.rest).replace(/\/$/, '')
    : '');
const trimAtWord = WP.trimAtWord || ((t, n) => (t || '').slice(0, n));
const formatPostData = WP.formatPostData;
const fetchLiveArticles = WP.fetchLiveArticles || (async () => []);
const fetchPostsByCategory = WP.fetchPostsByCategory || (async () => []);

/**
 * Legacy category IDs for non-SSR / static prototype fallback only.
 * When [data-altijara-home-ssr] is present, loadRealContent skips REST fills.
 */
const HOME_WP_IDS = {
  chamber: 188,
  interviews: 319,
  personality: 703,
  logistics: 321,
  followups: 190,
  business: 320,
  energy: 372,
  automotive: 194,
  tech: 275,
  health: 326,
  tourism: 374
};

function articleHref(post, catKey) {
  return WP.articlePageUrl ? WP.articlePageUrl(post, catKey) : 'article.html';
}

function resolveWpId(catKey) {
  return HOME_WP_IDS[catKey] || WP.getWpCategoryId?.(catKey) || null;
}

function fillChamberLead(leadEl, post) {
  if (!leadEl || !post) return;
  const imgLink = leadEl.querySelector('.chamber-lead-img-wrap');
  const img = leadEl.querySelector('.chamber-lead-img-wrap img');
  if (imgLink) imgLink.href = articleHref(post, 'chamber');
  if (img) {
    img.src = post.img;
    img.alt = post.title;
  }
  const titleLink = leadEl.querySelector('.chamber-lead-title a');
  if (titleLink) {
    titleLink.textContent = post.title;
    titleLink.href = articleHref(post, 'chamber');
  }
  const excerpt = leadEl.querySelector('.chamber-lead-excerpt');
  if (excerpt) {
    const text = trimAtWord(post.excerpt || post.fullExcerpt || '', 160);
    excerpt.textContent = text || '';
  }
}

function fillChamberItem(item, post) {
  if (!item || !post) return;

  const thumbLink = item.querySelector('.chamber-item-thumb');
  const thumb = item.querySelector('.chamber-item-thumb img');
  if (thumbLink) thumbLink.href = articleHref(post, 'chamber');
  if (thumb) {
    thumb.src = post.img;
    thumb.alt = post.title;
  }

  const kicker = item.querySelector('.chamber-item-kicker');
  if (kicker) kicker.textContent = post.category || '';

  const titleLink = item.querySelector('.chamber-item-title a');
  if (titleLink) {
    titleLink.textContent = post.title;
    titleLink.href = articleHref(post, 'chamber');
  }

  const time = item.querySelector('.chamber-item-time');
  if (time) time.textContent = post.dateText || post.timeText || '';
}

function escapeHtml(value) {
  return String(value || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/** Middle hero cover slider — شخصية العدد (WP category 703). */
let heroCoverPosts = [];

function syncLeadToCoverPost(post) {
  if (!post) return;
  const title = post.title || '';
  const href = post.href || (typeof articleHref === 'function' ? articleHref(post) : (post.link || '#'));
  const excerpt = trimAtWord(post.fullExcerpt || post.excerpt || '', 560);
  const mainTitle = document.querySelector('.lead-primary-story .lead-title-giant a');
  if (mainTitle) {
    mainTitle.textContent = title;
    mainTitle.href = href;
  }
  const mainExcerpt = document.querySelector('.lead-primary-story .lead-excerpt-text');
  if (mainExcerpt) {
    mainExcerpt.textContent = excerpt;
  }
}

/** Seed slider state from SSR slides (category issue-cover-personality). */
function seedHeroCoverPostsFromDom(root) {
  if (!root) return;
  const slides = Array.from(root.querySelectorAll('.hero-cover-slide'));
  heroCoverPosts = slides.map((slide) => ({
    id: slide.dataset.postId || '',
    title: slide.dataset.title || slide.querySelector('.magazine-cover-title a')?.textContent?.trim() || '',
    excerpt: slide.dataset.excerpt || '',
    fullExcerpt: slide.dataset.excerpt || '',
    href: slide.dataset.href || slide.querySelector('.magazine-cover-title a')?.getAttribute('href') || '#',
    link: slide.dataset.href || '',
    dateText: slide.dataset.date || slide.querySelector('.magazine-cover-date')?.textContent?.trim() || '',
    img: slide.querySelector('img')?.getAttribute('src') || ''
  }));
}

function fillHeroCoverSlider(posts) {
  const root = document.getElementById('heroCoverSlider');
  if (!root || !posts?.length) return;

  const meta = root.querySelector('.hero-cover-meta');
  root.querySelectorAll('.hero-cover-slide').forEach((el) => el.remove());
  posts.forEach((p, i) => {
    const slide = document.createElement('div');
    slide.className = `hero-cover-slide${i === 0 ? ' is-active' : ''}`;
    const date = p.category === 'العدد الجديد' && p.dateText
      ? `${p.dateText} — الإصدار الرقمي`
      : (p.dateText || '');
    slide.innerHTML = `
      <div class="magazine-cover-img-wrap">
        <a href="${escapeHtml(articleHref(p))}">
          <img src="${escapeHtml(p.img)}" alt="${escapeHtml(p.title)}">
        </a>
      </div>
      <div class="magazine-cover-details">
        <h3 class="magazine-cover-title">
          <a href="${escapeHtml(articleHref(p))}">${escapeHtml(p.title)}</a>
        </h3>
        <span class="magazine-cover-date">${escapeHtml(date)}</span>
      </div>`;
    if (meta) root.insertBefore(slide, meta);
    else root.appendChild(slide);
  });

  const visibleDate = root.querySelector('.hero-cover-meta .magazine-cover-date');
  const firstDate = posts[0]?.dateText
    ? (posts[0].category === 'العدد الجديد' ? `${posts[0].dateText} — الإصدار الرقمي` : posts[0].dateText)
    : '';
  if (visibleDate) visibleDate.textContent = firstDate;

  root.dataset.index = '0';
  heroCoverPosts = posts.slice();
  syncLeadToCoverPost(posts[0]);
  const img = root.querySelector('.hero-cover-slide.is-active img');
  if (img && !img.complete) img.addEventListener('load', alignHeroLeadWithCover, { once: true });
  requestAnimationFrame(alignHeroLeadWithCover);
}

/** Middle hero list under the cover — لقاءات (WP category 319). */
function fillInterviewsList(posts) {
  if (!posts?.length) return;
  document.querySelectorAll('.international-wire-list .intl-wire-item').forEach((item, idx) => {
    const p = posts[idx];
    if (!p) return;
    const thumb = item.querySelector('.intl-wire-thumb img');
    if (thumb && p.img) {
      thumb.src = p.img;
      thumb.alt = p.title;
    }
    const catElem = item.querySelector('.intl-wire-cat');
    if (catElem) catElem.textContent = p.category || '';
    const titleLink = item.querySelector('.intl-wire-title a');
    if (titleLink) {
      titleLink.textContent = p.title;
      titleLink.href = articleHref(p, 'interviews');
    }
    const desc = item.querySelector('.intl-wire-desc');
    if (desc && p.excerpt) desc.textContent = p.excerpt;
    const timeElem = item.querySelector('.intl-wire-time');
    if (timeElem && (p.dateText || p.timeText)) timeElem.textContent = p.dateText || p.timeText;
  });
}

function fillChamberSection(posts) {
  if (!posts?.length) return;
  const box = document.querySelector('.chamber-box');
  if (!box) return;

  fillChamberLead(box.querySelector('.chamber-lead'), posts[0]);
  box.querySelectorAll('.chamber-opinion-list .chamber-opinion-item').forEach((item, idx) => {
    fillChamberItem(item, posts[idx + 1]);
  });
}

function revealLiveContent() {
  document.documentElement.classList.remove('live-pending');
  document.documentElement.classList.add('live-ready');
}

function ensureLiveLoadingUI() {
  if (!document.querySelector('.live-loading-bar')) {
    const bar = document.createElement('div');
    bar.className = 'live-loading-bar';
    bar.setAttribute('aria-hidden', 'true');
    document.body.prepend(bar);
  }
  if (!document.querySelector('.live-loading-hint')) {
    const hint = document.createElement('div');
    hint.className = 'live-loading-hint';
    hint.textContent = 'جارِ تحميل أحدث المقالات...';
    document.body.appendChild(hint);
  }
}

function applyHomeTruncation() {
  requestAnimationFrame(() => {
    document.querySelectorAll('.news-card-v .card-headline a').forEach((el) => {
      const full = el.getAttribute('data-full-title') || el.textContent;
      WP.truncateMiddleToLines?.(el, full, 2);
    });
    document.querySelectorAll('.most-read-text').forEach((el) => {
      const full = el.getAttribute('data-full-title') || el.getAttribute('title') || el.textContent;
      WP.truncateMiddleToLines?.(el, full, 2);
    });
  });
}

/** Paint from session cache immediately when switching pages */
function hydrateHomeFromCache() {
  const getCached = WP.getCachedPostsByCategory;
  if (!getCached) return false;

  let hits = 0;

  const chamber = getCached(HOME_WP_IDS.chamber, 7);
  if (chamber?.length) {
    hits++;
    fillChamberSection(chamber);
  }

  const interviews = getCached(HOME_WP_IDS.interviews, 10);
  if (interviews?.length) {
    hits++;
    fillInterviewsList(interviews);
  }

  const coverPersonality = getCached(HOME_WP_IDS.personality, 6);
  if (coverPersonality?.length) {
    hits++;
    fillHeroCoverSlider(coverPersonality);
  } else {
    const personality = getCached(HOME_WP_IDS.personality, 1);
    if (personality?.[0]) {
      hits++;
      syncLeadToCoverPost(personality[0]);
    }
  }

  // Logistics sub-hero is curated in HTML to match the designed layout/content.

  [
    ['followups', 'متابعات', 4],
    ['automotive', 'سيارات', 4],
    ['tech', 'تقنية', 3]
  ].forEach(([key, title, count]) => {
    const posts = getCached(resolveWpId(key), count);
    if (!posts?.length) return;
    hits++;
    const grid = document.querySelector(`[data-wp-cat="${key}"]`);
    if (!grid) return;
    grid.querySelectorAll('.news-card-v').forEach((card, idx) => fillNewsCard(card, posts[idx], title));
  });

  [
    ['business', 'مال وأعمال'],
    ['energy', 'الطاقة'],
    ['health', 'صحة'],
    ['tourism', 'سياحة وترفيه']
  ].forEach(([key, title]) => {
    const posts = getCached(resolveWpId(key), 3);
    if (!posts?.length) return;
    hits++;
    const col = document.querySelector(`.sector-col[data-wp-cat="${key}"]`);
    if (col) fillSectorColumn(col, posts, title);
  });

  const latest = WP.getCachedArticles?.('/posts?per_page=5&_embed');
  if (latest?.length) {
    hits++;
    document.querySelectorAll('.most-read-list[data-wp-cat="most-read"] .most-read-item').forEach((item, idx) => {
      const p = latest[idx];
      if (!p) return;
      const link = item.querySelector('.most-read-text');
      if (link) {
        link.setAttribute('data-full-title', p.title);
        link.textContent = p.title;
        link.href = articleHref(p);
      }
    });
  }

  return hits >= 3;
}

function fillNewsCard(card, post, sectionTitle, catKey) {
  if (!card || !post) return;

  const imgLink = card.querySelector('.card-img-wrap a');
  const img = card.querySelector('.card-img-wrap img');
  if (imgLink) imgLink.href = articleHref(post, catKey);
  if (img) {
    img.src = post.img;
    img.alt = post.title;
  }

  const tag = card.querySelector('.category-tag');
  if (tag) tag.textContent = post.category || '';

  const titleLink = card.querySelector('.card-headline a');
  if (titleLink) {
    titleLink.setAttribute('data-full-title', post.title);
    titleLink.textContent = post.title;
    titleLink.href = articleHref(post, catKey);
    if (typeof WP.truncateMiddleToLines === 'function') {
      WP.truncateMiddleToLines(titleLink, post.title, 2);
    }
  }

  let time = card.querySelector('.meta-time');
  if (!time) {
    time = document.createElement('span');
    time.className = 'meta-time';
    const bulletEl = card.querySelector('.card-bullet-sub');
    if (bulletEl) card.insertBefore(time, bulletEl);
    else card.appendChild(time);
  }
  time.textContent = post.dateText || post.timeText || '';

  const bullet = card.querySelector('.card-bullet-sub');
  if (bullet) {
    const full = post.excerpt || post.fullExcerpt || '';
    bullet.setAttribute('title', full);
    bullet.textContent = full;
  }
}

function fillSectorColumn(col, posts, sectionTitle, catKey) {
  if (!col || !posts?.length) return;

  const lead = col.querySelector('.sector-lead-card');
  if (lead && posts[0]) {
    const p = posts[0];
    const imgLink = lead.querySelector('.sector-lead-img-wrap a');
    const img = lead.querySelector('.sector-lead-img-wrap img');
    if (imgLink) imgLink.href = articleHref(p, catKey);
    if (img) {
      img.src = p.img;
      img.alt = p.title;
    }
    const titleLink = lead.querySelector('.sector-lead-title a');
    if (titleLink) {
      titleLink.textContent = p.title;
      titleLink.href = articleHref(p, catKey);
    }
    const desc = lead.querySelector('.sector-lead-desc');
    if (desc) {
      const exc = trimAtWord(p.excerpt || p.fullExcerpt || '', 160);
      if (exc) desc.textContent = exc;
    }
    const date = lead.querySelector('.sector-meta-date');
    if (date) date.textContent = p.dateText || p.timeText;
  }

  const subs = col.querySelectorAll('.sector-sub-card');
  subs.forEach((sub, idx) => {
    const p = posts[idx + 1];
    if (!p) return;
    const titleLink = sub.querySelector('.sector-sub-title a');
    if (titleLink) {
      titleLink.textContent = p.title;
      titleLink.href = articleHref(p, catKey);
    }
    const thumbLink = sub.querySelector('.sector-sub-thumb a');
    const thumb = sub.querySelector('.sector-sub-thumb img');
    if (thumbLink) thumbLink.href = articleHref(p, catKey);
    if (thumb) {
      thumb.src = p.img;
      thumb.alt = p.title;
    }
    const date = sub.querySelector('.sector-meta-date');
    if (date) date.textContent = p.dateText || p.timeText;
    const subTag = sub.querySelector('.sector-card-sub');
    if (subTag) subTag.textContent = p.category || '';
  });
}

function fillGridByCat(catKey, sectionTitle) {
  const grid = document.querySelector(`[data-wp-cat="${catKey}"]`);
  if (!grid) return Promise.resolve();
  const wpId = resolveWpId(catKey);
  if (!wpId) return Promise.resolve();

  const cards = grid.querySelectorAll('.news-card-v');
  const count = Math.max(cards.length, 1);

  return fetchPostsByCategory(wpId, count).then((posts) => {
    if (!posts.length) return;
    cards.forEach((card, idx) => fillNewsCard(card, posts[idx], sectionTitle, catKey));
  });
}

function fillSectorByCat(catKey, sectionTitle) {
  const col = document.querySelector(`.sector-col[data-wp-cat="${catKey}"]`);
  if (!col) return Promise.resolve();
  const wpId = resolveWpId(catKey);
  if (!wpId) return Promise.resolve();

  return fetchPostsByCategory(wpId, 3).then((posts) => {
    fillSectorColumn(col, posts, sectionTitle, catKey);
  });
}

/** Map SSR section keys → fill helpers (server-rendered foundation). */
function hydrateHomeFromSSR() {
  const ssr = window.AltijaraSSR;
  if (!ssr || typeof ssr !== 'object') return false;
  let used = false;
  if (ssr.hero_chamber?.length) {
    fillChamberSection(ssr.hero_chamber);
    used = true;
  }
  if (ssr.hero_interviews?.length) {
    fillInterviewsList(ssr.hero_interviews);
    used = true;
  }
  if (ssr.hero_personality?.length) {
    fillHeroCoverSlider(ssr.hero_personality);
    used = true;
  }
  return used;
}

async function loadRealContent() {
  // Phase 2: homepage already rendered in PHP — only keep slider / truncation UX.
  if (document.body.classList.contains('altijara-home-ssr') || document.querySelector('[data-altijara-home-ssr]')) {
    document.documentElement.classList.remove('live-pending');
    document.documentElement.classList.add('live-ready');
    revealLiveContent();
    applyHomeTruncation();
    initHeroCoverSlider();
    WP.localizeArticleLinks?.(document);
    return;
  }

  ensureLiveLoadingUI();

  // Prefer PHP SSR payload when available.
  const ssrHit = hydrateHomeFromSSR();
  if (ssrHit) {
    revealLiveContent();
    applyHomeTruncation();
  }

  // If we already visited this session, paint from cache first (instant)
  const cachedHit = hydrateHomeFromCache();
  if (cachedHit) {
    revealLiveContent();
    applyHomeTruncation();
  }

  const failSafe = setTimeout(revealLiveContent, 8000);
  const tasks = [];
  const ssr = window.AltijaraSSR || {};

  // When SSR already filled a section, skip the matching remote fetch.
  if (!ssr.hero_chamber?.length) {
    tasks.push(
      fetchPostsByCategory(HOME_WP_IDS.chamber, 7).then((chamberPosts) => {
        if (!chamberPosts.length) return;
        fillChamberSection(chamberPosts);
      })
    );
  }

  if (!ssr.hero_interviews?.length) {
    tasks.push(
      fetchPostsByCategory(HOME_WP_IDS.interviews, 10).then((interviewPosts) => {
        fillInterviewsList(interviewPosts);
      })
    );
  }

  if (!ssr.hero_personality?.length) {
    tasks.push(
      fetchPostsByCategory(HOME_WP_IDS.personality, 6).then((personalitySlides) => {
        fillHeroCoverSlider(personalitySlides);
      })
    );
  }

  // Logistics sub-hero stays curated (lead + list layout from design).


  tasks.push(fillGridByCat('followups', 'متابعات'));
  tasks.push(fillSectorByCat('business', 'مال وأعمال'));
  tasks.push(fillSectorByCat('energy', 'الطاقة'));
  tasks.push(fillGridByCat('automotive', 'سيارات'));
  tasks.push(fillGridByCat('tech', 'تقنية'));
  tasks.push(fillSectorByCat('health', 'صحة'));
  tasks.push(fillSectorByCat('tourism', 'سياحة وترفيه'));

  tasks.push(
    fetchLiveArticles('/posts?per_page=5&_embed').then((posts) => {
      if (!posts.length) return;
      document.querySelectorAll('.most-read-list[data-wp-cat="most-read"] .most-read-item').forEach((item, idx) => {
        const p = posts[idx];
        if (!p) return;
        const link = item.querySelector('.most-read-text');
        if (link) {
          link.setAttribute('data-full-title', p.title);
          link.textContent = p.title;
          link.href = articleHref(p);
        }
      });
    })
  );

  await Promise.allSettled(tasks);
  clearTimeout(failSafe);
  alignHeroLeadWithCover();
  revealLiveContent();
  applyHomeTruncation();
  WP.localizeArticleLinks?.(document);
}

function initHeroCoverSlider() {
  const root = document.getElementById('heroCoverSlider');
  if (!root || root.dataset.bound === '1') return;
  root.dataset.bound = '1';
  root.dataset.index = '0';
  seedHeroCoverPostsFromDom(root);
  if (heroCoverPosts[0]) syncLeadToCoverPost(heroCoverPosts[0]);

  const show = (next) => {
    const slides = Array.from(root.querySelectorAll('.hero-cover-slide'));
    if (!slides.length) return;
    const index = (next + slides.length) % slides.length;
    root.dataset.index = String(index);
    slides.forEach((slide, i) => slide.classList.toggle('is-active', i === index));
    if (!heroCoverPosts.length) seedHeroCoverPostsFromDom(root);
    syncLeadToCoverPost(heroCoverPosts[index]);
    const visibleDate = root.querySelector('.hero-cover-meta .magazine-cover-date');
    const slideDate = slides[index].querySelector('.magazine-cover-date');
    if (visibleDate && slideDate) visibleDate.textContent = slideDate.textContent;
    const img = slides[index].querySelector('img');
    if (img && !img.complete) img.addEventListener('load', alignHeroLeadWithCover, { once: true });
    requestAnimationFrame(alignHeroLeadWithCover);
  };

  root.querySelector('.hero-cover-arrow--next')?.addEventListener('click', () => {
    show(Number(root.dataset.index || 0) + 1);
  });
  root.querySelector('.hero-cover-arrow--prev')?.addEventListener('click', () => {
    show(Number(root.dataset.index || 0) - 1);
  });
}

function alignHeroLeadWithCover() {
  const leadStory = document.querySelector('.lead-primary-story');
  if (leadStory) leadStory.style.minHeight = '';
}

function initLiveSearch() {
  // site-chrome.js owns header search when present.
  if (window.__altijaraSearchReady) return;

  const searchInput = document.getElementById('headerSearchInput');
  const searchResults = document.getElementById('headerSearchResults');
  if (!searchInput || !searchResults) return;

  let searchTimeout = null;
  const restRoot = API_BASE ? API_BASE.replace(/\/wp\/v2\/?$/, '') : '';

  searchInput.addEventListener('input', (e) => {
    const query = e.target.value.trim();
    clearTimeout(searchTimeout);

    if (query.length < 2 && !/^\d+$/.test(query)) {
      searchResults.innerHTML = '';
      searchResults.classList.remove('active');
      return;
    }

    searchResults.innerHTML = '<div class="header-search-status">جارِ البحث...</div>';
    searchResults.classList.add('active');

    searchTimeout = setTimeout(async () => {
      try {
        if (!restRoot) throw new Error('Search REST URL missing');
        const endpoint = WP.searchEndpoint
          ? WP.searchEndpoint(query, 5, 1)
          : `${restRoot}/altijara/v1/search?search=${encodeURIComponent(query)}&per_page=5&page=1`;
        const res = await fetch(endpoint);
        if (!res.ok) throw new Error('Search failed');
        const posts = await res.json();

        if (!Array.isArray(posts) || posts.length === 0) {
          searchResults.innerHTML = '<div class="header-search-status">لا توجد نتائج مطابقة</div>';
          return;
        }

        const decodeHtml = (value) => {
          if (window.AltijaraWP && typeof window.AltijaraWP.decodeHtml === 'function') {
            return window.AltijaraWP.decodeHtml(value);
          }
          const el = document.createElement('textarea');
          el.innerHTML = String(value || '');
          return el.value;
        };
        const esc = (value) => String(value || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
        const home = (window.AltijaraTheme && window.AltijaraTheme.urls && window.AltijaraTheme.urls.home) || '/';
        const mapped = typeof formatPostData === 'function'
          ? posts.map(formatPostData)
          : posts;
        searchResults.innerHTML = mapped.map((p) => {
          const title = esc(decodeHtml(p.title || 'بدون عنوان'));
          const link = esc(p.link || (typeof articleHref === 'function' ? articleHref(p) : home));
          const img = esc(p.img || '');
          const category = esc(decodeHtml(p.category || 'مجلة التجارة'));
          return `
            <a href="${link}" class="header-search-item">
              <img src="${img}" alt="" class="header-search-item-thumb" loading="lazy">
              <div class="header-search-item-info">
                <h5 class="header-search-item-title">${title}</h5>
                <div class="header-search-item-meta"><span>${category}</span></div>
              </div>
            </a>
          `;
        }).join('');
      } catch (err) {
        searchResults.innerHTML = '<div class="header-search-status header-search-status--error">تعذر جلب النتائج حالياً</div>';
      }
    }, 280);
  });
}

function updateTopDate() {
  const dateElem = document.getElementById('topDateText');
  if (!dateElem) return;
  try {
    const now = new Date();
    const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    dateElem.textContent = new Intl.DateTimeFormat('ar-SA-u-ca-gregory', options).format(now);
  } catch (e) {
    console.warn('Could not format dynamic date:', e);
  }
}

function updateMarketData() {
  const tasiElem = document.getElementById('valTasi');
  const oilElem = document.getElementById('valOil');
  
  if (tasiElem) {
    const baseTasi = 12048.2;
    const diff = (Math.random() * 6 - 2.5).toFixed(1);
    const newTasi = (baseTasi + parseFloat(diff)).toFixed(1);
    tasiElem.textContent = `${newTasi} (${diff > 0 ? '+' : ''}${diff}%)`;
    tasiElem.className = diff >= 0 ? 'up' : 'down';
  }

  if (oilElem) {
    const baseOil = 74.85;
    const diff = (Math.random() * 0.4 - 0.15).toFixed(2);
    const newOil = (baseOil + parseFloat(diff)).toFixed(2);
    oilElem.textContent = `$${newOil}`;
    oilElem.className = diff >= 0 ? 'up' : 'down';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  WP.localizeArticleLinks?.(document);
  updateTopDate();
  loadRealContent();

  if (!window.__altijaraSearchReady) {
  initLiveSearch();
  }

  alignHeroLeadWithCover();
  window.addEventListener('resize', alignHeroLeadWithCover);
  initHeroCoverSlider();
  const coverImg = document.querySelector('.hero-cover-slide.is-active .magazine-cover-img-wrap img');
  if (coverImg) {
    if (coverImg.complete) {
      alignHeroLeadWithCover();
    } else {
      coverImg.addEventListener('load', alignHeroLeadWithCover);
    }
  }

  if (!window.__altijaraSearchReady && typeof window.SiteChrome?.initGlobalSearch === 'function') {
    window.SiteChrome.initGlobalSearch();
  } else if (!window.__altijaraSearchReady) {
  const searchBtn = document.getElementById('headerSearchBtn');
  const searchBar = document.getElementById('headerSearchBar');
  const searchClose = document.getElementById('headerSearchClose');
  const searchInput = document.getElementById('headerSearchInput');

  if (searchBtn && searchBar) {
    searchBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const isActive = searchBar.classList.toggle('active');
        searchBar.hidden = !isActive;
        const overlay = document.getElementById('headerSearchOverlay');
        if (overlay) {
          overlay.hidden = !isActive;
          overlay.classList.toggle('active', isActive);
        }
        document.body.style.overflow = isActive ? 'hidden' : '';
      if (isActive && searchInput) {
        setTimeout(() => searchInput.focus(), 100);
      }
    });
  }

  if (searchClose && searchBar) {
    searchClose.addEventListener('click', (e) => {
      e.preventDefault();
      searchBar.classList.remove('active');
        searchBar.hidden = true;
        const overlay = document.getElementById('headerSearchOverlay');
        if (overlay) {
          overlay.classList.remove('active');
          overlay.hidden = true;
        }
        document.body.style.overflow = '';
      const results = document.getElementById('headerSearchResults');
      if (results) results.classList.remove('active');
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && searchBar && searchBar.classList.contains('active')) {
      searchBar.classList.remove('active');
        searchBar.hidden = true;
        const overlay = document.getElementById('headerSearchOverlay');
        if (overlay) {
          overlay.classList.remove('active');
          overlay.hidden = true;
        }
        document.body.style.overflow = '';
      const results = document.getElementById('headerSearchResults');
      if (results) results.classList.remove('active');
    }
  });
  }

  setInterval(updateMarketData, 8000);
});
