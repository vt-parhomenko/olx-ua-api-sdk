<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use Parhomenko\Olx\Api\Categories;
use Parhomenko\Olx\Tests\OlxTestCase;

class CategoriesTest extends OlxTestCase
{
    public function testSuggestionSendsQuery(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $categories = new Categories($this->authenticator($client), $client);

        $categories->suggestion('iphone 13');

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/categories/suggestion', $request->getUri()->getPath());
        $this->assertStringContainsString('q=iphone', $request->getUri()->getQuery());
    }

    public function testAttributesPath(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $categories = new Categories($this->authenticator($client), $client);

        $categories->attributes(1808);

        $this->assertSame('/api/partner/categories/1808/attributes', $this->history[0]['request']->getUri()->getPath());
    }
}
