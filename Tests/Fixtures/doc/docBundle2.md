## Note [/notes/{id}]
Note description

+ Parameters

    + id (required, string, `68a5sdf67`) ... The note ID

+ Model

    + Headers

            Content-Type: application/json
            X-Request-ID: f72fc914
            X-Response-Time: 4ms

    + Body

            {
                "id": 1,
                "title": "Grocery list",
                "body": "Buy milk"
            }

### Get Note [GET]
Get a single note.

+ Response 200

    [Note][]

+ Response 404

    + Headers

            Content-Type: application/json
            X-Request-ID: f72fc914
            X-Response-Time: 4ms

    + Body

            {
                "error": "Note not found"
            }

### Update a Note [PUT]
Update a single note

+ Request

    + Headers

            Content-Type: application/json

    + Body

            {
                "title": "Grocery List (Safeway)"
            }

+ Response 200

    [Note][]

+ Response 404

    + Headers

            Content-Type: application/json
            X-Request-ID: f72fc914
            X-Response-Time: 4ms

    + Body

            {
                "error": "Note not found"
            }
