<?php

namespace App\Support;

/** Reviewed finite wording for the ten existing thematic questions, not new DB rows. */
final class ArticlesQuantifiersWidgetPlans
{
    public const SNAPSHOTS_SHA='b99388627ad4a426247ec8fb4afbf9b6eaa9b3cfc7cfff3a9efa3c6901ef36d7';
    public static function ids():array
    {
        return ['advanced-article-and-quantifier-nuance'=>[18329,18333,18365,18332,18362],
            'precision-with-articles-and-determiners'=>[18814,18806,18836,18823,18827]];
    }
    public static function hints():array
    {
        return [
            18329=>'Почни з великої кількості незлічуваного часу; передай минулу пасивну дію та марність витрати.',
            18333=>'Жоден із цих кандидатів не був повністю підготовлений; минула форма може узгоджуватися з групою або окремими людьми.',
            18365=>'Почни з цього питання; назви потребу в ретельному аналізі як діяльності, не в одному окремому дослідженні.',
            18332=>'Йдеться про більшість у визначеній групі студентів; передай згоду в минулому, не всіх учасників.',
            18362=>'Передай значний поступ у минулому; назва прогресу незлічувана та не потребує артикля.',
            18814=>'Обидва названі варіанти прийнятні, але вибрати можна один; збережи умовну оцінку прийнятності.',
            18806=>'Підметом є загальна кількість скарг як один показник; передай її зростання простим минулим часом.',
            18836=>'Йдеться про інформацію в інтернеті загалом; узгодь теперішню форму з незлічуваним словом та збережи потребу перевірки.',
            18823=>'Охопи всі наявні свідчення цієї справи; вони підтримують інше пояснення, не обов’язково доводять його.',
            18827=>'Назви конкретне підвищення цін невеликим у минулому; не перетворюй малий розмір зміни на відсутність зміни.',
        ];
    }
    public static function conditions():array
    {
        return [18329=>'Велику кількість часу було витрачено даремно.',18333=>'Ніхто з цих кандидатів не був повністю підготовлений.',
            18365=>'Це питання потребує ретельного аналізу.',18332=>'Більшість студентів цієї групи погодилася.',
            18362=>'Вони досягли значного прогресу.',18814=>'Будь-який із цих двох варіантів був би прийнятним.',
            18806=>'Кількість скарг зросла.',18836=>'Більшість інформації в інтернеті потребує перевірки.',
            18823=>'Усі наявні докази вказують на інше пояснення.',18827=>'Підвищення цін було незначним.'];
    }
    public static function instructions():array
    {
        $out=[];foreach(self::ids()as$ids)foreach($ids as$id)$out[$id]='Склади повне англійське речення; збережи кількість і зміст.';return$out;
    }
    /** Shared DB options retain historical casing; these three exact captions are ordinary nouns/adjectives. */
    public static function tokenLabels(int$id,array$tokens,array$aliases):array
    {
        $labels=ArticlesQuantifiersPracticeSupplement::tokenLabels($tokens,$aliases);
        foreach(([18329=>['Great'=>'great'],18332=>['Students'=>'students'],18362=>['Progress'=>'progress']][$id]??[])as$raw=>$caption){
            $labels[$raw]=$caption.(isset($labels[$raw])?substr($labels[$raw],strlen($raw)):'');
        }return$labels;
    }
    public static function aliases():array
    {
        $out=[
            18329=>['A large amount of time was wasted','A lot of time was wasted','Much time was wasted'],
            18333=>['None of the candidates were fully prepared','None of the candidates was completely prepared','None of the candidates were completely prepared'],
            18332=>[],
            18362=>['They made substantial progress','They made considerable progress'],
            18814=>['Either alternative would be acceptable','Either of the two options would be acceptable','Either of these two options would be acceptable'],
            18806=>['The number of complaints rose','The number of complaints grew'],
            18836=>['Most information online needs checking','Most information online requires verification','Most information online needs verification','Most online information requires checking','Most online information needs checking','Most online information requires verification','Most online information needs verification'],
            18823=>['All the evidence points to another explanation','All of the evidence points to a different explanation','All of the evidence points to another explanation'],
            18827=>['The rise in prices was slight','The increase in prices was small','The rise in prices was small','The price increase was slight','The price increase was small'],
        ];
        foreach(['issue','question','matter']as$noun)foreach(['requires','needs']as$verb)foreach(['careful','thorough']as$adjective){$sentence='This '.$noun.' '.$verb.' '.$adjective.' analysis';if($sentence!=='This issue requires careful analysis')$out[18365][]=$sentence;}
        return$out;
    }
}
