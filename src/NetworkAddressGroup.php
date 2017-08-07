<?php
/**
 * this file is part of webnium/network-address-group library.
 *
 * Licensed under MIT license refs LICENSE file located on root of project.
 */

namespace Webnium\IpAddress;

use Leth\IPAddress\IP\NetworkAddress;
use Leth\IPAddress\IP\Address;

class NetworkAddressGroup implements \Countable
{
    /** @var NetworkAddress[] */
    private $networks;

    /** @var boolean */
    private $optimized = true;

    public function __construct(array $networks = [])
    {
        $networks = array_unique($networks);
        foreach ($networks as $network) {
            $this->add($network);
        }
    }

    /**
     * Add network address
     *
     * @param string|NetworkAddress $network
     */
    public function add($network)
    {
        $this->optimized = count($this->networks) === 0;
        $this->networks[] = NetworkAddress::factory($network);
    }

    /**
     * Count network addresses in group
     *
     * @return int
     */
    public function count()
    {
        return count($this->networks);
    }

    /**
     * optimize
     */
    public function optimize()
    {
        if ($this->optimized) {
            return;
        }

        $this->networks = NetworkAddress::merge($this->networks);
        usort($this->networks, [NetworkAddress::class, 'compare']);
        $networks = $this->networks;
        $current = array_shift($networks);
        $optimized = [$current];
        foreach ($networks as $next) {
            if ($current->encloses_subnet($next)) {
                continue;
            }

            $optimized[] = $next;
            $current = $next;
        }

        $this->networks = $optimized;
        $this->optimized = true;
    }

    /**
     * @param string|Address $address
     *
     * @return boolean true: $this encloses $address, false: otherwise
     */
    public function encloses($address)
    {
        $this->optimize();
        $address = Address::factory($address);
        $networks = $this->networks;

        $min = 0;
        $max = count($this) - 1;

        for ($index = $max >> 1; $min <= $max; $index = ($max+$min) >> 1) {
            $network = $networks[$index];
            if ($network->get_network_start()->compare_to($address) > 0) {
                $max = $index - 1;
                continue;
            }

            if ($network->get_network_end()->compare_to($address) < 0) {
                $min = $index + 1;
                continue;
            }

            return true;
        }

        return false;
    }
}
