<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        // Defaults if none
        if (!isset($settings['app_name'])) {
            $settings['app_name'] = 'SI - JEMAAH';
        }
        if (!isset($settings['app_logo'])) {
            $settings['app_logo'] = null; // null means no custom logo
        }

        return Inertia::render('Settings/Index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:50',
            'app_logo' => 'nullable|image|max:2048', // 2MB max
        ]);

        Setting::updateOrCreate(
            ['key' => 'app_name'],
            ['value' => $request->app_name]
        );

        if ($request->hasFile('app_logo')) {
            $file = $request->file('app_logo');
            $path = $file->store('logos', 'public');
            
            // Delete old logo if exist
            $oldLogo = Setting::where('key', 'app_logo')->first();
            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete($oldLogo->value);
            }

            Setting::updateOrCreate(
                ['key' => 'app_logo'],
                ['value' => $path]
            );
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
