<?php $__env->startSection('title', 'English Test Hub'); ?>

<?php $__env->startSection('content'); ?>
<!-- Past Perfect (Минулий доконаний час) — вставний блок -->
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
    <h2 class="gw-title">Past Perfect — Минулий доконаний час</h2>
    <p class="gw-sub">Використовуємо, щоб показати дію, яка сталася <strong>раніше іншої минулої події</strong>.</p>
  </header>

  <div class="gw-grid">
    <!-- ЛІВА КОЛОНКА -->
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li><strong>Подія А</strong> відбулася, а потім сталася <strong>подія Б</strong> (обидві в минулому). Для події А — <em>Past Perfect</em>, для події Б — <em>Past Simple</em>.</li>
          <li>Часто з маркерами: <em>before, after, by the time, already, when</em>.</li>
        </ul>

        <div class="gw-ex">
          <div class="gw-en">I had finished my homework <u>before</u> my friend called.</div>
          <div class="gw-ua">Я закінчив домашнє завдання <u>перед тим</u>, як подзвонив друг.</div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">had</span> + <span style="color:#86efac">V3 (дієслово у 3-й формі / Past Participle)</span>
I had <span style="color:#86efac">seen</span> / She had <span style="color:#86efac">gone</span> / They had <span style="color:#86efac">eaten</span></pre>

        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">had not</span> (hadn’t) + V3
I hadn’t <span style="color:#86efac">seen</span> that movie before.</pre>

        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula"><span style="color:#93c5fd">Had</span> + [підмет] + V3?
Had you <span style="color:#86efac">studied</span> before the test?</pre>
      </div>

      <div class="gw-box">
        <h3>Маркери часу</h3>
        <div class="gw-chips">
          <span class="gw-chip">before — перед</span>
          <span class="gw-chip">after — після</span>
          <span class="gw-chip">by the time — до того часу як</span>
          <span class="gw-chip">already — вже</span>
          <span class="gw-chip">when — коли</span>
          <span class="gw-chip">until/till — до (моменту)</span>
        </div>

        <div class="gw-ex">
          <div class="gw-en">By the time we started, they <strong>had already prepared</strong> everything.</div>
          <div class="gw-ua">До того, як ми почали, вони <strong>вже підготували</strong> все.</div>
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
            <p><strong>A сталося перед B → A: Past Perfect, B: Past Simple.</strong></p>
            <p class="gw-ua">Коли порядок подій і так зрозумілий (через <em>before/after</em>), <em>Past Perfect</em> інколи можна опустити. Але з ним зрозуміліше.</p>
          </div>
        </div>

        <div class="gw-ex" style="margin-top:10px">
          <div class="gw-en">When I arrived, she <strong>had left</strong>.</div>
          <div class="gw-ua">Коли я прийшов, вона <strong>вже пішла</strong>.</div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Порівняння</h3>
        <table class="gw-table" aria-label="Порівняння Past Simple та Past Perfect">
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
              <td><strong>Past Simple</strong></td>
              <td>Звичайна минула дія/факт (B)</td>
              <td>V2 (went, saw) / did + V1</td>
              <td><span class="gw-en">My friend <strong>called</strong>.</span></td>
            </tr>
            <tr>
              <td><strong>Past Perfect</strong></td>
              <td>Раніша минула дія перед іншою (A)</td>
              <td>had + V3</td>
              <td><span class="gw-en">I <strong>had finished</strong> before he called.</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="gw-box">
        <h3>Типові помилки</h3>
        <ul class="gw-list">
          <li><span class="tag-warn">✗</span> Використовувати <em>had + V3</em> без другої минулої події/контексту.</li>
          <li><span class="tag-warn">✗</span> Плутати з <em>Present Perfect</em> (це про зв’язок із теперішнім, а не з іншою минулою дією).</li>
          <li><span class="tag-ok">✓</span> Думай: “<em>Що сталося раніше?</em>” — туди став <strong>Past Perfect</strong>.</li>
        </ul>
      </div>
    </div>
  </div>
</section>
<!-- ====== SHARED STYLES for grammar cards (include once) ====== -->
<section class="grammar-card" lang="uk">
  <style>
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
  <!-- Цю "порожню" секцію можна видалити після копіювання стилів нагору сторінки -->
