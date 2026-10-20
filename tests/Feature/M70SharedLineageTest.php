<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Current additive compatibility; immutable older raw-byte assertions are not rewritten. */
class M70SharedLineageTest extends TestCase
{
    public function test_only_reviewed_m70_hunks_differ_from_the_accepted_base_in_eleven_shared_files(): void
    {
        $path=base_path('tests/Fixtures/m70-owned-source-hunks.json');
        self::assertSame('9ab7e99221f70d0c2ef3e5e41da8c84d81dd2baaafe75f5e86e6db8b8bc1b323',hash_file('sha256',$path));
        $fixture=json_decode(file_get_contents($path),true,flags:JSON_THROW_ON_ERROR);
        self::assertSame('be2b1c2549e70d91cc6f3c47cd7708a68ff7eb71',$fixture['base_sha']);
        self::assertCount(11,$fixture['files']);
        foreach($fixture['files'] as $file){
            $raw=file_get_contents(base_path($file['path']));$copy=str_replace("\r\n","\n",$raw);
            foreach($file['hunks'] as $hunk){
                self::assertSame(1,substr_count($copy,$hunk['after']),$file['path'].' exact finite owned hunk');
                $copy=str_replace($hunk['after'],$hunk['before'],$copy);
            }
            self::assertSame($file['base_lf_sha256'],hash('sha256',$copy),$file['path'].' accepted base with preserved foreign work');
            self::assertSame($raw,file_get_contents(base_path($file['path'])),'No file writes or runtime substitution.');
        }
    }
}
