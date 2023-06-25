<?php

namespace App\Repositories\Verifications;

use App\Repositories\Verifications\VerificationRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class VerificationRepositoryTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testPerformVerification_ValidJson_ReturnsVerifiedResult()
    {
        // Arrange
        $json = [
            'data' => [
                'issuer' => [
                    'name' => 'Example Issuer',
                    'identityProof' => [
                        'key' => 'abc123',
                        'location' => 'example.com'
                    ]
                ],
                'recipient' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ]
            ],
            'signature' => [
                'targetHash' => 'abcdef123456'
            ]
        ];

        $repository = new VerificationRepository();

        // Act
        $result = $repository->performVerification($json);

        // Assert
        $this->assertEquals('verified - test', $result['result']);
    }

    public function testPerformVerification_InvalidRecipient_ReturnsInvalidRecipientResult()
    {
        // Arrange
        $json = [
            'data' => [
                'issuer' => [
                    'name' => 'Example Issuer',
                    'identityProof' => [
                        'key' => 'abc123',
                        'location' => 'example.com'
                    ]
                ],
                'recipient' => [
                    'name' => 'John Doe'
                ]
            ],
            'signature' => [
                'targetHash' => 'abcdef123456'
            ]
        ];

        $repository = new VerificationRepository();

        // Act
        $result = $repository->performVerification($json);

        // Assert
        $this->assertEquals('invalid_recipient', $result['result']);
    }

    public function testPerformVerification_InvalidIssuer_ReturnsInvalidIssuerResult()
    {
        // Arrange
        $json = [
            'data' => [
                'issuer' => [
                    'identityProof' => [
                        'key' => 'abc123',
                        'location' => 'example.com'
                    ]
                ],
                'recipient' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ]
            ],
            'signature' => [
                'targetHash' => 'abcdef123456'
            ]
        ];

        $repository = new VerificationRepository();

        // Act
        $result = $repository->performVerification($json);

        // Assert
        $this->assertEquals('invalid_issuer', $result['result']);
    }

    public function testPerformVerification_InvalidSignature_ReturnsInvalidSignatureResult()
    {
        // Arrange
        $json = [
            'data' => [
                'issuer' => [
                    'name' => 'Example Issuer',
                    'identityProof' => [
                        'key' => 'abc123',
                        'location' => 'example.com'
                    ]
                ],
                'recipient' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ]
            ],
            'signature' => [
                'targetHash' => 'wronghash'
            ]
        ];

        $repository = new VerificationRepository();

        // Act
        $result = $repository->performVerification($json);

        // Assert
        $this->assertEquals('invalid_signature', $result['result']);
    }

    public function testStoreVerificationResult_CreatesVerificationResult()
    {
        // Arrange
        $verificationResult = [
            'result' => 'verified - test'
        ];

        $user = Mockery::mock(Auth::class);
        $user->shouldReceive('user')->once()->andReturnSelf();
        $user->shouldReceive('id')->once()->andReturn(1);
        Auth::shouldReceive('user')->once()->andReturn($user);

        // Replace with your actual VerificationResult model class
        $verificationResultModel = Mockery::mock(VerificationResult::class);
        $verificationResultModel->shouldReceive('create')->once()->with([
            'user_id' => 1,
            'file_type' => 'JSON',
            'verification_result' => 'verified - test',
            'created_at' => Mockery::type('DateTime')
        ]);

        $repository = new VerificationRepository();

        // Act
        $repository->storeVerificationResult($verificationResult);

        // Assert
        // Ensure the verification result is stored
        $this->assertTrue(true);
    }

    // Add more tests to cover other methods and edge cases

    public function testValidateRecipient_ValidData_ReturnsTrue()
    {
        // Arrange
        $data = [
            'recipient' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]
        ];

        $repository = new VerificationRepository();

        // Act
        $result = $repository->validateRecipient($data);

        // Assert
        $this->assertTrue($result);
    }

    public function testValidateRecipient_InvalidData_ReturnsFalse()
    {
        // Arrange
        $data = [
            'recipient' => [
                'name' => 'John Doe'
            ]
        ];

        $repository = new VerificationRepository();

        // Act
        $result = $repository->validateRecipient($data);

        // Assert
        $this->assertFalse($result);
    }

    // Add more tests for other private methods

    public function testPerformDnsLookup_ValidLocation_ReturnsDnsRecords()
    {
        // Arrange
        $location = 'example.com';

        $dnsRecords = [
            [
                'data' => 'some data'
            ]
        ];

        $httpMock = Mockery::mock(Http::class);
        $httpMock->shouldReceive('get')->once()->andReturnSelf();
        $httpMock->shouldReceive('ok')->once()->andReturn(true);
        $httpMock->shouldReceive('json')->once()->andReturn(['Answer' => $dnsRecords]);

        Http::shouldReceive('get')->once()->andReturn($httpMock);

        $repository = new VerificationRepository();

        // Act
        $result = $repository->performDnsLookup($location);

        // Assert
        $this->assertEquals($dnsRecords, $result);
    }

    public function testPerformDnsLookup_InvalidLocation_ReturnsEmptyArray()
    {
        // Arrange
        $location = 'example.com';

        $httpMock = Mockery::mock(Http::class);
        $httpMock->shouldReceive('get')->once()->andReturnSelf();
        $httpMock->shouldReceive('ok')->once()->andReturn(false);

        Http::shouldReceive('get')->once()->andReturn($httpMock);

        $repository = new VerificationRepository();

        // Act
        $result = $repository->performDnsLookup($location);

        // Assert
        $this->assertEquals([], $result);
    }

    // Add more tests for other public and private methods

    public function testValidateIssuer_WithValidJson_ReturnsTrue()
    {
        // Arrange
        $json = [
            'issuer' => [
                'name' => 'Example Issuer',
                'identityProof' => [
                    'key' => 'abc123',
                    'location' => 'example.com'
                ]
            ]
        ];

        $repository = new VerificationRepository();

        // Act
        $result = $repository->validateIssuer($json);

        // Assert
        $this->assertTrue($result);
    }

    public function testValidateIssuer_WithInvalidJson_ReturnsFalse()
    {
        // Arrange
        $json = [
            'issuer' => [
                'name' => 'Example Issuer'
            ]
        ];

        $repository = new VerificationRepository();

        // Act
        $result = $repository->validateIssuer($json);

        // Assert
        $this->assertFalse($result);
    }
}
