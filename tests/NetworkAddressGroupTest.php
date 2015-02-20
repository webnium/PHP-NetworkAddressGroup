<?php

namespace Webnium\IpAddress;

use Leth\IPAddress\IP\NetworkAddress;
use Leth\IPAddress\IP\Address;

class NetworkAddressGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provideNetworks
     */
    public function createWithNetworks($networks)
    {
        $instance = new NetworkAddressGroup($networks);

        $this->assertInstanceOf(NetworkAddressGroup::class, $instance);

        return $instance;
    }

    /**
     * provides newtorks
     *
     * @return array
     */
    public function provideNetworks()
    {
        return [
            'by string' => [['192.168.0.0/24', '10.0.5.0/24']],
            'by Leth\IPAddress\IP\NetworkAddress instance' => [[NetworkAddress::factory('192.168.0.0/24'), NetworkAddress::factory('10.0.5.0/24')]],
            'by mixed' => [[NetworkAddress::factory('192.168.0.0/24'), '10.0.5.0/24']],
        ];
    }

    /**
     * @test
     */
    public function countMethodReturnsNumberOfNetworkAddressesInGroup()
    {
        $group = new NetworkAddressGroup(['192.168.0.0/24', '10.0.5.0/24']);
        $this->assertEquals(2, count($group));
    }

    /**
     * @test
     */
    public function optimizeMethodCompressNetworkAdresses()
    {
        $group = new NetworkAddressGroup(['192.168.0.0/24', '192.168.1.0/24', '10.0.5.0/24', '10.0.0.0/16']);
        $group->optimize();

        $this->assertEquals(3, count($group));
    }

    /**
     * @test
     * @dataProvider provideForEnclosesMethod
     */
    public function enclosesMethodReturnsAddressEnclosedInNetworkGroupOrNot($networks, $address, $expected)
    {
        $group = new NetworkAddressGroup($networks);

        $this->assertSame($expected, $group->encloses($address));
    }

    public function provideForEnclosesMethod()
    {
        $networks = ['192.168.0.0/24', '192.168.1.0/24', '10.0.0.0/24', '10.0.2.0/14'];

        return [
            'not enclosed, by string' => [$networks, '127.0.0.1', false],
            'not enclosed, by Address instance' => [$networks, Address::factory('127.0.0.1'), false],
            'not enclosed high boundary' => [$networks, '192.168.2.0', false],
            'not enclosed middle' => [$networks, '10.0.1.2', false],
            'not enclosed low boundary' => [$networks, '9.255.255.255', false],
            'enclosed high boundary' => [$networks, '192.168.1.255', true],
            'enclosed low boundary' => [$networks, '10.0.0.0', true],
            'enclosed one network' => [['192.168.1.0/24'], '192.168.1.254', true],
            'enclosed one /32 network' => [['192.168.1.1/32'], '192.168.1.1', true],
            'not enclosed with empty group' => [[], '192.168.1.1', false],
        ];
    }
}
