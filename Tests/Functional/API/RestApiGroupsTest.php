<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestApiGroupsTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        if (!isset($this->client)) {
            $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
        } else {
            $this->client->restart();
        }
    }

    /**
     * @return array
     */
    public function testApiCreateGroup()
    {
        $request = array(
            "group" => array(
                "name" => 'Group_'.mt_rand(100, 500),
                "roles" => array(2),
            )
        );

        $this->client->request('POST', $this->client->generate('oro_api_post_group'), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @depends testApiCreateGroup
     * @param  array $request
     * @return array $group
     */
    public function testApiGetGroups($request)
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_groups'));
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        $flag = 1;
        foreach ($result as $group) {
            if ($group['name'] == $request['group']['name']) {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals(0, $flag);

        return $group;
    }

    /**
     * @depends testApiCreateGroup
     * @depends testApiGetGroups
     * @param  array $request
     * @param  array $group
     * @return array $group
     */
    public function testApiUpdateGroup($request, $group)
    {
        $request['group']['name'] .= '_updated';
        $this->client->request('PUT', $this->client->generate('oro_api_put_group', array('id' => $group['id'])), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', $this->client->generate('oro_api_get_group', array('id' => $group['id'])));
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals($result['name'], $request['group']['name'], 'Group does not updated');

        return $group;
    }

    /**
     * @depends testApiUpdateGroup
     * @param $group
     */
    public function apiDeleteGroup($group)
    {
        $this->client->request('DELETE', $this->client->generate('oro_api_delete_group', array('id' => $group['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', $this->client->generate('oro_api_get_group', array('id' => $group['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
