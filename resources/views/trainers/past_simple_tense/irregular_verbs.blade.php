<style>
    body { font-family: Arial, sans-serif; background: #f4f6fa; padding: 40px;}
    h1 { text-align: center; }
    #search { 
      display: block; margin: 0 auto 20px auto; 
      font-size: 20px; padding: 8px 14px; width: 350px; border-radius: 8px; border: 1px solid #ddd;
      box-shadow: 0 2px 6px #0001;
    }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; box-shadow: 0 2px 10px #0001; }
    th, td { padding: 10px 8px; border: 1px solid #ddd; text-align: center; }
    th { background: #f0f2f8; }
    tr:hover { background: #eaf3ff; }
    @media (max-width: 600px) {
      #search { width: 100%; }
      table, th, td { font-size: 14px; }
    }
  </style>
  <h1>Таблиця неправильних дієслів</h1>
  <input type="text" id="search" placeholder="Пошук за словом (будь-яка форма або переклад)..." autofocus>
  <table id="verbs">
    <thead>
      <tr>
        <th>Infinitive (V1)</th>
        <th>Past Simple (V2)</th>
        <th>Past Participle (V3)</th>
        <th>Переклад</th>
      </tr>
    </thead>
    <tbody>
      <!-- Таблиця заповнюється через JS -->
    </tbody>
  </table>
  <script>
    // Дані для таблиці (можна доповнювати!)
    const verbs = [
      { v1: "be", v2: "was/were", v3: "been", ua: "бути" },
      { v1: "become", v2: "became", v3: "become", ua: "ставати" },
      { v1: "begin", v2: "began", v3: "begun", ua: "починати" },
      { v1: "break", v2: "broke", v3: "broken", ua: "ламати" },
      { v1: "bring", v2: "brought", v3: "brought", ua: "приносити" },
      { v1: "build", v2: "built", v3: "built", ua: "будувати" },
      { v1: "buy", v2: "bought", v3: "bought", ua: "купувати" },
      { v1: "catch", v2: "caught", v3: "caught", ua: "ловити" },
      { v1: "choose", v2: "chose", v3: "chosen", ua: "вибирати" },
      { v1: "come", v2: "came", v3: "come", ua: "приходити" },
      { v1: "do", v2: "did", v3: "done", ua: "робити" },
      { v1: "drink", v2: "drank", v3: "drunk", ua: "пити" },
      { v1: "drive", v2: "drove", v3: "driven", ua: "водити" },
      { v1: "eat", v2: "ate", v3: "eaten", ua: "їсти" },
      { v1: "fall", v2: "fell", v3: "fallen", ua: "падати" },
      { v1: "feel", v2: "felt", v3: "felt", ua: "відчувати" },
      { v1: "find", v2: "found", v3: "found", ua: "знаходити" },
      { v1: "fly", v2: "flew", v3: "flown", ua: "літати" },
      { v1: "forget", v2: "forgot", v3: "forgotten", ua: "забувати" },
      { v1: "get", v2: "got", v3: "got/gotten", ua: "отримувати" },
      { v1: "give", v2: "gave", v3: "given", ua: "давати" },
      { v1: "go", v2: "went", v3: "gone", ua: "йти" },
      { v1: "have", v2: "had", v3: "had", ua: "мати" },
      { v1: "hear", v2: "heard", v3: "heard", ua: "чути" },
      { v1: "know", v2: "knew", v3: "known", ua: "знати" },
      { v1: "leave", v2: "left", v3: "left", ua: "залишати" },
      { v1: "make", v2: "made", v3: "made", ua: "робити/виготовляти" },
      { v1: "meet", v2: "met", v3: "met", ua: "зустрічати" },
      { v1: "put", v2: "put", v3: "put", ua: "класти" },
      { v1: "read", v2: "read", v3: "read", ua: "читати" },
      { v1: "run", v2: "ran", v3: "run", ua: "бігти" },
      { v1: "say", v2: "said", v3: "said", ua: "казати" },
      { v1: "see", v2: "saw", v3: "seen", ua: "бачити" },
      { v1: "sit", v2: "sat", v3: "sat", ua: "сидіти" },
      { v1: "speak", v2: "spoke", v3: "spoken", ua: "говорити" },
      { v1: "take", v2: "took", v3: "taken", ua: "брати" },
      { v1: "teach", v2: "taught", v3: "taught", ua: "вчити" },
      { v1: "tell", v2: "told", v3: "told", ua: "розповідати" },
      { v1: "think", v2: "thought", v3: "thought", ua: "думати" },
      { v1: "write", v2: "wrote", v3: "written", ua: "писати" }
    ];

    const tbody = document.querySelector('#verbs tbody');
    const search = document.getElementById('search');

    function renderTable(filter = "") {
      tbody.innerHTML = "";
      const f = filter.trim().toLowerCase();
      verbs
        .filter(verb =>
          verb.v1.includes(f) ||
          verb.v2.includes(f) ||
          verb.v3.includes(f) ||
          verb.ua.toLowerCase().includes(f)
        )
        .forEach(verb => {
          tbody.innerHTML += `<tr>
            <td>${verb.v1}</td>
            <td>${verb.v2}</td>
            <td>${verb.v3}</td>
            <td>${verb.ua}</td>
          </tr>`;
        });
      if (tbody.innerHTML === "") tbody.innerHTML = '<tr><td colspan="4">Не знайдено :(</td></tr>';
    }

    search.addEventListener('input', e => renderTable(e.target.value));
    renderTable();
  </script>