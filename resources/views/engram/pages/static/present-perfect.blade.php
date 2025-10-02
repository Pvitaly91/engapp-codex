<!-- Present Perfect (Теперішній доконаний час) — вставний блок -->
<section class="grammar-card" lang="uk">
  <style>
    /* СТИЛІ ЛИШЕ ДЛЯ ЦЬОГО БЛОКУ */
    .grammar-card { --bg:#ffffff; --text:#1f2937; --muted:#6b7280; --accent:#2563eb; --chip:#f3f4f6; --ok:#10b981; --warn:#f59e0b;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
      color: var(--text); background: var(--bg); border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; max-width: 980px; margin: 0 auto 24px; box-shadow: 0 6px 18px rgba(0,0,0,.04);
    }
    .grammar-card * { box-sizing: border-box; }
    .gw-title { font-size: 28px; line-height: 1.2; margin: 0 0 10px; }
    .gw-sub { color: var(--muted); margin: 0 0 18px; }
    .gw-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
    .gw-box { border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: #fff; }
    .gw-box h3 { margin: 0 0 8px; font-size: 18px; }
    .gw-list { margin: 8px 0 0 18px; }
    .gw-list li { margin: 6px 0; }
    .gw-formula { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      background: #0b1220; color: #e5e7eb; border-radius: 10px; padding: 12px; font-size: 14px; overflow:auto;
    }
    .gw-code-badge { display: inline-block; font-size: 12px; color:#d1d5db; border:1px solid #334155; padding:2px 8px; border-radius:999px; margin-bottom:8px; }
    .gw-ex { background: #f9fafb; border-left: 4px solid var(--accent); padding: 10px 12px; border-radius: 8px; margin-top: 10px; }
    .gw-en { font-weight: 600; }
    .gw-ua { color: var(--muted); }
    .gw-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
    .gw-chip { background: var(--chip); border:1px solid #e5e7eb; padding:6px 10px; border-radius:999px; font-size:13px; }
    .gw-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .gw-table th, .gw-table td { border: 1px solid #e5e7eb; padding: 10px; vertical-align: top; }
    .gw-table th { background:#f8fafc; text-align:left; }
    .gw-hint { display:flex; gap:10px; align-items:flex-start; background:#f8fafc; border:1px dashed #cbd5e1; padding:10px 12px; border-radius:10px; }
    .gw-emoji { font-size: 18px; }
    .tag-ok { color: var(--ok); font-weight: 700; }
    .tag-warn { color: var(--warn); font-weight: 700; }
    @media (min-width: 720px) {
      .gw-grid { grid-template-columns: 1.2fr 1fr; }
    }
  </style>

  <header>
    <h2 class="gw-title">Present Perfect — Теперішній доконаний час</h2>
    <p class="gw-sub">Показує <strong>результат або досвід</strong> до теперішнього моменту. Час важливий зараз; конкретну минулу дату зазвичай <strong>не вказуємо</strong>.</p>
  </header>

  <div class="gw-grid">
    <!-- ЛІВА КОЛОНКА -->
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li><strong>Досвід у житті</strong>: ever, never.</li>
          <li><strong>Нещодавно завершено</strong>, ефект помітний зараз: just, already, yet.</li>
          <li><strong>Тривалість до тепер</strong>: for, since.</li>
          <li>Звіт/результат «на зараз»: «Я вже зробив звіт».</li>
        </ul>

        <div class="gw-ex">
          <div class="gw-en">I <strong>have finished</strong> the report.</div>
          <div class="gw-ua">Я <strong>вже закінчив</strong> звіт (результат є зараз).</div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">have/has</span> + <span style="color:#86efac">V3 (Past Participle)</span>
I/You/We/They <span style="color:#93c5fd">have</span> <span style="color:#86efac">seen</span>.
He/She/It <span style="color:#93c5fd">has</span> <span style="color:#86efac">seen</span>.</pre>

        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[Підмет] + have/has not + V3
She <span style="color:#93c5fd">hasn’t</span> <span style="color:#86efac">visited</span> us since 2022.</pre>

        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula"><span style="color:#93c5fd">Have/Has</span> + [підмет] + V3?
<span style="color:#93c5fd">Have</span> you <span style="color:#86efac">ever been</span> to Rome?</pre>
      </div>

      <div class="gw-box">
        <h3>Маркери часу</h3>
        <div class="gw-chips">
          <span class="gw-chip">already</span><span class="gw-chip">yet</span><span class="gw-chip">just</span>
          <span class="gw-chip">ever</span><span class="gw-chip">never</span>
          <span class="gw-chip">for</span><span class="gw-chip">since</span><span class="gw-chip">so far</span><span class="gw-chip">recently/lately</span>
        </div>

        <div class="gw-ex">
          <div class="gw-en">We <strong>have lived</strong> here <u>since</u> 2020.</div>
          <div class="gw-ua">Ми <strong>живемо</strong> тут <u>з</u> 2020 року (і досі).</div>
        </div>
      </div>
    </div>

    <!-- ПРАВА КОЛОНКА -->
    <div class="gw-col">
      <div class="gw-box">
        <h3>Швидка пам’ятка</h3>
        <div class="gw-hint">
          <div class="gw-emoji">🧠</div>
          <div>
            <p><strong>Present Perfect ≠ конкретний минулий час.</strong> Якщо є «вчора», «у 2019» тощо — це вже <b>Past Simple</b>.</p>
            <div class="gw-ex" style="margin-top:6px">
              <div class="gw-en"><span class="tag-warn">✗</span> I have finished it yesterday.</div>
              <div class="gw-ua">Правильно: <b>I finished it yesterday.</b> (Past Simple)</div>
            </div>
          </div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Порівняння</h3>
        <table class="gw-table" aria-label="Порівняння Present Perfect та Past Simple">
          <thead>
            <tr>
              <th>Час</th>
              <th>Що виражає</th>
              <th>Формула</th>
              <th>Приклад</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Present Perfect</strong></td>
              <td>Результат/досвід «на зараз», без конкретної минулої дати</td>
              <td>have/has + V3</td>
              <td><span class="gw-en">I <strong>have lost</strong> my keys.</span> <span class="gw-ua">Я загубив ключі (і досі без них).</span></td>
            </tr>
            <tr>
              <td><strong>Past Simple</strong></td>
              <td>Завершена дія в минулому з часом/контекстом</td>
              <td>V2 / did + V1</td>
              <td><span class="gw-en">I <strong>lost</strong> my keys <u>yesterday</u>.</span> <span class="gw-ua">Учора загубив (факт у минулому).</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="gw-box">
        <h3>Типові помилки</h3>
        <ul class="gw-list">
          <li><span class="tag-warn">✗</span> Додавати конкретну минулу дату: <em>*I have visited in 2019</em>.</li>
          <li><span class="tag-warn">✗</span> Плутати <em>for</em> і <em>since</em>:
            <div class="gw-ex" style="margin-top:6px">
              <div class="gw-en"><b>for</b> + період: for 3 years; <b>since</b> + точка: since 2020.</div>
            </div>
          </li>
          <li><span class="tag-ok">✓</span> Для 3-ї особи однини — <b>has</b>; іншим — <b>have</b>.</li>
        </ul>
      </div>
    </div>
  </div>
</section>
