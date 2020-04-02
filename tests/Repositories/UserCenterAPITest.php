<?php

namespace SouthCN\EasyUC\Tests\Repositories;

use SouthCN\EasyUC\Repositories\UserCenterAPI;
use SouthCN\EasyUC\Tests\TestCase;

class UserCenterAPITest extends TestCase
{
    protected $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = new UserCenterAPI;
    }

    public function test_get_service_area_list()
    {
        $response = $this->api->getServiceAreaList();

        $this->assertObjectHasAttribute('id', $response[0]);
        $this->assertObjectHasAttribute('name', $response[0]);
        $this->assertObjectHasAttribute('is_ipv6', $response[0]);
        $this->assertObjectHasAttribute('sort', $response[0]);
    }

    public function test_get_org_list()
    {
        $response = $this->api->getOrgList('all', uniqid());

        $this->assertObjectHasAttribute('list', $response);
        $this->assertObjectHasAttribute('is_finished', $response);
        $this->assertObjectHasAttribute('version', $response);
        $this->assertObjectHasAttribute('sync_option', $response);
        $this->assertObjectHasAttribute('sync_period_id', $response);
    }

    public function test_get_site_list()
    {
        $response = $this->api->getSiteList('all', uniqid());

        $this->assertObjectHasAttribute('list', $response);
        $this->assertObjectHasAttribute('is_finished', $response);
        $this->assertObjectHasAttribute('version', $response);
        $this->assertObjectHasAttribute('sync_option', $response);
        $this->assertObjectHasAttribute('sync_period_id', $response);
    }

    public function test_get_user_list()
    {
        $response = $this->api->getUserList('all', uniqid());

        $this->assertObjectHasAttribute('list', $response);
        $this->assertObjectHasAttribute('is_finished', $response);
        $this->assertObjectHasAttribute('version', $response);
        $this->assertObjectHasAttribute('sync_option', $response);
        $this->assertObjectHasAttribute('sync_period_id', $response);
    }
}
