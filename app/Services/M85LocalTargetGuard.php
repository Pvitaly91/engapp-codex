<?php

namespace App\Services;

use App\Support\M85VerbToBe as Package;
use Illuminate\Http\Request;

/** Exact six-owner scope on the unchanged loopback-only local release guard. */
final class M85LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='M85';
    protected const PRIVATE_DIRECTORY='seo-m85-local';
    protected const ENDPOINT='m85-target-';
    public static function allowsProofRequest(Request $request,string $nonce,string $applicationRoot,string $documentRoot):bool
    {
        return !$request->headers->has('origin')&&parent::allowsProofRequest($request,$nonce,$applicationRoot,$documentRoot);
    }
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk','path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
