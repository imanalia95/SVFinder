<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        $this->apiBase = config('services.fastapi.url', 'http://localhost:8000'); 
    }

    // GET - Show the search form
    public function index()
    {
        // Fectch stats from FastApi to display in the sidebar
        try {
            $stats = Http::timeout(5)->get("{$this->apiBase}/api/stats")->json();
        } catch (\Exception $e) {
            $stats = null; // Api not reachable just show the form anyway
        }

        return view('recommend.index', compact('stats'));
    }

    // POST - Submit query, call FastAPI, show results
    public function recommend(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'title'       => 'required|string|min:3|max:1000',
            'description' => 'nullable|string|max:10000',
            'top_n'       => 'nullable|integer|min:1|max:10',
        ]);

        $payload = [
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? '',
            'top_n'       => $validated['top_n'] ?? 3,
        ];

        try {
            // Call FastAPI - long timeout
            $response = Http::timeout(120)
                ->post("{$this->apiBase}/api/recommend", $payload);

            if ($response->failed()) {
                $error = $response->json('detail') ?? 'FastAPI returned an error.';
                return back()
                    ->withInput()
                    ->withErrors(['api' => "API error: {$error}"]);
            }

            $result = $response->json();

            return view('recommend.results', 
            [
                'title'       => $payload['title'],
                'description' => $payload['description'],
                'top_hits'    => $result['candidates'] ?? $result['top_hits'] ?? [],
                'candidates'  => $result['candidates']  ?? [],
                'reasoning'   => $result['reasoning']   ?? '',
                'meta'        => $result['meta']        ?? [],
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('RecommendController error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['api' =>
                    'Could not connect to the API server. '
                    . 'Make sure FastAPI is running: uvicorn main:app --port 8000'
                ]);
        } catch (\Exception $e) {
            Log::error('RecommendController error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['api' => 'Unexpcted error: ' . $e->getMessage()]);              
        }
    }

    // GET - Staff directory
    public function lecturers()
    {
        try {
            $response = Http::timeout(10)->get("{$this->apiBase}/api/lecturers");
            $lecturers = $response->json('lecturers') ?? [];
            $count = $response->json('count') ?? 0;
        } catch (\Exception $e) {
            $lecturers = [];
            $count = 0;
        }

        return view('recommend.lecturers', compact('lecturers', 'count'));
    }

    // GET - API health testing
    public function health()
    {
        try {
            $response = Http::timeout(5)->get("{$this->apiBase}/api/health");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['status' => 'unreachable', 'error' => $e->getMessage()], 503);
        }
    }
    
}