# Verification Portal API Documentation

## Overview

The Verification API allows you to perform verifications and store verification results.

Base URL: `http://127.0.0.1:8000`

## Authentication

API requests require authentication. Include an `Authorization` header with a valid API key in the request.

Example header: `Authorization: Bearer {api_key}`

## Endpoints

### Verification

Perform verification and store verification results.

- URL: `/api/verification`
- Method: POST

#### Request Parameters

| Parameter | Type   | Required | Description              |
|-----------|--------|----------|--------------------------|
| data      | object | Yes      | The verification data.   |

Example Request Body:
```json
{
  "data": {
    "id": "63c79bd9303530645d1cca00",
    "name": "Certificate of Completion",
    "recipient": {
      "name": "Marty McFly",
      "email": "marty.mcfly@gmail.com"
    },
    "issuer": {
      "name": "Accredify",
      "identityProof": {
        "type": "DNS-DID",
        "key": "did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller",
        "location": "ropstore.accredify.io"
      }
    },
    "issued": "2022-12-23T00:00:00+08:00"
  },
  "signature": {
    "type": "SHA3MerkleProof",
    "targetHash": "288f94aadadf486cfdad84b9f4305f7d51eac62db18376d48180cc1dd2047a0e"
  }
}
```
## Response

Allowed values for results are "verified", "invalid_recipient", "invalid_issuer", or "invalid_signature".

### Success Response

Status Code: 200 (OK)

Body:
```json
{
    "data": {
        "issuer": "Accredify", 
        "result": "verified" 
    }
}
```

## Error Responses
Status Code: 400 (Bad Request)

Body:
```json
{
  "error": "Invalid request data."
}
```
Status Code: 401 (Unauthorized)

Body:
```json
{
  "error": "Authentication failed. Invalid API key."
}
```
Status Code: 500 (Internal Server Error)

Body:
```json
{
  "error": "An unexpected error occurred."
}
```
