# Altijara Theme (Phase 1)

WordPress theme converted from the static Altijara Magazine prototype.

The HTML/CSS/JS prototype remains at the repository root for visual comparison — **do not delete it**.

## Install (Hostinger staging)

1. Upload `altijara-theme/` to `wp-content/themes/`
2. Appearance → Themes → activate **Altijara Theme**
3. Settings → Permalinks → Save (flush rewrite rules for `issue` CPT)
4. Create pages and assign templates where needed:
   - من نحن → `page-about.php`
   - اتصل بنا → `page-contact.php`
   - أعلن معنا → `page-advertise.php`
   - اشترك → `page-subscribe.php`
   - تسجيل الدخول → `page-login.php`
5. **Altijara Theme** admin menu → General: map section keys → WP category **slugs**
6. Reading → Front page displays: latest posts (uses `front-page.php`)

## Structure

See `inc/` for setup, enqueue, queries, caching, helpers, theme options, section manager.

Prototype assets live under `assets/css`, `assets/js`, `assets/images`.

## Phase 1 + Phase 2

- Theme activation + hierarchy templates
- Layout conversion (class names preserved)
- Query + transient caching foundation
- Settings architecture + 12 homepage section IDs
- SSR article (`single.php`)
- **Phase 2:** Interactive Section Manager (visibility, category, count, offset, sort, manual posts, layout, cache purge)
- **Phase 2:** Homepage SSR via `template-parts/home/*` driven by Section Manager (`front-page.php` v2)
