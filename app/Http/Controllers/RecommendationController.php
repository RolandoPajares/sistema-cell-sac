<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecommendationRequest;
use App\Services\RecommendationService;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    public function __construct(private readonly RecommendationService $recommendationService) {}

    public function index(RecommendationRequest $request): View
    {
        return view('catalog.recommendations', [
            'products' => $this->recommendationService->recommend($request->validated()),
        ]);
    }
}
