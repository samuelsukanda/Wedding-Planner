<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Gift;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function index(Request $request)
    {
        $wedding = Wedding::current();
        $query = $wedding->gifts();

        if ($request->filled('type')) {
            $query->where('gift_type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('giver_name', 'like', '%' . $request->search . '%');
        }

        $gifts = $query->latest()->get();

        $giftTypes = DropdownOption::getOptions('gift_type', ['Cash', 'Barang']);
        $totalCash = $wedding->gifts()->where('gift_type', 'Cash')->sum('nominal');
        $totalGoodsCount = $wedding->gifts()->where('gift_type', 'Barang')->count();
        $thankYouSentCount = $wedding->gifts()->where('is_thank_you_sent', true)->count();

        return view('gifts.index', compact('wedding', 'gifts', 'giftTypes', 'totalCash', 'totalGoodsCount', 'thankYouSentCount'));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();
        $validated = $request->validate([
            'giver_name' => 'required|string|max:255',
            'gift_type' => 'required|string',
            'nominal' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_thank_you_sent' => 'nullable|boolean',
        ]);

        $validated['is_thank_you_sent'] = $request->has('is_thank_you_sent');

        $wedding->gifts()->create($validated);

        return redirect()->route('gifts.index')->with('success', 'Catatan hadiah berhasil disimpan!');
    }

    public function toggleThankYou(Gift $gift)
    {
        $gift->is_thank_you_sent = !$gift->is_thank_you_sent;
        $gift->save();

        return redirect()->back()->with('success', 'Status kartu ucapan diperbarui!');
    }

    public function destroy(Gift $gift)
    {
        $gift->delete();
        return redirect()->route('gifts.index')->with('success', 'Catatan hadiah dihapus!');
    }

    public function exportCsv()
    {
        $wedding = Wedding::current();
        $gifts = $wedding->gifts;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Hadiah');

        // Title Block
        $sheet->setCellValue('A1', 'LAPORAN HADIAH & UCAPAN PERNIKAHAN');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('5F6F5B'));

        $totalCash = $gifts->where('gift_type', 'Cash')->sum('nominal');
        $totalGoods = $gifts->where('gift_type', 'Barang')->count();
        $sheet->setCellValue('A2', $wedding->couple_name . ' — Total Uang Tunai: Rp ' . number_format($totalCash, 0, ',', '.') . ' | Total Hadiah Barang: ' . $totalGoods . ' Item');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('706F6C'));

        // Table Header
        $headers = ['No', 'Nama Pemberi', 'Jenis Hadiah', 'Nominal (Rp)', 'Deskripsi / Detail', 'Status Thank You Card'];
        $columnLetter = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnLetter . '4', $header);
            $columnLetter++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '5F6F5B'],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);

        // Data Rows
        $row = 5;
        foreach ($gifts as $index => $g) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $g->giver_name);
            $sheet->setCellValue('C' . $row, $g->gift_type);
            $sheet->setCellValue('D' . $row, $g->nominal ? (float)$g->nominal : 0);
            $sheet->setCellValue('E' . $row, $g->description ?? '—');
            $sheet->setCellValue('F' . $row, $g->is_thank_you_sent ? 'Tersampaikan' : 'Belum');

            $bgColor = ($index % 2 == 0) ? 'FFFFFF' : 'FAF7F2';
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor],
                ],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'E3E3E0']]],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Summary Row
        $sheet->setCellValue("A{$row}", "TOTAL UANG TUNAI");
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("D{$row}", (float)$totalCash);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('Rp #,##0');
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE],
            ],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Auto-fit column widths
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Laporan_Hadiah_Pernikahan.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
