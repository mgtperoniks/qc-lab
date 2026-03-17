<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use Illuminate\Http\Request;

class MillCertificateController extends Controller
{
    public function index()
    {
        return view('reports.mill_certificate');
    }

    public function generate(Request $request)
    {
        $heatsStr = $request->input('heats', '');
        
        // Split strictly by newline to support Excel row-by-row pasting
        // Filter out empty lines but keep the relative order of non-empty lines
        $heatList = collect(preg_split('/\r?\n/', $heatsStr))
            ->map(fn($h) => trim($h))
            ->filter(fn($h) => !empty($h))
            ->values();

        if ($heatList->isEmpty()) {
            return response()->json(['data' => []]);
        }

        // Fetch valid samples
        $samples = Sample::with(['spectroResult', 'tensileTest', 'hardnessTest'])
            ->whereIn('heat_no', $heatList->unique())
            ->get()
            ->keyBy('heat_no');
            
        // Map back to the original input list to maintain sequence
        $data = $heatList->map(function ($heatNo) use ($samples) {
            $s = $samples->get($heatNo);

            if (!$s) {
                // Placeholder for 'Not Found' but maintains sequence
                return [
                    'heat_no' => $heatNo,
                    'found'   => false,
                    'chem'    => array_fill_keys(['c','si', 'mn', 'p', 's', 'cr', 'ni', 'mo', 'fe'], '-'),
                    'mech'    => array_fill_keys(['ts', 'ys', 'el', 'hb'], '-'),
                    'copy_string' => implode("\t", array_fill(0, 13, '')) // 9 chem + 4 mech = 13 empty tabs
                ];
            }

            $chem = $s->spectroResult;
            $tensile = $s->tensileTest;
            $hardness = $s->hardnessTest;

            $c  = $chem->c  ?? 0;
            $si = $chem->si ?? 0;
            $mn = $chem->mn ?? 0;
            $p  = $chem->p  ?? 0;
            $s_val = $chem->s ?? 0;
            $cr = $chem->cr ?? 0;
            $ni = $chem->ni ?? 0;
            $mo = $chem->mo ?? 0;

            // 304 detection: starts with A or LA
            $is304 = preg_match('/^(A|LA)/', $s->heat_no);
            if ($is304) {
                $mo = 0;
            }

            // Fe calculation: 100 - sum of others
            $fe = 100 - ($c + $si + $mn + $p + $s_val + $cr + $ni + $mo);

            return [
                'heat_no' => $s->heat_no,
                'found'   => true,
                'chem' => [
                    'c'  => number_format($c, 4, '.', ''),
                    'si' => number_format($si, 4, '.', ''),
                    'mn' => number_format($mn, 4, '.', ''),
                    'p'  => number_format($p, 4, '.', ''),
                    's'  => number_format($s_val, 4, '.', ''),
                    'cr' => number_format($cr, 4, '.', ''),
                    'ni' => number_format($ni, 4, '.', ''),
                    'mo' => ($is304 || $mo > 0) ? number_format($mo, 4, '.', '') : '-',
                    'fe' => number_format($fe, 4, '.', ''),
                ],
                'mech' => [
                    'ts' => $tensile->uts_mpa ?? '-',
                    'ys' => $tensile->ys_mpa  ?? '-',
                    'el' => $tensile->elong_pct ?? '-',
                    'hb' => $hardness->avg_value ?? '-',
                ],
                // For clipboard: TAB separated (Exclude Heat No)
                'copy_string' => implode("\t", [
                    number_format($c, 4, '.', ''),
                    number_format($si, 4, '.', ''),
                    number_format($mn, 4, '.', ''),
                    number_format($p, 4, '.', ''),
                    number_format($s_val, 4, '.', ''),
                    number_format($cr, 4, '.', ''),
                    number_format($ni, 4, '.', ''),
                    ($is304 || $mo > 0) ? number_format($mo, 4, '.', '') : '0.0000',
                    number_format($fe, 4, '.', ''),
                    $tensile->uts_mpa ?? '0',
                    $tensile->ys_mpa  ?? '0',
                    $tensile->elong_pct ?? '0',
                    $hardness->avg_value ?? '0',
                ])
            ];
        });

        return response()->json(['data' => $data]);
    }
}
