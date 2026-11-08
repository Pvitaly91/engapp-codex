<?php
namespace App\Support;

/** Finite reviewed teaching data; original M37 control values remain authoritative. */
final class RelativeClausesNativePlans
{
    public static function retained():array{return['complex-relative-clauses'=>[
        ['They placed|the folder|beside the lamp.|The folder|was damaged.','Пошкоджена папка, не лампа. Вони поклали папку біля лампи. Склади два однозначні речення.','Збережи розташування папки та її пошкодження; не приписуй пошкодження лампі.'],
        ['The workshop|whose three presses|need servicing|has hired Iva,|who maintains them.','Є дві майстерні. Та, якій належать три преси, найняла вже відому Іву. Вона обслуговує ці преси; вони потребують обслуговування.','Збережи власницю трьох пресів та Іву як виконавицю обслуговування; не стверджуй, що роботу завершено.'],
    ]];}
    public static function additions():array
    {
        $medical=[];foreach(['doctor','physician']as$noun)foreach(['research','studies']as$work)foreach(['changed','altered']as$verb){$a='We met a '.$noun.' whose '.$work.' '.$verb.' this method.';if($a!=='We met a doctor whose research changed this method.')$medical[]=$a;}
        $places=['This is the house in which my grandfather grew up.','This is the house where my grandfather grew up.','This is the house that my grandfather grew up in.','This is the house which my grandfather grew up in.','This is the house my grandfather grew up in.'];$house=array_slice($places,1);foreach(['granddad','grandad','grandpa']as$relative)foreach($places as$sentence)$house[]=str_replace('grandfather',$relative,$sentence);
        return ['complex-relative-clauses'=>[
            ['gap','We thanked the engineer {a1} design reduced the noise.','whose','Чий: збережи інженера як автора проєкту; потрібна присвійна відносна форма перед уже наведеним іменником.'],
            ['gap','The colleague to {a1} I forwarded your message is on leave.','whom','Після вже наведеного прийменника використай формальну форму відносного займенника в ролі додатка для людини; підмет не повторюй.'],
            ['translation','Це будинок, у якому виріс мій дідусь.','This is|the house|in which|my grandfather|grew up.','Почни з указування на будинок; збережи мого дідуся та його дитинство саме там.',$house],
            ['translation','Ми зустріли лікарку, чиї дослідження змінили цей метод.','We met|a doctor|whose research|changed this method.','Почни з «ми»; лікарку вводимо вперше. Дослідження належать саме їй та змінили конкретний метод.',$medical],
        ]];
    }
    public static function copy():array{return['complex-relative-clauses'=>[
        'selects'=>[
            ['Поєднай через належність без зміни власника.',[['en','We rented a studio. Its windows face the river.']],["We rented a studio whose windows face the river.","We rented a studio who’s windows face the river.","We rented a studio whose its windows face the river."]],
            ['Винеси правильні прийменники перед відносними словами.',[['en','The conservator who we spoke to recommended a softer brush. The frame which the portrait rests on is wooden.']],["The conservator to whom we spoke…; the frame on which the portrait rests…","The conservator with whom we spoke…; the frame to which the portrait rests…","The conservator to that we spoke…; the frame on that the portrait rests…"]],
        ],
        'choices'=>[
            ['Обери версію про дві фотографії з шести.',[['uk','Лише дві з шести фотографій мають білі рамки; пересунули саме ці дві.']],["The photographs which have white frames were moved. Без ком відбираємо дві з шести.","The photographs, which have white frames, were moved. Коми відбирають дві з шести."]],
            ['Де можна опустити займенник і яку форму вжити у вкладеній частині?', [['en','a. The editor who checked the captions called. b. The captions which we checked were accurate. c. Those captions, which we checked yesterday, were accurate.'],['en','The restorer ____ we believe can repair the frame is away.']],["Опущення лише в b; у вкладеній частині who — підмет can repair.","Опущення в a та c; у вкладеній частині whom через сусіднє we believe."]],
        ],
    ]];}
}
