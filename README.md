# NetworkAddressGroup

Utility for grouped network address manipulation

## Usage

```php
use Webnium\IpAddress\NetworkAddressGroup;

$group = new NetworkAddressGroup(['192.168.0.0/24', '192.168.2.0/24', '10.1.0.0/16', '10.0.1.64/27']);

var_dump($group->encloses('192.168.0.53')); // bool(true)
var_dump($group->encloses('192.168.1.1')); // bool(false)

```

## License

MIT License
