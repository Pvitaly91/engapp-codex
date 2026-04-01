<?php

namespace Tests\Unit;

use App\Modules\FileManager\Services\FileSystemService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FileSystemServiceTest extends TestCase
{
    private string $relativeBasePath = 'storage/framework/testing/file-manager-service';

    private string $absoluteBasePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->absoluteBasePath = base_path($this->relativeBasePath);

        File::ensureDirectoryExists($this->absoluteBasePath);
        File::cleanDirectory($this->absoluteBasePath);

        config(['file-manager.base_path' => $this->relativeBasePath]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->absoluteBasePath);

        parent::tearDown();
    }

    public function test_it_can_create_update_and_delete_files_and_directories(): void
    {
        $service = new FileSystemService();

        $directoryResult = $service->createDirectory('docs');
        $this->assertTrue($directoryResult['success']);
        $this->assertDirectoryExists($this->absoluteBasePath.'/docs');

        $fileResult = $service->createFile('docs/readme.md', '# Draft');
        $this->assertTrue($fileResult['success']);
        $this->assertFileExists($this->absoluteBasePath.'/docs/readme.md');

        $updateResult = $service->updateFileContent('docs/readme.md', '# Final');
        $this->assertTrue($updateResult['success']);
        $this->assertSame('# Final', File::get($this->absoluteBasePath.'/docs/readme.md'));

        $info = $service->getFileInfo('docs/readme.md');
        $this->assertNotNull($info);
        $this->assertSame('file', $info['type']);

        $deleteDirectoryResult = $service->deletePath('docs');
        $this->assertTrue($deleteDirectoryResult['success']);
        $this->assertDirectoryDoesNotExist($this->absoluteBasePath.'/docs');
    }

    public function test_it_rejects_paths_outside_the_base_path(): void
    {
        $service = new FileSystemService();

        $result = $service->createFile('../outside.txt', 'blocked');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Недійсний шлях', $result['error'] ?? '');
        $this->assertFileDoesNotExist(dirname($this->absoluteBasePath).'/outside.txt');
    }
}
