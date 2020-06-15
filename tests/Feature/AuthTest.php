<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\User;

class AuthTest extends TestCase
{

    // All test classes must have this declaration if
    // they are going to use tearDownAfterClass
    protected function tearDown(): void {}


    // Runs when all tests are finishing running.
    public static function tearDownAfterClass(): void {
      parent::tearDownAfterClass();

      $u = User::where("email", "testy@testerson.com")->first();

      if ($u) {
        $u->delete();
      }
    }

    protected function onNotSuccessfulTest($t): void
    {
        self::tearDownAfterClass();

        fwrite(STDOUT, __METHOD__ . "\n");
        throw $t;
    }


    /**
     * Test User Registration with Valid parameters
     *
     * @return void
     */
    public function testUserRegistrationValidParams()
    {
        $data = [
          'email' => 'testy@testerson.com',
          'password' => 'password1234',
          'password_confirmation' => 'password1234',
          'first_name' => 'Testy',
          'last_name' => 'Testerson',
          'address_line_1' => '123 Test St.',
          'city' => 'Macon',
          'state' => 'GA',
          'postal' => '31206',
          'country' => 'US',
          'referer' => 'Other',
          'phone' => '111-222-3333'
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(200);
    }

    /**
     * Test User Registration with Existing Email
     *
     * @return void
     */
    public function testUserRegistrationExistingEmail()
    {
        $data = [
          'email' => 'testy@testerson.com',
          'password' => 'password1234',
          'password_confirmation' => 'password1234',
          'first_name' => 'Testy',
          'last_name' => 'Testerson',
          'address_line_1' => '123 Test St.',
          'city' => 'Macon',
          'state' => 'GA',
          'postal' => '31206',
          'country' => 'US',
          'referer' => 'Other',
          'phone' => '111-222-3333'
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(400);

        $response->assertJson([
          'data' => [
            'email' => [
              'The email has already been taken.'
            ]
          ]
        ]);
    }

    /**
     * Test Login with Valid Creds
     *
     * @return void
     */
    public function testLoginValidCreds()
    {
        $data = [
          'email' => 'testy@testerson.com',
          'password' => 'password1234'
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(200);
    }


    /**
     * Test Login with Invalid Creds
     *
     * @return void
     */
    public function testLoginInvalidCreds()
    {
        $data = [
          'email' => 'testy@testerson.com',
          'password' => 'incorrect'
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(400);
    }

    // TODO: Add a test here for logging in with valid remember token

    /**
     * Test Login with Invalid Remember Token
     *
     * @return void
     */
    public function testLoginInvalidRememberToken()
    {
        // Login with Remember Token
        $data = [
          'token' => '1234'
        ];

        $response = $this->postJson('/api/login/token', $data);

        $response->assertStatus(400);
    }
}
