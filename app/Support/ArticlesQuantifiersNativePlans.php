<?php

namespace App\Support;

/** Reviewed finite teaching data, independent of presentation and grading policy. */
final class ArticlesQuantifiersNativePlans
{
    public static function retained():array
    {
        return [
            'advanced-article-and-quantifier-nuance'=>[
                ['There is|not much evidence|for this explanation.','На користь цього пояснення мало свідчень. Передай це через заперечення великої кількості.','Збережи обмежену підтримку пояснення; не перетворюй малу кількість на повну відсутність.'],
                ['We have|a little|useful information.|It is|not enough for|a complete catalogue.','Є трохи корисної інформації, але її недостатньо для повного каталогу. Склади два речення.','Збережи наявність невеликої кількості та прямо названу недостатність для повного каталогу.'],
            ],
            'precision-with-articles-and-determiners'=>[
                ['Not both lamps|work.','Перша з двох ламп не працює; другу не перевіряли. Склади підтверджений висновок і вибери два уточнення.','Запереч, що працюють обидві; не стверджуй, що не працює жодна або що працює рівно одна.'],
                ['Six folders|were placed in|the cabinet.|Two of|the six folders|were checked.|Both of|the checked folders|were dry.','До шафи поклали шість папок; дві перевірили, обидві перевірені сухі. Про решту нічого не відомо.','Назви всі шість папок, підгрупу двох і результат лише для перевірених папок; не додавай безпеку чи причинність.'],
            ],
        ];
    }

    public static function additions():array
    {
        return [
            'advanced-article-and-quantifier-nuance'=>[
                ['gap','A small {a1} of salt improves the flavour without making the soup salty.','amount','Кількість (обсяг): доповни сполуку перед незлічуваною назвою солі; приймаються два природні іменники кількості.',['quantity']],
                ['gap','The number of requests {a1} steadily each month.','increases','Зростає: узгодь теперішню форму з кількістю як єдиним показником, а не з окремими запитами.',['rises','grows']],
                ['translation','Нам бракує часу, щоб перевірити кожну деталь.','We do not|have enough time|to check|every detail.','Почни з «ми»; запереч достатність часу для перевірки всіх деталей окремо.',['We do not have enough time to examine every detail.','We do not have sufficient time to check every detail.','We do not have sufficient time to examine every detail.']],
                ['translation','Лише кілька учасників поставили запитання після доповіді.','Only a few|participants asked questions|after the talk.','Збережи невелику ненульову кількість учасників і час після доповіді; не називай точного числа.',['Only a few participants asked questions after the presentation.']],
            ],
            'precision-with-articles-and-determiners'=>[
                ['gap','Both of the replacement screens {a1} the same amount of power.','consume','Споживають: узгодь теперішню форму з двома названими екранами; передай використання електроенергії.',['use']],
                ['gap','Each of the three packages {a1} a separate tracking number.','has','Має: у формальній нотатці узгодь теперішню форму з кожною посилкою окремо; підмет не повторюй.'],
                ['translation','Жодна з цих двох відповідей не пояснює затримку.','Neither of|these two answers|explains the delay.','Збережи визначену пару відповідей і заперечення для кожної; використай формальну однину.',['Neither of these two responses explains the delay.']],
                ['translation','Усі відвідувачі, які зареєструвалися вчора, отримали перепустку.','All the visitors|who registered yesterday|received a pass.','Охопи всю визначену групу вчорашньої реєстрації, а не всіх відвідувачів узагалі.',['All the visitors that registered yesterday received a pass.','All of the visitors who registered yesterday received a pass.','All of the visitors that registered yesterday received a pass.','All the visitors who registered yesterday got a pass.','All the visitors that registered yesterday got a pass.','All of the visitors who registered yesterday got a pass.','All of the visitors that registered yesterday got a pass.']],
            ],
        ];
    }
}
