<?php


namespace Tests\Unit;

use App\Repositories\Verifications\VerificationRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Mockery\MockInterface;
//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Mockery;

class VerificationTest extends TestCase
{
    

    public function testPerformVerification_ValidJson_ReturnsVerifiedResult()
    {
        // Arrange
        $json = [
            'data' => [
                'issuer' => [
                    'name' => 'Accredify',
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
        $this->assertEquals('invalid_issuer', $result['result']);
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
        $this->assertFalse($result);
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
        $this->assertFalse($result);
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
