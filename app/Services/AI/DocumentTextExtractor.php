<?php

namespace App\Services\AI;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class DocumentTextExtractor
{
    private const MAX_CHARS = 12000;

    private const ALLOWED_EXTENSIONS = ['txt', 'md', 'pdf', 'docx'];

    /**
     * @return array{0:string, 1:string} Tuple of [extension, extracted text]
     */
    public function extract(UploadedFile $file): array
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException('Unsupported document type. Use PDF, DOCX, TXT or MD.');
        }

        $path = $file->getRealPath();

        if ($path === false || ! is_readable($path)) {
            throw new RuntimeException('Unable to read uploaded document.');
        }

        $text = match ($extension) {
            'txt', 'md' => $this->readPlainText($path),
            'pdf' => $this->readPdf($path),
            'docx' => $this->readDocx($path),
        };

        $clean = $this->normalize($text);

        if ($clean === '') {
            throw new RuntimeException('The uploaded document is empty or unreadable.');
        }

        return [$extension, $this->truncate($clean)];
    }

    private function readPlainText(string $path): string
    {
        $content = file_get_contents($path);

        return $content === false ? '' : $content;
    }

    private function readPdf(string $path): string
    {
        try {
            $parser = new PdfParser;
            $pdf = $parser->parseFile($path);

            return $pdf->getText();
        } catch (Throwable $e) {
            throw new RuntimeException('Could not parse the PDF file: '.$e->getMessage(), previous: $e);
        }
    }

    private function readDocx(string $path): string
    {
        try {
            $document = WordIOFactory::load($path);
            $buffer = '';

            foreach ($document->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $buffer .= $this->elementToText($element)."\n";
                }
            }

            return $buffer;
        } catch (Throwable $e) {
            throw new RuntimeException('Could not parse the DOCX file: '.$e->getMessage(), previous: $e);
        }
    }

    private function elementToText(object $element): string
    {
        if (method_exists($element, 'getText')) {
            $value = $element->getText();

            if (is_string($value)) {
                return $value;
            }
        }

        if (method_exists($element, 'getElements')) {
            $parts = [];

            foreach ($element->getElements() as $child) {
                $parts[] = $this->elementToText($child);
            }

            return implode(' ', array_filter($parts, fn ($v) => $v !== ''));
        }

        return '';
    }

    private function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function truncate(string $text): string
    {
        if (mb_strlen($text) <= self::MAX_CHARS) {
            return $text;
        }

        return mb_substr($text, 0, self::MAX_CHARS)."\n\n[... document truncated ...]";
    }
}
