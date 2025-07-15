<?php

namespace AndrewAndante\SilverStripePDFParser\Extractor;

use Psr\Log\LoggerInterface;
use SilverStripe\Assets\File;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor;
use Smalot\PdfParser\Parser;
use Throwable;

class PDFParserTextExtractor extends FileTextExtractor
{
    use Extensible;

    /**
     * Number of page to parse. Default to all.
     *
     * @config
     */
    private static ?int $pages_to_parse = null;

    /**
     * Convert into single line and remove extra white spaces.
     *
     * @config
     */
    private static bool $convert_to_single_line = false;

    /**
     * @inheritDoc
     */
    public function isAvailable(): bool
    {
        return class_exists(Parser::class);
    }

    /**
     * @inheritDoc
     */
    public function supportsExtension($extension): bool
    {
        return strtolower($extension) === 'pdf';
    }

    /**
     * @inheritDoc
     */
    public function supportsMime($mime): bool
    {
        return in_array(
            strtolower($mime),
            [
                'application/pdf',
                'application/x-pdf',
                'application/x-bzpdf',
                'application/x-gzpdf'
            ]
        );
    }

    /**
     * Get instance of PDF parser
     */
    protected function getParser(): Parser
    {
        $pdfParser = new Parser();

        $this->extend('onInitParser', $pdfParser);

        return $pdfParser;
    }

    /**
     * @inheritDoc
     */
    public function getContent($file): string
    {
        $pdfParser = $this->getParser();

        try {
            $path = $file instanceof File ? self::getPathFromFile($file) : $file;
            $text = $pdfParser->parseFile($path)->getText(self::config()->get('pages_to_parse'));

            // Remove new lines and spaces
            if (self::config()->get('convert_to_single_line')) {
                $text = str_replace("\n", ' ', $text);
                $text = preg_replace('/\s+/', ' ', $text);
            }

            $this->extend('updateParsedText', $text);
            return $text;
        } catch (Throwable $e) {
            Injector::inst()->get(LoggerInterface::class)->info(
                sprintf(
                    '[PDFParserTextExtractor] Error extracting text from "%s" (message: %s)',
                    $path ?? 'unknown file',
                    $e->getMessage()
                )
            );
        }

        return '';
    }
}
