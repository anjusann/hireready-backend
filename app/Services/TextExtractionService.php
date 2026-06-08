<?php

namespace App\Services;

use App\Enums\ResumeFileType;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;
use Throwable;

class TextExtractionService
{
    public function __construct(protected ?Parser $pdfParser = null) {}

    public function extract(string $filePath, ResumeFileType $fileType): string
    {
        return match ($fileType) {
            ResumeFileType::Pdf => $this->extractFromPdf($filePath),
            ResumeFileType::Docx => $this->extractFromDocx($filePath),
        };
    }

    public function extractFromPdf(string $filePath): string
    {
        $pdf = $this->pdfParser()->parseFile($filePath);

        return trim($pdf->getText());
    }

    public function extractFromDocx(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= $this->extractTextFromElement($element);
            }

            $text .= "\n";
        }

        return trim($text);
    }

    public function tryExtract(string $filePath, ResumeFileType $fileType): ?string
    {
        try {
            return $this->extract($filePath, $fileType);
        } catch (Throwable $exception) {
            Log::error('Resume text extraction failed', [
                'file_path' => $filePath,
                'file_type' => $fileType->value,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function extractTextFromElement(AbstractElement $element): string
    {
        if ($element instanceof Text) {
            return $element->getText();
        }

        if ($element instanceof TextRun) {
            return collect($element->getElements())
                ->map(fn (AbstractElement $child) => $this->extractTextFromElement($child))
                ->implode('');
        }

        if (method_exists($element, 'getElements')) {
            return collect($element->getElements())
                ->map(fn (AbstractElement $child) => $this->extractTextFromElement($child))
                ->implode("\n");
        }

        if (method_exists($element, 'getRows')) {
            return collect($element->getRows())
                ->map(function ($row) {
                    if (! method_exists($row, 'getCells')) {
                        return '';
                    }

                    return collect($row->getCells())
                        ->map(fn ($cell) => method_exists($cell, 'getElements')
                            ? collect($cell->getElements())
                                ->map(fn (AbstractElement $child) => $this->extractTextFromElement($child))
                                ->implode(' ')
                            : '')
                        ->implode(' ');
                })
                ->implode("\n");
        }

        return '';
    }

    protected function pdfParser(): Parser
    {
        return $this->pdfParser ??= new Parser;
    }
}
