Payload
=======

[Back to index](../README.md#index)

# Usage

The Payload object is mainly a container for a [Prooph\ProcessingType\Type](https://github.com/prooph/processing/blob/master/library/Type/Type.php).
It provides special json serialization logic and a static factory method to reconstitute itself from a json decoded payload array.

# API

```php
class Payload implements \JsonSerializable
{
    /**
     * @param \Prooph\Processing\Type\Prototype $aPrototype
     * @return Payload
     */
    public static function fromPrototype(\Prooph\Processing\Type\Prototype $aPrototype);

    /**
     * @param \Prooph\Processing\Type\Type $aType
     * @return Payload
     */
    public static function fromType(\Prooph\Processing\Type\Type $aType);

    /**
     * @param array $jsonDecodedData
     * @return Payload
     */
    public static function fromJsonDecodedData(array $jsonDecodedData);

    public function jsonSerialize();

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param mixed $newData
     */
    public function changeData($newData);

    /**
     * @param mixed $newData
     */
    public function replaceData($newData);

    /**
     * @return string
     */
    public function getTypeClass();

    /**
     * @param string $newTypeClass
     */
    public function changeTypeClass($newTypeClass);

    /**
     * @return \Prooph\Processing\Type\Type
     */
    public function toType();
}
```

## Static Factory Methods

Payload can be constructed from a [Prooph\ProcessingType\Type](https://github.com/prooph/processing/blob/master/library/Type/Type.php) or [Prooph\ProcessingType\Prototype](https://github.com/prooph/processing/blob/master/library/Type/Prototype.php)
and also from an array that represents a json decoded payload:

```php
$originalPayload = \Prooph\Processing\Message\Payload::fromType(
    \Prooph\Processing\Type\String::fromNativeValue("Processing rocks")
);

$jsonDecodedPayloadArr = json_decode(json_encode($originalPayload), true);

$copiedPayload = \Prooph\Processing\Message\Payload::fromJsonDecodedData($jsonDecodedPayloadArr);

echo ($originalPayload->getData() == $copiedPayload->getData())? "payload is equal" : "payload is not equal";

//Output: payload is equal
```

## Manipulating The Payload

The payload can be changed in a variety of ways. In a workflow it is often required to manipulate, map, add or remove data from
the source payload before the payload can be processed by the target. The method `changeData` merges the given data with the already existing data.
The `replaceData` method instead overrides the existing data completely. Beside these data manipulation methods you can also change the type of the data.
This is useful when the source system and the target system uses different Prooph\ProcessingType\Types for the same native data.
Imagine the following scenario:

A user has registered for your online shop and you want to sync the new profile with your CRM. The online shop WorkflowMessageHandler uses a My\Shop\User type and
the CRM on the other side uses a My\CRM\User type with a slightly different structure, for example the My\Shop\User has a givenname and a lastname property but
the My\CRM\User only has a name property. Before the My\Shop\User can be imported in the CRM givenname and lastname need to be concatenated to single name string:

```php

$shopUser = My\Shop\User::fromNativeType(array('givenname' => 'John', 'lastname' => 'Doe'));

$payload = Payload::fromType($shopUser);

$data = $payload->getData();

$data['name'] = $data['givenname'] . ' ' . $data['lastname'];

unset($data['givenname']);
unset($data['lastname']);

$payload->replaceData($data);

$payload->changeTypeClass('My\CRM\User');

$crmUser = $payload->toType();

echo $crmUser->property('name')->value();

//Output: John Doe

```

This is just an example. Normally the scenario would be more complex. The payload would be delivered from the shop system to the CRM via
a [ProcessingMessage\WorkflowMessage](workflow_message.md). The WorkflowMessage would be dispatched by a ProophServiceBus and maybe exchanged between the
systems via remote interface. In this scenario the My\Shop\User representation needs to be converted to a primitive type (in processing this is always a json string) and
on the target system (the CRM) it needs to be converted to My\CRM\User type which performs assertions on the data to ensure data integrity.