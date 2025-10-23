<?php

namespace App\Http\Controllers;

use App\Rules\Cnpj;
use Illuminate\Http\Request;

class s2200Controller extends Controller
{
    public function generateQuery(Request $request)
    {
        $request->validate([
            'cpfs' => ['nullable'],
            'cnpj' => ['required', new Cnpj],
            'xmls' => ['required', 'array'], 
            'xmls.*' => ['required', 'file'], 
        ]);
    }

    public function generateS2200Query()
    {
        //
    }

    public function generateS2200DependenteQuery()
    {
        //
    }

    public function generateHistoricoQuery()
    {
        //
    }
}
