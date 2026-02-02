<?php

namespace App\Http\Controllers;

use App\Services\JamaahCSVImportService;
use App\Services\JamaahCSVExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index()
    {
        return inertia('Import/Index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('imports', $fileName);

            $fullPath = Storage::disk('local')->path($filePath);
            $importService = new JamaahCSVImportService();
            $result = $importService->import($fullPath);

            Storage::delete($filePath);

            return back()->with([
                'success' => 'Import berhasil!',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'file' => 'Import gagal: ' . $e->getMessage()
            ]);
        }
    }

    public function downloadTemplate()
    {
        $importService = new JamaahCSVImportService();
        $template = $importService->getCSVTemplate();

        $fileName = 'template_import_jamaah.csv';
        $content = implode("\n", $template);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['desa_id', 'kelompok_id', 'jenis_kelamin', 'status_pernikahan']);

        try {
            $exportService = new JamaahCSVExportService($filters);
            $jamaahs = $exportService->export();
            $csvContent = $exportService->mapToCSV($jamaahs);
            $fileName = $exportService->generateFilename();

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        } catch (\Exception $e) {
            return back()->withErrors([
                'export' => 'Export gagal: ' . $e->getMessage()
            ]);
        }
    }
}