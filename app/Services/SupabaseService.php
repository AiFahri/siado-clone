<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SupabaseService
{
    protected $apiKey;
    protected $projectUrl;
    protected $bucketName;

    public function __construct()
    {
        $this->apiKey = config('services.supabase.api_key');
        $this->projectUrl = config('services.supabase.project_url');
        $this->bucketName = config('services.supabase.bucket_name');
    }

    public function uploadFile($filePath, $fileName)
    {
        $timestamp = time();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $uniqueFileName = $baseName . '_' . $timestamp . '.' . $extension;

        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->attach('file', file_get_contents($filePath), $uniqueFileName)
            ->post("{$this->projectUrl}/storage/v1/object/{$this->bucketName}/{$uniqueFileName}");

        if ($response->failed()) {
            Log::error("Failed to upload file to Supabase", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception("Failed to upload file to Supabase.");
        }

        return "{$this->projectUrl}/storage/v1/object/public/{$this->bucketName}/{$uniqueFileName}";
    }
}
