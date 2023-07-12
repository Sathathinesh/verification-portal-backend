<?php

namespace App\Repositories\Verifications;

use App\Http\Requests\IssuerRequest;
use App\Http\Requests\RecipientRequest;
use App\Models\VerificationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VerificationRepository implements VerificationInterface
{
    public function performVerification(array $jsonData): array
    {
        // Initialize the verification result array
        $verificationResult = [
            'issuer' => $jsonData['data']['issuer']['name'],
            'result' => 'verified'
        ];

        // Condition 1: Validate Recipient
        if (!$this->validateRecipient($jsonData['data']['recipient'])) {
            $verificationResult['result'] = 'invalid_recipient';
            return $verificationResult;
        }

        // Condition 2: Validate Issuer
        if (!$this->validateIssuer($jsonData['data']['issuer'])) {
            $verificationResult['result'] = 'invalid_issuer';
            return $verificationResult;
        }

        // Condition 3: Validate Signature
        if (!$this->validateSignature($jsonData)) {
            $verificationResult['result'] = 'invalid_signature';
            return $verificationResult;
        }

        return $verificationResult;
    }

    public function storeVerificationResult(array $verificationResult)
    {
        //$userId = Auth::user()->id;
        $userId = 1;
        // Store the verification result in the database
        VerificationResult::create([
            'user_id' => $userId,
            'file_type' => 'JSON',
            'verification_result' => $verificationResult['result'],
            'created_at' => now()
        ]);
    }

    public function validateRecipient(array $recipient): bool
    {
        $request = new RecipientRequest();
        $request->merge($recipient);

        // Apply the validation rules from the request class
        $validator = Validator::make($request->all(), $request->rules(), $request->messages(), $request->attributes());

        // Check if the validation fails
        if ($validator->fails()) {
            return false;
        }

        return true;
    }
    
    public function validateIssuer(array $issuer): bool
    {
        if (!isset($issuer)) {
            return false;
        }

        $request = new IssuerRequest();
        $request->merge($issuer);

        // Apply the validation rules from the request class
        $validator = Validator::make($request->all(), $request->rules(), $request->messages(), $request->attributes());

        // Check if the validation fails
        if ($validator->fails()) {
            return false;
        }

        $identityProof = $issuer['identityProof'];

        if (!isset($identityProof['key']) || !isset($identityProof['location'])) {
            return false;
        }

        $key = $identityProof['key'];
        $location = $identityProof['location'];

        // Perform DNS lookup and check if the Ethereum wallet address key is found in the TXT record
        $dnsRecords = $this->performDnsLookup($location);

        foreach ($dnsRecords as $record) {
            if (isset($record['data']) && strpos($record['data'], $key) !== false) {
                return true;
            }
        }

        return false;
    }

    public function validateSignature(array $jsonData): bool
    {
        // Compute the target hash and compare it with the signature's targetHash
        $computedTargetHash = $this->computeTargetHash($jsonData);
        $targetHash = $jsonData['signature']['targetHash'];

        return $computedTargetHash === $targetHash;
    }

    public function computeTargetHash(array $jsonData): string
    {
        // Transform the JSON object
        $associateValues = $this->transformObject($jsonData);
        $associateValuesHashArray = [];

        // Compute hash for each key-value pair and store in an array
        foreach ($associateValues as $key => $associateValue) {
            $associateValuesHashArray[] = $this->computeHash($key, $associateValue);
        }

        // Sort the property hashes alphabetically
        sort($associateValuesHashArray);

        // Concatenate the sorted property hashes
        $concatenatedHashes = implode('', $associateValuesHashArray);

        // Compute the SHA256 hash of the concatenated hashes
        $targetHash = hash('sha256', $concatenatedHashes);

        return $targetHash;
    }

    public function transformObject(array $jsonData): array
    {
        // Transform the JSON object into an associative array
        return [
            "id" => $jsonData['data']['id'],
            "name" => $jsonData['data']['name'],
            "recipient.name" => $jsonData['data']['recipient']['name'],
            "recipient.email" => $jsonData['data']['recipient']['email'],
            "issuer.name" => $jsonData['data']['issuer']['name'],
            "issuer.identityProof.type" => $jsonData['data']['issuer']['identityProof']['type'],
            "issuer.identityProof.key" => $jsonData['data']['issuer']['identityProof']['key'],
            "issuer.identityProof.location" => $jsonData['data']['issuer']['identityProof']['location'],
            "issued" => $jsonData['data']['issued']
        ];
    }

    public function computeHash(string $key, string $value): string
    {
        // Create an associative array with the key-value pair
        $data = [$key => $value];

        // Convert the array to a JSON string
        // generate a JSON string that retains the original form of forward slashes and 
        //Unicode characters without escaping them.
        // control how certain characters are represented in the resulting JSON string
        $json_string = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Compute the SHA256 hash of the JSON string
        $sha256_hash = hash('sha256', $json_string);

        return $sha256_hash;
    }

    public function performDnsLookup(string $location): array
    {
        // Perform DNS TXT record lookup using Google DNS API
        $dnsLookupUrl = "https://dns.google/resolve?name=" . urlencode($location) . "&type=TXT";
        $dnsLookupResponse = Http::get($dnsLookupUrl);

        if (!$dnsLookupResponse->ok()) {
            return [];
        }

        $dnsLookupData = $dnsLookupResponse->json();

        return $dnsLookupData['Answer'] ?? [];
    }

}
