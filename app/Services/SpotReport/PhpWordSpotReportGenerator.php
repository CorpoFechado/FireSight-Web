<?php

namespace App\Services\SpotReport;

use App\Support\SpotReport\SpotReportData;
use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Paper;
use PhpOffice\PhpWord\Style\Tab;

/**
 * Builds the BFP Spot Investigation Report (SIR) as an editable Word document,
 * mirroring form BFP-QSF-FAID-002.
 */
class PhpWordSpotReportGenerator implements SpotReportGenerator
{
    private const FONT = 'Arial';

    private const LABEL_WIDTH_CM = 6.0;

    private const COLON_WIDTH_CM = 0.6;

    private const VALUE_WIDTH_CM = 9.4;

    /** @var array<string, mixed> */
    private const TIGHT = ['spaceBefore' => 0, 'spaceAfter' => 0, 'spacing' => 0];

    public function generate(SpotReportData $data): string
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName(self::FONT);
        $phpWord->setDefaultFontSize(11);
        $phpWord->getDocInfo()
            ->setTitle(config('bfp.spot_report.subject'))
            ->setSubject($data->reference)
            ->setCreator(config('app.name'));

        $paper = new Paper('A4');

        $section = $phpWord->addSection([
            'pageSizeW' => $paper->getWidth(),
            'pageSizeH' => $paper->getHeight(),
            'marginTop' => Converter::cmToTwip(1.5),
            'marginBottom' => Converter::cmToTwip(2.0),
            'marginLeft' => Converter::cmToTwip(2.54),
            'marginRight' => Converter::cmToTwip(2.54),
        ]);

        $this->addHeader($section);
        $this->addMemoBlock($section, $data);
        $this->addFieldBlock($section, $data);
        $this->addDetails($section, $data);
        $this->addSignatories($section);
        $this->addFooter($section);

        $path = tempnam(sys_get_temp_dir(), 'sir_');
        $docxPath = $path.'.docx';
        @unlink($path);

        IOFactory::createWriter($phpWord, 'Word2007')->save($docxPath);

