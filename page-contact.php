<?php
/**
 * Template Name: اتصل بنا
 * @package Altijara
 */
get_header();
?>
<main class="container static-shell">
    <section class="static-page static-page--wide">
      <header class="static-page-header">
        <h1 class="static-title">اتصل بنا</h1>
        <p class="static-lead">للاستفسارات التحريرية أو الاعلانية — يسعدنا تواصلكم عبر النموذج أو قنوات الاتصال المباشرة.</p>
      </header>

      <div class="static-contact-chips">
        <a class="static-chip" href="mailto:salah.abulwafa@jcci.org.sa"><strong>salah.abulwafa@jcci.org.sa</strong></a>
        <a class="static-chip" href="<?php echo esc_url( home_url( '/advertise/' ) ); ?>"><strong>صلاح أبو الوفا</strong></a>
        <span class="static-chip"><strong>الغرفة التجارية بجدة</strong></span>
      </div>

      <div class="static-split">
        <form class="static-form static-panel" id="contactForm" action="#" method="post">
          <h2 style="margin:0 0 14px;font-size:18px;font-weight:800;">أرسل رسالة</h2>
          <div class="static-form-row">
            <div class="static-field">
              <label for="cName">الاسم</label>
              <input id="cName" name="name" type="text" required autocomplete="name" placeholder="الاسم الكامل">
            </div>
            <div class="static-field">
              <label for="cEmail">البريد الإلكتروني</label>
              <input id="cEmail" name="email" type="email" required autocomplete="email" placeholder="name@example.com">
            </div>
          </div>
          <div class="static-field">
            <label for="cSubject">الموضوع</label>
            <select id="cSubject" name="subject" required>
              <option value="editorial">استفسار تحريري</option>
              <option value="advertise">إعلان / نشر أخبار</option>
              <option value="subscribe">اشتراك</option>
              <option value="other">أخرى</option>
            </select>
          </div>
          <div class="static-field">
            <label for="cMessage">الرسالة</label>
            <textarea id="cMessage" name="message" required placeholder="اكتب رسالتك هنا..."></textarea>
          </div>
          <div class="static-form-actions">
            <button type="submit" class="static-btn static-btn-primary">إرسال الرسالة</button>
            <a href="<?php echo esc_url( home_url( '/advertise/' ) ); ?>" class="static-btn static-btn-ghost">صفحة أعلن معنا</a>
          </div>
          <div class="static-success" id="contactSuccess" role="status">شكراً لتواصلكم — سيتم الرد في أقرب وقت.</div>
        </form>

        <aside class="ad-agent-card ad-agent-card--sidebar" aria-labelledby="contactAdAgentTitle">
          <span class="static-kicker">للإعلانات</span>
          <h2 id="contactAdAgentTitle">غرفة جدة</h2>
          <p class="ad-agent-intro">لمبيعات الإعلانات في الموقع والنسخة المطبوعة.</p>
          <div class="ad-agent-person">
            <strong>صلاح أبو الوفا رمضان</strong>
            <span>مدير التسويق والعلاقات العامة</span>
          </div>
          <ul class="ad-agent-contacts">
            <li>
              <a href="tel:+966122398148" class="ad-agent-link">
                <span class="ad-agent-icon" aria-hidden="true">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.68 2.35a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.75.32 1.54.55 2.35.68A2 2 0 0 1 22 16.92z"/></svg>
                </span>
                <span class="ad-agent-link-text">
                  <span class="label">هاتف</span>
                  <strong dir="ltr">+966 12 239 8148</strong>
                </span>
              </a>
            </li>
            <li>
              <a href="tel:+966504384206" class="ad-agent-link">
                <span class="ad-agent-icon" aria-hidden="true">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                </span>
                <span class="ad-agent-link-text">
                  <span class="label">جوال</span>
                  <strong dir="ltr">+966 50 438 4206</strong>
                </span>
              </a>
            </li>
            <li>
              <a href="mailto:salah.abulwafa@jcci.org.sa" class="ad-agent-link">
                <span class="ad-agent-icon" aria-hidden="true">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </span>
                <span class="ad-agent-link-text">
                  <span class="label">بريد</span>
                  <strong dir="ltr">salah.abulwafa@jcci.org.sa</strong>
                </span>
              </a>
            </li>
          </ul>
        </aside>
      </div>
    </section>
  </main>
<script>
    document.getElementById('contactForm')?.addEventListener('submit', function (e) {
      e.preventDefault();
      document.getElementById('contactSuccess')?.classList.add('is-visible');
      this.reset();
    });
  </script>
<?php get_footer(); ?>
