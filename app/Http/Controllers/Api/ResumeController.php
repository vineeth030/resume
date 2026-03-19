<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\ResumeReader;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadResumeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;

class ResumeController extends Controller
{
    /**
     * Handle the incoming resume upload request.
     */
    public function upload(UploadResumeRequest $request): JsonResponse
    {
        $file = $request->file('resume');

        $path = $file->store('resumes', 'local');
        $fullPath = Storage::disk('local')->path($path);

        try {
            $extractedText = Pdf::getText($fullPath);
            $trimmedText = trim($extractedText);

            $agent = new ResumeReader();
            $response = $agent->prompt($trimmedText);

            return response()->json([
                'success' => true,
                'message' => 'Resume processed successfully',
                'data' => [
                    'structured_data' => $response,
                ]
            ], 200);

        } catch (\Exception $e) {
            
            Storage::disk('local')->delete($path);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process resume',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    
}
