### init
- `printf "UID=$(id -u)\nGID=$(id -g)" > .env.local`
- `docker volume create packing_db`
- `docker-compose up -d`
- `docker-compose run shipmonk-packing-app bash`
- `composer install && vendor/bin/doctrine orm:schema-tool:create && vendor/bin/doctrine dbal:run-sql "$(cat data/packaging-data.sql)"`

### run
- `php run.php "$(cat sample.json)"`

### run with xdebug
- `./console-xdbg run.php "$(cat sample.json)"`

### adminer
- Open `http://localhost:8080/?server=mysql&username=root&db=packing`
- Password: secret

## Input Data
### Expected Format
The input must be a valid JSON object structured as follows:

```json
{
    "products": [
        {
            "id": integer,
            "width": float,
            "height": float,
            "length": float,
            "weight": float
        }
    ]
}
```
### Example
```json
{
    "products": [
        {
            "id": 1,
            "width": 3.4,
            "height": 2.1,
            "length": 3.0,
            "weight": 4.0
        }
    ]
}
```

### Application workflow

### Parse Input Data:

1. Reads and decodes the JSON body of the request.
2. Validates the presence of the products key in the input data.
3. Throws an exception if the products key is missing.

### Normalize Products & Boxes:

1. Delegates product normalization to the ProductService.
2. Retrieve boxes from database and normalize them

### Packing Operation:

1. Passes the normalized products and boxes to the PackingService to get the packing response.
2. Validates if only one box was used to pack all products.

### Process Packing Response:

1. Retrieves box information from the response.
2. Fetches the corresponding box from the BoxService.
3. Stores the packing result via PackingService.

### Error Handling:

1. Catches any exceptions during the process.
2. Logs the error and stores the result with the error data.

### Response Generation:

1. Creates an HTTP response via the ResponseHandler:
   1. 200 status with the packed box data if successful.
   2. 500 status with error details on failure.

## Fallback logic

Implementation of `$ composer require latuconsinafr/3d-bin-packager`

Documentation is available in [package's GitHub README](https://github.com/latuconsinafr/3d-bin-packager/blob/main/README.md)
