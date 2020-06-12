<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class APIAuthTest extends TestCase
{
    /**
     * Test Valid Registration Without Profile Image
     *
     * @return void
     */
    public function testValidRegistrationWithoutProfileImage()
    {
        $request = [
          'email' => 'test12345@email.com',
          'password' => 'password1234',
          'first_name' => 'Testy',
          'last_name' => 'Testerson',
          'phone' => '111-222-3333',
          'address_line_1' => '123 Test Street',
          'city' => 'Macon',
          'state' => 'GA',
          'postal' => '31206',
          'referer' => 'Other',
          'country' => 'US'
        ];

        $response = $this->postJson('/api/user', $request);

        $response->assertStatus(200);
    }
}
