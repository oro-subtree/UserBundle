<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestUsersApiTest extends WebTestCase
{

    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * @return array
     */
    public function testApiCreateUser()
    {
        $request = array(
            "user" => array (
                "username" => 'user_' . mt_rand(),
                "email" => 'test_'  . mt_rand() . '@test.com',
                "enabled" => '1',
                "plainPassword" => '1231231q',
                "firstName" => "firstName",
                "lastName" => "lastName",
                "rolesCollection" => array("1")
            )
        );
        $this->client->request('POST', 'http://localhost/api/rest/latest/user', $request);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @depends testApiCreateUser
     * @param  string $request
     * @return int
     */
    public function testApiUpdateUser($request)
    {
        //get user id
        $this->client->request('GET', 'http://localhost/api/rest/latest/users?limit=100');
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        $userId = $this->assertEqualsUser($request, $result);
        //update user
        $request['user']['username'] .= '_Updated';
        unset($request['user']['plainPassword']);
        $this->client->request('PUT', 'http://localhost/api/rest/latest/users' . '/' . $userId, $request);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 204);
        //open user by id
        $this->client->request('GET', 'http://localhost/api/rest/latest/users' . '/' . $userId);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 200);

        $result = json_decode($result->getContent(), true);
        //compare result
        $this->assertEquals($request['user']['username'], $result['username']);

        return $userId;
    }

    /**
     * @depends testApiUpdateUser
     * @param int $userId
     */
    public function testApiDeleteUser($userId)
    {
        $this->client->request('DELETE', 'http://localhost/api/rest/latest/users' . '/' . $userId);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 204);
        $this->client->request('GET', 'http://localhost/api/rest/latest/users' . '/' . $userId);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 404);
    }

    /**
     * Test API response status
     *
     * @param string $response
     * @param int    $statusCode
     */
    protected function assertJsonResponse($response, $statusCode = 201)
    {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $response->getContent()
        );
    }

    /**
     * Check created user
     *
     * @return int
     * @param  array $result
     * @param  array $request
     */
    protected function assertEqualsUser($request, $result)
    {
        $flag = 1;
        foreach ($result as $key => $object) {
            foreach ($request as $user) {
                if ($user['username'] == $result[$key]['username']) {
                    $flag = 0;
                    $userId = $result[$key]['id'];
                    break 2;
                }
            }
        }
        $this->assertEquals(0, $flag);

        return $userId;
    }
}
