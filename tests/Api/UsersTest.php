<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use Parhomenko\Olx\Api\Users;
use Parhomenko\Olx\Exceptions\NotFoundException;
use Parhomenko\Olx\Tests\OlxTestCase;

class UsersTest extends OlxTestCase
{
    public function testMeReturnsAuthenticatedUser(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 1, 'name' => 'Me' ] ]) ]);
        $users = new Users($this->authenticator($client), $client);

        $result = $users->me();

        $this->assertSame([ 'id' => 1, 'name' => 'Me' ], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/users/me', $request->getUri()->getPath());
        $this->assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
        $this->assertSame('2.0', $request->getHeaderLine('Version'));
    }

    public function testGetReturnsUserById(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 77, 'name' => 'Bob' ] ]) ]);
        $users = new Users($this->authenticator($client), $client);

        $result = $users->get(77);

        $this->assertSame([ 'id' => 77, 'name' => 'Bob' ], $result);
        $this->assertSame('/api/partner/users/77', $this->history[0]['request']->getUri()->getPath());
    }

    public function testGetMapsHttpErrorToTypedException(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(404, [ 'error' => [ 'title' => 'Not found', 'detail' => 'No user' ] ]) ]);
        $users = new Users($this->authenticator($client), $client);

        $this->expectException(NotFoundException::class);
        $users->get(999);
    }

    public function testAccountBalancePath(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'wallet' => 100 ] ]) ]);
        $users = new Users($this->authenticator($client), $client);

        $result = $users->accountBalance();

        $this->assertSame([ 'wallet' => 100 ], $result);
        $this->assertSame('/api/partner/users/me/account-balance', $this->history[0]['request']->getUri()->getPath());
    }

    public function testPaymentMethodsPath(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'account', 'postpaid' ] ]) ]);
        $users = new Users($this->authenticator($client), $client);

        $users->paymentMethods();

        $this->assertSame('/api/partner/users/me/payment-methods', $this->history[0]['request']->getUri()->getPath());
    }

    public function testBillingSendsPagination(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $users = new Users($this->authenticator($client), $client);

        $users->billing(2, 50);

        $request = $this->history[0]['request'];
        $this->assertSame('/api/partner/users/me/billing', $request->getUri()->getPath());
        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('page=2', $query);
        $this->assertStringContainsString('limit=50', $query);
    }

    public function testInvoicesUseDedicatedPaths(): void
    {
        $client = $this->mockClient([
            $this->jsonResponse(200, [ 'data' => [] ]),
            $this->jsonResponse(200, [ 'data' => [] ]),
        ]);
        $users = new Users($this->authenticator($client), $client);

        $users->prepaidInvoices();
        $users->postpaidInvoices();

        $this->assertSame('/api/partner/users/me/prepaid-invoices', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('/api/partner/users/me/postpaid-invoices', $this->history[1]['request']->getUri()->getPath());
    }
}
