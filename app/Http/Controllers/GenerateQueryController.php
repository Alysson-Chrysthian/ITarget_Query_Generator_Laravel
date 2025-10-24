<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GenerateQueryController extends Controller
{
    public function generateQuery(Request $request)
    {
        $request->validate([
            'eventName' => ['required', Rule::in('S2200', 'S1200', 'S1210')],
        ]);

        $message = '';

        if ($request->eventName == 'S2200') {
            $s2200Controller = new s2200Controller;
            $message = $s2200Controller->generateQuery($request);
        }
        if ($request->eventName == 'S1200') {
            $s1200Controller = new s1200Controller;
            $message = $s1200Controller->generateQuery($request);
        }
        if ($request->eventName == 'S1210') {
            $s1210Controller = new s1210Controller;
            $message = $s1210Controller->generateQuery($request);
        }

        return back()
            ->with([
                'message' => $message,
            ]);
    }
}
