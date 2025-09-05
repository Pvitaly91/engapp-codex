<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PagesSeeder extends Seeder
{
    private function style(): string
    {
        return <<<'HTML'
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
    .gw-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
    .gw-chip { background: var(--chip); border:1px solid #e5e7eb; padding:6px 10px; border-radius:999px; font-size:13px; }
    @media (min-width: 720px) {
      .gw-grid { grid-template-columns: 1.2fr 1fr; }
    }
  </style>
HTML;
    }

    private function card(string $title, string $sub, array $usage, string $formula, array $markers = []): string
    {
        $usageItems = implode('', array_map(fn($u) => "<li>$u</li>", $usage));
        $markersHtml = '';
        if ($markers) {
            $chips = implode('', array_map(fn($m) => "<span class=\"gw-chip\">$m</span>", $markers));
            $markersHtml = <<<HTML
      <div class="gw-box">
        <h3>Маркери часу</h3>
        <div class="gw-chips">$chips</div>
      </div>
HTML;
        }

        return <<<HTML
<section class="grammar-card" lang="uk">
  {$this->style()}
  <header>
    <h2 class="gw-title">$title</h2>
    <p class="gw-sub">$sub</p>
  </header>
  <div class="gw-grid">
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">$usageItems</ul>
      </div>
      <div class="gw-box">
        <h3>Формула</h3>
        <pre class="gw-formula">$formula</pre>
      </div>
      $markersHtml
    </div>
  </div>
</section>
HTML;
    }

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

        $pages = [
            'present-simple' => [
                'title' => 'Present Simple — Теперішній простий час',
                'sub' => 'Використовуємо для звичних дій, фактів і розкладів.',
                'usage' => [
                    'Регулярні дії: I go to school every day.',
                    'Факти й істини: Water boils at 100°C.',
                    'Розклади: The train leaves at 6 pm.',
                ],
                'formula' => '[Підмет] + V1 (+s у 3-й особі однини)',
                'markers' => ['always', 'usually', 'often', 'never', 'every day'],
            ],
            'present-continuous' => [
                'title' => 'Present Continuous — Теперішній тривалий час',
                'sub' => 'Показує дію, що відбувається зараз або навколо теперішнього моменту.',
                'usage' => [
                    'Дія в момент мовлення: She is reading now.',
                    'Тимчасові дії: I am staying with friends.',
                    'Заплановане майбутнє: We are meeting tomorrow.',
                ],
                'formula' => '[Підмет] + am/is/are + V-ing',
                'markers' => ['now', 'at the moment', 'today', 'this week'],
            ],
            'present-perfect' => [
                'title' => 'Present Perfect — Теперішній доконаний час',
                'sub' => 'Показує зв\'язок між минулим і теперішнім.',
                'usage' => [
                    'Досвід без уточнення часу: I have been to London.',
                    'Дія, що триває до тепер: She has lived here for years.',
                    'Нещодавно завершена дія з результатом: He has just finished.',
                ],
                'formula' => '[Підмет] + have/has + V3',
                'markers' => ['already', 'just', 'yet', 'ever', 'never', 'since', 'for'],
            ],
            'present-perfect-continuous' => [
                'title' => 'Present Perfect Continuous — Теперішній доконаний тривалий',
                'sub' => 'Підкреслює тривалість дії, що почалася в минулому і триває досі.',
                'usage' => [
                    'Дія триває до тепер: I have been working since morning.',
                    'Нещодавно завершена дія з наголосом на тривалості: She is tired because she has been running.',
                ],
                'formula' => '[Підмет] + have/has been + V-ing',
                'markers' => ['for', 'since', 'all day', 'lately'],
            ],
            'past-simple' => [
                'title' => 'Past Simple — Минулий простий час',
                'sub' => 'Розповідає про завершені дії в минулому.',
                'usage' => [
                    'Одноразова дія: I visited Paris last year.',
                    'Послідовність дій: He opened the door and entered.',
                    'Факт у минулому: She was 20 in 2010.',
                ],
                'formula' => '[Підмет] + V2/V-ed',
                'markers' => ['yesterday', 'last week', 'in 1990', 'ago'],
            ],
            'past-continuous' => [
                'title' => 'Past Continuous — Минулий тривалий час',
                'sub' => 'Описує дію, що тривала в конкретний момент у минулому.',
                'usage' => [
                    'Дія в процесі: At 5 pm I was cooking.',
                    'Паралельні дії: I was reading while he was playing.',
                    'Перервані дії: I was walking when it started to rain.',
                ],
                'formula' => '[Підмет] + was/were + V-ing',
                'markers' => ['while', 'when', 'at 5 pm'],
            ],
            'past-perfect' => [
                'title' => 'Past Perfect — Минулий доконаний час',
                'sub' => 'Показує дію, що сталася до іншої минулої події.',
                'usage' => [
                    'Перед іншою дією: I had finished before he arrived.',
                    'Причина: She was happy because she had won.',
                ],
                'formula' => '[Підмет] + had + V3',
                'markers' => ['by', 'before'],
            ],
            'past-perfect-continuous' => [
                'title' => 'Past Perfect Continuous — Минулий доконаний тривалий',
                'sub' => 'Підкреслює тривалість дії до певного моменту в минулому.',
                'usage' => [
                    'Дія тривала до іншої: We had been waiting for an hour when she came.',
                    'Причина стану: He was tired because he had been running.',
                ],
                'formula' => '[Підмет] + had been + V-ing',
                'markers' => ['for', 'since', 'before'],
            ],
            'future-simple' => [
                'title' => 'Future Simple — Майбутній простий час',
                'sub' => 'Говорить про прості дії в майбутньому.',
                'usage' => [
                    'Спонтанні рішення: I will help you.',
                    'Прогнози: It will rain tomorrow.',
                    'Обіцянки/пропозиції: I will call you.',
                ],
                'formula' => '[Підмет] + will + V1',
                'markers' => ['tomorrow', 'next week', 'soon'],
            ],
            'future-continuous' => [
                'title' => 'Future Continuous — Майбутній тривалий час',
                'sub' => 'Вказує на дію, що буде в процесі в конкретний момент у майбутньому.',
                'usage' => [
                    'Дія в процесі: At 5 pm I will be traveling.',
                    'Паралельні дії: She will be studying while we are working.',
                    'Ввічливі запитання: Will you be using the car?',
                ],
                'formula' => '[Підмет] + will be + V-ing',
                'markers' => ['this time tomorrow', 'at 8 pm'],
            ],
            'future-perfect-continuous' => [
                'title' => 'Future Perfect Continuous — Майбутній доконаний тривалий',
                'sub' => 'Підкреслює тривалість дії до певного моменту в майбутньому.',
                'usage' => [
                    'Дія триватиме до майбутньої точки: By June she will have been working here for a year.',
                    'Акцент на тривалості: He will have been studying for hours.',
                ],
                'formula' => '[Підмет] + will have been + V-ing',
                'markers' => ['for', 'by'],
            ],
        ];

        foreach ($pages as $slug => $data) {
            Page::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'text' => $this->card($data['title'], $data['sub'], $data['usage'], $data['formula'], $data['markers'])
                ]
            );
        }
    }
}
