<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Verifications\VerificationInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use App\Http\Resources\VerificationResource;
class VerificationController extends Controller
{
    private VerificationInterface $verification;

    public function __construct(
      VerificationInterface $verification
      ){
      $this->verification = $verification;
    }

    public function verify(Request $request)
    {
      $data = $request->all();

      // Perform verification
      $verificationResult = $this->verification->performVerification($data);

      // Store verification result in database
      $this->verification->storeVerificationResult($verificationResult);

      // Return response with transformed data
      return new VerificationResource($verificationResult);
    }
      
}
