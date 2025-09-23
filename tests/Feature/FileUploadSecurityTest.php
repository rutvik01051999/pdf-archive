<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;
use App\Rules\SecureFileUpload;
use Illuminate\Support\Facades\Validator;

class FileUploadSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_accepts_valid_image_files()
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);
        
        $fileUploadService = new FileUploadService();
        $result = $fileUploadService->uploadFile($file, 'image', 'test');
        
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertEquals('image/jpeg', $result['mime_type']);
    }

    /** @test */
    public function it_rejects_files_with_php_code()
    {
        $file = UploadedFile::fake()->createWithContent('test.jpg', '<?php echo "hack"; ?>');
        
        $fileUploadService = new FileUploadService();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File contains PHP code which is not allowed');
        
        $fileUploadService->uploadFile($file, 'image', 'test');
    }

    /** @test */
    public function it_rejects_files_with_script_tags()
    {
        $file = UploadedFile::fake()->createWithContent('test.jpg', '<script>alert("hack")</script>');
        
        $fileUploadService = new FileUploadService();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File contains script tags which are not allowed');
        
        $fileUploadService->uploadFile($file, 'image', 'test');
    }

    /** @test */
    public function it_rejects_files_with_double_extensions()
    {
        // Create a file with double extension but valid final extension
        $file = UploadedFile::fake()->createWithContent('test.php.jpg', 'fake image content');
        $file->mimeType = 'image/jpeg'; // Override mime type to pass initial validation
        
        $fileUploadService = new FileUploadService();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File contains suspicious double extension');
        
        $fileUploadService->uploadFile($file, 'image', 'test');
    }

    /** @test */
    public function it_rejects_files_exceeding_size_limit()
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(3000); // 3MB
        
        $fileUploadService = new FileUploadService();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File size exceeds maximum allowed size');
        
        $fileUploadService->uploadFile($file, 'image', 'test');
    }

    /** @test */
    public function it_rejects_invalid_mime_types()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'text/plain');
        
        $fileUploadService = new FileUploadService();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid file type');
        
        $fileUploadService->uploadFile($file, 'image', 'test');
    }

    /** @test */
    public function it_rejects_invalid_file_extensions()
    {
        $file = UploadedFile::fake()->create('test.exe', 100, 'image/jpeg');
        
        $fileUploadService = new FileUploadService();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid file extension');
        
        $fileUploadService->uploadFile($file, 'image', 'test');
    }

    /** @test */
    public function secure_file_upload_validation_rule_works()
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);
        
        $validator = Validator::make(['image' => $file], [
            'image' => [new SecureFileUpload('image')]
        ]);
        
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function secure_file_upload_validation_rule_rejects_malicious_files()
    {
        $file = UploadedFile::fake()->createWithContent('test.jpg', '<?php echo "hack"; ?>');
        
        $validator = Validator::make(['image' => $file], [
            'image' => [new SecureFileUpload('image')]
        ]);
        
        $this->assertFalse($validator->passes());
        $this->assertStringContainsString('PHP code', $validator->errors()->first('image'));
    }

    /** @test */
    public function it_generates_secure_filenames()
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);
        
        $fileUploadService = new FileUploadService();
        $result = $fileUploadService->uploadFile($file, 'image', 'test');
        
        $filename = $result['filename'];
        
        // Should not contain original filename
        $this->assertStringNotContainsString('test', $filename);
        
        // Should contain timestamp and random string
        $this->assertMatchesRegularExpression('/^\d+_[a-zA-Z0-9]+\.jpg$/', $filename);
    }

    /** @test */
    public function it_accepts_valid_video_files()
    {
        $file = UploadedFile::fake()->create('test.mp4', 1000, 'video/mp4');
        
        $fileUploadService = new FileUploadService();
        $result = $fileUploadService->uploadFile($file, 'video', 'test');
        
        $this->assertArrayHasKey('path', $result);
        $this->assertEquals('video/mp4', $result['mime_type']);
    }

    /** @test */
    public function it_accepts_valid_csv_files()
    {
        $file = UploadedFile::fake()->createWithContent('test.csv', 'name,email\nJohn,john@example.com');
        
        $fileUploadService = new FileUploadService();
        $result = $fileUploadService->uploadFile($file, 'document', 'test');
        
        $this->assertArrayHasKey('path', $result);
        $this->assertStringContainsString('csv', $result['filename']);
    }
}
