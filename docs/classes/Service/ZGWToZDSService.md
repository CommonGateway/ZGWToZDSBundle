# CommonGateway\ZGWToZDSBundle\Service\ZGWToZDSService  







## Methods

| Name | Description |
|------|-------------|
|[__construct](#zgwtozdsservice__construct)||
|[zgwToZdsHandler](#zgwtozdsservicezgwtozdshandler)|An example handler that is triggered by an action.|
|[zgwToZdsIdentificationHandler](#zgwtozdsservicezgwtozdsidentificationhandler)|Creates a ZDS Di02 call to the ZDS source, and takes the identification in the respons as case identifier|




### ZGWToZDSService::__construct  

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