</section>


<!-- Future Perfect (Майбутній доконаний час) — вставний блок -->
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
    <h2 class="gw-title">Future Perfect — Майбутній доконаний час</h2>
    <p class="gw-sub">Використовуємо, щоб показати, що дія буде <strong>завершена до певного моменту в майбутньому</strong>.</p>
  </header>

  <div class="gw-grid">
    <!-- ЛІВА КОЛОНКА -->
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li><strong>Завершення до дедлайну/події</strong>: «До п’ятниці вже зроблю».</li>
          <li><strong>Прогноз про виконання</strong> до конкретного часу/моменту.</li>
          <li><strong>У складних реченнях</strong> з <em>by (the time), before, until/till</em>.</li>
        </ul>

        <div class="gw-ex">
          <div class="gw-en">By 6 pm, I <strong>will have finished</strong> the report.</div>
          <div class="gw-ua">До 18:00 я <strong>вже закінчу</strong> звіт.</div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">will have</span> + <span style="color:#86efac">V3 (Past Participle)</span>
I <span style="color:#93c5fd">will have</span> <span style="color:#86efac">finished</span>.</pre>

        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[Підмет] + will not (won’t) have + V3
She <span style="color:#93c5fd">won’t have</span> <span style="color:#86efac">arrived</span> by noon.</pre>

        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula"><span style="color:#93c5fd">Will</span> + [підмет] + <span style="color:#93c5fd">have</span> + V3?
<span style="color:#93c5fd">Will</span> they <span style="color:#93c5fd">have</span> <span style="color:#86efac">completed</span> it by then?</pre>
      </div>

      <div class="gw-box">
        <h3>Маркери часу</h3>
        <div class="gw-chips">
          <span class="gw-chip">by … (by Friday, by 2030)</span>
          <span class="gw-chip">by the time …</span>
          <span class="gw-chip">before …</span>
          <span class="gw-chip">until/till …</span>
        </div>

        <div class="gw-ex">
          <div class="gw-en">By the time you come, we <strong>will have prepared</strong> everything.</div>
          <div class="gw-ua">До того часу, як ти прийдеш, ми <strong>вже підготуємо</strong> все.</div>
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
            <p><strong>Майбутня точка → до неї дія буде завершена.</strong></p>
            <p class="gw-ua">У підрядних часу після <em>when, after, before, by the time, until</em> зазвичай <b>Present Simple</b>, а не <em>will</em>:</p>
            <div class="gw-ex" style="margin-top:6px">
              <div class="gw-en">I will have finished <u>before you arrive</u>.</div>
              <div class="gw-ua">Я закінчу <u>перш ніж ти приїдеш</u> (не “will arrive”).</div>
            </div>
          </div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Порівняння</h3>
        <table class="gw-table" aria-label="Порівняння Future Simple та Future Perfect">
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
              <td><strong>Future Simple</strong></td>
              <td>Проста дія в майбутньому</td>
              <td>will + V1</td>
              <td><span class="gw-en">I will finish tomorrow.</span></td>
            </tr>
            <tr>
              <td><strong>Future Perfect</strong></td>
              <td>Дія завершиться <u>до</u> майбутньої точки</td>
              <td>will have + V3</td>
              <td><span class="gw-en">By tomorrow, I <strong>will have finished</strong>.</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="gw-box">
        <h3>Типові помилки</h3>
        <ul class="gw-list">
          <li><span class="tag-warn">✗</span> Ставити <em>will</em> після сполучників часу: <em>*when you will come</em>. Правильно: <em>when you come</em>.</li>
          <li><span class="tag-warn">✗</span> Плутати з <em>Future Continuous</em> (той підкреслює процес у майбутній точці).</li>
          <li><span class="tag-ok">✓</span> Думай про дедлайн у майбутньому: «Що <b>буде зроблено</b> до нього?»</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- 1) PRESENT SIMPLE -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Present Simple — Теперішній простий</h2>
    <p class="gw-sub">Факти, звички, розклади. He/She/It — додаємо <b>s/es</b>.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Звички, рутини, загальні факти.</li>
          <li>Розклади та графіки (уроки, рейси).</li>
          <li>Стан: know, like, love, believe тощо.</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">She works from home.</div><div class="gw-ua">Вона працює з дому.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + V1 (he/she/it + s/es)</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + do/does not + V1</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Do/Does + [S] + V1?</pre>
      </div>
      <div class="gw-box">
        <h3>Маркери</h3>
        <div class="gw-chips">
          <span class="gw-chip">always</span><span class="gw-chip">usually</span><span class="gw-chip">often</span>
          <span class="gw-chip">every day</span><span class="gw-chip">on Mondays</span>
        </div>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">I live in Lviv.</span> <span class="gw-ua">Я живу у Львові.</span></li>
          <li><span class="gw-en">He doesn’t eat meat.</span> <span class="gw-ua">Він не їсть м’яса.</span></li>
          <li><span class="gw-en">Do you play chess?</span> <span class="gw-ua">Ти граєш у шахи?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Типові помилки</h3>
        <ul class="gw-list">
          <li>Забувають <b>s</b> у 3-й особі однини.</li>
          <li>Використовують <em>am/is/are</em> з діями: <span class="tag-warn">✗</span> I am go.</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- 2) PRESENT CONTINUOUS -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Present Continuous — Теперішній тривалий</h2>
    <p class="gw-sub">Дія триває зараз/тимчасово. Також — узгоджені плани.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Відбувається зараз або навколо поточного моменту.</li>
          <li>Тимчасові ситуації, тренди, зміни.</li>
          <li>Узгоджені майбутні плани.</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">I’m working now.</div><div class="gw-ua">Я зараз працюю.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + am/is/are + V-ing</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + am/is/are not + V-ing</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Am/Is/Are + [S] + V-ing?</pre>
      </div>
      <div class="gw-box">
        <h3>Маркери</h3>
        <div class="gw-chips">
          <span class="gw-chip">now</span><span class="gw-chip">right now</span><span class="gw-chip">at the moment</span>
          <span class="gw-chip">currently</span><span class="gw-chip">these days</span>
        </div>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">She isn’t watching TV.</span> <span class="gw-ua">Вона не дивиться телевізор.</span></li>
          <li><span class="gw-en">Are you coming tonight?</span> <span class="gw-ua">Ти прийдеш сьогодні ввечері?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Примітка</h3>
        <p class="gw-ua">Зі state-verbs (know, like) зазвичай не вживаємо Continuous.</p>
      </div>
    </div>
  </div>
