# CommonGateway\ZGWToZDSBundle\Service\ZGWToZDSService

## Methods

| Name | Description |
|------|-------------|
|[\_\_construct](#zgwtozdsservice__construct)||
|[zgwToZdsHandler](#zgwtozdsservicezgwtozdshandler)|An example handler that is triggered by an action.|
|[zgwToZdsIdentificationHandler](#zgwtozdsservicezgwtozdsidentificationhandler)|Creates a ZDS Di02 call to the ZDS source, and takes the identification in the respons as case identifier|
|[zgwToZdsInformationObjectHandler](#zgwtozdsservicezgwtozdsinformationobjecthandler)|Translate information objects to Lk01 messages and send them to a source.|
|[zgwToZdsObjectIdentificationHandler](#zgwtozdsservicezgwtozdsobjectidentificationhandler)|Creates a ZDS Di02 call to the ZDS source, and takes the identification in the respons as case identifier|
|[zgwToZdsXmlEncodeHandler](#zgwtozdsservicezgwtozdsxmlencodehandler)|Does an xmlEncode on the response data. (temporary solution)|

### ZGWToZDSService::\_\_construct

**Description**

```php
 __construct (void)
```

**Parameters**

`This function has no parameters.`

**Return Values**

`void`

<hr />

### ZGWToZDSService::zgwToZdsHandler

**Description**

```php
public zgwToZdsHandler (array $data, array $configuration)
```

An example handler that is triggered by an action.

**Parameters**

* `(array) $data`
  : The data array
* `(array) $configuration`
  : The configuration array

**Return Values**

`array`

> A handler must ALWAYS return an array

<hr />

### ZGWToZDSService::zgwToZdsIdentificationHandler

**Description**

```php
public zgwToZdsIdentificationHandler (array $data, array $configuration)
```

Creates a ZDS Di02 call to the ZDS source, and takes the identification in the respons as case identifier

**Parameters**

* `(array) $data`
  : The data from the response.
* `(array) $configuration`
  : The configuration for this action.

**Return Values**

`array`

> The resulting data array.

<hr />

### ZGWToZDSService::zgwToZdsInformationObjectHandler

**Description**

```php
public zgwToZdsInformationObjectHandler (array $data, array $configuration)
```

Translate information objects to Lk01 messages and send them to a source.

**Parameters**

* `(array) $data`
  : The data array
* `(array) $configuration`
  : The configuration array

**Return Values**

`array`

> The updated data array

<hr />

### ZGWToZDSService::zgwToZdsObjectIdentificationHandler

**Description**

```php
public zgwToZdsObjectIdentificationHandler (array $data, array $configuration)
```

Creates a ZDS Di02 call to the ZDS source, and takes the identification in the respons as case identifier

**Parameters**

* `(array) $data`
  : The data from the response.
* `(array) $configuration`
  : The configuration for this action.

**Return Values**

`array`

> The resulting data array.

<hr />

### ZGWToZDSService::zgwToZdsXmlEncodeHandler

**Description**

```php
public zgwToZdsXmlEncodeHandler (array $data, array $configuration)
```

Does an xmlEncode on the response data. (temporary solution)

**Parameters**

* `(array) $data`
  : The data array.
* `(array) $configuration`
  : The configuration array.

**Return Values**

`array`

> The updated data array.

<hr />
