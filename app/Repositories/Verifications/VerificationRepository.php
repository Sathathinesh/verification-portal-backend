<?php

namespace App\Repositories\Verifications;

use App\Models\VerificationResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VerificationRepository implements VerificationInterface
{

    /*
    private $verification = null;

	public function __construct(VerificationInterface $verification)
	{

		$this->verification = $verification;
	}
*/
    
    public function performVerification($json): array
    {
        
        $verificationResult = [
            'issuer' => $json['data']['issuer']['name'],
            'result' => 'verified - test'
        ];

        // Condition 1: Valid Recipient
        if (!isset($json['data']['recipient']['name']) || !isset($json['data']['recipient']['email'])) {
            $verificationResult['result'] = 'invalid_recipient';
            return $verificationResult;
        }

        // Condition 2: Valid Issuer
        $found = $this->validateIssuer($json['data']);
        if (!$found) {
            $verificationResult['result'] = 'invalid_issuer';
            return $verificationResult;
        }


        // Condition 3: Valid Signature
        $computedTargetHash = $this->computeTargetHash($json);
        
        $targetHash = $json['signature']['targetHash'];

        if ($computedTargetHash !== $targetHash) {
            $verificationResult['result'] = 'invalid_signature';
            return $verificationResult;
        }

        
        return $verificationResult;
    }

    public function storeVerificationResult(array $verificationResult)
    {
        $userId = 1;
//        $user_id = Auth::user()->id;

        VerificationResult::create([
            'user_id' => $userId,
            'file_type' => 'JSON',
            'verification_result' => $verificationResult['result'],
            'created_at' => now()
        ]);
    }
    // Decode the JSON string into an associative array

    private function computeTargetHash($json){
        $associateValues = $this->transformObject($json);
        $associateValuesHashArray = [];

        foreach($associateValues as $key => $associateValue){
            $associateValuesHashArray[] = $this->computeHash($key, $associateValue);
        }
       // Log::info(array_values($associateValuesHashArray));
        // Sort all the property hashes alphabetically
        sort($associateValuesHashArray);
        Log::info($associateValuesHashArray);
        
        // Concatenate all the sorted property hashes together
        $concatenatedHashes = implode('', $associateValuesHashArray);
        //Log::info($concatenatedHashes);
        
        // Compute the SHA256 hash of the concatenated hashes
        $targetHash = hash('sha256', $concatenatedHashes);
        Log::info($targetHash);
        return $targetHash;
    }

    // Function to transform the JSON object
    private function transformObject($data):array  
    {
            
            $newData = [
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
    
            return $newData;
    }
    
    public function computeHash($key, $value): string
    {
        // Create an associative array with the key-value pair
        $data = [$key => $value];
    
        // Convert the array to a JSON string
        $json_string = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
        // Compute the SHA256 hash of the JSON string
        $sha256_hash = hash('sha256', $json_string);
    
        return $sha256_hash;
    }

    public function validateIssuer($json)
    {
        // Check if the JSON has an 'issuer' key
        if (!isset($json['issuer'])) {
            return false;
        }

        $issuer = $json['issuer'];

        // Check if 'name' and 'identityProof' keys exist in the 'issuer' object
        if (!isset($issuer['name']) || !isset($issuer['identityProof'])) {
            return false;
        }

        $identityProof = $issuer['identityProof'];

        // Check if 'key' and 'location' keys exist in the 'identityProof' object
        if (!isset($identityProof['key']) || !isset($identityProof['location'])) {
            return false;
        }

        $key = $identityProof['key'];
        $location = $identityProof['location'];

        // Perform DNS TXT record lookup using Google DNS API
        $dnsLookupUrl = "https://dns.google/resolve?name=" . urlencode($location) . "&type=TXT";
        $dnsLookupResponse = file_get_contents($dnsLookupUrl);

        if ($dnsLookupResponse === false) {
            // Error occurred while performing DNS lookup
            return false;
        }

        $dnsLookupData = json_decode($dnsLookupResponse, true);

        // Check if the Ethereum wallet address key is found in the TXT record
        if (!isset($dnsLookupData['Answer'])) {
            return false;
        }

        $dnsRecords = $dnsLookupData['Answer'];

        foreach ($dnsRecords as $record) {
            if (isset($record['data']) && strpos($record['data'], $key) !== false) {
                // Ethereum wallet address key found in the TXT record
                return true;
            }
        }

        return false;
    }


}