        return $docxPath;
    }

    private function addHeader(Section $section): void
    {
        $logos = config('bfp.spot_report.logos', []);

        $table = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'alignment' => Jc::CENTER,
            'cellMargin' => 0,
        ]);
        $table->addRow(Converter::cmToTwip(2.3));

        foreach (['left', 'center', 'right'] as $position) {
            $cell = $table->addCell(Converter::cmToTwip(2.8), ['valign' => 'center']);
            $logoPath = $logos[$position] ?? null;

            if (is_string($logoPath) && is_file($logoPath)) {
                $cell->addImage($logoPath, [
                    'height' => 62,
                    'alignment' => Jc::CENTER,
                ]);
            } else {
                $cell->addText('', [], self::TIGHT);
            }
        }

        $center = ['alignment' => Jc::CENTER] + self::TIGHT;

        foreach (config('bfp.station.agency_lines', []) as $line) {
            $section->addText($line, ['size' => 9], $center);
        }

        foreach (config('bfp.station.office_lines', []) as $line) {
            $section->addText($line, ['size' => 11, 'bold' => true], $center);
        }

        $section->addText(config('bfp.station.address'), ['size' => 8], $center);
        $section->addText('Tel. Number: '.config('bfp.station.telephone'), ['size' => 8], $center);
        $section->addText('Email: '.config('bfp.station.email'), ['size' => 8], $center);

        $section->addTextBreak(2, ['size' => 8]);
    }

    private function addMemoBlock(Section $section, SpotReportData $data): void
    {
        $section->addText('MEMORANDUM', ['bold' => true], ['spaceAfter' => 240]);

        $table = $this->borderlessTable($section);

        $table->addRow();
        $this->labelCell($table, 'FOR', 2.6);
        $cell = $table->addCell(Converter::cmToTwip(13.4));
        $cell->addText(':', ['bold' => true], self::TIGHT);
        $cell->addText(config('bfp.spot_report.memo_for'), ['bold' => true], ['indentation' => ['left' => Converter::cmToTwip(0.5)], 'spaceAfter' => 200]);

        $table->addRow();
        $this->labelCell($table, 'SUBJECT', 2.6);
        $table->addCell(Converter::cmToTwip(13.4))
            ->addText(': '.config('bfp.spot_report.subject'), ['bold' => true], ['spaceAfter' => 200]);

        $table->addRow();
        $this->labelCell($table, 'DATE', 2.6);
        $table->addCell(Converter::cmToTwip(13.4))
            ->addText(': '.$data->memoDate, ['bold' => true], self::TIGHT);

        // Horizontal rule under the memo heading.
        $section->addText('', [], [
            'borderBottomSize' => 8,
            'borderBottomColor' => '000000',
            'spaceAfter' => 240,
        ]);
    }

    private function addFieldBlock(Section $section, SpotReportData $data): void
    {
        $table = $this->borderlessTable($section);

        $rows = [
            ['DTPO', $data->dtpo],
            ['INVOLVED', $data->involved],
            ['NAME OF ESTABLISHMENT', $data->establishment],
            ['OWNER', $data->owner],
            ['OCCUPANT', $data->occupant],
        ];

        foreach ($rows as [$label, $value]) {
            $this->fieldRow($table, $label, $value);
        }

        // Spacer row before the casualty block, as on the printed form.
        $table->addRow(Converter::cmToTwip(0.4));
        $table->addCell(Converter::cmToTwip(self::LABEL_WIDTH_CM + self::COLON_WIDTH_CM + self::VALUE_WIDTH_CM), ['gridSpan' => 3]);

        $table->addRow();
        $casualtyCell = $table->addCell(Converter::cmToTwip(self::LABEL_WIDTH_CM));
        $casualtyRun = $casualtyCell->addTextRun(self::TIGHT + ['tabs' => [new Tab('left', Converter::cmToTwip(3.0))]]);
        $casualtyRun->addText('CASUALTY', ['bold' => true]);
        $casualtyRun->addText("\tFatality", ['bold' => true]);
        $this->colonCell($table);
        $this->valueCell($table, $data->fatalities);

        $table->addRow();
        $injuredCell = $table->addCell(Converter::cmToTwip(self::LABEL_WIDTH_CM));
        $injuredCell->addText('Injured', ['bold' => true], self::TIGHT + ['indentation' => ['left' => Converter::cmToTwip(3.0)]]);
        $this->colonCell($table);
        $this->valueCell($table, $data->injured);

        $this->fieldRow($table, 'ESTIMATED DAMAGE', $data->estimatedDamage);
        $this->fieldRow($table, 'TIME FIRE STARTED', $data->timeFireStarted);
        $this->fieldRow($table, 'TIME OF FIRE OUT', $data->timeFireOut);
        $this->fieldRow($table, 'ALARM', $data->alarm);

        $section->addTextBreak(1);
    }

    private function addDetails(Section $section, SpotReportData $data): void
    {
        $section->addText('DETAILS OF INVESTIGATION:', ['bold' => true], self::TIGHT);

        foreach ($data->detailParagraphs() as $paragraph) {
            $section->addText($paragraph, [], [
                'alignment' => Jc::BOTH,
                'indentation' => ['left' => Converter::cmToTwip(0.6)],
                'spaceAfter' => 60,
            ]);
        }

        $section->addText('DISPOSITION:', ['bold' => true], ['spaceBefore' => 120] + self::TIGHT);
        $section->addText('-   '.config('bfp.spot_report.disposition'), [], [
            'indentation' => ['left' => Converter::cmToTwip(0.6)],
        ] + self::TIGHT);
    }

    private function addSignatories(Section $section): void
    {
        $signatories = config('bfp.spot_report.signatories');
        $signatureLine = str_repeat('_', 32);

        $section->addTextBreak(2);
        $section->addText($signatureLine, [], ['alignment' => Jc::END] + self::TIGHT);
        $section->addText($signatories['investigator'], ['bold' => true], ['alignment' => Jc::END] + self::TIGHT);

        $indent = ['indentation' => ['left' => Converter::cmToTwip(1.5)]] + self::TIGHT;
        $nameIndent = ['indentation' => ['left' => Converter::cmToTwip(2.5)]] + self::TIGHT;

        $section->addTextBreak(1);
        $section->addText('Noted By:', ['bold' => true], $indent);
        $section->addTextBreak(1);
        $section->addText($signatureLine, [], $nameIndent);
        $section->addText($signatories['noted_by'], [], $nameIndent);

        $section->addTextBreak(1);
        $section->addText('Approved for Submission:', ['bold' => true], $indent);
        $section->addTextBreak(1);
        $section->addText($signatureLine, [], $indent);
        $section->addText($signatories['approved_by'], [], $indent);
    }

    private function addFooter(Section $section): void
    {
        $footer = $section->addFooter();
        $run = $footer->addTextRun(self::TIGHT);
        $font = ['size' => 8];

        $run->addText(config('bfp.spot_report.form_code').'  Page ', $font);
        $run->addField('PAGE', [], [], null, $font);
        $run->addText(' of ', $font);
        $run->addField('NUMPAGES', [], [], null, $font);
    }

    private function borderlessTable(Section $section): Table
    {
        return $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
        ]);
    }

    private function fieldRow(Table $table, string $label, string $value): void
    {
        $table->addRow();
        $this->labelCell($table, $label, self::LABEL_WIDTH_CM);
        $this->colonCell($table);
        $this->valueCell($table, $value);
    }

    private function labelCell(Table $table, string $label, float $widthCm): Cell
    {
        $cell = $table->addCell(Converter::cmToTwip($widthCm));
        $cell->addText($label, ['bold' => true], self::TIGHT);

        return $cell;
    }

    private function colonCell(Table $table): void
    {
        $table->addCell(Converter::cmToTwip(self::COLON_WIDTH_CM))
            ->addText(':', ['bold' => true], self::TIGHT);
    }

    private function valueCell(Table $table, string $value): void
    {
        $table->addCell(Converter::cmToTwip(self::VALUE_WIDTH_CM))
            ->addText($value, [], self::TIGHT);
    }
}
