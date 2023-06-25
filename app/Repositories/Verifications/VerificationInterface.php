<?php

namespace App\Repositories\Verifications;


interface VerificationInterface
{

    public function performVerification(array $json): array;

    public function storeVerificationResult(array $verificationResult);

}
