<?php
namespace App\Http\Controllers;

use App\Models\Sample;
use Illuminate\Http\Request;

class HeatNumberCheckerController extends Controller
{
    /** Show the checker view */
    public function index()
    {
        return view('reports.checker');
    }

    /** 
     * Verify heat numbers against database
     * Stateless: only returns current DB data
     */
    public function verify(Request $request)
    {
        $heats = $request->input('heats', []);
        
        if (empty($heats)) {
            return response()->json(['data' => []]);
        }

        // Fetch valid samples with relationships
        $samples = Sample::with(['spectroResult', 'tensileTest', 'hardnessTest'])
            ->whereIn('heat_no', array_unique($heats))
            ->get()
            ->keyBy('heat_no');

        // Map results
        $results = collect($heats)->map(function ($heatNo) use ($samples) {
            $s = $samples->get($heatNo);

            if (!$s) {
                return [
                    'heat_no' => $heatNo,
                    'found'   => false,
                ];
            }

            $chem = $s->spectroResult;
            $tensile = $s->tensileTest;
            $hardness = $s->hardnessTest;

            // Chemical Map
            $chemData = [
                'c'  => $chem->c  ?? 0,
                'si' => $chem->si ?? 0,
                'mn' => $chem->mn ?? 0,
                'p'  => $chem->p  ?? 0,
                's'  => $chem->s  ?? 0,
                'cr' => $chem->cr ?? 0,
                'ni' => $chem->ni ?? 0,
                'mo' => $chem->mo ?? 0,
            ];

            // 304 detection: starts with A or LA (Copy logic from MillCertificateController)
            if (preg_match('/^(A|LA)/', $s->heat_no)) {
                $chemData['mo'] = 0;
            }

            // Fe calculation (same logic as MillCertificateController)
            $chemData['fe'] = 100 - array_sum($chemData);

            // Mechanical Map
            $mechData = [
                'ts' => $tensile->uts_mpa ?? 0,
                'ys' => $tensile->ys_mpa  ?? 0,
                'el' => $tensile->elong_pct ?? 0,
                'hb' => $hardness->avg_value ?? 0,
            ];

            // Format numbers for comparison
            foreach ($chemData as $key => $val) {
                $chemData[$key] = number_format($val, 4, '.', '');
            }

            return [
                'heat_no' => $heatNo,
                'found'   => true,
                'chem'    => $chemData,
                'mech'    => $mechData,
            ];
        });

        return response()->json(['data' => $results]);
    }
}
