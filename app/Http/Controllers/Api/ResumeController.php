<?php

namespace App\Http\Controllers\Api;

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
            $structuredData = $this->structureResumeData($trimmedText);

            return response()->json([
                'success' => true,
                'message' => 'Resume processed successfully',
                'data' => [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'stored_path' => $path,
                    'extracted_text' => $trimmedText,
                    'structured_data' => $structuredData,
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

    /**
     * Structure resume data using Claude AI.
     */
    private function structureResumeData(string $resumeText): array
    {
        $prompt = <<<PROMPT
You are a resume parser. Extract information from the following resume text and return ONLY valid JSON with no markdown formatting, no code blocks, and no additional text.

The JSON must follow this exact structure:
{
  "name": "string or null",
  "email": "string or null",
  "phone": "string or null",
  "skills": ["skill1", "skill2"],
  "education": [
    {
      "degree": "string",
      "institution": "string",
      "year": "string or null"
    }
  ],
  "experience": [
    {
      "title": "string",
      "company": "string",
      "duration": "string or null",
      "description": "string or null"
    }
  ]
}

Rules:
- Extract only factual information present in the resume
- If information is not found, use null for strings or empty arrays []
- Do not invent or assume information
- Return pure JSON only, no markdown formatting
- Ensure all JSON is properly escaped and valid

Resume text:
{$resumeText}
PROMPT;

        $response = Ai::chat()
            ->model('claude-3-5-sonnet-20241022')
            ->text($prompt);

        // Clean and parse the response
        $cleanedResponse = $this->cleanJsonResponse($response);

        $structuredData = json_decode($cleanedResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse AI response as JSON: ' . json_last_error_msg());
        }

        return $structuredData;
    }

    /**
     * Clean AI response to ensure it's valid JSON.
     */
    private function cleanJsonResponse(string $response): string
    {
        // Remove markdown code blocks if present
        $response = preg_replace('/```json\s*/', '', $response);
        $response = preg_replace('/```\s*/', '', $response);

        // Trim whitespace
        return trim($response);
    }
}
