<?php

namespace Database\Seeders\pages;

use Illuminate\Database\Seeder;
use App\Models\Page;

class TheoryPageSeeder extends Seeder
{
    public function run(): void
    {
        Page::firstOrCreate(
            ['slug' => 'theory'],
            [
                'title' => 'Conditionals — Умовні речення (If-clauses)',
                'text' => <<<'HTML'
<section class="grammar-card" lang="uk">
  <style>
    /* СТИЛІ ЛИШЕ ДЛЯ ЦЬОГО БЛОКУ */
    .grammar-card { --bg:#ffffff; --text:#1f2937; --muted:#6b7280; --accent:#2563eb; --chip:#f3f4f6; --ok:#10b981; --warn:#f59e0b;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
      color: var(--text); background: var(--bg); border: 1px solid #e5e7eb; border-radius: 16px; padding: 16px; max-width: 1100px; margin: 0 auto 24px; box-shadow: 0 6px 18px rgba(0,0,0,.04);
    }
    .grammar-card * { box-sizing: border-box; }
    .gw-title { font-size: 24px; line-height: 1.2; margin: 0 0 10px; }
    .gw-sub { color: var(--muted); margin: 0 0 18px; font-size: 15px; }
    .gw-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
    .gw-col { display: flex; flex-direction: column; gap: 16px; }
    .gw-box { border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: #fff; }
    .gw-box h3 { margin: 0 0 8px; font-size: 17px; }
    .gw-list { margin: 8px 0 0 18px; }
    .gw-list li { margin: 6px 0; }
    .gw-formula { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      background: #0b1220; color: #e5e7eb; border-radius: 10px; padding: 12px; font-size: 13px; overflow:auto;
    }
    .gw-code-badge { display: inline-block; font-size: 12px; color:#d1d5db; border:1px solid #334155; padding:2px 8px; border-radius:999px; margin-bottom:8px; }
    .gw-ex { background: #f9fafb; border-left: 4px solid var(--accent); padding: 10px 12px; border-radius: 8px; margin-top: 10px; font-size: 14px; }
    .gw-en { font-weight: 600; }
    .gw-ua { color: var(--muted); }
    .gw-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
    .gw-chip { background: var(--chip); border:1px solid #e5e7eb; padding:6px 10px; border-radius:999px; font-size:13px; }
    .gw-table { width: 100%; border-collapse: collapse; font-size: 14px; display: block; overflow-x: auto; }
    .gw-table th, .gw-table td { border: 1px solid #e5e7eb; padding: 8px; vertical-align: top; }
    .gw-table th { background:#f8fafc; text-align:left; }
    .gw-hint { display:flex; gap:10px; align-items:flex-start; background:#f8fafc; border:1px dashed #cbd5e1; padding:10px 12px; border-radius:10px; font-size: 14px; }
    .gw-emoji { font-size: 18px; }
    .tag-ok { color: var(--ok); font-weight: 700; }
    .tag-warn { color: var(--warn); font-weight: 700; }

    /* Inversion box адаптивність */
    .gw-inversion {
      max-width: 100%;
      overflow-x: auto;
      white-space: normal;
    }
    .gw-inversion pre {
      max-width: 100%;
      overflow-x: auto;
      white-space: pre-wrap;
      word-break: break-word;
    }

    /* Адаптивність */
    @media (min-width: 640px) {
      .gw-title { font-size: 26px; }
      .gw-sub { font-size: 16px; }
      .gw-box h3 { font-size: 18px; }
      .gw-formula { font-size: 14px; }
    }
    @media (min-width: 860px) {
      .gw-grid { grid-template-columns: 1.15fr 1fr; }
    }
    @media (min-width: 1024px) {
      .gw-title { font-size: 30px; }
      .gw-sub { font-size: 17px; }
      .gw-formula { font-size: 15px; }
      .gw-box { padding: 18px; }
    }
  </style>

  <header>
    <h2 class="gw-title">Conditionals — Умовні речення (If-clauses)</h2>
    <p class="gw-sub">Моделі речень, де <strong>результат</strong> залежить від <strong>умови</strong>. Тип залежить від часу та реальності ситуації.</p>
  </header>

  <div class="gw-grid">
    <!-- ЛІВА КОЛОНКА -->
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li><strong>Zero Conditional</strong> — загальні істини, правила, рутини.</li>
          <li><strong>First Conditional</strong> — реальні/ймовірні наслідки у майбутньому.</li>
          <li><strong>Second Conditional</strong> — малоймовірні/уявні ситуації зараз або в майбутньому.</li>
          <li><strong>Third Conditional</strong> — уявні (нереальні) минулі ситуації та їх наслідки.</li>
          <li><strong>Mixed Conditionals</strong> — змішання часу умови та наслідку (минуле ↔ теперішнє/майбутнє).</li>
        </ul>

        <div class="gw-ex">
          <div class="gw-en">If it rains, the ground gets wet. (Zero)</div>
          <div class="gw-ua">Якщо йде дощ, земля намокає. (загальний факт)</div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Маркери та варіанти сполучників</h3>
        <div class="gw-chips">
          <span class="gw-chip">if — якщо</span>
          <span class="gw-chip">unless — якщо не</span>
          <span class="gw-chip">as long as — за умови що</span>
          <span class="gw-chip">provided (that) — за умови що</span>
          <span class="gw-chip">in case — про всяк випадок</span>
          <span class="gw-chip">when — коли (для Zero)</span>
        </div>

        <div class="gw-ex">
          <div class="gw-en">You can go out <strong>as long as</strong> you finish your homework.</div>
          <div class="gw-ua">Можеш вийти, <strong>якщо</strong> закінчиш домашнє завдання.</div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Важливо про часи в if-клаузі</h3>
        <div class="gw-hint">
          <div class="gw-emoji">🧠</div>
          <div>
            <p><strong>Умовна частина не вживає will/would.</strong> Майбутнє виражається теперішнім часом у підрядній частині.</p>
            <p class="gw-ua"><em>Правильно:</em> If it <u>rains</u>, we <u>will stay</u> home. <br><em>Неправильно:</em> If it <u>will rain</u>, we will stay home.</p>
          </div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Zero & First — формули</h3>
        <div class="gw-code-badge">Zero Conditional (правила/факти)</div>
        <pre class="gw-formula">If + Present Simple, Present Simple
When/If water <span style="color:#86efac">reaches</span> 100°C, it <span style="color:#86efac">boils</span>.</pre>

        <div class="gw-code-badge">First Conditional (реальне майбутнє)</div>
        <pre class="gw-formula">If + Present Simple, <span style="color:#93c5fd">will</span> + V1
If it <span style="color:#86efac">rains</span>, we <span style="color:#93c5fd">will stay</span> at home.</pre>

        <div class="gw-ex">
          <div class="gw-en">If you study, you will pass.</div>
          <div class="gw-ua">Якщо ти будеш вчитися, ти складеш іспит.</div>
        </div>
      </div>
       <div class="gw-box gw-inversion">
        <h3>Inversion (формальніший варіант без if)</h3>

        <div class="gw-code-badge">Другий тип</div>
        <pre class="gw-formula">Were I you, I would reconsider.  (= If I were you, ...)</pre>

        <div class="gw-code-badge">Третій тип</div>
        <pre class="gw-formula">Had we left earlier, we would have arrived on time.  (= If we had left, ...)</pre>

        <div class="gw-code-badge">Перший тип (рідше)</div>
        <pre class="gw-formula">Should you need help, call me.  (= If you need help, ...)</pre>
      </div>
    </div>

    <!-- ПРАВА КОЛОНКА -->
    <div class="gw-col">
      <div class="gw-box">
        <h3>Second, Third & Mixed — формули</h3>

        <div class="gw-code-badge">Second Conditional (уявне теперіш./майбутнє)</div>
        <pre class="gw-formula">If + Past Simple, <span style="color:#93c5fd">would</span> + V1
If I <span style="color:#86efac">had</span> more time, I <span style="color:#93c5fd">would travel</span> more.</pre>

        <div class="gw-code-badge">Third Conditional (нереальне минуле)</div>
        <pre class="gw-formula">If + Past Perfect (<span style="color:#86efac">had + V3</span>), <span style="color:#93c5fd">would have</span> + V3
If she <span style="color:#86efac">had left</span> earlier, she <span style="color:#93c5fd">would have caught</span> the train.</pre>

        <div class="gw-code-badge">Mixed (минуле → теперішній наслідок)</div>
        <pre class="gw-formula">If + Past Perfect, <span style="color:#93c5fd">would</span> + V1
If I <span style="color:#86efac">had studied</span> medicine, I <span style="color:#93c5fd">would be</span> a doctor now.</pre>

        <div class="gw-ex">
          <div class="gw-en">If I were you, I would take the offer.</div>
          <div class="gw-ua">На твоєму місці я б прийняв пропозицію. (формальне: “were” для всіх осіб)</div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Порівняння типів</h3>
        <table class="gw-table" aria-label="Порівняння типів умовних речень">
          <thead>
            <tr>
              <th>Тип</th>
              <th>Значення</th>
              <th>Формула</th>
              <th>Приклад</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Zero</strong></td>
              <td>Загальний факт/рутина</td>
              <td>If + Present, Present</td>
              <td class="gw-en">If people <strong>don’t drink</strong>, they <strong>get</strong> thirsty.</td>
            </tr>
            <tr>
              <td><strong>First</strong></td>
              <td>Реальний наслідок у майбутньому</td>
              <td>If + Present, will + V1</td>
              <td class="gw-en">If you <strong>hurry</strong>, you <strong>will catch</strong> the bus.</td>
            </tr>
            <tr>
              <td><strong>Second</strong></td>
              <td>Малоймовірне/уявне теперіш./майбутнє</td>
              <td>If + Past, would + V1</td>
              <td class="gw-en">If I <strong>won</strong> the lottery, I <strong>would move</strong> abroad.</td>
            </tr>
            <tr>
              <td><strong>Third</strong></td>
              <td>Нереальне минуле</td>
              <td>If + Past Perfect, would have + V3</td>
              <td class="gw-en">If we <strong>had left</strong> earlier, we <strong>would have arrived</strong> on time.</td>
            </tr>
            <tr>
              <td><strong>Mixed</strong></td>
              <td>Минуле → теперішній/майб. наслідок</td>
              <td>If + Past Perfect, would + V1</td>
              <td class="gw-en">If she <strong>had studied</strong> art, she <strong>would be</strong> a designer now.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="gw-box">
        <h3>Типові помилки</h3>
        <ul class="gw-list">
          <li><span class="tag-warn">✗</span> Вживати <em>will/would</em> у підрядній частині: <em>If it <u>will</u> rain</em>. <span class="tag-ok">✓</span> Правильно: <em>If it rains, …</em></li>
          <li><span class="tag-warn">✗</span> Плутати Second і Third: теперішня уява ≠ минуле, якого вже не змінити.</li>
          <li><span class="tag-warn">✗</span> Невірний порядок слів у наслідку: <em>would have + V3</em> лише для минулих нереальних наслідків.</li>
          <li><span class="tag-ok">✓</span> Пам’ятай: <strong>кома</strong> ставиться, якщо if-клаузу ставимо на початку: <em>If you call, I’ll answer.</em></li>
        </ul>
      </div>

     
    </div>
  </div>
</section>
HTML,
            ]
        );
    }
}
