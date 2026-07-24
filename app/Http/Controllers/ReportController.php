<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Budget;
use App\Models\Vendor;
use App\Models\Checklist;
use App\Models\Guest;
use App\Models\Gift;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index()
    {
        $wedding = Wedding::first();

        // Budget summary
        $totalBudget   = $wedding->total_budget;
        $totalActual   = $wedding->budgets()->sum('actual_cost');

        // Budget breakdown by category
        $budgetByCategory = $wedding->budgets()
            ->selectRaw('category, SUM(actual_cost) as total_actual')
            ->groupBy('category')
            ->orderByDesc('total_actual')
            ->get();

        // Checklist summary
        $totalChecklists = $wedding->checklists()->count();
        $checklistDone   = $wedding->checklists()->where('status', 'Done')->count();

        // Vendor summary
        $vendorStatusSummary = $wedding->vendors()
            ->selectRaw('booking_status, COUNT(*) as total')
            ->groupBy('booking_status')
            ->get()
            ->pluck('total', 'booking_status');

        // Guest summary
        $totalGuests  = $wedding->guests()->count();
        $guestAttend  = $wedding->guests()->where('attendance_status', 'Attend')->count();
        $guestPending = $wedding->guests()->where('attendance_status', 'Pending')->count();
        $guestDecline = $wedding->guests()->where('attendance_status', 'Decline')->count();
        $totalPax     = $wedding->guests()->sum('guest_count');

        // Payment summary
        $paymentStatusSummary = $wedding->vendorPayments()
            ->selectRaw('status, SUM(nominal) as total')
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        // Gift summary
        $totalCashGift = $wedding->gifts()->where('gift_type', 'Cash')->sum('nominal');

        return view('reports.index', compact(
            'wedding',
            'totalBudget',
            'totalActual',
            'budgetByCategory',
            'totalChecklists',
            'checklistDone',
            'vendorStatusSummary',
            'totalGuests',
            'guestAttend',
            'guestPending',
            'guestDecline',
            'totalPax',
            'paymentStatusSummary',
            'totalCashGift'
        ));
    }

    public function export()
    {
        $wedding = Wedding::first();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ------------------ SHEET 1: RINGKASAN UTAMA ------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan Laporan');

        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI PERNIKAHAN SAMUEL & ANGELA');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('5F6F5B'));

        $sheet->setCellValue('A2', 'Tanggal Ekspor: ' . date('d F Y, H:i') . ' WIB');
        $sheet->mergeCells('A2:C2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('706F6C'));

        // Section 1: Ringkasan Anggaran
        $sheet->setCellValue('A4', 'I. REKAPITULASI ANGGARAN & DANA');
        $sheet->mergeCells('A4:C4');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A4:C4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('5F6F5B');

        $totalBudget = (float) $wedding->total_budget;
        $totalActual = (float) $wedding->budgets()->sum('actual_cost');
        $sisaBudget = $totalBudget - $totalActual;

        $sheet->setCellValue('A5', 'Total Budget Direncanakan');
        $sheet->setCellValue('B5', $totalBudget);
        $sheet->getStyle('B5')->getNumberFormat()->setFormatCode('Rp #,##0');

        $sheet->setCellValue('A6', 'Total Budget Terealisasi (Terpakai)');
        $sheet->setCellValue('B6', $totalActual);
        $sheet->getStyle('B6')->getNumberFormat()->setFormatCode('Rp #,##0');

        $sheet->setCellValue('A7', 'Sisa Anggaran / Efisiensi');
        $sheet->setCellValue('B7', $sisaBudget);
        $sheet->getStyle('B7')->getNumberFormat()->setFormatCode('Rp #,##0');

        // Section 2: Ringkasan Tamu
        $sheet->setCellValue('A9', 'II. REKAPITULASI TAMU UNDANGAN');
        $sheet->mergeCells('A9:C9');
        $sheet->getStyle('A9')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A9:C9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('5F6F5B');

        $sheet->setCellValue('A10', 'Total Undangan');
        $sheet->setCellValue('B10', $wedding->guests()->count() . ' Tamu');

        $sheet->setCellValue('A11', 'Jumlah Hadir (Attend)');
        $sheet->setCellValue('B11', $wedding->guests()->where('attendance_status', 'Attend')->count() . ' Tamu');

        $sheet->setCellValue('A12', 'Jumlah Pending');
        $sheet->setCellValue('B12', $wedding->guests()->where('attendance_status', 'Pending')->count() . ' Tamu');

        $sheet->setCellValue('A13', 'Jumlah Decline');
        $sheet->setCellValue('B13', $wedding->guests()->where('attendance_status', 'Decline')->count() . ' Tamu');

        $sheet->setCellValue('A14', 'Total Pax Hadir');
        $sheet->setCellValue('B14', $wedding->guests()->sum('guest_count') . ' Pax');

        // Section 3: Ringkasan Vendor & Payments
        $sheet->setCellValue('A16', 'III. REKAPITULASI STATUS VENDOR');
        $sheet->mergeCells('A16:C16');
        $sheet->getStyle('A16')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A16:C16')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('5F6F5B');

        $r = 17;
        foreach ($wedding->vendors()->selectRaw('booking_status, COUNT(*) as total')->groupBy('booking_status')->get() as $v) {
            $sheet->setCellValue('A' . $r, 'Status: ' . $v->booking_status);
            $sheet->setCellValue('B' . $r, $v->total . ' Vendor');
            $r++;
        }

        $sheet->getStyle('A5:B' . ($r - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        // ------------------ SHEET 2: RINCIAN BUDGET ------------------
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Rincian Budget');
        $sheet2->setCellValue('A1', 'DETAIL RINCIAN ANGGARAN PERNIKAHAN');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('5F6F5B'));

        $headers2 = ['No', 'Kategori', 'Nama Item', 'Budget Direncanakan (Rp)', 'Realisasi (Rp)', 'Selisih (Rp)', 'Catatan'];
        $col2 = 'A';
        foreach ($headers2 as $h) {
            $sheet2->setCellValue($col2 . '3', $h);
            $col2++;
        }
        $sheet2->getStyle('A3:G3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '5F6F5B']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $bRow = 4;
        foreach ($wedding->budgets as $idx => $b) {
            $diff = (float)$b->planned_budget - (float)$b->actual_cost;
            $sheet2->setCellValue('A' . $bRow, $idx + 1);
            $sheet2->setCellValue('B' . $bRow, $b->category);
            $sheet2->setCellValue('C' . $bRow, $b->item_name);
            $sheet2->setCellValue('D' . $bRow, (float)$b->planned_budget);
            $sheet2->setCellValue('E' . $bRow, (float)$b->actual_cost);
            $sheet2->setCellValue('F' . $bRow, $diff);
            $sheet2->setCellValue('G' . $bRow, $b->notes ?? '—');

            $sheet2->getStyle("D{$bRow}:F{$bRow}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $sheet2->getStyle("A{$bRow}:G{$bRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $bRow++;
        }
        foreach (range('A', 'G') as $c) { $sheet2->getColumnDimension($c)->setAutoSize(true); }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Laporan_Pernikahan_Samuel_Angela.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