</section>

<!-- 3) PRESENT PERFECT -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Present Perfect — Теперішній доконаний</h2>
    <p class="gw-sub">Досвід/результат до тепер; не вказуємо конкретний минулий час.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Досвід (ever, never).</li>
          <li>Нещодавно завершено з ефектом зараз (just, already, yet).</li>
          <li>Тривалість до тепер (for, since).</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">I have just finished.</div><div class="gw-ua">Я щойно закінчив.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + have/has + V3</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + have/has not + V3</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Have/Has + [S] + V3?</pre>
      </div>
      <div class="gw-box">
        <h3>Маркери</h3>
        <div class="gw-chips">
          <span class="gw-chip">already</span><span class="gw-chip">yet</span><span class="gw-chip">just</span>
          <span class="gw-chip">ever</span><span class="gw-chip">never</span><span class="gw-chip">for</span><span class="gw-chip">since</span>
        </div>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">She hasn’t visited us since 2022.</span> <span class="gw-ua">Вона не навідувала нас з 2022.</span></li>
          <li><span class="gw-en">Have you ever been to Rome?</span> <span class="gw-ua">Був у Римі?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Типова помилка</h3>
        <p class="gw-ua"><span class="tag-warn">✗</span> Не став конкретний минулий час — тоді це Past Simple.</p>
      </div>
    </div>
  </div>
</section>

