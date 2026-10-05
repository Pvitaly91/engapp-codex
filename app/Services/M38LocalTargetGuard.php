<?php

namespace App\Services;

class M38LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M38';
    protected const PRIVATE_DIRECTORY = 'seo-m38-local';
    protected const ENDPOINT = 'm38-target-';

    /** Temporary route policy, including Laravel's automatic HEAD alias; SELECT must follow this check. */
    public static function allowsProofRequest(\Illuminate\Http\Request $request, string $nonce, string $applicationRoot, string $documentRoot): bool
    {
        $normal = static fn (string $path): string => strtolower(str_replace('\\', '/', (string) realpath($path)));
        foreach ($request->headers->keys() as $header) {
            if ($header === 'forwarded' || str_starts_with($header, 'x-forwarded-')
                || in_array($header, ['x-real-ip', 'via', 'authorization', 'cookie'], true)) {
                return false;
            }
        }
        return preg_match('/^[a-f0-9]{32}$/D', $nonce) === 1
            && $request->getMethod() === 'GET' && $request->server('REQUEST_METHOD') === 'GET'
            && in_array($request->server('HTTP_HOST'), ['gramlyze.loc', 'gramlyze.loc:80'], true)
            && $request->getHost() === 'gramlyze.loc' && $request->getScheme() === 'http' && $request->getPort() === 80
            && in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)
            && $request->path() === 'api/_local/m38-target-'.$nonce
            && $normal($applicationRoot) === $normal(self::ROOT)
            && $normal($documentRoot) === $normal(self::ROOT.'/public');
    }

    /** Safe public identity: never serialize connection configuration or credentials. */
    public static function publicIdentity(\Illuminate\Database\Connection $db, string $documentRoot): array
    {
        $actual=(array)$db->selectOne('SELECT DATABASE() AS db, @@port AS port');
        return ['environment'=>app()->environment(),
            'site_mode'=>app(\App\Support\SiteMode::class)->forHost('gramlyze.loc'),
            'application_root'=>strtolower(str_replace('\\','/',(string)realpath(base_path()))),
            'document_root'=>strtolower(str_replace('\\','/',(string)realpath($documentRoot))),
            'db_driver'=>$db->getDriverName(),'db_host'=>$db->getConfig('host'),
            'db_port'=>(int)$actual['port'],'database'=>$actual['db']];
    }

    /** Explicit temporary GET-only Laravel proof; removed after apply/no-op. */
    protected function webProof(string $nonce): array
    {
        $curl=curl_init('http://gramlyze.loc/api/_local/m38-target-'.$nonce);
        curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,
            CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>15,CURLOPT_PROXY=>'',
            CURLOPT_RESOLVE=>['gramlyze.loc:80:127.0.0.1'],CURLOPT_HTTPHEADER=>['Accept: application/json']]);
        $body=curl_exec($curl); $status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE); $ip=curl_getinfo($curl,CURLINFO_PRIMARY_IP);
        if ($body===false || $status!==200 || $ip!=='127.0.0.1') { throw new \RuntimeException('M38 live loopback proof unavailable; no writes.'); }
        $web=json_decode($body,true,flags:JSON_THROW_ON_ERROR);
        if (($web['runtime']??null)!==self::publicIdentity(\Illuminate\Support\Facades\DB::connection(),self::ROOT.'/public')
            || !is_array($web['proof']??null)) {
            throw new \RuntimeException('M38 safe runtime identity differs; no writes.');
        }
        return $web['proof'];
    }
}
