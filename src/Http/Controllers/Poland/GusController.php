<?php

namespace Upsoftware\Registry\Http\Controllers\Poland;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
           'value' => ['required']
        ]);

        $value = $request->value;
        $type = $request->type ?? registry()->poland()->getTypeFromValue($value);
        $full = $request->full ?? false;

        if ($type === null) {
            throw ValidationException::withMessages([
                'type' => [trans('registry::validation.Unknown identifier type')],
            ]);
        }

        return registry()->poland()->getGusRegonApi($value, $type, $full);
    }
}
