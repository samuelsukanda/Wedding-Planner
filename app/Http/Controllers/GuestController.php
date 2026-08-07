<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Guest;
use App\Models\DropdownOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $guestTitles = DropdownOption::getOptions('guest_title', ['Bpk.', 'Ibu.', 'Mr.', 'Mrs.', 'Ms.', 'Sdr.']);

        $categoryPax = DropdownOption::where('group_key', 'guest_category')
            ->whereNotNull('meta_value')
            ->pluck('meta_value', 'option_value')
            ->map(function ($v) { return (int) $v; })
            ->toArray();

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
            'guestTitles',
            'categoryPax',
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
            'title' => 'nullable|string|max:50',
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
            'title' => 'nullable|string|max:50',
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

    public function sendWa(Guest $guest)
    {
        $phone = preg_replace('/[^0-9]/', '', $guest->phone);
        if (!$phone) {
            return redirect()->route('guests.index')->with('error', 'Nomor telepon tamu tidak tersedia.');
        }

        $waUrl = $this->buildWaUrl($guest);

        return redirect()->away($waUrl);
    }

    public function sendWaAll()
    {
        $wedding = Wedding::first();
        $guests = $wedding->guests()->whereNotNull('phone')->where('phone', '!=', '')->get();

        $links = $guests->map(function ($g) {
            $phone = preg_replace('/[^0-9]/', '', $g->phone);
            if (!$phone) return null;

            $namaLengkap = $g->title ? $g->title . ' ' . $g->name : $g->name;
            $waUrl = $this->buildWaUrl($g);

            $phoneDisplay = $phone;
            if (str_starts_with($phone, '62') && strlen($phone) > 2) {
                $phoneDisplay = '0' . substr($phone, 2);
            }

            return (object) [
                'id' => $g->id,
                'nama' => $namaLengkap,
                'phone' => $phone,
                'phone_display' => $phoneDisplay,
                'wa_sent' => (bool) $g->wa_sent,
                'url' => $waUrl,
            ];
        })->filter();

        return view('guests.wa-all', compact('links'));
    }

    public function toggleWaSent(Guest $guest)
    {
        $guest->update(['wa_sent' => !$guest->wa_sent]);

        $status = $guest->wa_sent ? 'Undangan WA ditandai terkirim.' : 'Undangan WA ditandai belum terkirim.';
        return redirect()->back()->with('success', $status);
    }
    private function buildWaUrl(Guest $guest): string
    {
        $wedding = Wedding::first();
        $namaLengkap = $guest->title ? $guest->title . ' ' . $guest->name : $guest->name;

        $bride = $wedding->bride_name;
        $groom = $wedding->groom_name;
        $date = $wedding->wedding_date ? $wedding->wedding_date->format('d F Y') : '—';
        $time = '17:00';
        $location = $wedding->location ?: 'Bandung';
        $link = 'weddingplanner.web.id';

        $msg = "Kepada Yth. {$namaLengkap},\n\n";
        $msg .= "Dengan penuh sukacita, kami mengundang {$namaLengkap} untuk hadir dan turut merayakan hari bahagia kami dalam acara pernikahan:\n\n";
        $msg .= "*{$bride} & {$groom}*\n\n";
        $msg .= "Tanggal: *{$date}*\n";
        $msg .= "Waktu: *{$time}*\n";
        $msg .= "Tempat: *{$location}*\n\n";
        $msg .= "Merupakan suatu kebahagiaan dan kehormatan bagi kami apabila {$namaLengkap} berkenan hadir serta memberikan doa dan ucapan selamat untuk mengiringi langkah baru kami.\n\n";
        $msg .= "Informasi lengkap mengenai acara dapat dilihat melalui undangan digital berikut:\n\n";
        $msg .= "*{$link}*\n\n";
        $msg .= "Atas kehadiran, doa, dan perhatian yang diberikan, kami mengucapkan terima kasih.\n\n";
        $msg .= "Hormat kami,\n\n";
        $msg .= "*{$bride} & {$groom}*";

        $phone = preg_replace('/[^0-9]/', '', $guest->phone);
        $phoneNum = $phone[0] === '0' ? substr($phone, 1) : $phone;

        return 'https://api.whatsapp.com/send?phone=62' . $phoneNum . '&text=' . rawurlencode($msg);
    }

    public function exportLabels()
    {
        $wedding = Wedding::first();
        $guests = $wedding->guests;

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="UTF-8"><title>Label Undangan</title>';
        $html .= '<!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom></w:WordDocument></xml><![endif]-->';
        $html .= '<!--[if gte mso 9]><xml><w:PageSetup><w:PageWidth>29.7cm</w:PageWidth><w:PageHeight>21cm</w:PageHeight><w:Orient w:val="landscape"/></w:PageSetup></xml><![endif]-->';
        $html .= '<style>';
        $html .= '@page { size: 29.7cm 21cm landscape; mso-page-orientation: landscape; margin: 0.5cm; }';
        $html .= 'body { margin: 0; padding: 0; }';
        $html .= 'table { border-collapse: collapse; width: 19.2cm; margin: 0 auto; }';
        $html .= 'td { width: 6.4cm; height: 3.2cm; padding: 0.2cm 0.3cm; vertical-align: middle; text-align: center; font-family: Arial, sans-serif; font-size: 9pt; border: 1px dashed #ccc; }';
        $html .= '.label-name { font-weight: bold; font-size: 10pt; margin-bottom: 4px; }';
        $html .= '.label-place { font-size: 10pt; font-weight: bold; }';
        $html .= '</style>';
        $html .= '</head><body>';
        $html .= '<table>';

        $count = 0;
        $cols = 3;
        foreach ($guests as $g) {
            if ($count % $cols == 0) {
                if ($count > 0) $html .= '</tr>';
                $html .= '<tr>';
            }
            $label = $g->title ? $g->title . ' ' . $g->name : $g->name;
            $html .= '<td>';
            $html .= '<div class="label-name">' . e($label) . '</div>';
            $html .= '<div style="height:6px"></div>';
            $html .= '<div class="label-place">Di</div>';
            $html .= '<div style="height:2px"></div>';
            $html .= '<div class="label-place">Tempat</div>';
            $html .= '</td>';
            $count++;
        }

        // Fill remaining cells in last row
        $remainder = $count % $cols;
        if ($remainder > 0) {
            for ($i = $remainder; $i < $cols; $i++) {
                $html .= '<td>&nbsp;</td>';
            }
        }
        if ($count > 0) $html .= '</tr>';

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="Label_Undangan.doc"',
        ]);
    }
}