<!-- 4) PRESENT PERFECT CONTINUOUS -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Present Perfect Continuous — Теперішній доконано-тривалий</h2>
    <p class="gw-sub">Дія триває від минулого до тепер; акцент на тривалості/слідах.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Почалось раніше, триває досі.</li>
          <li>Пояснюємо сліди дії (втома, безлад).</li>
          <li>Питання <em>How long...?</em></li>
        </ul>
        <div class="gw-ex"><div class="gw-en">I’ve been studying for 3 hours.</div><div class="gw-ua">Я вчуся вже 3 години.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + have/has been + V-ing</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + have/has not been + V-ing</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Have/Has + [S] + been + V-ing?</pre>
      </div>
      <div class="gw-box">
        <h3>Маркери</h3>
        <div class="gw-chips">
          <span class="gw-chip">for</span><span class="gw-chip">since</span><span class="gw-chip">how long</span>
          <span class="gw-chip">lately</span><span class="gw-chip">recently</span>
        </div>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">She hasn’t been sleeping well lately.</span> <span class="gw-ua">Вона останнім часом погано спить.</span></li>
          <li><span class="gw-en">Have you been working here since May?</span> <span class="gw-ua">Працюєш тут з травня?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Нотатка</h3>
        <p class="gw-ua">Якщо важливіший результат ніж тривалість — дивись Present Perfect.</p>
      </div>
    </div>
  </div>
</section>

<!-- 5) PAST SIMPLE -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Past Simple — Минулий простий</h2>
    <p class="gw-sub">Завершена дія в минулому з конкретним часом/контекстом.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Факт у минулому (yesterday, last week, in 2019).</li>
          <li>Послідовність минулих подій.</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">We moved in 2019.</div><div class="gw-ua">Ми переїхали у 2019.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + V2 / was, were</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + did not + V1 / was, were not</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Did + [S] + V1? / Was/Were + [S] ...?</pre>
      </div>
      <div class="gw-box">
        <h3>Маркери</h3>
        <div class="gw-chips">
          <span class="gw-chip">yesterday</span><span class="gw-chip">last week</span><span class="gw-chip">in 2019</span><span class="gw-chip">ago</span>
        </div>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">He didn’t call yesterday.</span> <span class="gw-ua">Він учора не подзвонив.</span></li>
          <li><span class="gw-en">Did you enjoy the film?</span> <span class="gw-ua">Фільм сподобався?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Нотатка</h3>
        <p class="gw-ua">Регулярні — <em>-ed</em>; неправильні — форми V2 з таблиці.</p>
      </div>
    </div>
  </div>
</section>

<!-- 6) PAST CONTINUOUS -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Past Continuous — Минулий тривалий</h2>
    <p class="gw-sub">Дія була у процесі в конкретний момент; часто її перервала коротка дія.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Фонова дія в певний час/момент у минулому.</li>
          <li>Переривання: Past Continuous + Past Simple.</li>
          <li>Паралельні довгі дії (while).</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">I was cooking when she arrived.</div><div class="gw-ua">Я готував, коли вона прийшла.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + was/were + V-ing</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + was/were not + V-ing</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Was/Were + [S] + V-ing?</pre>
      </div>
      <div class="gw-box">
        <h3>Маркери</h3>
        <div class="gw-chips">
          <span class="gw-chip">while</span><span class="gw-chip">when</span><span class="gw-chip">at 6 pm</span><span class="gw-chip">all evening</span>
        </div>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">They weren’t listening.</span> <span class="gw-ua">Вони не слухали.</span></li>
          <li><span class="gw-en">What were you doing at 9 pm?</span> <span class="gw-ua">Що ти робив о 21:00?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Нотатка</h3>
        <p class="gw-ua"><em>When</em> + Past Simple часто «обриває» тривалу дію.</p>
      </div>
    </div>
  </div>
</section>

