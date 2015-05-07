# Insert pagination headers in your response

This part is strongly inspired by this [angular repository](https://github.com/begriffs/angular-paginate-anything)

To understand the mechanics, it is recommended to read the documentation and the demo of this repository

To insert the right Link header with CORS, you can use the ```php Kilix\Bundle\ApiCoreBundle\Request\PaginatedResponse``` class.

You just have to give it some HTTP headers with data given by the input request :

* Range-Unit (example : users)
* Range (example : 0-30)

```php
// your controller method
public function yourAction() {

    // your logic...
    // $response would be for example your JSON array with the paginated elements

    return new PaginatedResponse($response, $status, [
        'Accept-Ranges' => 'items', // the Range-Unit header value, sent from the front
        'Content-Range' => '0-30/660', // Range / Max-items. Range is the Range header value given by the front
        'Content-Location' => 'http://core.appli.com/route' // url leading to the resource like http://api.example.org/articles/comments
    ]);
}

```

The Content-Range value is the concatenation of the Range header value with the total number of items.

Be careful when you use it with filters, don't forget to count the total number of filtered items, and not the total number of rows in your database.