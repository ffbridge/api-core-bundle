### Get Notes [GET]
Get a list of notes.

+ Response 200

    [Note List][]

### Create New Note [POST]
Create a new note

+ Request

    + Headers

            Content-Type: application/json

    + Body

            {
                "title": "My new note",
                "body": "..."
            }

+ Response 201

+ Response 400

    + Headers

            Content-Type: application/json

    + Body

            {
                "error": "Invalid title"
            }
