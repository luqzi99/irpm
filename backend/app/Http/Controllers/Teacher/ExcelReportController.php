<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Evaluation;
use App\Models\Subject;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class ExcelReportController extends Controller
{
    // TP Colors for styling
    private $tpColors = [
        1 => 'FF0000', // Red
        2 => 'FFA500', // Orange
        3 => 'FFFF00', // Yellow
        4 => '90EE90', // Light Green
        5 => '32CD32', // Green
        6 => '008000', // Dark Green
    ];

    /**
     * Download Excel report for a class
     */
    public function download(Request $request, ClassRoom $class)
    {
        // Handle token-based auth for browser download
        $user = $request->user();
        if (!$user && $request->has('token')) {
            $token = PersonalAccessToken::findToken($request->token);
            if ($token) {
                $user = $token->tokenable;
            }
        }

        if (!$user) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 401);
        }

        if ($class->teacher_id !== $user->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::with(['topics.subtopics'])->find($request->subject_id);
        // Note: name is an accessor (decrypted), so we can't ORDER BY in SQL
        // Get students and sort in PHP after decryption
        $students = $class->students->sortBy('name')->values();
        
        // Get all subtopics
        $subtopics = [];
        foreach ($subject->topics as $topic) {
            foreach ($topic->subtopics as $subtopic) {
                $subtopics[] = $subtopic;
            }
        }

        // Get evaluations
        $studentIds = $students->pluck('id');
        $evaluations = Evaluation::where('teacher_id', $user->id)
            ->where('subject_id', $request->subject_id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        // Calculate student data
        $studentsData = [];
        foreach ($students as $student) {
            $studentEvals = $evaluations->get($student->id, collect());
            
            $evalsBySubtopic = [];
            $tpSum = 0;
            $evalCount = 0;
            
            foreach ($studentEvals as $eval) {
                if (!isset($evalsBySubtopic[$eval->subtopic_id]) || 
                    $eval->evaluation_date > $evalsBySubtopic[$eval->subtopic_id]['date']) {
                    if (isset($evalsBySubtopic[$eval->subtopic_id])) {
                        $tpSum -= $evalsBySubtopic[$eval->subtopic_id]['tp'];
                    } else {
                        $evalCount++;
                    }
                    $evalsBySubtopic[$eval->subtopic_id] = [
                        'tp' => $eval->tp,
                        'date' => $eval->evaluation_date,
                    ];
                    $tpSum += $eval->tp;
                }
            }

            $averageTp = $evalCount > 0 ? (int) round($tpSum / $evalCount) : null;

            $studentsData[] = [
                'id' => $student->id,
                'name' => $student->name,
                'evaluations' => $evalsBySubtopic,
                'average_tp' => $averageTp,
            ];
        }

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        
        // Sheet 1: LAPORAN
        $this->createReportSheet($spreadsheet, $class, $subject, $subtopics, $studentsData);
        
        // Sheet 2: ANALISA PBD
        $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex(1);
        $this->createAnalysisSheet($spreadsheet, $class, $subject, $studentsData);

        // Set first sheet as active
        $spreadsheet->setActiveSheetIndex(0);

        // Generate filename
        $filename = sprintf(
            'Laporan_PBD_%s_%s_%s.xlsx',
            str_replace(' ', '_', $class->name),
            str_replace(' ', '_', $subject->name),
            date('Y-m-d')
        );

        // Output to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save('php://output');
        exit;
    }

    /**
     * Create LAPORAN sheet - Student TP matrix
     */
    private function createReportSheet($spreadsheet, $class, $subject, $subtopics, $studentsData)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('LAPORAN');

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        // Title row
        $sheet->setCellValue('A1', 'PELAPORAN PENCAPAIAN MURID');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']],
        ]);

        // Info row
        $sheet->setCellValue('A3', 'KELAS:');
        $sheet->setCellValue('B3', $class->name);
        $sheet->setCellValue('C3', 'MATA PELAJARAN:');
        $sheet->setCellValue('D3', $subject->name);
        $sheet->setCellValue('E3', 'TAHUN:');
        $sheet->setCellValue('F3', date('Y'));

        // Column headers
        $headerRow = 5;
        $sheet->setCellValue('A' . $headerRow, 'BIL');
        $sheet->setCellValue('B' . $headerRow, 'NAMA MURID');
        
        $col = 'C';
        foreach ($subtopics as $subtopic) {
            $sheet->setCellValue($col . $headerRow, $subtopic->code);
            $sheet->getColumnDimension($col)->setWidth(6);
            $col++;
        }
        
        $sheet->setCellValue($col . $headerRow, 'TP PURATA');
        $tpPurataCol = $col;
        $col++;
        $sheet->setCellValue($col . $headerRow, 'ULASAN');
        $lastCol = $col;

        // Style header row
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray($headerStyle);
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension($tpPurataCol)->setWidth(12);
        $sheet->getColumnDimension($lastCol)->setWidth(30);

        // Data rows
        $row = $headerRow + 1;
        $bil = 1;
        foreach ($studentsData as $student) {
            $sheet->setCellValue('A' . $row, $bil);
            $sheet->setCellValue('B' . $row, $student['name']);
            
            $col = 'C';
            foreach ($subtopics as $subtopic) {
                $tp = $student['evaluations'][$subtopic->id]['tp'] ?? null;
                if ($tp !== null) {
                    $sheet->setCellValue($col . $row, $tp);
                    $sheet->getStyle($col . $row)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $this->tpColors[$tp] ?? 'FFFFFF']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
                $col++;
            }
            
            // Average TP
            $avgTp = $student['average_tp'];
            if ($avgTp !== null) {
                $sheet->setCellValue($col . $row, 'TP' . $avgTp);
                $sheet->getStyle($col . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $this->tpColors[$avgTp] ?? 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'font' => ['bold' => true],
                ]);
            }
            $col++;
            
            // Ulasan (comment based on TP)
            $ulasan = $this->getUlasan($avgTp);
            $sheet->setCellValue($col . $row, $ulasan);

            // Add borders
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            $row++;
            $bil++;
        }
    }

    /**
     * Create ANALISA PBD sheet - Summary with charts
     */
    private function createAnalysisSheet($spreadsheet, $class, $subject, $studentsData)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ANALISA');

        // Title
        $sheet->setCellValue('A1', 'PENCAPAIAN PENTAKSIRAN BILIK DARJAH');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EF4444']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info
        $sheet->setCellValue('A3', 'KELAS:');
        $sheet->setCellValue('B3', $class->name);
        $sheet->setCellValue('C3', 'SUBJEK:');
        $sheet->setCellValue('D3', $subject->name);

        // Student list with TP
        $sheet->setCellValue('A5', 'BIL');
        $sheet->setCellValue('B5', 'NAMA MURID');
        $sheet->setCellValue('C5', 'TP');
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $row = 6;
        $bil = 1;
        foreach ($studentsData as $student) {
            $sheet->setCellValue('A' . $row, $bil);
            $sheet->setCellValue('B' . $row, $student['name']);
            $sheet->setCellValue('C' . $row, $student['average_tp'] ?? '-');
            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $row++;
            $bil++;
        }

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(8);

        // Calculate TP distribution
        $tpCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];
        $totalWithTp = 0;
        foreach ($studentsData as $student) {
            if ($student['average_tp'] !== null) {
                $tpCounts[$student['average_tp']]++;
                $totalWithTp++;
            }
        }

        // Summary table
        $tableStartCol = 'E';
        $tableStartRow = 5;
        
        $sheet->setCellValue('E5', 'JUMLAH TP KESELURUHAN');
        $sheet->mergeCells('E5:H5');
        $sheet->getStyle('E5:H5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('E6', 'TP');
        $sheet->setCellValue('F6', 'BIL MURID');
        $sheet->setCellValue('G6', '%');
        $sheet->setCellValue('H6', '');
        $sheet->getStyle('E6:H6')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
        ]);

        $summaryRow = 7;
        $belowMtm = 0;
        $meetMtm = 0;
        
        for ($tp = 1; $tp <= 6; $tp++) {
            $count = $tpCounts[$tp];
            $percent = $totalWithTp > 0 ? round(($count / $totalWithTp) * 100) : 0;
            
            $sheet->setCellValue('E' . $summaryRow, $tp);
            $sheet->setCellValue('F' . $summaryRow, $count);
            $sheet->setCellValue('G' . $summaryRow, $percent . '%');
            
            // Apply TP color
            $sheet->getStyle('E' . $summaryRow)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $this->tpColors[$tp]]],
            ]);

            if ($tp <= 3) {
                $belowMtm += $count;
            } else {
                $meetMtm += $count;
            }

            $summaryRow++;
        }

        // MTM percentages
        $belowMtmPercent = $totalWithTp > 0 ? round(($belowMtm / $totalWithTp) * 100) : 0;
        $meetMtmPercent = $totalWithTp > 0 ? round(($meetMtm / $totalWithTp) * 100) : 0;

        $sheet->setCellValue('I7', '% TP TIDAK MENCAPAI MTM');
        $sheet->setCellValue('J7', $belowMtmPercent . '%');
        $sheet->getStyle('I7:J7')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFB3B3']],
        ]);

        $sheet->setCellValue('I10', '% TP MENCAPAI MTM');
        $sheet->setCellValue('J10', $meetMtmPercent . '%');
        $sheet->getStyle('I10:J10')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '90EE90']],
        ]);

        // Totals
        $sheet->setCellValue('E13', 'JUMLAH MURID');
        $sheet->setCellValue('F13', count($studentsData));
        $sheet->setCellValue('G13', 'TP(PMP)');
        $tpPmp = $totalWithTp > 0 ? (int) round(array_sum(array_map(fn($s) => $s['average_tp'] ?? 0, $studentsData)) / $totalWithTp) : '-';
        $sheet->setCellValue('H13', $tpPmp);

        // Add borders to summary table
        $sheet->getStyle('E5:H13')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Create Pie Chart
        $this->createPieChart($sheet, $tpCounts, $totalWithTp, $subject->name);
        
        // Create Bar Chart
        $this->createBarChart($sheet, $tpCounts, $subject->name);
    }

    /**
     * Create pie chart for TP distribution
     */
    private function createPieChart($sheet, $tpCounts, $total, $subjectName)
    {
        // Data for chart
        $sheet->setCellValue('L5', 'TP');
        $sheet->setCellValue('M5', 'Bil');
        for ($tp = 1; $tp <= 6; $tp++) {
            $sheet->setCellValue('L' . (5 + $tp), 'TP' . $tp);
            $sheet->setCellValue('M' . (5 + $tp), $tpCounts[$tp]);
        }

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'ANALISA!$M$5', null, 1),
        ];
        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'ANALISA!$L$6:$L$11', null, 6),
        ];
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'ANALISA!$M$6:$M$11', null, 6),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_PIECHART,
            null,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $title = new Title('ANALISA PERATUS ' . strtoupper($subjectName));

        $chart = new Chart(
            'pieChart',
            $title,
            $legend,
            $plotArea
        );

        $chart->setTopLeftPosition('E15');
        $chart->setBottomRightPosition('K30');

        $sheet->addChart($chart);
    }

    /**
     * Create bar chart for TP count
     */
    private function createBarChart($sheet, $tpCounts, $subjectName)
    {
        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'ANALISA!$M$5', null, 1),
        ];
        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'ANALISA!$L$6:$L$11', null, 6),
        ];
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'ANALISA!$M$6:$M$11', null, 6),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $title = new Title('ANALISA BILANGAN TP ' . strtoupper($subjectName));

        $chart = new Chart(
            'barChart',
            $title,
            $legend,
            $plotArea
        );

        $chart->setTopLeftPosition('E32');
        $chart->setBottomRightPosition('K47');

        $sheet->addChart($chart);
    }

    /**
     * Get ulasan (comment) based on TP level
     */
    private function getUlasan($tp)
    {
        return match($tp) {
            1 => 'Tahap penguasaan amat lemah, perlu lebih usaha',
            2 => 'Tahap penguasaan lemah, perlu bimbingan',
            3 => 'Tahap penguasaan sederhana, teruskan usaha',
            4 => 'Tahap penguasaan baik, tingkatkan lagi',
            5 => 'Tahap penguasaan sangat baik, cemerlang',
            6 => 'Tahap penguasaan cemerlang, terpuji',
            default => '-',
        };
    }
}
