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
        // Enhanced file validation for security
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            $file = $request->file('file');
            
            // Additional security checks
            if (!$file->isValid()) {
                throw new \Exception('File upload tidak valid');
            }

            // Verify file content is actually CSV (not just extension)
            $fileContent = file_get_contents($file->getPathname());
            if (!$this->isValidCSVContent($fileContent)) {
                throw new \Exception('File content bukan format CSV yang valid');
            }

            // Sanitize filename to prevent directory traversal
            $originalName = $file->getClientOriginalName();
            $sanitizedName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
            $fileName = time() . '_' . $sanitizedName;
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
            // Clean up file on error
            if (isset($filePath)) {
                Storage::delete($filePath);
            }
            
            return back()->withErrors([
                'file' => 'Import gagal: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validate file content to ensure it's proper CSV
     * Supports both old format (lowercase) and new format from Plan.md (uppercase Indonesian)
     */
    private function isValidCSVContent($content)
    {
        // Check if content contains expected CSV structure
        $lines = explode("\n", $content);
        if (count($lines) < 2) {
            return false; // At least header + 1 data row
        }

        // Basic CSV format check
        $header = str_getcsv($lines[0]);
        
        // Map of equivalent headers (old format => new format alternatives)
        $headerMappings = [
            'nama_lengkap' => ['nama_lengkap', 'nama lengkap'],
            'tgl_lahir' => ['tgl_lahir', 'tanggal lahir', 'tanggal_lahir'],
            'jenis_kelamin' => ['jenis_kelamin', 'jenis kelamin'],
        ];
        
        // Normalize headers: lowercase, trim, replace underscores with spaces
        $headerNormalized = array_map(function($h) {
            return strtolower(trim(str_replace('_', ' ', $h)));
        }, $header);
        
        // Check if header contains at least one variation of each required field
        foreach ($headerMappings as $field => $variations) {
            $found = false;
            foreach ($variations as $variation) {
                $variationNormalized = strtolower(str_replace('_', ' ', $variation));
                if (in_array($variationNormalized, $headerNormalized)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }

        // Check if data rows have correct column count
        $expectedColumns = count($header);
        for ($i = 1; $i < min(6, count($lines)); $i++) { // Check first 5 data rows
            if (!empty(trim($lines[$i]))) {
                $dataRow = str_getcsv($lines[$i]);
                if (count($dataRow) !== $expectedColumns) {
                    return false;
                }
            }
        }

        return true;
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
        $filters = $request->only([
            'desa_id', 'kelompok_id', 'jenis_kelamin', 'status_pernikahan',
            'kategori_usia', 'paket', 'kategori_sodaqoh', 'status_mubaligh', 'search',
        ]);

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