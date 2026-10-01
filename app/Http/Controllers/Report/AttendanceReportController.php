<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $employees = User::where('role', 'employee')->get();

        $attendances = Attendance::selectRaw("
                user_id,
                COUNT(CASE WHEN status = 'hadir' THEN 1 END) as present,
                COUNT(CASE WHEN status = 'terlambat' THEN 1 END) as late,
                COUNT(CASE WHEN status = 'cuti' THEN 1 END) as cuti,
                COUNT(CASE WHEN status = 'sakit' THEN 1 END) as sick,
                COUNT(CASE WHEN status = 'alpha' THEN 1 END) as absent
            ")
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $summary = $employees->map(function ($employee) use ($attendances) {
            $data = $attendances->get($employee->id);
            return (object) [
                'name' => $employee->name,
                'present' => $data->present ?? 0,
                'late' => $data->late ?? 0,
                'cuti' => $data->cuti ?? 0,
                'sick' => $data->sick ?? 0,
                'absent' => $data->absent ?? 0,
            ];
        });

        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [
            $m => Carbon::create()->month($m)->translatedFormat('F'),
        ]);

        $years = range(Carbon::now()->year - 2, Carbon::now()->year + 1);

        return view('reports.attendance', compact('summary', 'month', 'year', 'months', 'years'));
    }

    public function exportCsv(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $attendances = Attendance::with('user')
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->orderBy('attendance_date', 'asc')
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=reporte_asistencia_{$year}_{$month}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($attendances) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers (BOM para soportar caracteres especiales en Excel si fuera necesario, pero lo dejamos simple)
            fputcsv($file, ['Empleado', 'Fecha', 'Hora de ingreso', 'Hora de salida', 'Estado de asistencia']);
            
            foreach ($attendances as $row) {
                fputcsv($file, [
                    $row->user->name ?? 'Desconocido',
                    $row->attendance_date ? $row->attendance_date->format('Y-m-d') : '-',
                    $row->check_in ? $row->check_in->format('H:i') : '-',
                    $row->check_out ? $row->check_out->format('H:i') : '-',
                    ucfirst($row->status)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}