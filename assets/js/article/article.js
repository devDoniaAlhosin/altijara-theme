/**
 * Article page — open the shared layout for the post in ?slug= or ?id=
 */
(function () {
  const WP = window.AltijaraWP || {};
  const API = WP.API_BASE || 'https://altijaramagazine.com/wp-json/wp/v2';

  function esc(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function stripTags(html) {
    const el = document.createElement('div');
    el.innerHTML = html || '';
    return (el.textContent || '').replace(/\s+/g, ' ').trim();
  }

  let currentSectionKey = null;

  function sectionTitleFor(catKey) {
    return (catKey && window.CATEGORIES_DATA?.[catKey]?.title) || '';
  }

  function articleHref(post) {
    return WP.articlePageUrl
      ? WP.articlePageUrl(post, currentSectionKey)
      : 'article.html';
  }

  /** Category you came from (?cat=energy) wins — labels must match that section. */
  function resolveSectionFromUrl() {
    const key = new URLSearchParams(window.location.search).get('cat');
    if (key && window.CATEGORIES_DATA?.[key]?.wpCategoryId) {
      currentSectionKey = key;
      return key;
    }
    return null;
  }

  async function fetchRawPost() {
    const params = new URLSearchParams(window.location.search);
    const slug = params.get('slug');
    const id = params.get('id');
    if (!slug && !id) return null;
    const endpoint = slug
      ? `${API}/posts?slug=${encodeURIComponent(slug)}&_embed`
      : `${API}/posts/${encodeURIComponent(id)}?_embed`;
    const res = await fetch(endpoint, { headers: { Accept: 'application/json' } });
    if (!res.ok) return null;
    const data = await res.json();
    const post = Array.isArray(data) ? data[0] : data;
    return post && post.id ? post : null;
  }

  function renderMain(raw) {
    const formatted = WP.formatPostData ? WP.formatPostData(raw) : null;
    const title = (formatted && formatted.title) || stripTags(raw.title && raw.title.rendered);
    const titleEl = document.querySelector('.article-title-main');
    if (titleEl) titleEl.textContent = title;
    if (title) document.title = title + ' — مجلة التجارة';

    const heroSk = document.getElementById('articleHeroSkeleton');
    const img = document.querySelector('.article-featured-img');
    const media = raw._embedded && raw._embedded['wp:featuredmedia'] && raw._embedded['wp:featuredmedia'][0];
    if (img && ((formatted && formatted.img) || (media && media.source_url))) {
      img.src = (media && media.source_url) || formatted.img;
      img.alt = title;
      img.hidden = false;
      if (heroSk) heroSk.remove();
    } else if (heroSk) {
      heroSk.remove();
    }

    const caption = document.querySelector('.article-img-caption');
    if (caption) {
      const text = stripTags(media && media.caption && media.caption.rendered);
      caption.textContent = text;
      caption.hidden = !text;
    }

    const deck = document.querySelector('.article-deck-lead');
    const excerpt = stripTags(raw.excerpt && raw.excerpt.rendered);
    if (deck) {
      deck.textContent = excerpt;
      deck.hidden = !excerpt;
    }
    const shortDesc = document.querySelector('.article-short-desc');
    if (shortDesc) shortDesc.hidden = true;

    const body = document.querySelector('.article-body-content');
    if (body) {
      body.innerHTML = (raw.content && raw.content.rendered) || '';
      body.querySelectorAll('script').forEach((node) => node.remove());
      if (WP.localizeArticleLinks) WP.localizeArticleLinks(body);
    }

    const mainCol = document.getElementById('articleMainCol');
    if (mainCol) mainCol.classList.remove('is-skeleton');

    const shareText = encodeURIComponent(title || '');
    const shareUrl = encodeURIComponent(window.location.href);
    const wa = document.querySelector('a[title="واتساب"]');
    if (wa) wa.href = 'https://api.whatsapp.com/send?text=' + shareText + '%20' + shareUrl;
    const linkedin = document.querySelector('a[title="LinkedIn"]');
    if (linkedin) linkedin.href = 'https://www.linkedin.com/shareArticle?mini=true&url=' + shareUrl;
    const twitter = document.querySelector('a[title="منصة X"]');
    if (twitter) twitter.href = 'https://twitter.com/intent/tweet?text=' + shareText + '&url=' + shareUrl;
  }

  function categoryKeyByWpId(wpId) {
    const data = window.CATEGORIES_DATA || {};
    return Object.keys(data).find((key) => Number(data[key].wpCategoryId) === Number(wpId)) || null;
  }

  // WP term slug → site section key (e.g. cars → automotive)
  const WP_SLUG_TO_KEY = {
    'the-chamber-news': 'chamber',
    exhibitions: 'exhibitions',
    'follow-ups': 'followups',
    interviews: 'interviews',
    'money-and-business': 'business',
    logistic: 'logistics',
    industry: 'industry',
    energy: 'energy',
    cars: 'automotive',
    health: 'health',
    technique: 'tech',
    'tourism-and-entertainment': 'tourism',
    miscellaneous: 'misc'
  };

  /** Prefer a site section (سيارات, لوجستيات, …). Skip منوعات if a real section exists. */
  function articleSectionCategory(raw) {
    const data = window.CATEGORIES_DATA || {};
    const fromUrl = new URLSearchParams(window.location.search).get('cat');
    if (fromUrl && data[fromUrl]?.wpCategoryId) {
      return { catId: data[fromUrl].wpCategoryId, catKey: fromUrl };
    }

    const ids = Array.isArray(raw && raw.categories)
      ? raw.categories.map(Number)
      : [];
    const terms = raw && raw._embedded && raw._embedded['wp:term']
      ? (raw._embedded['wp:term'][0] || [])
      : [];

    const matches = [];
    const push = (key) => {
      if (key && data[key] && !matches.includes(key)) matches.push(key);
    };

    ids.forEach((id) => push(categoryKeyByWpId(id)));
    terms.forEach((term) => {
      if (!term) return;
      push(categoryKeyByWpId(term.id));
      push(WP_SLUG_TO_KEY[term.slug]);
      push(Object.keys(data).find((key) => data[key].title === term.name));
      // Child categories: use parent if it maps to a section
      if (term.parent) push(categoryKeyByWpId(term.parent));
    });

    const preferred = matches.find((key) => key !== 'misc') || matches[0] || null;
    if (!preferred) return { catId: null, catKey: null };
    return { catId: data[preferred].wpCategoryId, catKey: preferred };
  }

  async function fetchSameCategoryPosts(raw, perPage) {
    const { catId, catKey } = articleSectionCategory(raw);
    if (catId && WP.fetchPostsByCategory) {
      const posts = await WP.fetchPostsByCategory(catId, perPage);
      if (posts && posts.length) {
        return {
          catId,
          catKey,
          posts: posts.filter((p) => !raw || p.id !== raw.id)
        };
      }
    }
    const latest = WP.fetchLiveArticles
      ? await WP.fetchLiveArticles(`/posts?per_page=${perPage}&_embed`)
      : [];
    return {
      catId: null,
      catKey: null,
      posts: (latest || []).filter((p) => !raw || p.id !== raw.id)
    };
  }

  function fillLatestNews(posts, sectionTitle) {
    const list = document.querySelector('.rec-items-list');
    if (!list || !posts.length) return;

    list.innerHTML = posts.slice(0, 5).map((p) => `
      <div class="rec-item-card">
        <div class="rec-item-body">
          <span class="rec-kicker">${esc(sectionTitle || p.category || '')}</span>
          <a href="${esc(articleHref(p))}" class="rec-title">${esc(p.title)}</a>
          <span class="rec-time">${esc(p.dateText || '')}</span>
        </div>
        <img src="${esc(p.img)}" alt="" class="rec-item-thumb">
      </div>
    `).join('');
  }

  function fillRelated(posts, catId, catKey) {
    const list = document.querySelector('.bottom-cards-list');
    const section = document.querySelector('.bottom-related-section');
    if (!list) return;

    const sectionTitle = sectionTitleFor(catKey);
    const related = posts.slice(0, 2);
    if (!related.length) {
      if (section) section.hidden = true;
      return;
    }
    if (section) section.hidden = false;

    list.innerHTML = related.map((p) => `
      <article class="bottom-news-card">
        <div class="bottom-card-img-wrap">
          <img src="${esc(p.img)}" alt="${esc(p.title)}" class="bottom-card-img" loading="lazy">
        </div>
        <div class="bottom-card-body">
          <span class="bottom-card-kicker">${esc(sectionTitle || p.category || '')}</span>
          <h4 class="bottom-card-headline">
            <a href="${esc(articleHref(p))}">${esc(p.title)}</a>
          </h4>
          <p class="bottom-card-excerpt">${esc(WP.trimAtWord ? WP.trimAtWord(p.excerpt || '', 140) : (p.excerpt || ''))}</p>
          <span class="bottom-card-meta">${esc(p.dateText || '')}</span>
        </div>
      </article>
    `).join('');

    const moreBtn = document.querySelector('.bottom-section-footer .btn-cat-load-more');
    const key = catKey || (catId ? categoryKeyByWpId(catId) : null);
    if (moreBtn && key) {
      moreBtn.href = `category.html?cat=${key}`;
    }
  }

  function fillNewIssue() {
    const list = document.getElementById('catNewIssueList');
    if (!list) return;

    const recent = Array.isArray(window.AltijaraRecentIssues) ? window.AltijaraRecentIssues : [];
    const all = Array.isArray(window.AltijaraIssues) ? window.AltijaraIssues : [];
    const source = recent.length ? recent : all;
    const seen = new Set();
    const issues = source
      .filter((i) => i && (i.cover || i.src))
      .filter((i) => {
        const key = String(i.cover || i.src);
        if (!key || seen.has(key)) return false;
        seen.add(key);
        return true;
      })
      .slice(0, 8);
    const widget = list.closest('.cat-new-issue-widget');
    if (!issues.length) {
      if (widget) widget.hidden = true;
      return;
    }
    if (widget) widget.hidden = false;

    let index = 0;
    let busy = false;
    const prev = widget && widget.querySelector('.cat-new-issue-arrow--prev');
    const next = widget && widget.querySelector('.cat-new-issue-arrow--next');
    const arrows = widget && widget.querySelector('.cat-new-issue-arrows');
    if (arrows) arrows.hidden = issues.length < 2;

    const syncArrows = () => {
      if (prev) {
        prev.disabled = issues.length < 2;
        prev.classList.toggle('is-accent', false);
      }
      if (next) {
        next.disabled = issues.length < 2;
        next.classList.toggle('is-accent', issues.length > 1);
      }
    };

    const paintCover = (issue, skeletonFirst) => {
      const href = issue.flipbook || issue.link || issue.permalink || '#';
      const title = issue.title || `العدد ${issue.number || ''}`;
      const coverSrc = String(issue.cover || issue.src || '');
      if (!coverSrc) {
        busy = false;
        return;
      }
      let cover = list.querySelector('a.cat-new-issue-cover');
      let img = cover && cover.querySelector('img');

      if (!cover || !img) {
        list.innerHTML = `
          <a href="${esc(href)}" class="cat-new-issue-cover" target="_blank" rel="noopener">
            <span class="sk sk-cover" aria-hidden="true"></span>
            <img alt="${esc(title)}" decoding="async">
          </a>`;
        cover = list.querySelector('.cat-new-issue-cover');
        img = cover && cover.querySelector('img');
      } else {
        cover.setAttribute('href', href);
        if (!cover.querySelector('.sk-cover')) {
          cover.insertAdjacentHTML('afterbegin', '<span class="sk sk-cover" aria-hidden="true"></span>');
        }
        img.alt = title;
      }

      if (!cover || !img) {
        busy = false;
        return;
      }

      const startedAt = skeletonFirst ? Date.now() : 0;
      const minSkeletonMs = 220;
      let finished = false;

      const finish = () => {
        if (finished) return;
        finished = true;
        const reveal = () => {
          cover.classList.remove('is-switching');
          cover.classList.add('is-ready');
          busy = false;
          syncArrows();
        };
        if (!skeletonFirst) {
          reveal();
          return;
        }
        const wait = Math.max(0, minSkeletonMs - (Date.now() - startedAt));
        setTimeout(reveal, wait);
      };

      if (skeletonFirst) {
        cover.classList.remove('is-ready');
        cover.classList.add('is-switching');
        img.onload = null;
        img.onerror = null;
      }

      img.onerror = () => {
        img.onerror = null;
        finish();
      };
      img.onload = finish;

      const nextSrc = skeletonFirst && img.getAttribute('src') === coverSrc
        ? `${coverSrc}${coverSrc.indexOf('?') >= 0 ? '&' : '?'}_t=${Date.now()}`
        : coverSrc;

      requestAnimationFrame(() => {
        img.src = nextSrc;
        if (img.complete && img.naturalWidth) finish();
        setTimeout(() => {
          if (busy) finish();
        }, 2500);
      });
    };

    const show = (nextIndex, fromClick) => {
      if (busy && fromClick) return;
      if (issues.length < 2 && fromClick) return;
      busy = !!fromClick;
      index = (nextIndex + issues.length) % issues.length;
      paintCover(issues[index], !!fromClick);
    };

    const ssrCover = list.querySelector('a.cat-new-issue-cover img[src]');
    if (ssrCover) {
      const ssrSrc = ssrCover.getAttribute('src') || '';
      const matchIdx = issues.findIndex((i) => (i.cover || i.src) === ssrSrc);
      if (matchIdx >= 0) index = matchIdx;
      const cover = ssrCover.closest('.cat-new-issue-cover');
      if (ssrCover.complete && ssrCover.naturalWidth) {
        if (cover) cover.classList.add('is-ready');
      } else {
        ssrCover.onload = () => {
          if (cover) cover.classList.add('is-ready');
        };
      }
    } else {
      show(0, false);
    }

    syncArrows();
    if (prev) {
      prev.onclick = (e) => {
        e.preventDefault();
        show(index - 1, true);
      };
    }
    if (next) {
      next.onclick = (e) => {
        e.preventDefault();
        show(index + 1, true);
      };
    }
  }

  function fillMostRead(posts) {
    const list = document.querySelector('.cat-mr-list');
    if (!list) return;

    const items = posts.slice(0, 7);
    if (!items.length) {
      list.closest('.cat-most-read-widget').hidden = true;
      return;
    }

    list.innerHTML = items.map((p, idx) => `
      <li class="cat-mr-item">
        <span class="cat-mr-num">${idx + 1}</span>
        <div class="cat-mr-content">
          <h4 class="cat-mr-headline">
            <a href="${esc(articleHref(p))}">${esc(p.title)}</a>
          </h4>
        </div>
      </li>
    `).join('');
  }

  function initRelatedLoadMore() {
    const section = document.querySelector('.bottom-related-section');
    const btn = document.getElementById('relatedLoadMoreBtn');
    const wrap = document.getElementById('relatedLoadMoreWrap');
    if (!section || !btn) return;

    const perLoad =
      Number(section.getAttribute('data-related-per-load')) ||
      Number(window.AltijaraArticle?.relatedPerLoad) ||
      2;

    btn.addEventListener('click', () => {
      const hidden = section.querySelectorAll('.bottom-news-card.is-related-more[hidden]');
      if (!hidden.length) {
        if (wrap) wrap.hidden = true;
        return;
      }
      Array.from(hidden)
        .slice(0, perLoad)
        .forEach((card) => {
          card.hidden = false;
          card.classList.remove('is-related-more');
        });
      const stillHidden = section.querySelectorAll('.bottom-news-card.is-related-more[hidden]');
      if (!stillHidden.length && wrap) {
        wrap.hidden = true;
      }
    });
  }

  document.addEventListener('DOMContentLoaded', async () => {
    if (WP.localizeArticleLinks) WP.localizeArticleLinks(document);

    // Phase 1 SSR: single.php already rendered article + sidebars in PHP.
    if (window.AltijaraArticle && window.AltijaraArticle.ssr) {
      const mainCol = document.getElementById('articleMainCol');
      if (mainCol) mainCol.classList.remove('is-skeleton');
      fillNewIssue();
      initRelatedLoadMore();
      return;
    }

    try {
      const urlCat = resolveSectionFromUrl();
      const data = window.CATEGORIES_DATA || {};

      const rawPromise = fetchRawPost();
      const sidePromise = (urlCat && data[urlCat]?.wpCategoryId && WP.fetchPostsByCategory)
        ? WP.fetchPostsByCategory(data[urlCat].wpCategoryId, 12)
        : null;

      const raw = await rawPromise;
      if (raw) renderMain(raw);
      else {
        const mainCol = document.getElementById('articleMainCol');
        if (mainCol) mainCol.classList.remove('is-skeleton');
      }

      fillNewIssue();

      let catId;
      let catKey;
      let posts;
      if (sidePromise) {
        const sidePosts = await sidePromise;
        catId = data[urlCat].wpCategoryId;
        catKey = urlCat;
        posts = (sidePosts || []).filter((p) => !raw || p.id !== raw.id);
      } else {
        ({ catId, catKey, posts } = await fetchSameCategoryPosts(raw, 12));
      }

      if (!currentSectionKey) currentSectionKey = catKey;
      const activeKey = urlCat || currentSectionKey || catKey;
      const sectionTitle = sectionTitleFor(activeKey);
      fillLatestNews(posts, sectionTitle);
      fillMostRead(posts);
      fillRelated(posts.slice(5), catId, activeKey);
    } catch (err) {
      console.warn('Article page:', err && err.message);
      const mainCol = document.getElementById('articleMainCol');
      if (mainCol) mainCol.classList.remove('is-skeleton');
    }
  });
})();
