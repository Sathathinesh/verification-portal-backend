<?php

namespace App\Repositories\Verifications;

use App\Models\VerificationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VerificationRepository implements VerificationInterface
{
    public function performVerification(array $json): array
    {
        // Initialize the verification result array
        $verificationResult = [
            'issuer' => $json['data']['issuer']['name'],
            'result' => 'verified'
        ];

        // Condition 1: Validate Recipient
        if (!$this->validateRecipient($json['data'])) {
            $verificationResult['result'] = 'invalid_recipient';
            return $verificationResult;
        }

        // Condition 2: Validate Issuer
        if (!$this->validateIssuer($json['data'])) {
            $verificationResult['result'] = 'invalid_issuer';
            return $verificationResult;
        }

        // Condition 3: Validate Signature
        if (!$this->validateSignature($json)) {
            $verificationResult['result'] = 'invalid_signature';
            return $verificationResult;
        }

        return $verificationResult;
    }

    public function storeVerificationResult(array $verificationResult)
    {
        $userId = Auth::user()->id;
    
        // Store the verification result in the database
        VerificationResult::create([
            'user_id' => $userId,
            'file_type' => 'JSON',
            'verification_result' => $verificationResult['result'],
            'created_at' => now()
        ]);
    }

    public function validateRecipient(array $data): bool
    {
        // Check if the recipient name and email are set
        return isset($data['recipient']['name']) && isset($data['recipient']['email']);
    }

    public function validateSignature(array $json): bool
    {
        // Compute the target hash and compare it with the signature's targetHash
        $computedTargetHash = $this->computeTargetHash($json);
        $targetHash = $json['signature']['targetHash'];

        return $computedTargetHash === $targetHash;
    }

    public function computeTargetHash(array $json): string
    {
        // Transform the JSON object
        $associateValues = $this->transformObject($json);
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

    public function transformObject(array $data): array
    {
        // Transform the JSON object into an associative array
        return [
            "id" => $data['data']['id'],
            "name" => $data['data']['name'],
            "recipient.name" => $data['data']['recipient']['name'],
            "recipient.email" => $data['data']['recipient']['email'],
            "issuer.name" => $data['data']['issuer']['name'],
            "issuer.identityProof.type" => $data['data']['issuer']['identityProof']['type'],
            "issuer.identityProof.key" => $data['data']['issuer']['identityProof']['key'],
            "issuer.identityProof.location" => $data['data']['issuer']['identityProof']['location'],
            "issued" => $data['data']['issued']
        ];
    }

    public function computeHash(string $key, string $value): string
    {
        // Create an associative array with the key-value pair
        $data = [$key => $value];

        // Convert the array to a JSON string
        $json_string = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Compute the SHA256 hash of the JSON string
        $sha256_hash = hash('sha256', $json_string);

        return $sha256_hash;
    }

    public function validateIssuer(array $json): bool
    {
        if (!isset($json['issuer'])) {
            return false;
        }

        $issuer = $json['issuer'];

        if (!isset($issuer['name']) || !isset($issuer['identityProof'])) {
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
