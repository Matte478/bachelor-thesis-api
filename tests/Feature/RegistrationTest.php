<?php

namespace Tests\Feature;

use App\Client;
use App\Contractor;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function client_can_register()
    {
        $response = $this->json('POST', '/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'type' => 'client',
            'company' => 'Company',
            'city' => 'Bratislava',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/home');

        $client = Client::first();

        $this->assertNotNull($client);
        $this->assertEquals('Company', $client->company()->first()->company);
    }

    /** @test */
    public function contractor_can_register()
    {
        $this->withoutExceptionHandling();
        $response = $this->json('POST', '/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'type' => 'contractor',
            'restaurant' => 'Restaurant',
            'city' => 'Bratislava',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/home');

        $client = Contractor::first();

        $this->assertNotNull($client);
        $this->assertEquals('Restaurant', $client->restaurant()->first()->restaurant);
    }
}
