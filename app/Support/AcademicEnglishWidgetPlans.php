<?php

namespace App\Support;

/** Reviewed Ukrainian guidance and finite alternatives for the twenty retained thematic questions. */
final class AcademicEnglishWidgetPlans
{
    public const SNAPSHOTS_SHA='21f4657681335a13ea72872b9a530e988b499d8f04d29ff0451902b828d54373';
    private const DATA=<<<'JSON'
{
  "ids": {
    "hedging-and-cautious-language-basics": [
      17488,
      17457,
      17486,
      17470,
      17481
    ],
    "hedging-and-cautious-language": [
      17902,
      17917,
      17927,
      17889,
      17896
    ],
    "stance-register-and-evaluation": [
      18718,
      18750,
      18723,
      18752,
      18706
    ],
    "argumentation-and-academic-tone": [
      19137,
      19157,
      19175,
      19140,
      19165
    ]
  },
  "hints": {
    "17457": "Почни безособовою обережною оцінкою; реалістичність плану не подавай як доведений факт.",
    "17470": "Запереч неминучість постійності, а не будь-яку можливість постійної зміни.",
    "17481": "Передай теперішнє припущення про вже здійснене неправильне розуміння інструкцій.",
    "17486": "Передай існування кращого способу лише як можливість; збережи дію вирішення.",
    "17488": "Передай висновок опитування, не доведення; учням потрібна більша кількість практики.",
    "17889": "Почни безособовою обережною оцінкою підтримки ідеї; не додавай доведеність.",
    "17896": "Передай можливість минулого пропуску частини груп; не заперечуй охоплення всіх груп категорично.",
    "17902": "Збережи обережну можливість і ширший масштаб проблеми; вказівка ще не доводить причини.",
    "17917": "Обмеж оцінку словами про певну міру; не називай аргумент цілком переконливим.",
    "17927": "Уведи питальне слово сам; запитай лише про можливий висновок із цих результатів.",
    "18706": "Назви припущення оманливим, не стверджуй завершеного розв’язання проблеми від себе.",
    "18718": "Передай неможливість упевненого твердження про майбутній успіх плану; це не прогноз неминучої невдачі.",
    "18723": "Запереч саме переконливість доведення надійності моделі; не оголошуй модель ненадійною.",
    "18750": "Збережи обережну оцінку й процес посилення тенденції зараз, не завершену зміну.",
    "18752": "Передай можливість недооцінення ефекту в минулому пасивною конструкцією.",
    "19137": "Сформулюй допустиму аргументовану позицію безособово; політика потребує змін, не оголошена вже зміненою.",
    "19140": "Почни порівняльною оцінкою пояснення; зміна попиту вже відбулася й має теперішню вагу.",
    "19157": "Уведи питальне слово та запитай про доказ на підтримку саме цього твердження.",
    "19165": "Почни протиставним переходом; назви кілька обмежень цього підходу без повного його заперечення.",
    "19175": "Почни підсумковим переходом; передай перевагу позитивних сторін над недоліками."
  },
  "conditions": {
    "17927": "Про що можуть свідчити ці результати?",
    "18706": "Було б оманливо припускати, що проблему розв’язано.",
    "18718": "Не можна з упевненістю сказати, що план спрацює.",
    "18723": "Автор непереконливо доводить, що модель є надійною.",
    "18750": "Схоже, що ця тенденція посилюється.",
    "18752": "Можливо, що цей ефект був недооцінений.",
    "19137": "Можна стверджувати, що ця політика потребує перегляду.",
    "19140": "Більш переконливе пояснення полягає в тому, що попит змінився.",
    "19157": "Який доказ підтверджує це твердження?",
    "19165": "Однак цей підхід має кілька обмежень.",
    "19175": "У підсумку, переваги переважають недоліки."
  },
  "aliases": {
    "17457": [
      "It appears that the plan is realistic"
    ],
    "17481": [
      "It appears that he has misunderstood the instructions"
    ],
    "17486": [
      "There may be a better way to solve it",
      "There could be a better way to solve it",
      "There might be a better way to resolve it",
      "There may be a better way to resolve it",
      "There could be a better way to resolve it"
    ],
    "17488": [
      "The survey suggests that learners need more practice",
      "The survey indicates that students need more practice",
      "The survey suggests that students need more practice"
    ],
    "17889": [
      "It appears that the results support this idea"
    ],
    "17896": [
      "It is possible that the survey did not cover all groups"
    ],
    "17902": [
      "This may indicate a wider problem",
      "This could indicate a wider problem",
      "This might suggest a wider problem",
      "This may suggest a wider problem",
      "This could suggest a wider problem",
      "This might indicate a broader problem",
      "This may indicate a broader problem",
      "This could indicate a broader problem",
      "This might suggest a broader problem",
      "This may suggest a broader problem",
      "This could suggest a broader problem"
    ],
    "17917": [
      "To some extent this argument is persuasive"
    ],
    "17927": [
      "What may these results suggest",
      "What could these results suggest",
      "What might these results indicate",
      "What may these results indicate",
      "What could these results indicate"
    ],
    "18706": [
      "It would be misleading to suggest that the problem has been resolved"
    ],
    "18718": [
      "It cannot be stated with certainty that the plan will work"
    ],
    "18723": [
      "The author does not convincingly show that the model is reliable"
    ],
    "18750": [
      "It seems that this trend is becoming stronger",
      "It appears that this trend is growing stronger",
      "It seems that this trend is growing stronger"
    ],
    "19137": [
      "It can be argued that this policy needs revision"
    ],
    "19140": [
      "A more persuasive explanation is that demand has changed"
    ],
    "19157": [
      "What evidence supports this statement",
      "What evidence substantiates this claim",
      "What evidence substantiates this statement"
    ],
    "19165": [
      "Yet this approach has several limitations",
      "Nevertheless this approach has several limitations",
      "However this approach has several constraints",
      "Yet this approach has several constraints",
      "Nevertheless this approach has several constraints"
    ],
    "19175": [
      "In conclusion the advantages outweigh the drawbacks",
      "In conclusion the benefits outweigh the disadvantages",
      "In conclusion the advantages outweigh the disadvantages"
    ]
  }
}
JSON;
    public static function data():array{return json_decode(self::DATA,true,flags:JSON_THROW_ON_ERROR);}
    public static function ids():array{return self::data()['ids'];}
    public static function hints():array{return self::data()['hints'];}
    public static function conditions():array{return self::data()['conditions'];}
    public static function instructions():array{$out=[];foreach(self::ids()as$ids)foreach($ids as$id)$out[$id]='Склади повне речення; збережи обережність і зміст.';return$out;}
    public static function aliases():array{return self::data()['aliases'];}
}
