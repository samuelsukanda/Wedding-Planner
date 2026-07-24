<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Guest;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $wedding = Wedding::first();
        $query = $wedding->guests();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('attendance_status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $guests = $query->latest()->get();

        $categories = DropdownOption::getOptions('guest_category', ['Family', 'Friend', 'Office', 'VIP', 'Neighbor']);
        $statuses = DropdownOption::getOptions('guest_status', ['Pending', 'Attend', 'Decline']);

        $totalGuests = $wedding->guests()->count();
        $totalPax = $wedding->guests()->sum('guest_count');
        $attendCount = $wedding->guests()->where('attendance_status', 'Attend')->count();
        $pendingCount = $wedding->guests()->where('attendance_status', 'Pending')->count();
        $declineCount = $wedding->guests()->where('attendance_status', 'Decline')->count();

        return view('guests.index', compact(
            'wedding',
            'guests',
            'categories',
            'statuses',
            'totalGuests',
            'totalPax',
            'attendCount',
            'pendingCount',
            'declineCount'
        ));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::first();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'category' => 'required|string',
            'attendance_status' => 'required|string',
            'guest_count' => 'required|integer|min:1',
        ]);

        $wedding->guests()->create($validated);

        return redirect()->route('guests.index')->with('success', 'Tamu berhasil ditambahkan!');
    }

    public function update(Request $request, Guest $guest)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'category' => 'required|string',
            'attendance_status' => 'required|string',
            'guest_count' => 'required|integer|min:1',
        ]);

        $guest->update($validated);

        return redirect()->route('guests.index')->with('success', 'Data tamu berhasil diperbarui!');
    }

    public function destroy(Guest $guest)
    {
        $guest->delete();
        return redirect()->route('guests.index')->with('success', 'Tamu berhasil dihapus!');
    }

    public function exportCsv()
    {
        $wedding = Wedding::first();
        $guests = $wedding->guests;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Tamu');

        // Title Block
        $sheet->setCellValue('A1', 'LAPORAN DAFTAR TAMU UNDANGAN PERNIKAHAN');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('5F6F5B'));

        $totalPax = $guests->sum('guest_count');
        $attendCount = $guests->where('attendance_status', 'Attend')->count();
        $sheet->setCellValue('A2', 'Samuel & Angela — Total Undangan: ' . $guests->count() . ' Tamu | Hadir: ' . $attendCount . ' | Total Pax: ' . $totalPax);
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('706F6C'));

        // Table Header
        $headers = ['No', 'Nama Tamu', 'Nomor Telepon', 'Alamat', 'Kategori', 'Status Kehadiran', 'Jumlah Pax'];
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
        $sheet->getStyle('A4:G4')->applyFromArray($headerStyle);

        // Data Rows
        $row = 5;
        foreach ($guests as $index => $g) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $g->name);
            $sheet->setCellValue('C' . $row, $g->phone ?? '—');
            $sheet->setCellValue('D' . $row, $g->address ?? '—');
            $sheet->setCellValue('E' . $row, $g->category);
            $sheet->setCellValue('F' . $row, $g->attendance_status);
            $sheet->setCellValue('G' . $row, $g->guest_count);

            $bgColor = ($index % 2 == 0) ? 'FFFFFF' : 'FAF7F2';
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor],
                ],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'E3E3E0']]],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $row++;
        }

        // Summary Row
        $sheet->setCellValue("A{$row}", "TOTAL PAX");
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("G{$row}", $totalPax);
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE],
            ],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Auto-fit column widths
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Daftar_Tamu_Pernikahan.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
