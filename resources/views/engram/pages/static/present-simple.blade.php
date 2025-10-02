<!-- Present Simple (Теперішній простий час) — вставний блок -->
@include('engram.pages.static.partials.grammar-card-styles')

<section class="grammar-card" lang="uk">

  <header>
    <h2 class="gw-title">Present Simple — Теперішній простий час</h2>
    <p class="gw-sub">Використовуємо для <strong>фактів, звичок, розкладів</strong> та регулярних дій.</p>
  </header>

  <div class="gw-grid">
    <!-- ЛІВА КОЛОНКА -->
    <div class="gw-col">
      <div class="gw-box">
        <h3>Коли вживати?</h3>
        <ul class="gw-list">
          <li><strong>Факти</strong>: закони природи, загальні істини.</li>
          <li><strong>Звички</strong>: те, що робимо регулярно.</li>
          <li><strong>Розклади</strong>: поїзди, уроки, кіносеанси.</li>
          <li><strong>Стан</strong> (like, know, want) — не в Continuous.</li>
        </ul>

        <div class="gw-ex">
          <div class="gw-en">The sun <strong>rises</strong> in the east.</div>
          <div class="gw-ua">Сонце <strong>сходить</strong> на сході.</div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Формула</h3>
        <div class="gw-code-badge">Ствердження</div>
        <pre class="gw-formula">[Підмет] + V1 (+s/es для he/she/it)
I <span style="color:#86efac">work</span>.
She <span style="color:#86efac">works</span>.</pre>

        <div class="gw-code-badge">Заперечення</div>
        <pre class="gw-formula">[Підмет] + do/does not + V1
He <span style="color:#93c5fd">doesn’t</span> <span style="color:#86efac">like</span> coffee.</pre>

        <div class="gw-code-badge">Питання</div>
        <pre class="gw-formula"><span style="color:#93c5fd">Do/Does</span> + [підмет] + V1?
<span style="color:#93c5fd">Do</span> you <span style="color:#86efac">play</span> chess?</pre>
      </div>

      <div class="gw-box">
        <h3>Маркери часу</h3>
        <div class="gw-chips">
          <span class="gw-chip">always</span>
          <span class="gw-chip">usually</span>
          <span class="gw-chip">often</span>
          <span class="gw-chip">sometimes</span>
          <span class="gw-chip">rarely</span>
          <span class="gw-chip">never</span>
          <span class="gw-chip">every day / week</span>
          <span class="gw-chip">on Mondays</span>
        </div>

        <div class="gw-ex">
          <div class="gw-en">She <strong>goes</strong> to the gym every Friday.</div>
          <div class="gw-ua">Вона <strong>ходить</strong> у спортзал щоп’ятниці.</div>
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
            <p>Для <strong>he/she/it</strong> додаємо <b>-s/-es</b>: works, watches.</p>
            <p>В усіх інших випадках — дієслово у базовій формі.</p>
          </div>
        </div>
      </div>

      <div class="gw-box">
        <h3>Порівняння</h3>
        <table class="gw-table" aria-label="Порівняння Present Simple та Present Continuous">
          <thead>
            <tr>
              <th>Час</th>
              <th>Використання</th>
              <th>Формула</th>
              <th>Приклад</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Present Simple</strong></td>
              <td>Факти, звички, розклади</td>
              <td>V1 / do/does + V1</td>
              <td><span class="gw-en">She <strong>reads</strong> every evening.</span></td>
            </tr>
            <tr>
              <td><strong>Present Continuous</strong></td>
              <td>Дія у процесі зараз</td>
              <td>am/is/are + V-ing</td>
              <td><span class="gw-en">She <strong>is reading</strong> now.</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="gw-box">
        <h3>Типові помилки</h3>
        <ul class="gw-list">
          <li><span class="tag-warn">✗</span> Забувати додати <b>-s/-es</b> для he/she/it.</li>
          <li><span class="tag-warn">✗</span> Використовувати Present Simple для дії «зараз» (там треба Present Continuous).</li>
          <li><span class="tag-ok">✓</span> Використовуй Present Simple для <strong>звичок, фактів, розкладів</strong>.</li>
        </ul>
      </div>
    </div>
  </div>
</section>
