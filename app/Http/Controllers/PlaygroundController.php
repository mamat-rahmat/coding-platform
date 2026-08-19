<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PlaygroundController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Playground/Index', [
            'starterCode' => <<<'PYTHON'
# Python Playground
# Tulis kode Python dan tekan Run

print("Hello, World!")

for i in range(5):
    print(f"Item {i}")
PYTHON,
        ]);
    }
}