<!-- 7) PAST PERFECT (аналог твоєї картки — скорочена версія) -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Past Perfect — Минулий доконаний</h2>
    <p class="gw-sub">A сталося <b>до</b> B (обидва в минулому). A — Past Perfect, B — Past Simple.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Показати «раніше»: дія завершилась перед іншою минулою подією.</li>
          <li>Часто з: before, after, by the time, already, when, until.</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">We had left before it rained.</div><div class="gw-ua">Ми пішли до того, як пішов дощ.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + had + V3</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + had not + V3</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Had + [S] + V3?</pre>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">She hadn’t seen him before that day.</span> <span class="gw-ua">Вона не бачила його до того дня.</span></li>
          <li><span class="gw-en">Had you finished when he called?</span> <span class="gw-ua">Ти вже закінчив, коли він подзвонив?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Швидка пам’ятка</h3>
        <div class="gw-hint"><div class="gw-emoji">🧠</div><div>A перед B → A: Past Perfect, B: Past Simple.</div></div>
      </div>
    </div>
  </div>
</section>

<!-- 8) PAST PERFECT CONTINUOUS -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Past Perfect Continuous — Минулий доконано-тривалий</h2>
    <p class="gw-sub">Тривалість до іншої минулої події/моменту.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Дія тривала <em>до</em> B (минулої точки).</li>
          <li>Причина стану в минулому (втома, мокрий тощо).</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">He had been waiting for 2 hours before the bus came.</div><div class="gw-ua">Він чекав 2 години, перш ніж автобус приїхав.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + had been + V-ing</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + had not been + V-ing</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Had + [S] + been + V-ing?</pre>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">They hadn’t been sleeping well for weeks.</span> <span class="gw-ua">Вони тижнями погано спали.</span></li>
          <li><span class="gw-en">Had she been studying long before the exam?</span> <span class="gw-ua">Вона довго вчилася перед іспитом?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Маркер</h3>
        <div class="gw-chips"><span class="gw-chip">for</span><span class="gw-chip">since</span><span class="gw-chip">before</span><span class="gw-chip">by the time</span></div>
      </div>
    </div>
  </div>
</section>

<!-- 9) FUTURE SIMPLE (will) -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Future Simple — Майбутній простий (will)</h2>
    <p class="gw-sub">Спонтанні рішення, обіцянки, прогнози-думки.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Спонтанне рішення зараз.</li>
          <li>Обіцянки/пропозиції/відмови.</li>
          <li>Прогнози на думці (I think, probably, maybe).</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">I’ll help you.</div><div class="gw-ua">Я допоможу тобі.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + will + V1</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + will not (won’t) + V1</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Will + [S] + V1?</pre>
      </div>
      <div class="gw-box">
        <h3>Маркери</h3>
        <div class="gw-chips">
          <span class="gw-chip">I think</span><span class="gw-chip">probably</span><span class="gw-chip">maybe</span>
          <span class="gw-chip">tomorrow</span><span class="gw-chip">next week</span>
        </div>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">She won’t be late.</span> <span class="gw-ua">Вона не запізниться.</span></li>
          <li><span class="gw-en">Will you come tomorrow?</span> <span class="gw-ua">Прийдеш завтра?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Нотатка</h3>
        <p class="gw-ua">Плани/намір → частіше <b>be going to</b> або Present Continuous.</p>
      </div>
    </div>
  </div>
</section>

<!-- 10) FUTURE CONTINUOUS -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Future Continuous — Майбутній тривалий</h2>
    <p class="gw-sub">Дія буде в процесі в конкретний момент у майбутньому.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Процес у майбутній точці часу.</li>
          <li>Ввічливі питання про плани.</li>
          <li>Нейтральні регулярні дії в майбутньому.</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">This time next week, I’ll be travelling.</div><div class="gw-ua">Цього часу наступного тижня я буду в дорозі.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + will be + V-ing</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + will not be + V-ing</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Will + [S] + be + V-ing?</pre>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">He won’t be working at 8 pm.</span> <span class="gw-ua">Він не працюватиме о 20:00.</span></li>
          <li><span class="gw-en">Will you be using the car tonight?</span> <span class="gw-ua">Будеш користуватись авто ввечері?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Маркер</h3>
        <div class="gw-chips"><span class="gw-chip">at this time tomorrow</span><span class="gw-chip">at 8 pm next Friday</span></div>
      </div>
    </div>
  </div>
</section>

