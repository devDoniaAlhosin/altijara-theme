/**
 * Saved articles — account bookmarks, guest login prompt, post-login auto-save.
 */
(function (global) {
  'use strict';

  const cfg = global.AltijaraBookmarks || {};
  let savedSet = new Set(
    Array.isArray(cfg.savedIds) ? cfg.savedIds.map((id) => Number(id)).filter(Boolean) : []
  );
  let busyIds = new Set();

  function i18n(key, fallback) {
    return (cfg.i18n && cfg.i18n[key]) || fallback;
  }

  function ensureToastHost() {
    let host = document.getElementById('altijaraBookmarkToast');
    if (host) return host;
    host = document.createElement('div');
    host.id = 'altijaraBookmarkToast';
    host.className = 'altijara-bookmark-toast';
    host.setAttribute('role', 'status');
    host.hidden = true;
    document.body.appendChild(host);
    return host;
  }

  function showToast(message, ctaOrOpts) {
    const host = ensureToastHost();
    let ctaUrl = '';
    let ctaLabel = '';
    let success = false;
    let removed = false;
    if (typeof ctaOrOpts === 'string') {
      ctaUrl = ctaOrOpts;
    } else if (ctaOrOpts && typeof ctaOrOpts === 'object') {
      ctaUrl = ctaOrOpts.url || '';
      ctaLabel = ctaOrOpts.label || '';
      success = !!ctaOrOpts.success;
      removed = !!ctaOrOpts.removed;
    }

    host.innerHTML = '';
    host.classList.toggle('altijara-bookmark-toast--success', success);
    host.classList.toggle('altijara-bookmark-toast--removed', removed);

    if (success || removed) {
      const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      icon.setAttribute('class', 'altijara-bookmark-toast__icon');
      icon.setAttribute('viewBox', '0 0 24 24');
      icon.setAttribute('width', '18');
      icon.setAttribute('height', '18');
      icon.setAttribute('aria-hidden', 'true');
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('fill', 'currentColor');
      path.setAttribute(
        'd',
        success
          ? 'M9.55 17.3 4.8 12.55l1.4-1.4 3.35 3.35 8.25-8.25 1.4 1.4z'
          : 'M19 6.4 17.6 5 12 10.6 6.4 5 5 6.4 10.6 12 5 17.6 6.4 19 12 13.4 17.6 19 19 17.6 13.4 12z'
      );
      icon.appendChild(path);
      host.appendChild(icon);
    }

    const text = document.createElement('p');
    text.className = 'altijara-bookmark-toast__msg';
    text.textContent = message || i18n('needLogin', 'سجّل الدخول لحفظ المقالات في حسابك.');
    host.appendChild(text);
    if (ctaUrl) {
      const link = document.createElement('a');
      link.className = 'altijara-bookmark-toast__cta';
      link.href = ctaUrl;
      link.textContent = ctaLabel || i18n('loginCta', 'تسجيل الدخول');
      host.appendChild(link);
    }
    host.hidden = false;
    host.classList.add('is-visible');
    clearTimeout(showToast._timer);
    showToast._timer = setTimeout(() => {
      host.classList.remove('is-visible');
      host.classList.remove('altijara-bookmark-toast--success');
      host.classList.remove('altijara-bookmark-toast--removed');
      host.hidden = true;
    }, success || removed ? 4500 : 6000);
  }

  function showSavedToast() {
    showToast(i18n('savedSuccess', 'تم حفظ المقال في محفوظاتك.'), {
      url: cfg.savedUrl || '',
      label: i18n('viewSaved', 'عرض المحفوظات'),
      success: true,
    });
  }

  function showRemovedToast() {
    showToast(i18n('removedSuccess', 'تم إزالة المقال من محفوظاتك.'), {
      removed: true,
    });
  }

  function applyButtonState(btn, saved) {
    if (!btn) return;
    const span = btn.querySelector('span');
    btn.classList.toggle('saved', !!saved);
    btn.style.color = saved ? 'var(--color-brand, #1897B8)' : '';
    btn.title = saved
      ? i18n('savedLabel', 'تم الحفظ')
      : i18n('save', 'حفظ المقال');
    if (span) {
      span.textContent = saved
        ? i18n('savedLabel', 'تم الحفظ')
        : i18n('saveLabel', 'حفظ');
    }
  }

  function hydrateButtons(root) {
    const scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll('.btn-cat-save[data-post-id]').forEach((btn) => {
      const id = Number(btn.getAttribute('data-post-id'));
      if (!id) return;
      applyButtonState(btn, savedSet.has(id));
    });
  }

  function buildLoginUrl(postId) {
    const base = cfg.loginUrl || '/login/';
    try {
      const returnUrl = new URL(window.location.href);
      returnUrl.searchParams.delete('save_post');
      if (postId) returnUrl.searchParams.set('save_post', String(postId));
      const login = new URL(base, window.location.origin);
      login.searchParams.set('redirect_to', returnUrl.toString());
      return login.toString();
    } catch (e) {
      return base;
    }
  }

  function stripSavePostParam() {
    try {
      const url = new URL(window.location.href);
      if (!url.searchParams.has('save_post')) return;
      url.searchParams.delete('save_post');
      history.replaceState(null, '', url.toString());
    } catch (e) {
      /* ignore */
    }
  }

  async function requestToggle(postId, forceSave) {
    const body = new FormData();
    body.append('action', 'altijara_toggle_save');
    body.append('nonce', cfg.nonce || '');
    body.append('post_id', String(postId));
    body.append('return_url', window.location.href);
    if (forceSave) body.append('force_save', '1');

    const res = await fetch(cfg.ajaxUrl || '/wp-admin/admin-ajax.php', {
      method: 'POST',
      credentials: 'same-origin',
      body,
    });
    const json = await res.json();
    if (!json || !json.success) {
      const msg =
        (json && json.data && json.data.message) ||
        i18n('error', 'تعذر حفظ المقال. حاول مرة أخرى.');
      throw new Error(msg);
    }
    return json.data || {};
  }

  async function toggleSaveArticle(btn) {
    if (!btn || busyIds.size && busyIds.has(Number(btn.getAttribute('data-post-id')))) {
      return;
    }
    const postId = Number(btn.getAttribute('data-post-id'));
    if (!postId) return;

    if (!cfg.loggedIn) {
      const loginUrl = buildLoginUrl(postId);
      showToast(i18n('needLogin', 'سجّل الدخول لحفظ المقالات في حسابك.'), loginUrl);
      return;
    }

    const wasSaved = savedSet.has(postId);
    const willSave = !wasSaved;
    busyIds.add(postId);

    // Optimistic UI — toast + button update instantly; sync with server in background.
    if (willSave) {
      savedSet.add(postId);
      applyButtonState(btn, true);
      showSavedToast();
    } else {
      savedSet.delete(postId);
      applyButtonState(btn, false);
      const card = btn.closest('.altijara-saved-item');
      if (card) {
        const grid = card.parentElement;
        card.remove();
        if (grid && !grid.querySelector('.altijara-saved-item')) {
          const empty = document.createElement('p');
          empty.className = 'altijara-saved-empty';
          empty.textContent = i18n('emptySaved', 'لا توجد مقالات محفوظة بعد. استخدم زر «حفظ» في قوائم الأخبار.');
          grid.replaceWith(empty);
        }
      }
      showRemovedToast();
    }

    try {
      const data = await requestToggle(postId, false);
      if (data.needLogin) {
        if (willSave) savedSet.delete(postId);
        else savedSet.add(postId);
        applyButtonState(btn, wasSaved);
        showToast(data.message, data.loginUrl || buildLoginUrl(postId));
        return;
      }
      if (Array.isArray(data.ids)) {
        savedSet = new Set(data.ids.map((id) => Number(id)).filter(Boolean));
      } else if (data.saved) {
        savedSet.add(postId);
      } else {
        savedSet.delete(postId);
      }
      applyButtonState(btn, savedSet.has(postId));
    } catch (err) {
      if (willSave) savedSet.delete(postId);
      else savedSet.add(postId);
      applyButtonState(btn, wasSaved);
      showToast(err.message || i18n('error', 'تعذر حفظ المقال. حاول مرة أخرى.'));
    } finally {
      busyIds.delete(postId);
    }
  }

  async function handlePendingSaveFromLogin() {
    if (!cfg.loggedIn) return;
    let pending = 0;
    try {
      pending = Number(new URL(window.location.href).searchParams.get('save_post')) || 0;
    } catch (e) {
      return;
    }
    if (!pending) return;

    stripSavePostParam();
    try {
      const data = await requestToggle(pending, true);
      if (Array.isArray(data.ids)) {
        savedSet = new Set(data.ids.map((id) => Number(id)).filter(Boolean));
      } else {
        savedSet.add(pending);
      }
      hydrateButtons(document);
      showSavedToast();
      const btn = document.querySelector('.btn-cat-save[data-post-id="' + pending + '"]');
      if (btn) {
        btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    } catch (err) {
      showToast(err.message || i18n('error', 'تعذر حفظ المقال. حاول مرة أخرى.'));
    }
  }

  function boot() {
    hydrateButtons(document);
    handlePendingSaveFromLogin();
  }

  global.toggleSaveArticle = toggleSaveArticle;
  global.AltijaraBookmarksAPI = {
    hydrate: hydrateButtons,
    isSaved: (id) => savedSet.has(Number(id)),
    showToast,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})(window);
