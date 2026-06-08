<?php

namespace Tests\Unit\Services;

use App\Enums\ResumeFileType;
use App\Services\TextExtractionService;
use Mockery;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

class TextExtractionServiceTest extends TestCase
{
    private string $docxPath;

    protected function setUp(): void
    {
        parent::setUp();

        $phpWord = new PhpWord;
        $section = $phpWord->addSection();
        $section->addText('CareerAI DOCX Resume Content');

        $this->docxPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'careerai-test-'.uniqid().'.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($this->docxPath);
    }

    protected function tearDown(): void
    {
        if (isset($this->docxPath) && file_exists($this->docxPath)) {
            unlink($this->docxPath);
        }

        Mockery::close();

        parent::tearDown();
    }

    public function test_extract_from_pdf_returns_text(): void
    {
        $document = Mockery::mock(Document::class);
        $document->shouldReceive('getText')->once()->andReturn('CareerAI Test Resume');

        $parser = Mockery::mock(Parser::class);
        $parser->shouldReceive('parseFile')
            ->once()
            ->with('/path/to/resume.pdf')
            ->andReturn($document);

        $service = new TextExtractionService($parser);

        $text = $service->extractFromPdf('/path/to/resume.pdf');

        $this->assertSame('CareerAI Test Resume', $text);
    }

    public function test_extract_from_docx_returns_text(): void
    {
        $service = new TextExtractionService;

        $text = $service->extractFromDocx($this->docxPath);

        $this->assertStringContainsString('CareerAI DOCX Resume Content', $text);
    }

    public function test_extract_delegates_to_pdf_parser(): void
    {
        $document = Mockery::mock(Document::class);
        $document->shouldReceive('getText')->once()->andReturn('PDF content');

        $parser = Mockery::mock(Parser::class);
        $parser->shouldReceive('parseFile')->once()->andReturn($document);

        $service = new TextExtractionService($parser);

        $text = $service->extract('/path/to/resume.pdf', ResumeFileType::Pdf);

        $this->assertSame('PDF content', $text);
    }

    public function test_extract_delegates_to_docx_parser(): void
    {
        $service = new TextExtractionService;

        $text = $service->extract($this->docxPath, ResumeFileType::Docx);

        $this->assertStringContainsString('CareerAI DOCX Resume Content', $text);
    }

    public function test_try_extract_returns_null_on_failure(): void
    {
        $parser = Mockery::mock(Parser::class);
        $parser->shouldReceive('parseFile')->once()->andThrow(new \Exception('Parse failed'));

        $service = new TextExtractionService($parser);

        $result = $service->tryExtract('/non/existent/file.pdf', ResumeFileType::Pdf);

        $this->assertNull($result);
    }
}