<!-- 11) FUTURE PERFECT -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Future Perfect — Майбутній доконаний</h2>
    <p class="gw-sub">Дія буде завершена <b>до</b> певного моменту у майбутньому.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Завершення до дедлайну/майбутньої точки.</li>
          <li>Прогнози щодо виконання до певного часу.</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">By Friday, I will have finished the project.</div><div class="gw-ua">До п’ятниці я закінчу проєкт.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + will have + V3</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + will not have + V3</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Will + [S] + have + V3?</pre>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">She won’t have arrived by noon.</span> <span class="gw-ua">Вона не приїде до полудня.</span></li>
          <li><span class="gw-en">Will they have completed the task by then?</span> <span class="gw-ua">Виконають завдання до того часу?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Маркери</h3>
        <div class="gw-chips"><span class="gw-chip">by Friday</span><span class="gw-chip">by 2030</span><span class="gw-chip">by the time</span><span class="gw-chip">before</span></div>
      </div>
    </div>
  </div>
</section>

<!-- 12) FUTURE PERFECT CONTINUOUS -->
<section class="grammar-card" lang="uk">
  <header>
    <h2 class="gw-title">Future Perfect Continuous — Майбутній доконано-тривалий</h2>
    <p class="gw-sub">Скільки часу триватиме дія <b>до</b> майбутньої точки.</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li>Тривалість до певного моменту у майбутньому.</li>
          <li>Очікуваний стан до того часу (втома/досвід).</li>
        </ul>
        <div class="gw-ex"><div class="gw-en">By 2026, I’ll have been living here for 10 years.</div><div class="gw-ua">До 2026 я житиму тут 10 років.</div></div>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[S] + will have been + V-ing</pre>
        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[S] + will not have been + V-ing</pre>
        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula">Will + [S] + have been + V-ing?</pre>
      </div>
    </div>
    <div class="gw-col">
      <div class="gw-box">
        <h3>Приклади</h3>
        <ul class="gw-list">
          <li><span class="gw-en">He won’t have been studying for long by September.</span> <span class="gw-ua">До вересня він ще недовго навчатиметься.</span></li>
          <li><span class="gw-en">Will you have been working here for a year by May?</span> <span class="gw-ua">До травня ти пропрацюєш тут рік?</span></li>
        </ul>
      </div>
      <div class="gw-box">
        <h3>Маркер</h3>
        <div class="gw-chips"><span class="gw-chip">for</span><span class="gw-chip">since</span><span class="gw-chip">by then</span><span class="gw-chip">by the time</span></div>
      </div>
    </div>
  </div>
</section>

    <div class="text-center py-12">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-800 mb-6">English Test Hub</h1>
        <p class="text-lg text-gray-600 mb-8">Improve your grammar, vocabulary and translation skills with quick tests.</p>
        <a href="<?php echo e(route('words.test')); ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition">Start Training</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-xl font-semibold mb-2">Words Test</h3>
            <p class="text-gray-600 mb-4">Expand your vocabulary with random word quizzes.</p>
            <a href="<?php echo e(route('words.test')); ?>" class="text-blue-600 hover:underline">Try it</a>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-xl font-semibold mb-2">Translate Test</h3>
            <p class="text-gray-600 mb-4">Practice translating sentences from English.</p>
            <a href="<?php echo e(route('translate.test')); ?>" class="text-blue-600 hover:underline">Try it</a>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-xl font-semibold mb-2">Grammar Tests</h3>
            <p class="text-gray-600 mb-4">Create custom grammar tests for different tenses.</p>
            <a href="<?php echo e(route('grammar-test')); ?>" class="text-blue-600 hover:underline">Try it</a>
        </div>
        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-xl font-semibold mb-2">Question Review</h3>
            <p class="text-gray-600 mb-4">Fix mistakes by reviewing tricky questions.</p>
            <a href="<?php echo e(route('question-review.index')); ?>" class="text-blue-600 hover:underline">Try it</a>
        </div>
    </div>

    <div class="mt-12 text-center">
        <a href="<?php echo e(route('saved-tests.cards')); ?>" class="inline-block bg-gray-200 text-gray-800 px-5 py-3 rounded-lg hover:bg-gray-300 transition">Browse saved tests</a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.engram', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pvital01/data/www/gramlyze.com/resources/views/home.blade.php ENDPATH**/ ?>