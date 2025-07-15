<?php

namespace AndrewAndante\SilverStripePDFParser\Extractor\Tests;

use AndrewAndante\SilverStripePDFParser\Extractor\PDFParserTextExtractor;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor;

class PDFParserTextExtractorTest extends SapphireTest
{

    public function testIsAvailable(): void
    {
        $extractor = new PDFParserTextExtractor();
        $this->assertTrue($extractor->isAvailable());
    }

    public function testSupportsExtension(): void
    {
        $extractor = new PDFParserTextExtractor();
        $this->assertTrue($extractor->supportsExtension('pdf'));
        $this->assertFalse($extractor->supportsExtension('doc'));
    }

    public function testSupportsMime(): void
    {
        $extractor = new PDFParserTextExtractor();
        $this->assertTrue($extractor->supportsMime('application/pdf'));
        $this->assertTrue($extractor->supportsMime('application/x-bzpdf'));
        $this->assertTrue($extractor->supportsMime('application/x-gzpdf'));
        $this->assertTrue($extractor->supportsMime('application/x-pdf'));
        $this->assertFalse($extractor->supportsMime('application/vnd.ms-excel'));
    }

    public function testGetContent(): void
    {
        $extractor = new PDFParserTextExtractor();

        $this->assertStringStartsWith(
            "Sample PDF\nThis is a simple PDF ﬁle. Fun fun fun.\n",
            $extractor->getContent(__DIR__ . '/resources/sample.pdf')
        );

        PDFParserTextExtractor::config()->set('convert_to_single_line', true);
        $this->assertStringStartsWith(
            'Sample PDF This is a simple PDF ﬁle. Fun fun fun.',
            $extractor->getContent(__DIR__ . '/resources/sample.pdf')
        );
    }

    public function testGetExtractorByFile(): void
    {
        $this->assertInstanceOf(PDFParserTextExtractor::class, FileTextExtractor::for_file(__DIR__ . '/resources/sample.pdf'));
    }
}
