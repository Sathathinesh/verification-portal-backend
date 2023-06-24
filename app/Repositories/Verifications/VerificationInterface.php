<?php

namespace App\Repositories\Verifications;


interface VerificationInterface
{

    public function performVerification($json): array;

    public function storeVerificationResult(array $verificationResult);

}
