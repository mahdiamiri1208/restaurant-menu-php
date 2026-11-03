<?php

namespace Tests\Feature;

use App\Support\MenuImageProcessor;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Tests\TestCase;

class MenuImageProcessorTest extends TestCase
{
    private string $webRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webRoot = storage_path('framework/testing-webroot-'.bin2hex(random_bytes(8)));
        File::ensureDirectoryExists($this->webRoot);
        config(['menu.web_root' => $this->webRoot]);

        Storage::fake('tmp-for-tests');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->webRoot);

        parent::tearDown();
    }

    public function test_it_converts_an_image_to_webp_and_deletes_it_from_the_configured_web_root(): void
    {
        $image = imagecreatetruecolor(800, 400);
        self::assertNotFalse($image);

        $background = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $background);
        ob_start();
        imagepng($image);
        $contents = ob_get_clean();
        imagedestroy($image);
        self::assertIsString($contents);

        $upload = $this->temporaryUpload('valid.png', $contents);
        $processor = app(MenuImageProcessor::class);
        $storedPath = $processor->store($upload, 'image_url');
        $absolutePath = $this->webRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $storedPath);
        $imageInfo = getimagesize($absolutePath);

        self::assertIsArray($imageInfo);
        self::assertSame(IMAGETYPE_WEBP, $imageInfo[2]);
        self::assertSame(600, $imageInfo[0]);
        self::assertSame(300, $imageInfo[1]);

        $processor->delete('/'.$storedPath);
        self::assertFileDoesNotExist($absolutePath);
    }

    public function test_it_rejects_non_image_uploads(): void
    {
        $upload = $this->temporaryUpload('invalid.txt', 'not an image');

        $this->expectException(ValidationException::class);
        app(MenuImageProcessor::class)->store($upload, 'image_url');
    }

    private function temporaryUpload(string $filename, string $contents): TemporaryUploadedFile
    {
        Storage::disk('tmp-for-tests')->put('livewire-tmp/'.$filename, $contents);

        return TemporaryUploadedFile::createFromLivewire($filename);
    }
}
