<?php

namespace App\Http\Controllers;

use App\Models\VerificationResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Repositories\Verifications\VerificationInterface;

class VerificationController extends Controller
{
     private $verification = null;

      public function __construct(VerificationInterface $verification)
      {

        $this->verification = $verification;
      }

    public function verify(Request $request)
    {

        // Perform verification
        $verificationResult = $this->verification->performVerification($request);

        // Store verification result in database
        $this->verification->storeVerificationResult($verificationResult);

        // Return response
        return response()->json($verificationResult, 200);
        //return response()->json('thinesh', 200);
    }

}
