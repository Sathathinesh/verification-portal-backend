# Verification Portal

This repository contains a REST API built with Laravel for verifying JSON files. Authenticated users can send a JSON file and receive a verification result as a response.

You can check API Doc in `APIDOC.md` file. 

## Requirements

To use this API, make sure you have the following requirements:

- Laravel 
- PHP 
- Composer

## Sample JSON

Here is an example of the JSON structure that can be sent to the API:

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

The API response will be in the following format:
Allowed values for results are "verified", "invalid_recipient", "invalid_issuer", or "invalid_signature".
```json
{
    "data": {
        "issuer": "Accredify", 
        "result": "verified" 
    }
}
```

## Installation
Clone the repository: `git clone https://github.com/Sathathinesh/verification-portal-backend.git`

Navigate to the project directory: `cd verification-portal-backend`

Copy the example environment file and update the necessary configurations: `cp .env.example .env`

Install the dependencies: `composer install`

Set up the database: `php artisan migrate`

Serve the application: `php artisan serve`

Test http://localhost:8000/api/verification in your Postman.

## Testing
Test the application run the command indide the project folder : `./vendor/bin/phpunit`

## Contributing
If you'd like to contribute to this project, please follow these steps:

- Fork the repository.
- Create a new branch.
- Make your changes and commit them.
- Push your changes to your forked repository.
- Submit a pull request.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
