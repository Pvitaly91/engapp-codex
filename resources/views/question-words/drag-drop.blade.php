@extends('layouts.app')

@section('title', $test->name)

@section('content')
<div class="wrapper">
    <h1>{{ $test->name }}</h1>
    <p class="subtitle">Перетягни правильне <strong>question word</strong> у пропуск. Можна також торкнутися токена, а потім — пропуск (зручно на мобільних).</p>

    <div class="grid">
        <div class="card left" id="tasks"></div>

        <aside class="card right">
            <h3>Банк слів</h3>
            <div class="legend">Перетягуй або натискай, щоб обрати.</div>
            <div id="bank" class="bank" aria-label="Word bank"></div>

            <div class="controls">
                <button id="check">Перевірити</button>
                <button id="retry" class="secondary">Спробувати ще</button>
                <button id="show" class="ghost">Показати відповіді</button>
                <div class="score" id="score">0 / {{ count($questions) }}</div>
            </div>
            <p class="hint">Підсвічення: <span style="color:var(--ok);font-weight:700">зелений</span> — вірно, <span style="color:var(--bad);font-weight:700">червоний</span> — помилка.</p>
        </aside>
    </div>
</div>

<style>
  :root {
    --bg:#f8fafc; --card:#ffffff; --text:#0f172a; --muted:#64748b; --accent:#2563eb;
    --ok:#16a34a; --bad:#dc2626; --chip:#eef2ff; --chip-border:#c7d2fe;
  }
  .wrapper{max-width:1100px;margin:24px auto;padding:0 16px}
  .wrapper h1{font-size:clamp(22px,3vw,32px);margin:0 0 8px}
  .wrapper .subtitle{color:var(--muted);margin:0 0 20px}
  .grid{display:grid;grid-template-columns:1fr 300px;gap:20px}
  @media (max-width:900px){.grid{grid-template-columns:1fr}}
  .card{background:var(--card);border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 0 rgba(0,0,0,.02)}
  .left{padding:14px}
  .right{padding:14px;position:sticky;top:10px;align-self:start}
  .bank{
    display:flex;flex-wrap:wrap;gap:8px;min-height:54px;padding:8px;border:1px dashed #d1d5db;border-radius:10px;background:#fafafa
  }
  .token{
    user-select:none; cursor:grab; padding:8px 10px; border-radius:999px; font-weight:600;
    background:var(--chip); border:1px solid var(--chip-border);
    transition:transform .1s ease, box-shadow .1s ease, background .2s ease;
  }
  .token[draggable="true"]:active{cursor:grabbing; transform:scale(.96)}
  .token.selected{outline:2px solid var(--accent); box-shadow:0 0 0 2px #dbeafe}
  .controls{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
  button{
    appearance:none; border:1px solid #e5e7eb; background:#111827; color:#fff; padding:10px 14px;
    border-radius:10px; font-weight:700; cursor:pointer;
  }
  button.secondary{background:#fff;color:#111827}
  button.ghost{background:#fff;color:#111827;border-style:dashed}
  .score{margin-left:auto;font-weight:800}
  .row{display:grid; grid-template-columns:auto 1fr; gap:10px; padding:10px 8px; align-items:start; border-bottom:1px dashed #e5e7eb}
  .num{width:28px; text-align:right; color:var(--muted)}
  .sentence{display:flex; flex-wrap:wrap; gap:8px; align-items:center}
  .sentence span{text-wrap:wrap}
  .drop{
    min-width:110px; min-height:38px; display:inline-flex; align-items:center; justify-content:center;
    padding:4px 10px; border:2px dashed #d1d5db; border-radius:10px; background:#fff; color:#64748b;
    transition:border-color .15s ease, background .15s ease;
  }
  .drop.hover{border-color:#60a5fa; background:#eff6ff}
  .drop.filled{border-style:solid; color:var(--text)}
  .drop.correct{border-color:var(--ok); background:#ecfdf5}
  .drop.wrong{border-color:var(--bad); background:#fef2f2}
  .x{
    margin-left:6px; font-weight:900; color:#9ca3af; cursor:pointer; user-select:none; display:none;
  }
  .drop.filled .x{display:inline}
  .answer{color:var(--muted)}
  .hint{font-size:12px;color:var(--muted);margin:8px 0 0}
  .right h3{margin:6px 0 10px}
  .legend{font-size:12px;color:var(--muted);margin:6px 0 8px}
</style>

<script>
const QUESTIONS = @json($questions, JSON_UNESCAPED_UNICODE);

const tasksEl = document.getElementById('tasks');
const bankEl  = document.getElementById('bank');
const scoreEl = document.getElementById('score');
let selectedTokenId = null;

const TOKENS = (() => {
  const map = {};
  QUESTIONS.forEach(q => {
    const answer = q.answer;
    map[answer] = (map[answer] || 0) + 1;
  });
  return Object.entries(map).flatMap(([word, count]) =>
    Array.from({ length: count }, (_, i) => ({ word, id: `${word}-${i + 1}` }))
  );
})();

function splitTemplate(template) {
  const marker = '_____';
  const index = template.indexOf(marker);
  if (index === -1) {
    return { before: template, after: '' };
  }
  return {
    before: template.slice(0, index),
    after: template.slice(index + marker.length),
  };
}

function renderTasks(){
  tasksEl.innerHTML = '';
  QUESTIONS.forEach((question, idx) => {
    const row = document.createElement('div');
    row.className = 'row';

    const num = document.createElement('div');
    num.className = 'num';
    num.textContent = `${idx + 1}.`;
    row.appendChild(num);

    const sentence = document.createElement('div');
    sentence.className = 'sentence';

    const drop = document.createElement('span');
    drop.className = 'drop';
    drop.dataset.index = idx;
    drop.tabIndex = 0;
    drop.textContent = '_____';

    drop.addEventListener('dragover', e => {
      e.preventDefault();
      drop.classList.add('hover');
    });
    drop.addEventListener('dragleave', () => drop.classList.remove('hover'));
    drop.addEventListener('drop', e => {
      e.preventDefault();
      drop.classList.remove('hover');
      const id = e.dataTransfer.getData('text/plain');
      placeTokenInDrop(id, drop);
    });
    drop.addEventListener('click', () => {
      if (selectedTokenId) {
        placeTokenInDrop(selectedTokenId, drop);
      }
    });

    const { before, after } = splitTemplate(question.template || '_____');
    if (before) {
      const beforeSpan = document.createElement('span');
      beforeSpan.textContent = before;
      sentence.appendChild(beforeSpan);
    }

    sentence.appendChild(drop);

    if (after) {
      const afterSpan = document.createElement('span');
      afterSpan.textContent = after;
      sentence.appendChild(afterSpan);
    }

    if (question.tail) {
      const tailSpan = document.createElement('span');
      tailSpan.className = 'answer';
      tailSpan.textContent = ` ${question.tail}`;
      sentence.appendChild(tailSpan);
    }

    row.appendChild(sentence);
    tasksEl.appendChild(row);
  });
}

function renderBank(shuffle = true){
  bankEl.innerHTML = '';
  selectedTokenId = null;
  const items = shuffle ? shuffled(TOKENS) : TOKENS.slice();
  for (const token of items) {
    const el = document.createElement('div');
    el.className = 'token';
    el.textContent = token.word;
    el.setAttribute('draggable', 'true');
    el.dataset.id = token.id;
    el.dataset.word = token.word;

    el.addEventListener('dragstart', e => {
      e.dataTransfer.setData('text/plain', token.id);
    });

    el.addEventListener('click', () => {
      const alreadySelected = el.classList.contains('selected');
      [...bankEl.querySelectorAll('.token')].forEach(x => x.classList.remove('selected'));
      if (!alreadySelected) {
        el.classList.add('selected');
        selectedTokenId = token.id;
      } else {
        selectedTokenId = null;
      }
    });

    bankEl.appendChild(el);
  }
}

function placeTokenInDrop(tokenId, drop){
  const token = bankEl.querySelector(`.token[data-id="${CSS.escape(tokenId)}"]`);
  if (!token) {
    return;
  }

  const existing = drop.querySelector('.token');
  if (existing) {
    returnTokenToBank(existing);
  }

  token.classList.remove('selected');
  selectedTokenId = null;

  const clone = token.cloneNode(true);
  clone.removeAttribute('draggable');
  clone.style.cursor = 'default';

  const removeBtn = document.createElement('span');
  removeBtn.className = 'x';
  removeBtn.title = 'Видалити';
  removeBtn.textContent = '×';
  removeBtn.addEventListener('click', () => {
    returnTokenToBank(clone);
  });

  const wrapper = document.createElement('span');
  wrapper.appendChild(clone);
  wrapper.appendChild(removeBtn);

  drop.innerHTML = '';
  drop.appendChild(wrapper);
  drop.classList.add('filled');

  token.remove();
}

function returnTokenToBank(tokenInDrop){
  const word = tokenInDrop.textContent;

  const el = document.createElement('div');
  el.className = 'token';
  el.textContent = word;
  el.setAttribute('draggable', 'true');
  el.dataset.id = tokenInDrop.dataset.id || cryptoRandomId();
  el.dataset.word = word;

  el.addEventListener('dragstart', e => {
    e.dataTransfer.setData('text/plain', el.dataset.id);
  });
  el.addEventListener('click', () => {
    const alreadySelected = el.classList.contains('selected');
    [...bankEl.querySelectorAll('.token')].forEach(x => x.classList.remove('selected'));
    if (!alreadySelected) {
      el.classList.add('selected');
      selectedTokenId = el.dataset.id;
    } else {
      selectedTokenId = null;
    }
  });

  bankEl.appendChild(el);

  const drop = tokenInDrop.closest('.drop');
  if (drop) {
    drop.textContent = '_____';
    drop.classList.remove('filled', 'correct', 'wrong');
  }
}

function cryptoRandomId(){
  return 'id-' + Math.random().toString(36).slice(2, 9);
}

function getCurrentAnswers(){
  return [...tasksEl.querySelectorAll('.drop')].map(drop => {
    const token = drop.querySelector('.token');
    return token ? token.textContent.trim() : '';
  });
}

function normalize(value){
  return value.replace(/\s+/g, ' ').trim().toLowerCase();
}

function checkAnswers(){
  const current = getCurrentAnswers();
  let score = 0;

  current.forEach((value, index) => {
    const drop = tasksEl.querySelectorAll('.drop')[index];
    drop.classList.remove('correct', 'wrong');

    if (!value) {
      return;
    }

    if (normalize(value) === normalize(QUESTIONS[index].answer)) {
      drop.classList.add('correct');
      score++;
    } else {
      drop.classList.add('wrong');
    }
  });

  scoreEl.textContent = `${score} / ${QUESTIONS.length}`;
}

function showAnswers(){
  tasksEl.querySelectorAll('.drop .token').forEach(token => returnTokenToBank(token));

  QUESTIONS.forEach((question, index) => {
    const drop = tasksEl.querySelectorAll('.drop')[index];
    const token = bankEl.querySelector(`.token[data-word="${CSS.escape(question.answer)}"]`);
    if (token) {
      placeTokenInDrop(token.dataset.id, drop);
    }
    drop.classList.add('correct');
  });

  selectedTokenId = null;
  checkAnswers();
}

function retry(){
  tasksEl.querySelectorAll('.drop .token').forEach(token => returnTokenToBank(token));
  tasksEl.querySelectorAll('.drop').forEach(drop => drop.classList.remove('correct', 'wrong'));
  scoreEl.textContent = `0 / ${QUESTIONS.length}`;
  selectedTokenId = null;
  renderBank(true);
}

function shuffled(arr){
  const copy = arr.slice();
  for (let i = copy.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [copy[i], copy[j]] = [copy[j], copy[i]];
  }
  return copy;
}

renderTasks();
renderBank(true);

document.getElementById('check').addEventListener('click', checkAnswers);
document.getElementById('retry').addEventListener('click', retry);
document.getElementById('show').addEventListener('click', showAnswers);

tasksEl.addEventListener('keydown', event => {
  if (event.key === 'Enter') {
    const drop = event.target.closest('.drop');
    if (drop && selectedTokenId) {
      placeTokenInDrop(selectedTokenId, drop);
    }
  }
});
</script>
@endsection
