<?php

namespace App\Http\Controllers\Api;

use Throwable;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\Api\WordFrequencyService;
use App\Http\Requests\Api\WordFrequencyRequest;

class WordFrequencyController extends Controller
{
    use ApiResponseTrait;
    protected $wordFrequencyService;

    public function __construct(WordFrequencyService $service)
    {
        $this->wordFrequencyService = $service;
    }
    
    public function analyzeWordFrequency(Request $request)
    {
        //becuase this is for 1 functionality, I am writing validation logic here, otherwise need to make different request for it
        $rules = [
            'text' => 'required_without:file|string',
            'file' => 'required_without:text|file|mimes:txt,csv,log|max:16000',  // please set here the file size as per your testing for validation and don't forget to change max file upload size from php.ini file
            'top' => 'integer|min:1|max:100',
            'exclude' => 'sometimes|array',
        ];

        // Define custom validation messages
        $messages = [
            'text.required_without' => 'Either text or file must be provided.',
            'file.required_without' => 'Either text or file must be provided.',
            'top.integer' => 'Top must be a positive integer.',
        ];

        // Run the validation
        $validator = Validator::make($request->all(), $rules, $messages);

        // If validation fails, return error response
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            // Retrieve validated data
            $validated = $validator->validated();

            $top = $validated['top'] ?? 10;
            $exclude = $validated['exclude'] ?? [];

            // Perform word frequency analysis based on whether the request has a file or text
            if ($request->hasFile('file')) {
                $result = $this->wordFrequencyService->analyzeFile(
                    $request->file('file'),
                    $top,
                    $exclude
                );
            } else {
                $result = $this->wordFrequencyService->analyzeText(
                    $validated['text'],
                    $top,
                    $exclude
                );
            }

            // Return the success response with the analysis result
            return $this->successResponse($result, 'Word frequency analysed successfully!');

        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $exception) {
            Log::error('File is too large.');
            return $this->errorResponse(
                'File is too large. Maximum upload size exceeded.',
                413
            );
        } catch (\Symfony\Component\ErrorHandler\Error\FatalError $exception) {
            Log::error('Memory limit exceeded in analyzeWordFrequency: ' . $exception->getMessage());
            return $this->errorResponse(
                'Memory limit exceeded. Please reduce the file size or the text content.',
                500
            );
        } catch (Throwable $exception) {
            Log::error('Error in analyzeWordFrequency@WordFrequencyController: '.$exception->getMessage().' on file: '.$exception->getFile().' on line no.: '. $exception->getLine());
            return $this->errorResponse(
                'Something went wrong!',
                500
            );
        }
    }
}
