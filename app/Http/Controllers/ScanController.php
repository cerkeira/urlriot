<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScannerService;

class ScanController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function check(Request $request, ScannerService $scanner)
    {
           $url = trim($request->input('url'));

    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    $request->merge(['url' => $url]);

        $request->validate([
            'url' => ['required', 'url']
        ]);

        $results = $scanner->scan($request->input('url'));

        return view('home', [
            'results' => $results,
            'inputUrl' => $request->url,
        ]);
    }
}
