<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        Page::firstOrCreate(
            ['slug' => 'future-perfect'],
            [
                'title' => 'Future Perfect — Майбутній доконаний час',
                'text' => <<<'HTML'
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
HTML
            ]
        );
    }
}
