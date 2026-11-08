<?php
declare(strict_types=1);
// Pure canonical data fixture: no Laravel kernel, database, network, sessions or caches.
$root=dirname(__DIR__,2);require $root.'/vendor/autoload.php';
$app=new Illuminate\Foundation\Application($root);$app->instance('config',new Illuminate\Config\Repository(['app'=>['locale'=>'uk']]));
$document=['source'=>App\Support\ModalVerbsPracticeSupplement::load(),'compact'=>App\Support\ModalVerbsPracticeQualityCompact::load()];
echo json_encode($document,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
