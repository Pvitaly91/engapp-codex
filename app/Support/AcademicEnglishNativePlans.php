<?php

namespace App\Support;

/** Finite reviewed Academic English teaching plans; no presentation or grading policy. */
final class AcademicEnglishNativePlans
{
    private static function data():array
    {
        return json_decode(<<<'JSON'
{
  "retained": {
    "hedging-and-cautious-language-basics": [
      [
        "On this journey,|the new route|was relatively quick|compared with|our usual route:|twenty minutes|rather than forty-five.",
        "Ця поїздка: новий маршрут — двадцять хвилин, звичний — сорок п’ять. Порівняй їх без загальної гарантії.",
        "Оціни швидкість лише цієї поїздки щодо звичного маршруту; збережи обидва названі значення часу."
      ],
      [
        "The lamp|came on after|the battery|was replaced.|The old battery|may have caused|the problem.",
        "Після заміни батарейки лампа засвітилася; батарейку окремо не перевіряли. Назви спостереження й можливе пояснення.",
        "Спочатку назви спостереження, потім лише можливу минулу причину; не перетворюй послідовність подій на доказ."
      ]
    ],
    "hedging-and-cautious-language": [
      [
        "The queues|were shorter after|the new signs|were installed, but|fewer visitors|came that day.|The signs|may have helped;|the lower number|of visitors|is another|possible explanation.",
        "Після нових вказівників черги стали коротшими, але відвідувачів також було менше. Збережи обидва можливі пояснення.",
        "Не називай жодну причину доведеною; збережи коротші черги, нові вказівники та меншу кількість відвідувачів."
      ],
      [
        "Seven of the|eight volunteers|completed the|second search faster.|The new labels|may have helped,|but|everyone used|them second, so|practice may|also explain|the change.",
        "Семеро з восьми шукали швидше вдруге; усі використовували нові підписи другим проходженням. Врахуй можливий ефект практики.",
        "Збережи відомі числа й порядок проходжень; невизначеність стосується пояснення зміни, а не спостереженого результату."
      ]
    ],
    "stance-register-and-evaluation": [
      [
        "This edition improves|access to examples:|each term now|links to one,|unlike the|previous edition.",
        "У новій редакції кожен термін має посилання на приклад, у попередній — ні. Назви конкретну перевагу.",
        "Оцінюй доступ до прикладів; не додавай неперевіреного покращення розуміння читачами."
      ],
      [
        "For navigation|between sections,|the new|audio guide offers|a clear advantage:|listeners can select|a named section|directly. However,|it has been|checked on only|one device, so|its compatibility|with other devices|remains unknown.",
        "Новий аудіогід дозволяє прямо вибрати розділ, але перевірений лише на одному пристрої. Назви перевагу та межу.",
        "Критерій — навігація між розділами; сумісність з іншими пристроями лишається невідомою, не доведеною чи спростованою."
      ]
    ],
    "argumentation-and-academic-tone": [
      [
        "The poster changed|on Monday, and|attendance increased on|Tuesday. These facts|alone do not|establish that the|new poster caused|the increase.",
        "Афішу змінили в понеділок, відвідуваність зросла у вівторок. Не перетворюй цю послідовність на доведену причину.",
        "Збережи дні та обидва спостереження; самі ці факти не встановлюють причинного зв’язку."
      ],
      [
        "Guide B offers|a useful aid|for locating definitions.|Its index points|to every definition,|whereas Guide A|has no index.|Readers can therefore|use Guide B’s|index to find|the page of|a particular definition.|This does not|establish that they|will find it|faster, since search|times were not|measured.",
        "У посібнику Б є покажчик усіх визначень, у А — ні; швидкість не вимірювали. Обґрунтуй користь без перебільшення.",
        "Поясни зв’язок покажчика з пошуком сторінки; окремо збережи обмеження щодо неперевіреної швидкості."
      ]
    ]
  },
  "additions": {
    "hedging-and-cautious-language-basics": [
      [
        "gap",
        "The sound may {a1} from the cupboard.",
        "come",
        "Походити: назви можливе джерело звуку після модального слова; наведений предмет не повторюй.",
        [
          "originate"
        ]
      ],
      [
        "gap",
        "The result is {a1} stable when compared with yesterday’s reading.",
        "relatively",
        "Відносно: пом’якши оцінку стійкості щодо вже названого попереднього показника.",
        [
          "comparatively"
        ]
      ],
      [
        "translation",
        "Здається, цей міст менш слизький після ремонту.",
        "This bridge|seems to be|less slippery|after the repairs.",
        "Почни з мосту та збережи обережну оцінку, не певний факт.",
        [
          "This bridge appears to be less slippery after the repairs.",
          "This bridge seems to be less slippery after the repair.",
          "This bridge appears to be less slippery after the repair."
        ]
      ],
      [
        "translation",
        "Ці насінини зазвичай проростають повільніше в тіні.",
        "These seeds|tend to germinate|more slowly|in the shade.",
        "Передай типову тенденцію, не незмінну закономірність; збережи повільніший процес і тінь.",
        [
          "These seeds tend to sprout more slowly in the shade."
        ]
      ]
    ],
    "hedging-and-cautious-language": [
      [
        "gap",
        "The pattern appears {a1} specific to the coastal sites in this survey.",
        "to be",
        "Бути: передай обережну оцінку особливості, обмежену названими прибережними ділянками цього дослідження."
      ],
      [
        "gap",
        "The association alone does not {a1} that temperature caused the change.",
        "establish",
        "Доводити: заперечується встановлення причини, а не саме спостереження зв’язку.",
        [
          "demonstrate",
          "show",
          "prove"
        ]
      ],
      [
        "translation",
        "За цих умов менші отвори могли сприяти рівномірнішому висиханню.",
        "Under these conditions,|the smaller openings|may have contributed|to more even|drying.",
        "Назви ці умови й лише можливий минулий внесок менших отворів, не доведену єдину причину.",
        [
          "Under these conditions, the smaller openings might have contributed to more even drying.",
          "Under these conditions, the smaller openings could have contributed to more even drying."
        ]
      ],
      [
        "translation",
        "Ці результати не слід поширювати на села, які не увійшли до вибірки.",
        "These findings|should not be|generalised beyond|the sampled villages.",
        "Збережи застереження щодо меж узагальнення; не стверджуй, що результати хибні.",
        [
          "These findings should not be generalized beyond the sampled villages.",
          "These results should not be generalised beyond the sampled villages.",
          "These results should not be generalized beyond the sampled villages."
        ]
      ]
    ],
    "stance-register-and-evaluation": [
      [
        "gap",
        "On the criterion of repairability, this model offers a {a1} advantage over its predecessor.",
        "clear",
        "Виразну: оцінка стосується лише можливості ремонту, не всіх властивостей моделі.",
        [
          "distinct",
          "marked"
        ]
      ],
      [
        "gap",
        "The review praises the coating’s durability while {a1} its limited colour range.",
        "acknowledging",
        "Визнаючи: збережи позитивну оцінку міцності й одночасне визнання конкретного обмеження.",
        [
          "recognising",
          "recognizing",
          "noting"
        ]
      ],
      [
        "translation",
        "Наявні дані вказують, що ця система полегшує повторне використання деталей, але її безпеку ще не оцінили.",
        "The available evidence|suggests that|this system makes|components easier|to reuse,|but its safety|has not yet|been assessed.",
        "Збережи обережну опору на дані й відсутність оцінювання безпеки; не називай систему безпечною чи небезпечною.",
        [
          "The available evidence suggests that this system makes components easier to reuse, but its safety has not yet been evaluated."
        ]
      ],
      [
        "translation",
        "Цей висновок є переконливим щодо точності записів, а не щодо причин помилки.",
        "This conclusion|is persuasive|with regard to|the accuracy|of the records,|not the causes|of the error.",
        "Оцінюй переконливість лише щодо точності записів; причини помилки не оголошуй установленими.",
        [
          "This conclusion is convincing with regard to the accuracy of the records, not the causes of the error."
        ]
      ]
    ],
    "argumentation-and-academic-tone": [
      [
        "gap",
        "The timing supports an association, but it does not {a1} a causal connection.",
        "establish",
        "Доводити: відокрем спостережуваний часовий зв’язок від установленої причинності.",
        [
          "prove",
          "demonstrate",
          "show"
        ]
      ],
      [
        "gap",
        "The objection concerns cost; {a1}, it does not answer the claim about accessibility.",
        "therefore",
        "Отже: познач висновок із того, що заперечення й теза стосуються різних критеріїв.",
        [
          "consequently",
          "thus",
          "hence"
        ]
      ],
      [
        "translation",
        "Цифри підтверджують менше споживання води, але не доводять економії грошей.",
        "The figures|support lower|water consumption,|but do not|establish financial savings.",
        "Збережи різницю між підтвердженим споживанням води й непідтвердженим грошовим наслідком.",
        [
          "The figures support lower water consumption, but do not prove financial savings.",
          "The numbers support lower water consumption, but do not establish financial savings.",
          "The numbers support lower water consumption, but do not prove financial savings."
        ]
      ],
      [
        "translation",
        "Це заперечення послаблює рекомендацію для новачків, не спростовуючи її корисності для досвідчених читачів.",
        "This objection|weakens the recommendation|for beginners|without disproving|its usefulness|for experienced readers.",
        "Збережи різні групи читачів і часткове послаблення рекомендації, а не повне її спростування.",
        [
          "This criticism weakens the recommendation for beginners without disproving its usefulness for experienced readers.",
          "This objection weakens the recommendation for novices without disproving its usefulness for experienced readers.",
          "This criticism weakens the recommendation for novices without disproving its usefulness for experienced readers."
        ]
      ]
    ]
  }
}
JSON, true, flags: JSON_THROW_ON_ERROR);
    }
    public static function retained():array{return self::data()['retained'];}
    public static function additions():array{return self::data()['additions'];}
}

