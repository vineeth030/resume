<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\ResumeReader;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadResumeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Facades\Ai;
use Spatie\PdfToText\Pdf;

class ResumeController extends Controller
{
    /**
     * Handle the incoming resume upload request.
     */
    public function upload(UploadResumeRequest $request): JsonResponse
    {
        $file = $request->file('resume');

        // Store the file temporarily
        $path = $file->store('resumes', 'local');
        $fullPath = Storage::disk('local')->path($path);

        try {
            // Extract text from PDF
            $extractedText = Pdf::getText($fullPath);
            $trimmedText = trim($extractedText);

            // Structure the resume data using Claude AI
            //$structuredData = $this->structureResumeData($trimmedText);


            $agent = new ResumeReader();
            $response = $agent->prompt($trimmedText);
            //$data = json_decode($response->text, true);

            return response()->json([
                'success' => true,
                'message' => 'Resume processed successfully',
                'data' => [
                    // 'filename' => $file->getClientOriginalName(),
                    // 'size' => $file->getSize(),
                    // 'mime_type' => $file->getMimeType(),
                    // 'stored_path' => $path,
                    // 'extracted_text' => $trimmedText,
                    'structured_data' => $response,
                ]
            ], 200);

        } catch (\Exception $e) {
            // Clean up the stored file if extraction fails
            Storage::disk('local')->delete($path);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process resume',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    
}
