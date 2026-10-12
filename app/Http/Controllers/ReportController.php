<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Budget;
use App\Models\Vendor;
use App\Models\Checklist;
use App\Models\Guest;
use App\Models\Gift;
use App\Models\VendorPayment;
use App\Models\Souvenir;
use App\Models\DocumentRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index()
    {
        $wedding = Wedding::current();

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

        // Savings summary (Tabungan)
        $savingsTarget = (float) $wedding->savingsGoals()->sum('target_amount');
        $savingsBalance = (float) $wedding->savingsGoals()->sum('current_balance');
        $savingsShortfall = max(0, $savingsTarget - $savingsBalance);

        // Proposal summary (Lamaran)
        $proposalEvent = $wedding->proposalEvent;
        $proposalChecklistTotal = $wedding->proposalChecklists()->count();
        $proposalChecklistDone = $wedding->proposalChecklists()->where('status', 'Done')->count();
        $proposalGuestRows = $wedding->proposalGuests()->count();
        $proposalGuestPax = (int) $wedding->proposalGuests()->sum('guest_count');
        $proposalBudgetPlanned = (float) $wedding->proposalBudgets()->sum('planned_budget');
        $proposalBudgetActual = (float) $wedding->proposalBudgets()->sum('actual_cost');

        // Souvenir summary (Seserahan)
        $souvenirTotal = $wedding->souvenirs()->count();
        $souvenirReceived = $wedding->souvenirs()->where('status', Souvenir::STATUS_RECEIVED)->count();
        $souvenirPlanned = (float) $wedding->souvenirs()->sum('planned_price');
        $souvenirActual = (float) $wedding->souvenirs()->where('status', Souvenir::STATUS_RECEIVED)->sum('actual_cost');

        // Document summary (Persyaratan Nikah)
        $documentTotal = $wedding->documentRequirements()->count();
        $documentComplete = $wedding->documentRequirements()->where('status', 'Lengkap')->count();

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
            'totalCashGift',
            'savingsTarget',
            'savingsBalance',
            'savingsShortfall',
            'proposalEvent',
            'proposalChecklistTotal',
            'proposalChecklistDone',
            'proposalGuestRows',
            'proposalGuestPax',
            'proposalBudgetPlanned',
            'proposalBudgetActual',
            'souvenirTotal',
            'souvenirReceived',
            'souvenirPlanned',
            'souvenirActual',
            'documentTotal',
            'documentComplete'
        ));
    }

    public function export()
    {
        $wedding = Wedding::current();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ------------------ SHEET 1: RINGKASAN UTAMA ------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan Laporan');

        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI PERNIKAHAN ' . mb_strtoupper($wedding->couple_name));
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

        // Section 4: Ringkasan Tabungan
        $sheet->setCellValue('A' . $r, 'IV. REKAPITULASI TABUNGAN');
        $sheet->mergeCells('A' . $r . ':C' . $r);
        $sheet->getStyle('A' . $r)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A' . $r . ':C' . $r)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('5F6F5B');
        $r++;

        $savingsTarget = (float) $wedding->savingsGoals()->sum('target_amount');
        $savingsBalance = (float) $wedding->savingsGoals()->sum('current_balance');
        $sheet->setCellValue('A' . $r, 'Total Target Tabungan');
        $sheet->setCellValue('B' . $r, $savingsTarget);
        $sheet->getStyle('B' . $r)->getNumberFormat()->setFormatCode('Rp #,##0');
        $r++;
        $sheet->setCellValue('A' . $r, 'Saldo Terkumpul');
        $sheet->setCellValue('B' . $r, $savingsBalance);
        $sheet->getStyle('B' . $r)->getNumberFormat()->setFormatCode('Rp #,##0');
        $r++;
        $sheet->setCellValue('A' . $r, 'Kekurangan');
        $sheet->setCellValue('B' . $r, max(0, $savingsTarget - $savingsBalance));
        $sheet->getStyle('B' . $r)->getNumberFormat()->setFormatCode('Rp #,##0');
        $r++;

        // Section 5: Ringkasan Lamaran
        $sheet->setCellValue('A' . $r, 'V. REKAPITULASI LAMARAN');
        $sheet->mergeCells('A' . $r . ':C' . $r);
        $sheet->getStyle('A' . $r)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A' . $r . ':C' . $r)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('5F6F5B');
        $r++;

        $proposalEvent = $wedding->proposalEvent;
        $sheet->setCellValue('A' . $r, 'Tanggal Acara');
        $sheet->setCellValue('B' . $r, $proposalEvent?->event_date ? $proposalEvent->event_date->format('d F Y') : '—');
        $r++;
        $sheet->setCellValue('A' . $r, 'Checklist Selesai');
        $sheet->setCellValue('B' . $r, $wedding->proposalChecklists()->where('status', 'Done')->count() . ' dari ' . $wedding->proposalChecklists()->count() . ' item');
        $r++;
        $sheet->setCellValue('A' . $r, 'Tamu Lamaran');
        $sheet->setCellValue('B' . $r, $wedding->proposalGuests()->count() . ' undangan / ' . (int) $wedding->proposalGuests()->sum('guest_count') . ' pax');
        $r++;
        $sheet->setCellValue('A' . $r, 'Budget Lamaran (Rencana)');
        $sheet->setCellValue('B' . $r, (float) $wedding->proposalBudgets()->sum('planned_budget'));
        $sheet->getStyle('B' . $r)->getNumberFormat()->setFormatCode('Rp #,##0');
        $r++;
        $sheet->setCellValue('A' . $r, 'Budget Lamaran (Realisasi)');
        $sheet->setCellValue('B' . $r, (float) $wedding->proposalBudgets()->sum('actual_cost'));
        $sheet->getStyle('B' . $r)->getNumberFormat()->setFormatCode('Rp #,##0');
        $r++;

        // Section 6: Ringkasan Seserahan
        $sheet->setCellValue('A' . $r, 'VI. REKAPITULASI SESERAHAN');
        $sheet->mergeCells('A' . $r . ':C' . $r);
        $sheet->getStyle('A' . $r)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A' . $r . ':C' . $r)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('5F6F5B');
        $r++;

        $sheet->setCellValue('A' . $r, 'Item Diterima');
        $sheet->setCellValue('B' . $r, $wedding->souvenirs()->where('status', Souvenir::STATUS_RECEIVED)->count() . ' dari ' . $wedding->souvenirs()->count() . ' item');
        $r++;
        $sheet->setCellValue('A' . $r, 'Total Nilai Diterima');
        $sheet->setCellValue('B' . $r, (float) $wedding->souvenirs()->where('status', Souvenir::STATUS_RECEIVED)->sum('actual_cost'));
        $sheet->getStyle('B' . $r)->getNumberFormat()->setFormatCode('Rp #,##0');
        $r++;

        // Section 7: Ringkasan Persyaratan Nikah
        $sheet->setCellValue('A' . $r, 'VII. REKAPITULASI PERSYARATAN NIKAH');
        $sheet->mergeCells('A' . $r . ':C' . $r);
        $sheet->getStyle('A' . $r)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A' . $r . ':C' . $r)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('5F6F5B');
        $r++;

        $sheet->setCellValue('A' . $r, 'Dokumen Lengkap');
        $sheet->setCellValue('B' . $r, $wedding->documentRequirements()->where('status', 'Lengkap')->count() . ' dari ' . $wedding->documentRequirements()->count() . ' dokumen');
        $r++;

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

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '5F6F5B']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $thinBorder = function ($sheetX, $range) {
            $sheetX->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        };

        // ------------------ SHEET 3: RINCIAN TABUNGAN ------------------
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Rincian Tabungan');
        $sheet3->setCellValue('A1', 'DETAIL TARGET TABUNGAN');
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('5F6F5B'));

        $headers3 = ['No', 'Nama Target', 'Target (Rp)', 'Saldo Saat Ini (Rp)', 'Kekurangan (Rp)', 'Status'];
        $col3 = 'A';
        foreach ($headers3 as $h) { $sheet3->setCellValue($col3 . '3', $h); $col3++; }
        $sheet3->getStyle('A3:F3')->applyFromArray($headerStyle);

        $sRow = 4;
        foreach ($wedding->savingsGoals as $idx => $g) {
            $sheet3->setCellValue('A' . $sRow, $idx + 1);
            $sheet3->setCellValue('B' . $sRow, $g->name);
            $sheet3->setCellValue('C' . $sRow, (float) $g->target_amount);
            $sheet3->setCellValue('D' . $sRow, (float) $g->current_balance);
            $sheet3->setCellValue('E' . $sRow, max(0, (float) $g->target_amount - (float) $g->current_balance));
            $sheet3->setCellValue('F' . $sRow, $g->status ?? '—');
            $sheet3->getStyle("C{$sRow}:E{$sRow}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $thinBorder($sheet3, "A{$sRow}:F{$sRow}");
            $sRow++;
        }
        foreach (range('A', 'F') as $c) { $sheet3->getColumnDimension($c)->setAutoSize(true); }

        // ------------------ SHEET 4: RINCIAN LAMARAN ------------------
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Rincian Lamaran');
        $sheet4->setCellValue('A1', 'DETAIL CHECKLIST LAMARAN');
        $sheet4->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('5F6F5B'));

        $headers4 = ['No', 'Judul', 'Kategori', 'Deadline', 'Prioritas', 'Status'];
        $col4 = 'A';
        foreach ($headers4 as $h) { $sheet4->setCellValue($col4 . '3', $h); $col4++; }
        $sheet4->getStyle('A3:F3')->applyFromArray($headerStyle);

        $lRow = 4;
        foreach ($wedding->proposalChecklists()->orderBy('deadline', 'asc')->get() as $idx => $c) {
            $sheet4->setCellValue('A' . $lRow, $idx + 1);
            $sheet4->setCellValue('B' . $lRow, $c->title);
            $sheet4->setCellValue('C' . $lRow, $c->category ?? '—');
            $sheet4->setCellValue('D' . $lRow, $c->deadline ? $c->deadline->format('d M Y') : '—');
            $sheet4->setCellValue('E' . $lRow, $c->priority ?? '—');
            $sheet4->setCellValue('F' . $lRow, $c->status ?? '—');
            $thinBorder($sheet4, "A{$lRow}:F{$lRow}");
            $lRow++;
        }
        foreach (range('A', 'F') as $c) { $sheet4->getColumnDimension($c)->setAutoSize(true); }

        // ------------------ SHEET 5: RINCIAN SESERAHAN ------------------
        $sheet5 = $spreadsheet->createSheet();
        $sheet5->setTitle('Rincian Seserahan');
        $sheet5->setCellValue('A1', 'DETAIL SESERAHAN');
        $sheet5->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('5F6F5B'));

        $headers5 = ['No', 'Nama Item', 'Rencana (Rp)', 'Harga Beli (Rp)', 'Status', 'Tgl Diterima'];
        $col5 = 'A';
        foreach ($headers5 as $h) { $sheet5->setCellValue($col5 . '3', $h); $col5++; }
        $sheet5->getStyle('A3:F3')->applyFromArray($headerStyle);

        $svRow = 4;
        foreach ($wedding->souvenirs as $idx => $s) {
            $sheet5->setCellValue('A' . $svRow, $idx + 1);
            $sheet5->setCellValue('B' . $svRow, $s->name);
            $sheet5->setCellValue('C' . $svRow, (float) $s->planned_price);
            $sheet5->setCellValue('D' . $svRow, (float) $s->actual_cost);
            $sheet5->setCellValue('E' . $svRow, $s->status ?? '—');
            $sheet5->setCellValue('F' . $svRow, $s->received_date ? $s->received_date->format('d M Y') : '—');
            $sheet5->getStyle("C{$svRow}:D{$svRow}")->getNumberFormat()->setFormatCode('Rp #,##0');
            $thinBorder($sheet5, "A{$svRow}:F{$svRow}");
            $svRow++;
        }
        foreach (range('A', 'F') as $c) { $sheet5->getColumnDimension($c)->setAutoSize(true); }

        // ------------------ SHEET 6: RINCIAN PERSYARATAN ------------------
        $sheet6 = $spreadsheet->createSheet();
        $sheet6->setTitle('Rincian Persyaratan');
        $sheet6->setCellValue('A1', 'DETAIL PERSYARATAN NIKAH');
        $sheet6->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('5F6F5B'));

        $headers6 = ['No', 'Nama Dokumen', 'PIC', 'Deadline', 'Status'];
        $col6 = 'A';
        foreach ($headers6 as $h) { $sheet6->setCellValue($col6 . '3', $h); $col6++; }
        $sheet6->getStyle('A3:E3')->applyFromArray($headerStyle);

        $dRow = 4;
        foreach ($wedding->documentRequirements as $idx => $doc) {
            $sheet6->setCellValue('A' . $dRow, $idx + 1);
            $sheet6->setCellValue('B' . $dRow, $doc->name);
            $sheet6->setCellValue('C' . $dRow, $doc->pic_name ?? '—');
            $sheet6->setCellValue('D' . $dRow, $doc->deadline ? $doc->deadline->format('d M Y') : '—');
            $sheet6->setCellValue('E' . $dRow, $doc->status ?? '—');
            $thinBorder($sheet6, "A{$dRow}:E{$dRow}");
            $dRow++;
        }
        foreach (range('A', 'E') as $c) { $sheet6->getColumnDimension($c)->setAutoSize(true); }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Laporan_Pernikahan_' . str_replace([' ', '&'], '_', $wedding->couple_name) . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
